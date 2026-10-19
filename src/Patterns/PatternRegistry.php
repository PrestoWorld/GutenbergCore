<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Patterns;

use PrestoWorld\Gutenberg\Contracts\RegistryInterface;

/**
 * Registry of block patterns + pattern categories (Registry pattern).
 */
final class PatternRegistry implements RegistryInterface
{
    /** @var array<string, Pattern> */
    private array $patterns = [];

    /** @var array<string, PatternCategory> */
    private array $categories = [];

    public function register(string $name, mixed $definition): void
    {
        if ($definition instanceof Pattern) {
            $this->patterns[$definition->slug] = $definition;

            return;
        }

        if (is_array($definition)) {
            $this->patterns[$name] = new Pattern(
                slug: $name,
                title: (string) ($definition['title'] ?? $name),
                content: (string) ($definition['content'] ?? ''),
                categories: array_values(is_array($definition['categories'] ?? null) ? $definition['categories'] : []),
                blockTypes: array_values(is_array($definition['blockTypes'] ?? null) ? $definition['blockTypes'] : []),
                viewportWidth: isset($definition['viewportWidth']) && is_numeric($definition['viewportWidth'])
                    ? (int) $definition['viewportWidth']
                    : null,
                inserter: (bool) ($definition['inserter'] ?? true),
                description: isset($definition['description']) ? (string) $definition['description'] : null,
                keywords: array_values(is_array($definition['keywords'] ?? null) ? $definition['keywords'] : []),
                templateTypes: array_values(is_array($definition['templateTypes'] ?? null) ? $definition['templateTypes'] : []),
                postTypes: array_values(is_array($definition['postTypes'] ?? null) ? $definition['postTypes'] : []),
                sourceFile: isset($definition['sourceFile']) ? (string) $definition['sourceFile'] : null,
            );

            return;
        }

        throw new \InvalidArgumentException(sprintf('Pattern definition "%s" must be a Pattern or array.', $name));
    }

    public function registerCategory(string $slug, string $label, ?string $description = null): PatternCategory
    {
        $category = new PatternCategory($slug, $label, $description);
        $this->categories[$slug] = $category;

        return $category;
    }

    public function category(string $slug): ?PatternCategory
    {
        return $this->categories[$slug] ?? null;
    }

    /**
     * @return array<string, PatternCategory>
     */
    public function categories(): array
    {
        return $this->categories;
    }

    public function has(string $name): bool
    {
        return isset($this->patterns[$name]);
    }

    public function get(string $name): ?Pattern
    {
        return $this->patterns[$name] ?? null;
    }

    public function all(): array
    {
        return $this->patterns;
    }

    public function unregister(string $name): bool
    {
        if (!isset($this->patterns[$name])) {
            return false;
        }

        unset($this->patterns[$name]);

        return true;
    }
}
