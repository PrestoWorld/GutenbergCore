<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Bindings;

use PrestoWorld\Gutenberg\Contracts\RegistryInterface;

/**
 * Registry of block-bindings sources (Registry pattern).
 */
final class BindingsRegistry implements RegistryInterface
{
    /** @var array<string, BindingSource> */
    private array $sources = [];

    public function register(string $name, mixed $definition): void
    {
        if ($definition instanceof BindingSource) {
            $this->sources[$name] = $definition;

            return;
        }

        if (is_array($definition)) {
            $this->sources[$name] = new BindingSource($name, $definition);

            return;
        }

        throw new \InvalidArgumentException(sprintf(
            'Block bindings source "%s" must be a BindingSource or settings array.',
            $name
        ));
    }

    public function has(string $name): bool
    {
        return isset($this->sources[$name]);
    }

    public function get(string $name): ?BindingSource
    {
        return $this->sources[$name] ?? null;
    }

    public function all(): array
    {
        return $this->sources;
    }

    public function unregister(string $name): bool
    {
        if (!isset($this->sources[$name])) {
            return false;
        }

        unset($this->sources[$name]);

        return true;
    }
}
