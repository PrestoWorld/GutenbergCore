<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\ThemeJson\Css;

use PrestoWorld\Gutenberg\ThemeJson\ThemeJson;

/**
 * Compiles a merged ThemeJson document into a CSS stylesheet (Builder).
 *
 * Output mirrors what wp_theme_json_get_stylesheet produces for a block
 * theme: custom properties, layout engine, preset utility classes, root /
 * element / per-block styles and file-based block style variations.
 */
final class CssCompiler
{
    private const ELEMENT_SELECTORS = [
        'link' => 'a:where(:not(.wp-element-button))',
        'button' => '.wp-element-button, .wp-block-button__link',
        'heading' => '.wp-block-heading',
        'caption' => 'figcaption',
        'h1' => 'h1',
        'h2' => 'h2',
        'h3' => 'h3',
        'h4' => 'h4',
        'h5' => 'h5',
        'h6' => 'h6',
    ];

    public function __construct(
        private readonly ValueResolver $resolver = new ValueResolver(),
    ) {
    }

    public function compile(ThemeJson $json, array $blockStyleVariations = []): string
    {
        $css = '';
        $css .= $this->customProperties($json);
        $css .= $this->layoutEngine($json);
        $css .= $this->presetUtilities($json);
        $css .= $this->rootStyles($json);
        $css .= $this->elementStyles($json);
        $css .= $this->blockStyles($json);
        $css .= $this->fileBlockStyleVariations($blockStyleVariations);

        return $css;
    }

    private function customProperties(ThemeJson $json): string
    {
        $settings = $json->get('settings', []);
        if (!is_array($settings)) {
            $settings = [];
        }

        $props = [];
        $fontFaces = '';

        // Colors.
        foreach ($this->list($settings['color']['palette'] ?? []) as $color) {
            if (isset($color['slug'], $color['color'])) {
                $props['--wp--preset--color--' . $color['slug']] = $this->resolver->resolve((string) $color['color']);
            }
        }

        foreach ($this->list($settings['color']['gradients'] ?? []) as $gradient) {
            if (isset($gradient['slug'], $gradient['gradient'])) {
                $props['--wp--preset--gradient--' . $gradient['slug']] = $this->resolver->resolve((string) $gradient['gradient']);
            }
        }

        // Font families (+ font faces).
        foreach ($this->list($settings['typography']['fontFamilies'] ?? []) as $family) {
            if (isset($family['slug'], $family['fontFamily'])) {
                $props['--wp--preset--font-family--' . $family['slug']] = $this->resolver->resolve((string) $family['fontFamily']);
            }

            foreach ($this->list($family['fontFace'] ?? []) as $face) {
                $fontFaces .= $this->fontFace($face);
            }
        }

        // Font sizes.
        foreach ($this->list($settings['typography']['fontSizes'] ?? []) as $size) {
            if (isset($size['slug'], $size['size'])) {
                $props['--wp--preset--font-size--' . $size['slug']] = $this->resolver->resolve((string) $size['size']);
            }
        }

        // Spacing sizes.
        foreach ($this->list($settings['spacing']['spacingSizes'] ?? []) as $size) {
            if (isset($size['slug'], $size['size'])) {
                $props['--wp--preset--spacing--' . $size['slug']] = $this->resolver->resolve((string) $size['size']);
            }
        }

        // Layout sizes.
        $contentSize = $settings['layout']['contentSize'] ?? null;
        $wideSize = $settings['layout']['wideSize'] ?? null;

        if (is_string($contentSize) && $contentSize !== '') {
            $props['--wp--style--global--content-size'] = $this->resolver->resolve($contentSize);
        }

        if (is_string($wideSize) && $wideSize !== '') {
            $props['--wp--style--global--wide-size'] = $this->resolver->resolve($wideSize);
        }

        // Custom properties.
        $this->flattenCustom($settings['custom'] ?? [], '--wp--custom', $props);

        // Root block gap.
        $blockGap = $json->get('styles.spacing.blockGap');
        if (is_string($blockGap) || is_numeric($blockGap)) {
            $props['--wp--style--block-gap'] = $this->resolver->resolve((string) $blockGap);
        }

        if ($props === [] && $fontFaces === '') {
            return '';
        }

        $out = ":root {\n";

        foreach ($props as $name => $value) {
            $out .= sprintf("  %s: %s;\n", $name, $value);
        }

        $out .= "}\n\n";

        return $out . $fontFaces;
    }

