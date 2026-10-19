<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Parser\Node;

/**
 * A parsed block (mutable while the serializer is still building it).
 */
final class BlockNode implements ParsedNodeInterface
{
    /** @var list<BlockNode> */
    public array $innerBlocks = [];

    public string $innerHTML = '';

    /** @var list<string|null> */
    public array $innerContent = [];

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        public readonly string $name,
        public array $attributes = [],
    ) {
    }

    public function isBlock(): bool
    {
        return true;
    }

    public function toArray(): array
    {
        return [
            'blockName'    => $this->name,
            'attrs'        => $this->attributes,
            'innerBlocks'  => array_map(static fn (BlockNode $b): array => $b->toArray(), $this->innerBlocks),
            'innerHTML'    => $this->innerHTML,
            'innerContent' => $this->innerContent,
        ];
    }
}
