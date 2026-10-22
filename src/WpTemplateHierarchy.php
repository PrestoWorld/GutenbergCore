<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg;

/**
 * Template hierarchy for block themes.
 *
 * PHP, PORTED FROM WORDPRESS CORE — do not re-implement in app code.
 * Source: wp-includes/block-template-utils.php :: get_template_hierarchy()
 * (WordPress, GPL-2.0-or-later). Keeping it inside this GPL package is what
 * lets PrestoWorld app code stay license-clean: app controllers call this
 * class instead of carrying a copy of core's logic.
 *
 * The plugin `*_template_hierarchy` filters from core are intentionally not
 * applied here; behaviour otherwise mirrors core 6.7.
 */
final class WpTemplateHierarchy
{
    /**
     * @return list<string> ordered template slugs, most specific first
     */
    public static function for(string $slug, bool $isCustom = false, string $templatePrefix = ''): array
    {
        if ($slug === 'index') {
            return ['index'];
        }

        if ($isCustom) {
            return ['page', 'singular', 'index'];
        }

        if ($slug === 'front-page') {
            return ['front-page', 'home', 'index'];
        }

        $hierarchy = [$slug];

        if ($templatePrefix !== '') {
            [$type] = explode('-', $templatePrefix);
            if (!in_array($templatePrefix, [$slug, $type], true)) {
                $hierarchy[] = $templatePrefix;
            }
            if ($slug !== $type) {
                $hierarchy[] = $type;
            }
        } elseif (preg_match('/^(author|category|archive|tag|page)-.+$/', $slug, $matches) === 1) {
            $hierarchy[] = $matches[1];
        } elseif (preg_match('/^(taxonomy|single)-(.+)$/', $slug, $matches) === 1) {
            $type = $matches[1];
            $slugRemaining = $matches[2];
            $items = $type === 'single'
                ? ['post', 'page', 'attachment', 'wp_block', 'wp_navigation', 'wp_template', 'wp_template_part']
                : ['category', 'post_tag', 'post_format'];
            foreach ($items as $item) {
                if (!str_starts_with($slugRemaining, $item)) {
                    continue;
                }
                if ($slugRemaining === $item) {
                    $hierarchy[] = $type;
                    break;
                }
                if (strlen($slugRemaining) > strlen($item) + 1) {
                    $hierarchy[] = $type . '-' . $item;
                    $hierarchy[] = $type;
                    break;
                }
            }
        }

        foreach (['author', 'taxonomy', 'category', 'tag'] as $archivePrefix) {
            if (str_starts_with($slug, $archivePrefix)) {
                $hierarchy[] = 'archive';
                break;
            }
        }
        if ($slug === 'date') {
            $hierarchy[] = 'archive';
        }
        if ($slug === 'attachment') {
            $hierarchy[] = 'single';
        }
        if (str_starts_with($slug, 'single') || str_starts_with($slug, 'page') || $slug === 'attachment') {
            $hierarchy[] = 'singular';
        }

        $hierarchy[] = 'index';

        return array_values(array_unique($hierarchy));
    }
}
