<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Styles;

/**
 * Loads Global Styles variations (styles/*.json) and file-based block style
 * variations (nested json files under styles/ carrying `blockTypes`).
 */
final class StyleVariationLoader
{
    public function __construct(
        private readonly string $themeDir,
    ) {
    }

    /**
     * Top-level Global Styles variations — one level deep, `version` required.
     *
     * @return list<array<string, mixed>>
     */
    public function globalVariations(): array
    {
        $files = glob(rtrim($this->themeDir, '/') . '/styles/*.json') ?: [];
        $variations = [];

        foreach ($files as $file) {
            $data = $this->decode($file);

            if ($data === null || !isset($data['version'])) {
                continue;
            }

            $data['__file'] = $file;
            $variations[] = $data;
        }

        return $variations;
    }

    /**
     * Block style variations — any nested json under styles/ with `blockTypes`
     * (recursive), excluding the top-level global variations.
     *
     * @return list<array<string, mixed>>
     */
    public function blockStyleVariations(): array
    {
        $base = rtrim($this->themeDir, '/') . '/styles';
        $files = $this->recursiveJsonFiles($base);
        $variations = [];

        foreach ($files as $file) {
            // Skip top-level files — those are global variations.
            if (dirname($file) === $base) {
                continue;
            }

            $data = $this->decode($file);

            if ($data === null || !isset($data['blockTypes']) || !is_array($data['blockTypes'])) {
                continue;
            }

            $data['__file'] = $file;
            $variations[] = $data;
        }

        return $variations;
    }

    /**
     * @return list<string>
     */
    private function recursiveJsonFiles(string $dir): array
    {
        if (!is_dir($dir)) {
            return [];
        }

        $files = [];

        foreach (scandir($dir) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $dir . '/' . $entry;

            if (is_dir($path)) {
                $files = array_merge($files, $this->recursiveJsonFiles($path));
            } elseif (str_ends_with($entry, '.json')) {
                $files[] = $path;
            }
        }

        return $files;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decode(string $file): ?array
    {
        $data = json_decode((string) file_get_contents($file), true);

        return is_array($data) ? $data : null;
    }
}
