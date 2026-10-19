<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Contracts;

/**
 * Generic keyed registry (Registry pattern).
 *
 * @template T
 */
interface RegistryInterface
{
    public function register(string $name, mixed $definition): void;

    public function has(string $name): bool;

    /**
     * @return T|null
     */
    public function get(string $name): mixed;

    /**
     * @return array<string, T>
     */
    public function all(): array;

    public function unregister(string $name): bool;
}