    /**
     * @param array<string, mixed> $face
     */
    private function fontFace(array $face): string
    {
        $family = isset($face['fontFamily']) ? (string) $face['fontFamily'] : null;

        if ($family === null) {
            return '';
        }

        $src = $face['src'] ?? null;
        $sources = [];

        foreach ($this->list(is_string($src) ? [$src] : $src) as $entry) {
            if (is_string($entry)) {
                $sources[] = 'url(' . $this->resolver->resolve($entry) . ')';
            } elseif (is_array($entry) && isset($entry['url'])) {
                $sources[] = 'url(' . $this->resolver->resolve((string) $entry['url']) . ')';
            }
        }

        if ($sources === []) {
            return '';
        }

        $lines = ["@font-face {\n  font-family: {$family};"];
        $lines[] = '  src: ' . implode(', ', $sources) . ';';

        foreach (['fontStyle', 'fontWeight', 'fontStretch', 'fontDisplay', 'ascentOverride', 'descentOverride', 'lineGapOverride'] as $key) {
            if (isset($face[$key]) && is_scalar($face[$key])) {
                $cssKey = $this->toKebab($key);
                $lines[] = sprintf('  %s: %s;', $cssKey, $face[$key]);
            }
        }

        $lines[] = "}\n";

        return implode("\n", $lines) . "\n";
    }

    private function layoutEngine(ThemeJson $json): string
    {
        $css = "/* Layout engine */\n";
        $css .= ".is-layout-constrained > :where(:not(.alignleft):not(.alignright):not(.alignfull)) { max-width: var(--wp--style--global--content-size); margin-left: auto !important; margin-right: auto !important; }\n";
        $css .= ".is-layout-constrained > .alignwide { max-width: var(--wp--style--global--wide-size); }\n";
        $css .= ".is-layout-constrained .alignfull { max-width: none; }\n";
        $css .= ".is-layout-flow > :where(:not(.alignleft):not(.alignright):not(.alignfull)) { margin-left: auto; margin-right: auto; }\n";
        $css .= ".has-global-padding { padding-left: var(--wp--style--global--padding-left); padding-right: var(--wp--style--global--padding-right); }\n";
        $css .= ".is-layout-flex { display: flex; gap: var(--wp--style--block-gap, 1.25rem); }\n";
        $css .= ".is-layout-grid { display: grid; gap: var(--wp--style--block-gap, 1.25rem); }\n";
        $css .= ".is-vertical { flex-direction: column; }\n";
        $css .= ".items-justified-center { justify-content: center; }\n";
        $css .= ".items-justified-right { justify-content: flex-end; }\n";
        $css .= ".items-justified-space-between { justify-content: space-between; }\n";
        $css .= ".items-justified-left { justify-content: flex-start; }\n\n";

        if ($json->get('settings.useRootPaddingAwareAlignments', false)) {
            $padding = $json->get('styles.spacing.padding', []);
            $left = is_array($padding) ? ($padding['left'] ?? null) : null;
            $right = is_array($padding) ? ($padding['right'] ?? null) : null;

            $css .= ':root { --wp--style--global--padding-left: ' . $this->resolver->resolve((string) ($left ?? 'var(--wp--preset--spacing--50)')) . ';';
            $css .= ' --wp--style--global--padding-right: ' . $this->resolver->resolve((string) ($right ?? 'var(--wp--preset--spacing--50)')) . "; }\n";
            $css .= ".wp-site-blocks { padding-left: var(--wp--style--global--padding-left); padding-right: var(--wp--style--global--padding-right); }\n";
            $css .= ".wp-site-blocks > .alignfull { margin-left: calc(var(--wp--style--global--padding-left) * -1); margin-right: calc(var(--wp--style--global--padding-right) * -1); }\n\n";
        }

        return $css;
    }

