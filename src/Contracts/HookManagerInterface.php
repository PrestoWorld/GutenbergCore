<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Contracts;

/**
 * Observer-pattern hook surface used by the render pipeline.
 *
 * The default implementation adapts the host's global WordPress hooks
 * (add_filter/apply_filters) so plugins and themes can keep using them.
 */
interface HookManagerInterface
{
    /**
     * @param callable $callback
     */
    public function addFilter(string $tag, callable $callback, int $priority = 10, int $acceptedArgs = 1): void;

    /**
     * @param callable $callback
     */
    public function addAction(string $tag, callable $callback, int $priority = 10, int $acceptedArgs = 1): void;

    public function applyFilters(string $tag, mixed $value, mixed ...$args): mixed;

    public function doAction(string $tag, mixed ...$args): void;

    public function hasFilter(string $tag, ?callable $callback = null): int|false;
}
