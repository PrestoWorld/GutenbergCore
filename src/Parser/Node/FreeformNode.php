<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Parser\Node;

/**
 * Raw HTML that sits between (or outside) block delimiters.
 */
final class FreeformNode implements ParsedNodeInterface
{
    public function __construct(
        public readonly string $innerHTML,
    ) {
    }

    public function isBlock(): bool
    {
        return false;
    }

    public function toArray(): array
    {
        return [
            'blockName'    => null,
            'attrs'        => [],
            'innerBlocks'  => [],
            'innerHTML'    => $this->innerHTML,
            'innerContent' => [$this->innerHTML],
        ];
    }
}