    private function presetUtilities(ThemeJson $json): string
    {
        $css = "/* Preset utility classes */\n";

        foreach ($this->list($json->get('settings.color.palette', [])) as $color) {
            if (!isset($color['slug'])) {
                continue;
            }

            $slug = $color['slug'];
            $css .= ".has-{$slug}-color { color: var(--wp--preset--color--{$slug}) !important; }\n";
            $css .= ".has-{$slug}-background-color { background-color: var(--wp--preset--color--{$slug}) !important; }\n";
        }

        foreach ($this->list($json->get('settings.color.gradients', [])) as $gradient) {
            if (!isset($gradient['slug'])) {
                continue;
            }

            $slug = $gradient['slug'];
            $css .= ".has-{$slug}-gradient-background { background: var(--wp--preset--gradient--{$slug}) !important; }\n";
        }

        foreach ($this->list($json->get('settings.typography.fontSizes', [])) as $size) {
            if (!isset($size['slug'])) {
                continue;
            }

            $slug = $size['slug'];
            $css .= ".has-{$slug}-font-size { font-size: var(--wp--preset--font-size--{$slug}) !important; }\n";
        }

        foreach ($this->list($json->get('settings.typography.fontFamilies', [])) as $family) {
            if (!isset($family['slug'])) {
                continue;
            }

            $slug = $family['slug'];
            $css .= ".has-{$slug}-font-family { font-family: var(--wp--preset--font-family--{$slug}) !important; }\n";
        }

        foreach ($this->list($json->get('settings.spacing.spacingSizes', [])) as $size) {
            if (!isset($size['slug'])) {
                continue;
            }

            $slug = $size['slug'];
            $css .= ".has-{spacing-{$slug}} { --wp--preset--spacing--{$slug}: var(--wp--preset--spacing--{$slug}); }\n";
        }

        return $css . "\n";
    }

    private function rootStyles(ThemeJson $json): string
    {
        $styles = $json->get('styles', []);

        if (!is_array($styles)) {
            return '';
        }

        $declarations = $this->styleDeclarations($styles);

        if ($declarations === []) {
            return '';
        }

        return "body {\n" . $this->declarationsBlock($declarations) . "}\n\n";
    }

    private function elementStyles(ThemeJson $json): string
    {
        $elements = $json->get('styles.elements', []);

        if (!is_array($elements)) {
            return '';
        }

        $css = '';

        foreach ($elements as $element => $styles) {
            if (!is_string($element) || !is_array($styles)) {
                continue;
            }

            $selector = self::ELEMENT_SELECTORS[$element] ?? null;

            if ($selector === null) {
                continue;
            }

            $declarations = $this->styleDeclarations($styles);

            if ($declarations === []) {
                continue;
            }

            $css .= $selector . " {\n" . $this->declarationsBlock($declarations) . "}\n";
        }

        return $css === '' ? '' : $css . "\n";
    }

    private function blockStyles(ThemeJson $json): string
    {
        $blocks = $json->get('styles.blocks', []);

        if (!is_array($blocks)) {
            return '';
        }

        $css = '';

        foreach ($blocks as $blockName => $styles) {
            if (!is_string($blockName) || !is_array($styles)) {
                continue;
            }

            $selector = $this->blockSelector($blockName);
            $declarations = $this->styleDeclarations($styles);

            if ($declarations !== []) {
                $css .= $selector . " {\n" . $this->declarationsBlock($declarations) . "}\n";
            }

            $variations = is_array($styles['variations'] ?? null) ? $styles['variations'] : [];

            foreach ($variations as $variationName => $variationStyles) {
                if (!is_string($variationName) || !is_array($variationStyles)) {
                    continue;
                }

                $variationDeclarations = $this->styleDeclarations($variationStyles);

                if ($variationDeclarations === []) {
                    continue;
                }

                $css .= $selector . '.is-style-' . $variationName . " {\n" . $this->declarationsBlock($variationDeclarations) . "}\n";
            }
        }

        return $css === '' ? '' : $css . "\n";
    }

    /**
     * @param list<array<string, mixed>> $variations
     */
    private function fileBlockStyleVariations(array $variations): string
    {
        $css = '';

        foreach ($variations as $variation) {
            $slug = isset($variation['slug']) ? (string) $variation['slug'] : null;

            if ($slug === null) {
                continue;
            }

            $declarations = $this->styleDeclarations(is_array($variation['styles'] ?? null) ? $variation['styles'] : []);

            if ($declarations === []) {
                continue;
            }

            foreach ($this->list($variation['blockTypes'] ?? []) as $blockName) {
                if (!is_string($blockName)) {
                    continue;
                }

                $css .= $this->blockSelector($blockName) . '.is-style-' . $slug . " {\n"
                    . $this->declarationsBlock($declarations) . "}\n";
            }
        }

        return $css === '' ? '' : "/* File-based block style variations */\n" . $css . "\n";
    }

