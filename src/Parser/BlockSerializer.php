<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Parser;

use PrestoWorld\Gutenberg\Cache\CacheBridge;
use PrestoWorld\Gutenberg\Contracts\ParserInterface;
use PrestoWorld\Gutenberg\Parser\Node\BlockNode;
use PrestoWorld\Gutenberg\Parser\Node\FreeformNode;
use PrestoWorld\Gutenberg\Parser\Node\ParsedNodeInterface;

/**
 * Block serialization parser (State pattern).
 *
 * Faithful port of the WordPress `WP_Block_Parser` state machine, rewritten
 * as an object with explicit token/step/frame collaborators. Best-effort on
 * malformed input — never throws for unclosed delimiters, mirrors core.
 */
final class BlockSerializer implements ParserInterface
{
    /**
     * Single tokenizer regexp — same trick as core: one expression catches
     * openers, closers and void blocks; PHP disambiguates afterwards.
     */
    private const TOKENIZER = '/<!--\s+(?P<closer>\/)?wp:(?P<namespace>[a-z][a-z0-9_-]*\/)?(?P<name>[a-z][a-z0-9_-]*)\s+(?P<attrs>{(?:(?:[^}]+|}+(?=})|(?!}\s+\/?-->).)*+)?}\s+)?(?P<void>\/)?-->/s';

    /** Parse-cache cap (entries per worker). */
    private const PARSE_CACHE_MAX = 256;

    /**
     * Parse cache — per worker, key = sha1(salt . content).
     *
     * Content-addressed nên self-invalidating: file đổi → hash đổi. Salt do
     * host cấp (dashboard Clear). Cached nodes an toàn để share: render chỉ
     * đọc qua BlockNode::toArray() (mảng con COW — ghi sẽ tách copy, node
     * gốc không đổi).
     *
     * @var array<string, list<ParsedNodeInterface>>
     */
    private static array $parseCache = [];

    private string $document = '';

    private int $offset = 0;

    /** @var list<ParsedNodeInterface> */
    private array $output = [];

    /** @var list<Frame> */
    private array $stack = [];

    private AttributeDecoder $attributes;

    public function __construct(?AttributeDecoder $attributes = null)
    {
        $this->attributes = $attributes ?? new AttributeDecoder();
    }

    public function parse(string $content): array
    {
        $bridge = CacheBridge::get();
        $useCache = $bridge !== null && $bridge->enabled('parse');
        $key = $useCache ? sha1($bridge->salt('parse') . $content) : '';

        if ($useCache && isset(self::$parseCache[$key])) {
            $bridge->stat('parse', 'hit');

            return self::$parseCache[$key];
        }

        $this->document = $content;
        $this->offset = 0;
        $this->output = [];
        $this->stack = [];

        while ($this->proceed()) {
            // Intentionally empty — the state machine drives itself.
        }

        if ($useCache) {
            if (count(self::$parseCache) >= self::PARSE_CACHE_MAX) {
                self::$parseCache = [];
                $bridge->stat('parse', 'evict');
            }

            self::$parseCache[$key] = $this->output;
            $bridge->stat('parse', 'miss');
        }

        return $this->output;
    }

    /**
     * Số entry parse cache trong worker hiện tại (dashboard stats).
     *
     * @return array{entries: int, cap: int}
     */
    public static function cacheStats(): array
    {
        return ['entries' => count(self::$parseCache), 'cap' => self::PARSE_CACHE_MAX];
    }

    /** Xoá cục bộ worker này (purge từ dashboard/API). */
    public static function flushLocalCache(): void
    {
        self::$parseCache = [];
    }

