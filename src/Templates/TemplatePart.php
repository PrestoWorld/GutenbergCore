<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Templates;

/**
 * A block template part (parts/*.html).
 */
final class TemplatePart
{
    public function __construct(
        public readonly string $slug,
        public readonly string $content,
        public readonly ?string $title = null,
        public readonly ?string $area = null,
        public readonly ?string $sourceFile = null,
    ) {
    }
}
