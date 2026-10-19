<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\ThemeJson\Css;

/**
 * Resolves theme.json value notations:
 *  - `var:preset|spacing|50` → `var(--wp--preset--spacing--50)`
 *  - `var:custom|my-token`   → `var(--wp--custom--my-token)`
 *  - `file:./assets/x.woff2` → `<baseUri>/assets/x.woff2`
 */
final class ValueResolver
{
    public function __construct(
        private readonly string $themeUri = '',
    ) {
    }

    public function resolve(string $value): string
    {
        $value = (string) preg_replace_callback(
            '#var:([^|]+)\|([^|\s]+)(?:\|([^|\s]+))?#',
            function (array $m): string {
                $group = $m[1];
                $slug = $m[2];
                $sub = $m[3] ?? null;

                if ($group === 'preset') {
                    return $sub !== null
                        ? sprintf('var(--wp--preset--%s--%s)', $slug, $sub)
                        : sprintf('var(--wp--preset--%s)', $slug);
                }

                if ($group === 'custom') {
                    return $sub !== null
                        ? sprintf('var(--wp--custom--%s--%s)', $slug, $sub)
                        : sprintf('var(--wp--custom--%s)', $slug);
                }

                if ($group === 'theme') {
                    return sprintf('var(--wp--theme--%s)', $slug);
                }

                return $m[0];
            },
            $value
        );

        if (str_starts_with($value, 'file:./')) {
            return rtrim($this->themeUri, '/') . '/' . substr($value, 7);
        }

        return $value;
    }

    public function themeUri(): string
    {
        return $this->themeUri;
    }
}
