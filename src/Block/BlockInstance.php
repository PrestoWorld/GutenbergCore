<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Block;

use PrestoWorld\Gutenberg\Block\Type\BlockTypeRegistry;
use PrestoWorld\Gutenberg\Exceptions\GutenbergException;
use PrestoWorld\Gutenberg\Rendering\BlockRenderer;

/**
 * A live block instance bound to its metadata, attributes and context.
 *
 * Mirrors WP_Block: owns the parsed payload, resolves context inheritance
 * and delegates rendering to the pipeline (BlockRenderer).
 */
final class BlockInstance
{
    public ?BlockType $blockType;

    /** @var array<string, mixed> */
    public array $attributes;

    /** @var array<string, mixed> context provided *to* this block */
    public array $context = [];

    /** @var array<string, mixed> context available *from* ancestry */
    public array $availableContext;

    /** @var list<BlockInstance> */
    public array $innerBlocks = [];

    /** @var list<string|null> */
    public array $innerContent = [];

    public ?BlockInstance $parent;

    private ?BlockTypeRegistry $registry;

    /**
     * @param array<string, mixed> $parsedBlock WP-shaped parsed block array
     * @param array<string, mixed> $availableContext
     */
    public function __construct(
        public array $parsedBlock,
        array $availableContext = [],
        private ?BlockRenderer $renderer = null,
        ?BlockTypeRegistry $registry = null,
        ?BlockInstance $parent = null,
    ) {
        $this->parent = $parent;
        $this->availableContext = $availableContext;

        $name = isset($parsedBlock['blockName']) && is_string($parsedBlock['blockName'])
            ? $parsedBlock['blockName']
            : '';

        $registry ??= $renderer?->blockTypes();
        $this->registry = $registry;
        $this->blockType = $name !== '' && $registry !== null ? $registry->get($name) : null;

        $rawAttributes = is_array($parsedBlock['attrs'] ?? null) ? $parsedBlock['attrs'] : [];
        $this->attributes = $this->blockType !== null
            ? $this->blockType->parseAttributes($rawAttributes)
            : $rawAttributes;

        $this->resolveContext();

        $this->buildInnerBlocks();
    }

    public function name(): string
    {
        return is_string($this->parsedBlock['blockName'] ?? null) ? $this->parsedBlock['blockName'] : '';
    }

    public function setRenderer(BlockRenderer $renderer): void
    {
        $this->renderer = $renderer;
    }

    public function renderer(): BlockRenderer
    {
        if ($this->renderer === null) {
            throw new GutenbergException(
                sprintf('BlockInstance "%s" has no renderer attached.', $this->name())
            );
        }

        return $this->renderer;
    }

    /**
     * Render this block (and its inner blocks) to HTML.
     *
     * @param array{dynamic?: bool} $options
     */
    public function render(array $options = []): string
    {
        return $this->renderer()->renderBlock($this, $options);
    }

    /**
     * WordPress-compatible parsed array (e.g. for filters).
     *
     * @return array<string, mixed>
     */
    public function toParsedArray(): array
    {
        $parsed = $this->parsedBlock;
        $parsed['attrs'] = $this->attributes;
        $parsed['innerBlocks'] = array_map(
            static fn (BlockInstance $block): array => $block->toParsedArray(),
            $this->innerBlocks
        );

        return $parsed;
    }

    /**
     * Re-resolve context + attributes after the parsed payload was filtered.
     */
    public function refreshDependents(): void
    {
        $rawAttributes = is_array($this->parsedBlock['attrs'] ?? null) ? $this->parsedBlock['attrs'] : [];
        $this->attributes = $this->blockType !== null
            ? $this->blockType->parseAttributes($rawAttributes)
            : $rawAttributes;

        $this->resolveContext();
    }

    /**
     * Apply a context set produced by the `render_block_context` filter and
     * cascade it down the tree (port of WP_Block::refresh_context_dependents).
     *
     * @param array<string, mixed> $context
     */
    public function applyContext(array $context): void
    {
        $this->context = $context;
        $this->availableContext = array_merge($this->availableContext, $context);

        foreach ($this->innerBlocks as $child) {
            $child->recontextualize($this->availableContext);
        }
    }

    /**
     * Re-derive this block's context from an updated ancestor context.
     *
     * @param array<string, mixed> $availableContext
     */
    public function recontextualize(array $availableContext): void
    {
        $this->availableContext = array_merge($this->availableContext, $availableContext);
        $this->resolveContext();

        foreach ($this->innerBlocks as $child) {
            $child->recontextualize($this->availableContext);
        }
    }

    private function resolveContext(): void
    {
        $this->context = [];

        if ($this->blockType === null) {
            return;
        }

        foreach ($this->blockType->usesContext as $contextName) {
            if (array_key_exists($contextName, $this->availableContext)) {
                $this->context[$contextName] = $this->availableContext[$contextName];
            }
        }

        foreach ($this->blockType->providesContext as $contextName => $attributeName) {
            if (array_key_exists($attributeName, $this->attributes)) {
                $this->context[$contextName] = $this->attributes[$attributeName];
            }
        }

        $this->availableContext = array_merge($this->availableContext, $this->context);
    }

    private function buildInnerBlocks(): void
    {
        $rawInner = is_array($this->parsedBlock['innerBlocks'] ?? null)
            ? $this->parsedBlock['innerBlocks']
            : [];

        $this->innerBlocks = [];

        foreach ($rawInner as $child) {
            if (!is_array($child)) {
                continue;
            }

            $this->innerBlocks[] = new BlockInstance(
                $child,
                $this->availableContext,
                $this->renderer,
                $this->registry,
                $this,
            );
        }

        $this->innerContent = is_array($this->parsedBlock['innerContent'] ?? null)
            ? array_values($this->parsedBlock['innerContent'])
            : [is_string($this->parsedBlock['innerHTML'] ?? null) ? $this->parsedBlock['innerHTML'] : ''];
    }
}
