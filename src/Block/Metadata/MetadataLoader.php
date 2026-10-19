<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Block\Metadata;

use PrestoWorld\Gutenberg\Block\BlockName;

/**
 * Loads block.json files from the package's metadata mirror directory.
 */
final class MetadataLoader
{
    public function __construct(
        private readonly string $metadataDir,
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function load(string $blockName): ?array
    {
        $name = BlockName::from($blockName);

        if (!$name->isCore()) {
            return null;
        }

        $file = rtrim($this->metadataDir, '/') . '/' . $name->slug . '/block.json';

        if (!is_file($file)) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents($file), true);

        return is_array($decoded) ? $decoded : null;
    }

    public function dir(): string
    {
        return $this->metadataDir;
    }
}
