<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Hooks;

use PrestoWorld\Gutenberg\Contracts\HookManagerInterface;

/**
 * In-process Observer used by the render pipeline.
 *
 * Kept deliberately separate from the host's legacy hook bridge so that a
 * throwing render filter propagates loudly instead of being swallowed.
 * When the host exposes global WordPress hooks, they are chained AFTER the
 * internal observers (see GlobalHooksAdapter).
 */
final class InternalHooks implements HookManagerInterface
{
    /** @var array<string, array<int, list<array{callback: callable, accepted_args: int}>>> */
    private array $filters = [];

    /** @var array<string, array<int, list<array{callback: callable, accepted_args: int}>>> */
    private array $actions = [];

    public function addFilter(string $tag, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        $this->filters[$tag][$priority][] = ['callback' => $callback, 'accepted_args' => $acceptedArgs];
    }

    public function addAction(string $tag, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        $this->actions[$tag][$priority][] = ['callback' => $callback, 'accepted_args' => $acceptedArgs];
    }

    public function applyFilters(string $tag, mixed $value, mixed ...$args): mixed
    {
        foreach ($this->sorted($this->filters[$tag] ?? []) as $hook) {
            $callArgs = array_slice(array_merge([$value], $args), 0, $hook['accepted_args']);
            $value = ($hook['callback'])(...$callArgs);
        }

        return $value;
    }

    public function doAction(string $tag, mixed ...$args): void
    {
        foreach ($this->sorted($this->actions[$tag] ?? []) as $hook) {
            ($hook['callback'])(...array_slice($args, 0, $hook['accepted_args']));
        }
    }

    public function hasFilter(string $tag, ?callable $callback = null): int|false
    {
        if (!isset($this->filters[$tag])) {
            return false;
        }

        if ($callback === null) {
            return array_sum(array_map('count', $this->filters[$tag])) ?: false;
        }

        foreach ($this->filters[$tag] as $priority => $hooks) {
            foreach ($hooks as $hook) {
                if ($hook['callback'] === $callback) {
                    return $priority;
                }
            }
        }

        return false;
    }

    public function removeAll(string $tag): void
    {
        unset($this->filters[$tag], $this->actions[$tag]);
    }

    /**
     * @param array<int, list<array{callback: callable, accepted_args: int}>> $hooksByPriority
     * @return list<array{callback: callable, accepted_args: int}>
     */
    private function sorted(array $hooksByPriority): array
    {
        ksort($hooksByPriority);

        $flat = [];
        foreach ($hooksByPriority as $hooks) {
            foreach ($hooks as $hook) {
                $flat[] = $hook;
            }
        }

        return $flat;
    }
}
