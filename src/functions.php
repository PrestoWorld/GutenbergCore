<?php

declare(strict_types=1);

/**
 * WordPress-compatible API surface used by block themes (functions.php,
 * patterns) on the PrestoWorld runtime.
 *
 * Every function is function_exists()-guarded: the host app's compile layer
 * (wp-shims/wp-compatibility) wins when it already defines a symbol.
 * Registrations made before the kernel boots are queued in ThemeContext and
 * consumed by the kernel when it is constructed.
 */

use PrestoWorld\Gutenberg\Compatibility\PostFormats;
use PrestoWorld\Gutenberg\Compatibility\ThemeContext;
use PrestoWorld\Gutenberg\Exceptions\MissingApiException;
use PrestoWorld\Gutenberg\Gutenberg;

if (!function_exists('get_parent_theme_file_uri')) {
    function get_parent_theme_file_uri(string $path = ''): string
    {
        if (function_exists('get_template_directory_uri')) {
            $base = \get_template_directory_uri();
        } elseif (class_exists(\PrestoWorld\Core\ThemeManager::class)) {
            $base = \PrestoWorld\Core\ThemeManager::directoryUri();
        } else {
            $base = '';
        }

        return rtrim($base, '/') . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('get_parent_theme_file_path')) {
    function get_parent_theme_file_path(string $path = ''): string
    {
        if (class_exists(\PrestoWorld\Core\ThemeManager::class)) {
            $base = \PrestoWorld\Core\ThemeManager::directory();
        } elseif (class_exists(\PrestoWorld\Gutenberg\Theme\ThemeBooter::class)) {
            $base = \PrestoWorld\Gutenberg\Theme\ThemeBooter::themePath() ?? '';
        } else {
            $base = '';
        }

        return rtrim($base, '/') . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('register_block_style')) {
    function register_block_style(string $blockName, array $args): void
    {
        ThemeContext::registerBlockStyle($blockName, $args);

        // Live kernel: push inline styles into the compiled CSS on next render.
        $kernel = Gutenberg::kernel();

        if ($kernel !== null) {
            $css = (string) ($args['inline_style'] ?? '');

            if ($css !== '') {
                $kernel->renderContext()->set('pending_block_style_css', trim($css));
            }
        }
    }
}

if (!function_exists('register_block_pattern_category')) {
    function register_block_pattern_category(string $slug, array $args): void
    {
        ThemeContext::registerPatternCategory($slug, $args);

        $kernel = Gutenberg::kernel();

        if ($kernel !== null) {
            $kernel->patterns()->registerCategory(
                $slug,
                (string) ($args['label'] ?? $slug),
                isset($args['description']) ? (string) $args['description'] : null,
            );
        }
    }
}

if (!function_exists('register_block_bindings_source')) {
    function register_block_bindings_source(string $name, array $args): void
    {
        $callback = $args['get_value_callback'] ?? null;

        if (!is_callable($callback)) {
            throw new MissingApiException(sprintf(
                'register_block_bindings_source("%s") requires a get_value_callback.',
                $name
            ));
        }

        $settings = [
            'label' => (string) ($args['label'] ?? $name),
            'uses_context' => array_values(is_array($args['uses_context'] ?? null) ? $args['uses_context'] : []),
            'get_value_callback' => $callback,
        ];

        ThemeContext::registerBindingsSource($name, $settings);

        $kernel = Gutenberg::kernel();

        if ($kernel !== null) {
            $kernel->bindings()->register($name, $settings);
        }
    }
}

if (!function_exists('get_post_format')) {
    /**
     * @param array<string, mixed>|int|string|null $post
     */
    function get_post_format($post = null): string|false
    {
        if (is_array($post)) {
            return PostFormats::slug($post);
        }

        $kernel = Gutenberg::kernel();
        $current = $kernel?->renderContext()->currentPost();

        return PostFormats::slug($current);
    }
}

if (!function_exists('get_post_format_string')) {
    function get_post_format_string(string $slug): string
    {
        return PostFormats::label($slug);
    }
}

if (!function_exists('strip_shortcodes')) {
    function strip_shortcodes(string $content): string
    {
        $content = (string) preg_replace('/\[\/?[^\]]+\]/', '', $content);

        return $content;
    }
}

if (!function_exists('wpautop')) {
    function wpautop(string $text, bool $br = true): string
    {
        $text = trim($text);

        if ($text === '') {
            return '';
        }

        if (str_starts_with($text, '<')) {
            // Keep blocks of HTML intact — wrap only loose text lines.
            return $text . "\n";
        }

        $paragraphs = preg_split('/\n\s*\n/', $text) ?: [];
        $out = '';

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);

            if ($paragraph === '') {
                continue;
            }

            $paragraph = $br ? nl2br($paragraph) : $paragraph;
            $out .= '<p>' . $paragraph . "</p>\n";
        }

        return $out !== '' ? $out : '<p>' . ($br ? nl2br($text) : $text) . "</p>\n";
    }
}

if (!function_exists('get_avatar_url')) {
    /**
     * @param array<string, mixed> $args
     */
    function get_avatar_url(int|string $idOrEmail, array $args = []): string
    {
        $email = is_string($idOrEmail) && str_contains($idOrEmail, '@') ? $idOrEmail : '';

        if ($email === '') {
            $kernel = Gutenberg::kernel();
            $comment = $kernel?->renderContext()->get('current_comment');

            if (is_array($comment) && isset($comment['author_email'])) {
                $email = (string) $comment['author_email'];
            }
        }

        $hash = $email !== '' ? md5(strtolower(trim($email))) : '00000000000000000000000000000000';
        $size = (int) ($args['size'] ?? 48);

        return 'https://secure.gravatar.com/avatar/' . $hash . '?s=' . $size . '&d=mp';
    }
}

if (!function_exists('absint')) {
    function absint(mixed $value): int
    {
        return abs((int) $value);
    }
}

if (!function_exists('esc_html_x')) {
    function esc_html_x(string $text, string $context, string $domain = 'default'): string
    {
        return htmlspecialchars(
            function_exists('_x') ? (string) \_x($text, $context, $domain) : $text,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }
}

if (!function_exists('esc_attr_x')) {
    function esc_attr_x(string $text, string $context, string $domain = 'default'): string
    {
        return htmlspecialchars(
            function_exists('_x') ? (string) \_x($text, $context, $domain) : $text,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }
}

if (!function_exists('esc_attr__')) {
    function esc_attr__(string $text, string $domain = 'default'): string
    {
        return htmlspecialchars(
            function_exists('__') ? (string) \__($text, $domain) : $text,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }
}

if (!function_exists('esc_html__')) {
    function esc_html__(string $text, string $domain = 'default'): string
    {
        return htmlspecialchars(
            function_exists('__') ? (string) \__($text, $domain) : $text,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }
}

if (!function_exists('wp_parse_args')) {
    /**
     * @param array<string, mixed> $defaults
     * @return array<string, mixed>
     */
    function wp_parse_args(array $args, array $defaults = []): array
    {
        return array_merge($defaults, $args);
    }
}
