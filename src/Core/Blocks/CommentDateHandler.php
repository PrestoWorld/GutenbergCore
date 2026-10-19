<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class CommentDateHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $comment = $this->context($block)->get('current_comment');

        if (!is_array($comment)) {
            $comment = $block->context['comment'] ?? null;
        }

        if (!is_array($comment)) {
            return '';
        }

        $raw = (string) ($comment['created_at'] ?? $comment['comment_date'] ?? '');

        if ($raw === '' || ($timestamp = strtotime($raw)) === false) {
            return '';
        }

        $format = $this->stringAttr($attributes, 'format');

        if ($format === '') {
            $format = (string) (\get_option('date_format') ?: 'F j, Y');
        }

        $href = (string) ($comment['url'] ?? '');

        $html = '<time datetime="' . $this->esc(date(DATE_ATOM, $timestamp)) . '">'
            . $this->esc(date($format, $timestamp)) . '</time>';

        if ($href !== '') {
            $html = '<a href="' . $this->esc($href) . '">' . $html . '</a>';
        }

        return '<div class="wp-block-comment-date">' . $html . '</div>';
    }
}
