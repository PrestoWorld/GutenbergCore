<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Patterns;

/**
 * Immutable block pattern value object.
 */
final class Pattern
{
    /**
     * @param list<string> $categories
     * @param list<string> $blockTypes
     * @param list<string> $keywords
     * @param list<string> $templateTypes
     * @param list<string> $postTypes
     */
    public function __construct(
        public readonly string $slug,
        public readonly string $title,
        public readonly string $content,
        public readonly array $categories = [],
        public readonly array $blockTypes = [],
        public readonly ?int $viewportWidth = null,
        public readonly bool $inserter = true,
        public readonly ?string $description = null,
        public readonly array $keywords = [],
        public readonly array $templateTypes = [],
        public readonly array $postTypes = [],
        public readonly ?string $sourceFile = null,
    ) {
    }
}
