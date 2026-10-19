<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Supports\Support;

use PrestoWorld\Gutenberg\Block\BlockInstance;
use PrestoWorld\Gutenberg\Supports\SupportInterface;

/**
 * Layout support — constrained/flow/flex/grid utility classes.
 */
final class LayoutSupport implements SupportInterface
{
    public function classes(BlockInstance $block): array
    {
        if (($block->blockType?->supports['layout'] ?? false) === false) {
            return [];
        }

        $layout = is_array($block->attributes['layout'] ?? null) ? $block->attributes['layout'] : [];
        $type = isset($layout['type']) && is_string($layout['type']) ? $layout['type'] : '';

        if ($type === '') {
            return [];
        }

        $classes = ['is-layout-' . $type];

        if (isset($layout['justifyContent']) && is_string($layout['justifyContent'])) {
            $classes[] = 'items-justified-' . str_replace('_', '-', $layout['justifyContent']);
        }

        if (($layout['orientation'] ?? null) === 'vertical') {
            $classes[] = 'is-vertical';
        }

        if (isset($layout['flexWrap']) && $layout['flexWrap'] === 'nowrap') {
            $classes[] = 'is-nowrap';
        }

        if ($layout['inherit'] ?? false) {
            $classes[] = 'wp-block-post-template-inherit-inner-blocks';
        }

        return $classes;
    }

    public function styles(BlockInstance $block): array
    {
        return [];
    }
}
