<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Supports\Support;

use PrestoWorld\Gutenberg\Block\BlockInstance;
use PrestoWorld\Gutenberg\Supports\SupportInterface;

/**
 * align attribute support — `align{value}` utility class.
 */
final class AlignSupport implements SupportInterface
{
    public function classes(BlockInstance $block): array
    {
        $enabled = $block->blockType?->supports['align'] ?? false;

        if ($enabled === false || $enabled === []) {
            return [];
        }

        $align = $block->attributes['align'] ?? null;

        if (!is_string($align) || $align === '') {
            return [];
        }

        return ['align' . $align];
    }

    public function styles(BlockInstance $block): array
    {
        return [];
    }
}
