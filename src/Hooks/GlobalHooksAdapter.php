<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Hooks;

use PrestoWorld\Gutenberg\Contracts\HookManagerInterface;

/**
 * Adapter over the host runtime's global WordPress hooks.
 *
 * Delegates exclusively to the global add_filter()/apply_filters() surface
 * when it is loaded (Observer pattern through the host), and transparently
 * falls back to the internal in-process observers otherwise — e.g. in bare
 * unit tests. Exactly one store is used at a time, so a callback is never
 * executed twice for the same hook.
 */
final class GlobalHooksAdapter implements HookManagerInterface
{
    public function __construct(
        private readonly InternalHooks $fallback = new InternalHooks(),
    ) {
    }

    public function addFilter(string $tag, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        if (function_exists('add_filter')) {
            \add_filter($tag, $callback, $priority, $acceptedArgs);

            return;
        }

        $this->fallback->addFilter($tag, $callback, $priority, $acceptedArgs);
    }

    public function addAction(string $tag, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        if (function_exists('add_action')) {
            \add_action($tag, $callback, $priority, $acceptedArgs);

            return;
        }

        $this->fallback->addAction($tag, $callback, $priority, $acceptedArgs);
    }

    public function applyFilters(string $tag, mixed $value, mixed ...$args): mixed
    {
        if (function_exists('apply_filters')) {
            return \apply_filters($tag, $value, ...$args);
        }

        return $this->fallback->applyFilters($tag, $value, ...$args);
    }

    public function doAction(string $tag, mixed ...$args): void
    {
        if (function_exists('do_action')) {
            \do_action($tag, ...$args);

            return;
        }

        $this->fallback->doAction($tag, ...$args);
    }

    public function hasFilter(string $tag, ?callable $callback = null): int|false
    {
        if (function_exists('has_filter')) {
            return \has_filter($tag, $callback);
        }

        return $this->fallback->hasFilter($tag, $callback);
    }
}
