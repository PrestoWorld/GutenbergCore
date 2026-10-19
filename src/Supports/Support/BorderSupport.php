<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Supports\Support;

use PrestoWorld\Gutenberg\Block\BlockInstance;
use PrestoWorld\Gutenberg\Supports\SupportInterface;
use PrestoWorld\Gutenberg\ThemeJson\Css\ValueResolver;

/**
 * Border support: style.border.* → inline styles.
 */
final class BorderSupport implements SupportInterface
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
        if (($block->blockType?->supports['border'] ?? false) === false) {
            return [];
        }

        $style = is_array($block->attributes['style'] ?? null) ? $block->attributes['style'] : [];
        $border = is_array($style['border'] ?? null) ? $style['border'] : [];

        $styles = [];

        foreach (['color', 'style', 'width'] as $property) {
            if (isset($border[$property]) && is_scalar($border[$property])) {
                $styles[] = 'border-' . $property . ': ' . $this->resolver->resolve((string) $border[$property]);
            }
        }

        if (isset($border['radius'])) {
            $radius = $border['radius'];
            if (is_array($radius)) {
                foreach (['top-left', 'top-right', 'bottom-left', 'bottom-right'] as $corner) {
                    if (isset($radius[$corner]) && is_scalar($radius[$corner])) {
                        $styles[] = 'border-' . $corner . '-radius: ' . $this->resolver->resolve((string) $radius[$corner]);
                    }
                }
            } elseif (is_scalar($radius)) {
                $styles[] = 'border-radius: ' . $this->resolver->resolve((string) $radius);
            }
        }

        return $styles;
    }
}