    private function blockSelector(string $blockName): string
    {
        if ($blockName === 'core/button') {
            return '.wp-element-button, .wp-block-button__link';
        }

        $slug = str_contains($blockName, '/') ? explode('/', $blockName, 2)[1] : $blockName;

        return '.wp-block-' . str_replace('_', '-', $slug);
    }

    /**
     * Flatten a theme.json style element into CSS declarations.
     *
     * @param array<string, mixed> $styles
     * @return list<string>
     */
    private function styleDeclarations(array $styles): array
    {
        $declarations = [];
        $color = is_array($styles['color'] ?? null) ? $styles['color'] : [];
        $typography = is_array($styles['typography'] ?? null) ? $styles['typography'] : [];
        $spacing = is_array($styles['spacing'] ?? null) ? $styles['spacing'] : [];
        $border = is_array($styles['border'] ?? null) ? $styles['border'] : [];

        if (isset($color['text']) && is_scalar($color['text'])) {
            $declarations[] = 'color: ' . $this->resolver->resolve((string) $color['text']);
        }

        if (isset($color['background']) && is_scalar($color['background'])) {
            $declarations[] = 'background-color: ' . $this->resolver->resolve((string) $color['background']);
        }

        if (isset($color['gradient']) && is_scalar($color['gradient'])) {
            $declarations[] = 'background: ' . $this->resolver->resolve((string) $color['gradient']);
        }

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
            if (isset($typography[$key]) && is_scalar($typography[$key])) {
                $declarations[] = $property . ': ' . $this->resolver->resolve((string) $typography[$key]);
            }
        }

        if (isset($spacing['blockGap']) && is_scalar($spacing['blockGap'])) {
            $declarations[] = 'gap: ' . $this->resolver->resolve((string) $spacing['blockGap']);
        }

        if (is_array($spacing['padding'] ?? null)) {
            foreach (['top', 'right', 'bottom', 'left'] as $side) {
                if (isset($spacing['padding'][$side]) && is_scalar($spacing['padding'][$side])) {
                    $declarations[] = 'padding-' . $side . ': ' . $this->resolver->resolve((string) $spacing['padding'][$side]);
                }
            }
        }

        if (is_array($spacing['margin'] ?? null)) {
            foreach (['top', 'right', 'bottom', 'left'] as $side) {
                if (isset($spacing['margin'][$side]) && is_scalar($spacing['margin'][$side])) {
                    $declarations[] = 'margin-' . $side . ': ' . $this->resolver->resolve((string) $spacing['margin'][$side]);
                }
            }
        }

        if (isset($border['radius']) && is_scalar($border['radius'])) {
            $declarations[] = 'border-radius: ' . $this->resolver->resolve((string) $border['radius']);
        }

        if (isset($border['color']) && is_scalar($border['color'])) {
            $declarations[] = 'border-color: ' . $this->resolver->resolve((string) $border['color']);
        }

        if (isset($border['style']) && is_scalar($border['style']) && isset($border['width']) && is_scalar($border['width'])) {
            $declarations[] = sprintf(
                'border: %s %s %s',
                $this->resolver->resolve((string) $border['width']),
                $border['style'],
                isset($border['color']) && is_scalar($border['color']) ? $this->resolver->resolve((string) $border['color']) : 'currentcolor'
            );
        }

        if (isset($styles['css']) && is_string($styles['css']) && trim($styles['css']) !== '') {
            $declarations[] = trim($styles['css']);
        }

        return $declarations;
    }

    /**
     * @param list<string> $declarations
     */
    private function declarationsBlock(array $declarations): string
    {
        $block = '';

        foreach ($declarations as $declaration) {
            $block .= '  ' . $declaration . ";\n";
        }

        return $block;
    }

    /**
     * @param array<string, mixed> $custom
     * @param array<string, string> $props
     */
    private function flattenCustom(array $custom, string $prefix, array &$props): void
    {
        foreach ($custom as $key => $value) {
            if (is_array($value)) {
                $this->flattenCustom($value, $prefix . '--' . $key, $props);
                continue;
            }

            if (is_scalar($value)) {
                $props[$prefix . '--' . $key] = $this->resolver->resolve((string) $value);
            }
        }
    }

    private function toKebab(string $key): string
    {
        return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '-$0', $key));
    }

    /**
     * @param mixed $value
     * @return list<array<string, mixed>>
     */
    private function list(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, 'is_array'));
    }
}
