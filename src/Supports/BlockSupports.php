<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Supports;

use PrestoWorld\Gutenberg\Block\BlockInstance;
use PrestoWorld\Gutenberg\Supports\Support\AlignSupport;
use PrestoWorld\Gutenberg\Supports\Support\BorderSupport;
use PrestoWorld\Gutenberg\Supports\Support\ColorSupport;
use PrestoWorld\Gutenberg\Supports\Support\CustomClassNameSupport;
use PrestoWorld\Gutenberg\Supports\Support\GeneratedClassNameSupport;
use PrestoWorld\Gutenberg\Supports\Support\LayoutSupport;
use PrestoWorld\Gutenberg\Supports\Support\SpacingSupport;
use PrestoWorld\Gutenberg\Supports\Support\TypographySupport;
use PrestoWorld\Gutenberg\ThemeJson\Css\ValueResolver;

/**
 * Chain of Responsibilities: runs every support over a block and merges
 * the resulting wrapper attributes.
 */
final class BlockSupports
{
    /** @var list<SupportInterface> */
    private array $supports;

    /**
     * @param list<SupportInterface>|null $supports
     */
    public function __construct(?array $supports = null)
    {
        $this->supports = $supports ?? self::defaults();
    }

    /**
     * @return list<SupportInterface>
     */
    public static function defaults(?ValueResolver $resolver = null): array
    {
        $resolver ??= new ValueResolver();

        return [
            new GeneratedClassNameSupport(),
            new CustomClassNameSupport(),
            new AlignSupport(),
            new ColorSupport(),
            new TypographySupport(),
            new SpacingSupport($resolver),
            new BorderSupport($resolver),
            new LayoutSupport(),
        ];
    }

    public function add(SupportInterface $support): void
    {
        $this->supports[] = $support;
    }

    /**
     * @return array{class: list<string>, style: list<string>}
     */
    public function collect(BlockInstance $block): array
    {
        $classes = [];
        $styles = [];

        foreach ($this->supports as $support) {
            foreach ($support->classes($block) as $class) {
                $classes[] = $class;
            }

            foreach ($support->styles($block) as $style) {
                $styles[] = $style;
            }
        }

        return [
            'class' => array_values(array_unique(array_filter($classes))),
            'style' => array_values(array_filter($styles)),
        ];
    }
}
