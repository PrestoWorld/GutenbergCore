<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Kernel;

use PrestoWorld\Gutenberg\Block\Metadata\MetadataLoader;
use PrestoWorld\Gutenberg\Block\Type\BlockTypeRegistry;
use PrestoWorld\Gutenberg\Bindings\BindingsRegistry;
use PrestoWorld\Gutenberg\Bindings\BindingsProcessor;
use PrestoWorld\Gutenberg\Core\Blocks\Registrar;
use PrestoWorld\Gutenberg\Core\BaseStyles;
use PrestoWorld\Gutenberg\Compatibility\ThemeContext;
use PrestoWorld\Gutenberg\Contracts\HookManagerInterface;
use PrestoWorld\Gutenberg\Gutenberg;
use PrestoWorld\Gutenberg\Parser\BlockSerializer;
use PrestoWorld\Gutenberg\Patterns\Pattern;
use PrestoWorld\Gutenberg\Patterns\PatternRegistry;
use PrestoWorld\Gutenberg\Patterns\Source\ThemeFilePatternSource;
use PrestoWorld\Gutenberg\Rendering\BlockRenderer;
use PrestoWorld\Gutenberg\Rendering\RenderContext;
use PrestoWorld\Gutenberg\Templates\TemplateRepository;
use PrestoWorld\Gutenberg\Templates\Source\ThemeFileTemplateSource;
use PrestoWorld\Gutenberg\ThemeJson\Css\CssCompiler;
use PrestoWorld\Gutenberg\ThemeJson\ThemeJsonResolver;

/**
 * Composition root of the Gutenberg engine (Facade + wiring).
 *
 * One kernel per request: owns the block type registry, pattern registry,
 * template repository, bindings registry, theme.json CSS and the render
 * pipeline (BlockRenderer).
 */
final class Kernel
{
    private BlockTypeRegistry $blockTypes;

    private BlockSerializer $parser;

    private BlockRenderer $renderer;

    private PatternRegistry $patterns;

    private BindingsRegistry $bindings;

    private TemplateRepository $templates;

    private RenderContext $renderContext;

    private ?string $themeCss = null;

    /**
     * @param array<string, mixed> $services render-context services
     *        (post_repository, post_resolver, site_title, ...)
     */
    public function __construct(
        private readonly string $themeDir,
        private readonly array $services = [],
        private readonly ?HookManagerInterface $hooks = null,
    ) {
        $hooks ??= Gutenberg::hooks();

        $this->blockTypes = new BlockTypeRegistry();
        Registrar::register($this->blockTypes, new MetadataLoader(__DIR__ . '/../Core/metadata'));

        $this->bindings = new BindingsRegistry();

        foreach (ThemeContext::bindingsSources() as $sourceName => $settings) {
            $this->bindings->register($sourceName, $settings);
        }

        $this->parser = new BlockSerializer();
        $this->patterns = new PatternRegistry();
        $source = new ThemeFilePatternSource($this->themeDir);

        foreach ($source->discover() as $pattern) {
            if ($pattern instanceof Pattern) {
                $this->patterns->register($pattern->slug, $pattern);
            }
        }

        // Pattern categories registered by the theme (register_block_pattern_category).
        foreach (ThemeContext::patternCategories() as $slug => $settings) {
            $this->patterns->registerCategory(
                $slug,
                (string) ($settings['label'] ?? $slug),
                isset($settings['description']) ? (string) $settings['description'] : null,
            );
        }

        $this->templates = new TemplateRepository(new ThemeFileTemplateSource($this->themeDir));

        $this->renderContext = new RenderContext(array_merge([
            'block_parser' => $this->parser,
            'pattern_registry' => $this->patterns,
            'template_repository' => $this->templates,
            'bindings_registry' => $this->bindings,
            'site_title' => (string) (function_exists('get_option') ? \get_option('blogname') : '') ?: 'PrestoWorld',
            'site_tagline' => (string) (function_exists('get_option') ? \get_option('blogdescription') : '') ?: '',
            'site_url' => function_exists('home_url') ? (string) \home_url('/') : '/',
            'search_url' => function_exists('home_url') ? (string) \home_url('/search') : '/search',
        ], $services));

        $this->renderer = new BlockRenderer(
            $this->blockTypes,
            $hooks,
            new BindingsProcessor($this->bindings),
            $this->renderContext,
        );
    }

    public function themeDir(): string
    {
        return $this->themeDir;
    }

    public function blockTypes(): BlockTypeRegistry
    {
        return $this->blockTypes;
    }

    public function parser(): BlockSerializer
    {
        return $this->parser;
    }

    public function renderer(): BlockRenderer
    {
        return $this->renderer;
    }

    public function patterns(): PatternRegistry
    {
        return $this->patterns;
    }

    public function bindings(): BindingsRegistry
    {
        return $this->bindings;
    }

    public function templates(): TemplateRepository
    {
        return $this->templates;
    }

    public function renderContext(): RenderContext
    {
        return $this->renderContext;
    }

    public function supports(string $template): bool
    {
        return $this->templates->find($template) !== null;
    }

    /**
     * Render a theme template (slug without extension) to HTML.
     *
     * @param array<string, mixed> $context
     */
    public function render(string $template, array $context = []): string
    {
        if ($context !== []) {
            $this->renderContext->merge($context);
        }

        $resolved = $this->templates->resolve($template);

        return $this->renderer->render($this->parser->parse($resolved->content));
    }

    /**
     * theme.json compiled CSS + base core-block CSS + registered block styles.
     */
    public function themeCss(): string
    {
        if ($this->themeCss !== null) {
            return $this->themeCss;
        }

        $resolver = new ThemeJsonResolver();
        $json = $resolver->resolve($this->themeDir);

        $css = (new CssCompiler())->compile($json);

        // Inline styles from register_block_style() calls.
        foreach (ThemeContext::blockStyles() as $blockName => $styles) {
            foreach ($styles as $style) {
                $inline = (string) ($style['inline_style'] ?? '');

                if ($inline !== '') {
                    $css .= "\n" . $inline;
                }
            }
        }

        $css .= "\n" . BaseStyles::css();

        return $this->themeCss = $css;
    }
}
