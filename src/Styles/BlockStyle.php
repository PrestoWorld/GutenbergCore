<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Styles;

/**
 * A registered block style variation (name + label + optional CSS).
 */
final class BlockStyle
{
    public function __construct(
        public readonly string $name,
        public readonly string $label,
        public readonly ?string $inlineStyle = null,
        public readonly bool $isDefault = false,
    ) {
    }
}
