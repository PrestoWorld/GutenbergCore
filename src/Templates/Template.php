<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Templates;

/**
 * A block template (templates/*.html).
 */
final class Template
{
    public function __construct(
        public readonly string $slug,
        public readonly string $content,
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?string $sourceFile = null,
    ) {
    }
}
