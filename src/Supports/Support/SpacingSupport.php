<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Supports\Support;

use PrestoWorld\Gutenberg\Block\BlockInstance;
use PrestoWorld\Gutenberg\Supports\SupportInterface;
use PrestoWorld\Gutenberg\ThemeJson\Css\ValueResolver;

/**
 * Spacing support: padding / margin / blockGap → inline styles.
 */
final class SpacingSupport implements SupportInterface
{
    public function __construct(
        private readonly ValueResolver $resolver = new ValueResolver(),
    ) {
    }

    public function classes(BlockInstance $block): array
    {
        return [];
    }

    public function styles(BlockInstance $block): array
    {
        if (($block->blockType?->supports['spacing'] ?? false) === false) {
            return [];
        }

        $style = is_array($block->attributes['style'] ?? null) ? $block->attributes['style'] : [];
        $spacing = is_array($style['spacing'] ?? null) ? $style['spacing'] : [];

        $styles = [];

        if (is_array($spacing['padding'] ?? null)) {
            foreach (['top', 'right', 'bottom', 'left', 'x', 'y'] as $side) {
                if (isset($spacing['padding'][$side]) && is_scalar($spacing['padding'][$side])) {
                    $styles[] = 'padding-' . $side . ': ' . $this->resolver->resolve((string) $spacing['padding'][$side]);
                }
            }
        }

        if (is_array($spacing['margin'] ?? null)) {
            foreach (['top', 'right', 'bottom', 'left', 'x', 'y'] as $side) {
                if (isset($spacing['margin'][$side]) && is_scalar($spacing['margin'][$side])) {
                    $styles[] = 'margin-' . $side . ': ' . $this->resolver->resolve((string) $spacing['margin'][$side]);
                }
            }
        }

        if (isset($spacing['blockGap']) && is_scalar($spacing['blockGap'])) {
            $styles[] = 'gap: ' . $this->resolver->resolve((string) $spacing['blockGap']);
        }

        return $styles;
    }
}
