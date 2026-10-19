<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Patterns;

/**
 * Pattern category value object.
 */
final class PatternCategory
{
    public function __construct(
        public readonly string $slug,
        public readonly string $label,
        public readonly ?string $description = null,
    ) {
    }
}
