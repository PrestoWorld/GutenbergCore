<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Parser;

use PrestoWorld\Gutenberg\Parser\Node\BlockNode;

/**
 * A partially-parsed block sitting on the serializer stack.
 */
final class Frame
{
    public function __construct(
        public readonly BlockNode $block,
        public readonly int $tokenStart,
        public readonly int $tokenLength,
        public int $prevOffset,
        public readonly ?int $leadingHtmlStart = null,
    ) {
    }
}
