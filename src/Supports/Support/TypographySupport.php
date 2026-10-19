<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Supports\Support;

use PrestoWorld\Gutenberg\Block\BlockInstance;
use PrestoWorld\Gutenberg\Supports\SupportInterface;

/**
 * Typography support: preset font-size classes + inline style properties.
 */
final class TypographySupport implements SupportInterface
{
    public function classes(BlockInstance $block): array
    {
        if (!$this->enabled($block)) {
            return [];
        }

        $fontSize = $block->attributes['fontSize'] ?? null;

        if (!is_string($fontSize) || $fontSize === '') {
            return [];
        }

        return ['has-' . $fontSize . '-font-size'];
    }

    public function styles(BlockInstance $block): array
    {
        if (!$this->enabled($block)) {
            return [];
        }

        $styles = [];
        $style = is_array($block->attributes['style'] ?? null) ? $block->attributes['style'] : [];
        $typography = is_array($style['typography'] ?? null) ? $style['typography'] : [];

        $map = [
            'fontFamily' => 'font-family',
            'fontSize' => 'font-size',
            'fontWeight' => 'font-weight',
            'lineHeight' => 'line-height',
            'fontStyle' => 'font-style',
            'letterSpacing' => 'letter-spacing',
            'textTransform' => 'text-transform',
            'textDecoration' => 'text-decoration',
        ];

        foreach ($map as $key => $property) {
            if (!isset($typography[$key]) || !is_string($typography[$key]) && !is_numeric($typography[$key])) {
                continue;
            }

            // Preset font sizes are emitted as classes instead.
            if ($key === 'fontSize' && ($block->attributes['fontSize'] ?? null) !== null) {
                continue;
            }

            $styles[] = $property . ': ' . $this->resolve((string) $typography[$key]);
        }

        return $styles;
    }

    private function enabled(BlockInstance $block): bool
    {
        $support = $block->blockType?->supports['typography'] ?? false;

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
