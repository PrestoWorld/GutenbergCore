<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Supports\Support;

use PrestoWorld\Gutenberg\Block\BlockInstance;
use PrestoWorld\Gutenberg\Block\BlockName;
use PrestoWorld\Gutenberg\Supports\SupportInterface;

/**
 * Adds the base `wp-block-{slug}` class — always on, mirrors core.
 */
final class GeneratedClassNameSupport implements SupportInterface
{
    public function classes(BlockInstance $block): array
    {
        $name = $block->name();

        if ($name === '') {
            return [];
        }

        $slug = BlockName::from($name)->slug;

        return ['wp-block-' . str_replace('_', '-', $slug)];
    }

    public function styles(BlockInstance $block): array
    {
        return [];
    }
}
