<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Block;

use PrestoWorld\Gutenberg\Exceptions\GutenbergException;

/**
 * Value object for a block name — normalizes `group` → `core/group`.
 */
final class BlockName
{
    public const DEFAULT_NAMESPACE = 'core';

    private function __construct(
        public readonly string $namespace,
        public readonly string $slug,
    ) {
    }

    public static function from(string $name): self
    {
        $name = trim($name);

        if ($name === '') {
            throw new GutenbergException('Block name cannot be empty.');
        }

        if (str_contains($name, '/')) {
            [$namespace, $slug] = explode('/', $name, 2);
        } else {
            $namespace = self::DEFAULT_NAMESPACE;
            $slug = $name;
        }

        return new self($namespace, $slug);
    }

    public function full(): string
    {
        return $this->namespace . '/' . $this->slug;
    }

    public function isCore(): bool
    {
        return $this->namespace === self::DEFAULT_NAMESPACE;
    }

    public function __toString(): string
    {
        return $this->full();
    }
}
