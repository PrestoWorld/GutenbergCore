<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Parser\Node;

/**
 * A single node produced by the block serializer.
 */
interface ParsedNodeInterface
{
    public function isBlock(): bool;

    /**
     * WordPress-compatible array shape:
     * [blockName, attrs, innerBlocks, innerHTML, innerContent]
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
