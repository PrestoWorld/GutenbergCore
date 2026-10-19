<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class PostTitleHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $post = $this->currentPost($block);

        if ($post === null) {
            return '';
        }

        $title = (string) ($post['title'] ?? $post['post_title'] ?? '');
        $level = (int) ($attributes['level'] ?? 2);
        $tag = $level >= 1 && $level <= 6 ? 'h' . $level : 'p';

        $href = $post['slug'] ?? $post['post_name'] ?? null;
        $isLink = (bool) ($attributes['isLink'] ?? true);
        $prefix = $this->stringAttr($attributes, 'prefix');
        $text = $prefix !== '' ? '<span class="wp-block-post-title__prefix">' . $this->esc($prefix) . '</span> ' : '';

        if ($isLink && is_string($href) && $href !== '') {
            $text .= '<a href="' . $this->esc('/' . ltrim($href, '/')) . '">' . $this->esc($title) . '</a>';
        } else {
            $text .= $this->esc($title);
        }

        return sprintf('<%1$s%2$s>%3$s</%1$s>', $tag, $this->wrapper($block), $text);
    }
}