    private function proceed(): bool
    {
        $token = $this->nextToken();
        $stackDepth = count($this->stack);

        $leadingHtmlStart = $token->start > $this->offset ? $this->offset : null;

        switch ($token->type) {
            case Token::NO_MORE:
                if ($stackDepth === 0) {
                    $this->addFreeform();

                    return false;
                }

                if ($stackDepth === 1) {
                    $this->addBlockFromStack();

                    return false;
                }

                while ($this->stack !== []) {
                    $this->addBlockFromStack();
                }

                return false;

            case Token::VOID:
                $block = new BlockNode((string) $token->name, $token->attributes);

                if ($stackDepth === 0) {
                    if ($leadingHtmlStart !== null) {
                        $this->output[] = new FreeformNode(
                            substr($this->document, $leadingHtmlStart, $token->start - $leadingHtmlStart)
                        );
                    }

                    $this->output[] = $block;
                    $this->offset = $token->start + $token->length;

                    return true;
                }

                $this->addInnerBlock($block, $token->start, $token->length);
                $this->offset = $token->start + $token->length;

                return true;

            case Token::OPENER:
                $this->stack[] = new Frame(
                    new BlockNode((string) $token->name, $token->attributes),
                    $token->start,
                    $token->length,
                    $token->start + $token->length,
                    $leadingHtmlStart,
                );
                $this->offset = $token->start + $token->length;

                return true;

            case Token::CLOSER:
                if ($stackDepth === 0) {
                    $this->addFreeform();

                    return false;
                }

                if ($stackDepth === 1) {
                    $this->addBlockFromStack($token->start);
                    $this->offset = $token->start + $token->length;

                    return true;
                }

                /** @var Frame $stackTop */
                $stackTop = array_pop($this->stack);
                $html = substr($this->document, $stackTop->prevOffset, $token->start - $stackTop->prevOffset);
                $stackTop->block->innerHTML .= $html;
                $stackTop->block->innerContent[] = $html;
                $stackTop->prevOffset = $token->start + $token->length;

                $this->addInnerBlock(
                    $stackTop->block,
                    $stackTop->tokenStart,
                    $stackTop->tokenLength,
                    $token->start + $token->length
                );
                $this->offset = $token->start + $token->length;

                return true;

            default:
                $this->addFreeform();

                return false;
        }
    }

    private function nextToken(): Token
    {
        $matches = [];

        $hasMatch = preg_match(
            self::TOKENIZER,
            $this->document,
            $matches,
            PREG_OFFSET_CAPTURE,
            $this->offset
        );

        if ($hasMatch === false || $hasMatch === 0) {
            return new Token(Token::NO_MORE);
        }

        [$match, $startedAt] = $matches[0];
        $length = strlen($match);
        $isCloser = isset($matches['closer']) && $matches['closer'][1] !== -1;
        $isVoid = isset($matches['void']) && $matches['void'][1] !== -1;
        $namespace = isset($matches['namespace']) && $matches['namespace'][1] !== -1
            ? $matches['namespace'][0]
            : 'core/';
        $name = $namespace . $matches['name'][0];
        $hasAttrs = isset($matches['attrs']) && $matches['attrs'][1] !== -1;

        $attributes = $hasAttrs
            ? $this->attributes->decode($matches['attrs'][0])
            : [];

        if ($isVoid) {
            return new Token(Token::VOID, $name, $attributes, $startedAt, $length);
        }

        if ($isCloser) {
            return new Token(Token::CLOSER, $name, [], $startedAt, $length);
        }

        return new Token(Token::OPENER, $name, $attributes, $startedAt, $length);
    }

    private function addFreeform(?int $length = null): void
    {
        $length ??= strlen($this->document) - $this->offset;

        if ($length === 0) {
            return;
        }

        $this->output[] = new FreeformNode(substr($this->document, $this->offset, $length));
    }

    private function addInnerBlock(BlockNode $block, int $tokenStart, int $tokenLength, ?int $lastOffset = null): void
    {
        /** @var Frame $parent */
        $parent = $this->stack[array_key_last($this->stack)];
        $parent->block->innerBlocks[] = $block;

        $html = substr($this->document, $parent->prevOffset, $tokenStart - $parent->prevOffset);

        if ($html !== '') {
            $parent->block->innerHTML .= $html;
            $parent->block->innerContent[] = $html;
        }

        $parent->block->innerContent[] = null;
        $parent->prevOffset = $lastOffset ?? $tokenStart + $tokenLength;
    }

    private function addBlockFromStack(?int $endOffset = null): void
    {
        /** @var Frame $stackTop */
        $stackTop = array_pop($this->stack);
        $prevOffset = $stackTop->prevOffset;

        $html = $endOffset !== null
            ? substr($this->document, $prevOffset, $endOffset - $prevOffset)
            : substr($this->document, $prevOffset);

        if ($html !== '') {
            $stackTop->block->innerHTML .= $html;
            $stackTop->block->innerContent[] = $html;
        }

        if ($stackTop->leadingHtmlStart !== null) {
            $this->output[] = new FreeformNode(
                substr($this->document, $stackTop->leadingHtmlStart, $stackTop->tokenStart - $stackTop->leadingHtmlStart)
            );
        }

        $this->output[] = $stackTop->block;
    }
}
