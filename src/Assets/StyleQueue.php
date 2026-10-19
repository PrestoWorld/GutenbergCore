<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Assets;

/**
 * Dependency-aware style queue (Queue + topological ordering).
 */
final class StyleQueue
{
    /** @var array<string, StyleAsset> */
    private array $registered = [];

    /** @var list<string> */
    private array $queue = [];

    public function register(StyleAsset $asset): void
    {
        $this->registered[$asset->handle] = $asset;
    }

    public function enqueue(string $handle, string $src = '', array $deps = [], string|bool|null $version = null, string $media = 'all'): void
    {
        if (!isset($this->registered[$handle])) {
            $this->register(new StyleAsset($handle, $src, $deps, $version, $media));
        }

        if (!in_array($handle, $this->queue, true)) {
            $this->queue[] = $handle;
        }
    }

    public function addInline(string $handle, string $css): void
    {
        $asset = $this->registered[$handle] ?? new StyleAsset($handle);

        $this->registered[$handle] = new StyleAsset(
            $asset->handle,
            $asset->src,
            $asset->dependencies,
            $asset->version,
            $asset->media,
            trim($asset->inlineCss ?? '') . "\n" . trim($css),
        );

        if (!in_array($handle, $this->queue, true)) {
            $this->queue[] = $handle;
        }
    }

    /**
     * Enqueued assets in dependency order.
     *
     * @return list<StyleAsset>
     */
    public function ordered(): array
    {
        $ordered = [];
        $visited = [];

        foreach ($this->queue as $handle) {
            $this->visit($handle, $ordered, $visited);
        }

        return $ordered;
    }

    public function flush(): void
    {
        $this->queue = [];
    }

    /**
     * @param list<StyleAsset> $ordered
     * @param array<string, bool> $visited
     */
    private function visit(string $handle, array &$ordered, array &$visited): void
    {
        if (isset($visited[$handle])) {
            return;
        }

        $visited[$handle] = true;

        $asset = $this->registered[$handle] ?? null;

        if ($asset === null) {
            return;
        }

        foreach ($asset->dependencies as $dependency) {
            $this->visit($dependency, $ordered, $visited);
        }

        $ordered[] = $asset;
    }
}
