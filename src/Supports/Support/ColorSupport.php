<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Supports\Support;

use PrestoWorld\Gutenberg\Block\BlockInstance;
use PrestoWorld\Gutenberg\Supports\SupportInterface;

/**
 * Color support: preset palette classes + inline styles for raw values.
 */
final class ColorSupport implements SupportInterface
{
    public function __construct(
        private readonly array $palette = [],
    ) {
    }

    public function classes(BlockInstance $block): array
    {
        if (!$this->enabled($block)) {
            return [];
        }

        $classes = [];

        $text = $block->attributes['textColor'] ?? null;
        if (is_string($text) && $text !== '') {
            $classes[] = 'has-' . $text . '-color';
        }

        $background = $block->attributes['backgroundColor'] ?? null;
        if (is_string($background) && $background !== '') {
            $classes[] = 'has-' . $background . '-background-color';
        }

        $gradient = $block->attributes['gradient'] ?? null;
        if (is_string($gradient) && $gradient !== '') {
            $classes[] = 'has-' . $gradient . '-gradient-background';
        }

        return $classes;
    }

    public function styles(BlockInstance $block): array
    {
        if (!$this->enabled($block)) {
            return [];
        }

        $styles = [];
        $style = is_array($block->attributes['style'] ?? null) ? $block->attributes['style'] : [];
        $color = is_array($style['color'] ?? null) ? $style['color'] : [];

        // Raw (non-preset) colors arrive through style.color.*.
        if (isset($color['text']) && ($block->attributes['textColor'] ?? null) === null) {
            $styles[] = 'color: ' . $this->resolve((string) $color['text']);
        }

        if (isset($color['background']) && ($block->attributes['backgroundColor'] ?? null) === null) {
            $styles[] = 'background-color: ' . $this->resolve((string) $color['background']);
        }

        if (isset($color['gradient']) && ($block->attributes['gradient'] ?? null) === null) {
            $styles[] = 'background: ' . $this->resolve((string) $color['gradient']);
        }

        return $styles;
    }

    private function enabled(BlockInstance $block): bool
    {
        $support = $block->blockType?->supports['color'] ?? false;

        return $support !== false;
    }

    private function resolve(string $value): string
    {
        return (string) preg_replace_callback(
            '#var:([^|]+)\|([^|]+)\|([^|]+)#',
            static fn (array $m): string => sprintf('var(--wp--preset--%s--%s)', $m[2], $m[3]),
            $value
        );
    }
}
