<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Supports\Support;

use PrestoWorld\Gutenberg\Block\BlockInstance;
use PrestoWorld\Gutenberg\Supports\SupportInterface;

/**
 * className attribute support.
 */
final class CustomClassNameSupport implements SupportInterface
{
    public function classes(BlockInstance $block): array
    {
        $enabled = $block->blockType?->supports['className'] ?? true;

        if ($enabled === false) {
            return [];
        }

        $className = $block->attributes['className'] ?? null;

        if (!is_string($className) || trim($className) === '') {
            return [];
        }

        return array_values(array_filter(explode(' ', preg_replace('/\s+/', ' ', trim($className)))));
    }

    public function styles(BlockInstance $block): array
    {
        return [];
    }
}
