<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class PostExcerptHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $post = $this->currentPost($block);

        if ($post === null) {
            return '';
        }

        $excerpt = (string) ($post['excerpt'] ?? $post['post_excerpt'] ?? '');

        if ($excerpt === '') {
            $full = (string) ($post['content'] ?? $post['post_content'] ?? '');
            $excerpt = trim(wp_strip_all_tags(strip_shortcodes($full)));
            $maxLength = (int) ($attributes['excerptLength'] ?? 55);
            $excerpt = (string) preg_replace('/\s+/', ' ', $excerpt);
            if (mb_strlen($excerpt) > $maxLength) {
                $excerpt = rtrim(mb_substr($excerpt, 0, $maxLength)) . '…';
            }
        }

        if (trim($excerpt) === '') {
            return '';
        }

        $html = $this->esc($excerpt);

        if ((bool) ($attributes['showMoreOnClick'] ?? false)) {
            $slug = (string) ($post['slug'] ?? $post['post_name'] ?? '');
            $moreText = $this->stringAttr($attributes, 'moreText', function_exists('__') ? \__('Read more', 'twentytwentyfive') : 'Read more');
            $html .= ' <a href="' . $this->esc('/' . ltrim($slug, '/')) . '" class="wp-block-post-excerpt__more-link">' . $this->esc($moreText) . '</a>';
        }

        $tag = (bool) ($attributes['displayAsParagraph'] ?? false) ? 'p' : 'div';

        return sprintf('<%1$s%2$s>%3$s</%1$s>', $tag, $this->wrapper($block), $html);
    }
}
