<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Compatibility;

/**
 * Cross-request-safe store for theme API registrations made by theme
 * functions.php (add_theme_support delegates to the host runtime; block
 * styles / pattern categories / editor styles land here and are consumed by
 * the Kernel at boot).
 */
final class ThemeContext
{
    /** @var array<string, mixed> */
    private static array $supports = [];

    /** @var list<string> */
    private static array $editorStyles = [];

    /** @var array<string, array<string, mixed>> blockName => styleName => settings */
    private static array $blockStyles = [];

    /** @var array<string, array<string, mixed>> slug => settings */
    private static array $patternCategories = [];

    /** @var array<string, array<string, mixed>> name => settings */
    private static array $bindingsSources = [];

    public static function addSupport(string $feature, array $args = []): void
    {
        self::$supports[$feature] = $args === [] ? true : $args;
    }

    public static function supports(string $feature): bool
    {
        return isset(self::$supports[$feature]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function allSupports(): array
    {
        return self::$supports;
    }

    public static function addEditorStyle(string $stylesheet): void
    {
        if (!in_array($stylesheet, self::$editorStyles, true)) {
            self::$editorStyles[] = $stylesheet;
        }
    }

    /**
     * @return list<string>
     */
    public static function editorStyles(): array
    {
        return self::$editorStyles;
    }

    /**
     * @param array<string, mixed> $settings
     */
    public static function registerBlockStyle(string $blockName, array $settings): void
    {
        $styleName = (string) ($settings['name'] ?? '');

        if ($styleName === '') {
            throw new \PrestoWorld\Gutenberg\Exceptions\GutenbergException(
                sprintf('register_block_style for "%s" requires args.name.', $blockName)
            );
        }

        self::$blockStyles[$blockName][$styleName] = $settings;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function blockStyles(): array
    {
        return self::$blockStyles;
    }

    /**
     * @param array<string, mixed> $settings
     */
    public static function registerPatternCategory(string $slug, array $settings): void
    {
        self::$patternCategories[$slug] = $settings;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function patternCategories(): array
    {
        return self::$patternCategories;
    }

    /**
     * @param array<string, mixed> $settings
     */
    public static function registerBindingsSource(string $name, array $settings): void
    {
        self::$bindingsSources[$name] = $settings;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function bindingsSources(): array
    {
        return self::$bindingsSources;
    }
}
