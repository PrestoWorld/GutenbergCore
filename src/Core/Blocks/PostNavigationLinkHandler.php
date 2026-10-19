<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class PostNavigationLinkHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $direction = $this->stringAttr($attributes, 'type', 'next') === 'previous' ? 'previous' : 'next';
        $post = $this->currentPost($block);

        if ($post === null) {
            return '';
        }

        $navigation = $this->context($block)->get('post_navigation');

        $target = null;

        if (is_array($navigation)) {
            $target = $direction === 'previous'
                ? ($navigation['previous'] ?? null)
                : ($navigation['next'] ?? null);
        }

        if (!is_array($target)) {
            return '';
        }

        $label = $this->stringAttr($attributes, 'label');

        if ($label === '') {
            $label = $direction === 'previous'
                ? (function_exists('__') ? \__('Previous', 'twentytwentyfive') : 'Previous')
                : (function_exists('__') ? \__('Next', 'twentytwentyfive') : 'Next');
        }

        $title = (string) ($target['title'] ?? $target['post_title'] ?? '');
        $slug = (string) ($target['slug'] ?? $target['post_name'] ?? '');
        $showTitle = (bool) ($attributes['showTitle'] ?? true);
        $arrow = $this->stringAttr($attributes, 'arrow');

        $text = $this->esc($label);

        if ($arrow !== '') {
            $text .= ' <span class="wp-block-post-navigation-link__arrow is-arrow-' . $this->esc($arrow) . '">'
                . ($direction === 'previous' ? '←' : '→') . '</span>';
        }

        if ($showTitle && $title !== '') {
            $text .= ' <a href="' . $this->esc('/' . ltrim($slug, '/')) . '">' . $this->esc($title) . '</a>';
        }

        $prefix = $this->stringAttr($attributes, 'prefix');
        $suffix = $this->stringAttr($attributes, 'suffix');

        return sprintf(
            '<div%s>%s%s%s</div>',
            $this->wrapper($block, ['class' => 'post-navigation-link-' . $direction]),
            $prefix !== '' ? $this->esc($prefix) . ' ' : '',
            $text,
            $suffix !== '' ? ' ' . $this->esc($suffix) : ''
        );
    }
}
