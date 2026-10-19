<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg;

use PrestoWorld\Gutenberg\Block\BlockInstance;
use PrestoWorld\Gutenberg\Block\Metadata\MetadataLoader;
use PrestoWorld\Gutenberg\Block\Type\BlockTypeRegistry;
use PrestoWorld\Gutenberg\Bindings\BindingsRegistry;
use PrestoWorld\Gutenberg\Bindings\BindingsProcessor;
use PrestoWorld\Gutenberg\Contracts\HookManagerInterface;
use PrestoWorld\Gutenberg\Core\Blocks\Registrar;
use PrestoWorld\Gutenberg\Hooks\GlobalHooksAdapter;
use PrestoWorld\Gutenberg\Hooks\InternalHooks;
use PrestoWorld\Gutenberg\Kernel\Kernel;
use PrestoWorld\Gutenberg\Parser\BlockSerializer;
use PrestoWorld\Gutenberg\Patterns\PatternRegistry;
use PrestoWorld\Gutenberg\Patterns\Source\ThemeFilePatternSource;
use PrestoWorld\Gutenberg\Rendering\BlockRenderer;
use PrestoWorld\Gutenberg\Rendering\RenderContext;
use PrestoWorld\Gutenberg\Templates\TemplateRepository;
use PrestoWorld\Gutenberg\Templates\Source\ThemeFileTemplateSource;
use PrestoWorld\Gutenberg\ThemeJson\Css\CssCompiler;
use PrestoWorld\Gutenberg\ThemeJson\ThemeJsonResolver;
use PrestoWorld\Gutenberg\Core\BaseStyles;
use PrestoWorld\Gutenberg\Compatibility\ThemeContext;

/**
 * Static facade for the Gutenberg engine (Service Locator over the Kernel).
 */
final class Gutenberg
{
    private static ?Kernel $kernel = null;

    private static ?BlockInstance $currentBlock = null;

    public static function boot(string $themeDir, array $services = []): Kernel
    {
        if (self::$kernel !== null) {
            return self::$kernel;
        }

        $kernel = new Kernel($themeDir, $services);
        self::$kernel = $kernel;

        return $kernel;
    }

    public static function kernel(): ?Kernel
    {
        return self::$kernel;
    }

    public static function booted(): bool
    {
        return self::$kernel !== null;
    }

    public static function reset(): void
    {
        self::$kernel = null;
        self::$currentBlock = null;
    }

    public static function setCurrentBlock(?BlockInstance $block): void
    {
        self::$currentBlock = $block;
    }

    public static function currentBlock(): ?BlockInstance
    {
        return self::$currentBlock;
    }

    /**
     * Global hook manager: adapts the host's WordPress hooks when present.
     */
    public static function hooks(): HookManagerInterface
    {
        static $hooks = null;

        if ($hooks === null) {
            $hooks = function_exists('apply_filters') && function_exists('add_filter')
                ? new GlobalHooksAdapter()
                : new InternalHooks();
        }

        return $hooks;
    }

    /**
     * Block type registry shared with the running kernel (or a fresh one).
     */
    public static function blockTypes(): BlockTypeRegistry
    {
        if (self::$kernel !== null) {
            return self::$kernel->blockTypes();
        }

        $registry = new BlockTypeRegistry();
        Registrar::register($registry, new MetadataLoader(__DIR__ . '/Core/metadata'));

        return $registry;
    }

    public static function patterns(): PatternRegistry
    {
        if (self::$kernel !== null) {
            return self::$kernel->patterns();
        }

        return new PatternRegistry();
    }

    public static function bindings(): BindingsRegistry
    {
        if (self::$kernel !== null) {
            return self::$kernel->bindings();
        }

        return new BindingsRegistry();
    }

    public static function render(string $templateSlug, array $context = []): string
    {
        if (self::$kernel === null) {
            throw new Exceptions\GutenbergException(
                'Gutenberg kernel is not booted — call Gutenberg::boot($themeDir) first.'
            );
        }

        return self::$kernel->render($templateSlug, $context);
    }

    public static function themeCss(): string
    {
        return self::$kernel !== null ? self::$kernel->themeCss() : BaseStyles::css();
    }
}
