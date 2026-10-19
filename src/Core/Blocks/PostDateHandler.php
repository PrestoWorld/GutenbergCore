<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class PostDateHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $post = $this->currentPost($block);

        if ($post === null) {
            return '';
        }

        $raw = (string) ($post['created_at'] ?? $post['post_date'] ?? $post['date'] ?? '');

        if ($raw === '') {
            return '';
        }

        $timestamp = strtotime($raw);

        if ($timestamp === false) {
            return '';
        }

        $format = $this->stringAttr($attributes, 'format');

        if ($format === '') {
            $format = function_exists('get_option') && ($option = \get_option('date_format'))
                ? (string) $option
                : 'F j, Y';
        }

        $displayed = date($format, $timestamp);
        $prefix = $this->stringAttr($attributes, 'prefix');
        $suffix = $this->stringAttr($attributes, 'suffix');

        $linkTo = $this->stringAttr($attributes, 'linkTo');
        $text = $this->esc($prefix . $displayed . $suffix);

        if ($linkTo === 'post' && isset($post['slug'])) {
            $text = '<a href="' . $this->esc('/' . ltrim((string) $post['slug'], '/')) . '">' . $text . '</a>';
        }

        return '<time' . $this->wrapper($block, ['datetime' => date(DATE_ATOM, $timestamp)]) . '>' . $text . '</time>';
    }
}
