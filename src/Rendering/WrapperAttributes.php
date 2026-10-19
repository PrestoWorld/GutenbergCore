<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Rendering;

use PrestoWorld\Gutenberg\Block\BlockInstance;
use PrestoWorld\Gutenberg\Supports\BlockSupports;

/**
 * Builds the `class="..." style="..." ...` attribute string for a wrapper
 * element — port of get_block_wrapper_attributes().
 */
final class WrapperAttributes
{
    public function __construct(
        private readonly BlockSupports $supports,
    ) {
    }

    /**
     * @param array<string, string> $extra extra attributes (class/style merged, others appended)
     */
    public function build(BlockInstance $block, array $extra = []): string
    {
        $collected = $this->supports->collect($block);

        $classes = $collected['class'];
        $styles = $collected['style'];

        if (isset($extra['class']) && is_string($extra['class'])) {
            foreach (preg_split('/\s+/', trim($extra['class'])) ?: [] as $class) {
                if ($class !== '') {
                    $classes[] = $class;
                }
            }
        }

        if (isset($extra['style']) && is_string($extra['style'])) {
            foreach (explode(';', $extra['style']) as $declaration) {
                $declaration = trim($declaration);
                if ($declaration !== '') {
                    $styles[] = $declaration;
                }
            }
        }

        $attributes = [];

        $classes = array_values(array_unique(array_filter($classes)));
        if ($classes !== []) {
            $attributes['class'] = implode(' ', $classes);
        }

        $styles = array_map(
            static fn (string $s): string => rtrim(trim($s), ';'),
            array_values(array_unique(array_filter($styles)))
        );
        if ($styles !== []) {
            $attributes['style'] = implode(';', $styles) . ';';
        }

        foreach ($extra as $key => $value) {
            if ($key === 'class' || $key === 'style') {
                continue;
            }
            if ($value === '' || $value === null) {
                continue;
            }
            $attributes[$key] = (string) $value;
        }

        $out = '';
        foreach ($attributes as $key => $value) {
            $out .= ' ' . $key . '="' . htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"';
        }

        return $out;
    }
}
