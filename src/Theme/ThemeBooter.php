<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Theme;

use PrestoWorld\Gutenberg\Gutenberg;

/**
 * Boots the active theme's functions.php through the WP lifecycle:
 * compile layer (host shims) → require functions.php → after_setup_theme.
 */
final class ThemeBooter
{
    private static ?string $themePath = null;

    private static bool $booted = false;

    private function __construct()
    {
    }

    public static function boot(string $themePath): void
    {
        $themePath = rtrim($themePath, '/');

        if (self::$booted) {
            return;
        }

        // Custom frontend mode: never load the theme engine.
        if (class_exists(\App\Services\Frontend\FrontendMode::class)
            && \App\Services\Frontend\FrontendMode::isCustom()) {
            return;
        }

        if (!is_dir($themePath) || !is_file($themePath . '/functions.php')) {
            return;
        }

        self::$booted = true;
        self::$themePath = $themePath;

        if (!defined('ABSPATH')) {
            define('ABSPATH', dirname($themePath) . '/');
        }

        if (!defined('PRESTOWORLD')) {
            define('PRESTOWORLD', true);
        }

        self::loadCompileLayer();

        require_once $themePath . '/functions.php';

        self::dispatch('after_setup_theme');
    }

    /**
     * Fire an action through the host's global hooks when available.
     */
    public static function dispatch(string $tag, mixed ...$args): void
    {
        if (function_exists('do_action')) {
            \do_action($tag, ...$args);
        } else {
            Gutenberg::hooks()->doAction($tag, ...$args);
        }
    }

    private static function loadCompileLayer(): void
    {
        if (!class_exists(\Prestoworld\WordpressShim\ShimLoader::class)) {
            return;
        }

        $shims = new \Prestoworld\WordpressShim\ShimLoader();
        $shims->loadGroup('hooks');
        $shims->loadGroup('compat');
        $shims->registerAutoload();
    }

    public static function themePath(): ?string
    {
        return self::$themePath;
    }

    public static function booted(): bool
    {
        return self::$booted;
    }
}
