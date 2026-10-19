<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class PostFeaturedImageHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $post = $this->currentPost($block);

        if ($post === null) {
            return '';
        }

        $url = (string) ($post['featured_image_url'] ?? $post['featured_image'] ?? $post['thumbnail_url'] ?? '');

        if ($url === '') {
            return '';
        }

        $slug = (string) ($post['slug'] ?? $post['post_name'] ?? '');
        $title = (string) ($post['title'] ?? $post['post_title'] ?? '');
        $alt = $this->stringAttr($attributes, 'alt', $title);

        $aspect = $this->stringAttr($attributes, 'aspectRatio');
        $style = $aspect !== '' ? 'aspect-ratio: ' . $this->esc($aspect) : '';

        $img = sprintf(
            '<img src="%s" alt="%s" class="wp-image-%d" style="%s" />',
            $this->esc($url),
            $this->esc($alt),
            (int) ($post['id'] ?? $post['ID'] ?? 0),
            $this->esc($style)
        );

        if (($attributes['isLink'] ?? true) && $slug !== '') {
            $img = '<a href="' . $this->esc('/' . ltrim($slug, '/')) . '">' . $img . '</a>';
        }

        return '<figure' . $this->wrapper($block) . '>' . $img . '</figure>';
    }
}
