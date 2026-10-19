<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class CommentReplyLinkHandler extends AbstractHandler
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

        $commentId = (int) ($comment['id'] ?? 0);
        $label = $this->stringAttr($attributes, 'replyLabel');

        if ($label === '') {
            $label = function_exists('__') ? \__('Reply', 'twentytwentyfive') : 'Reply';
        }

        $post = $this->currentPost($block);
        $slug = is_array($post) ? (string) ($post['slug'] ?? '') : '';

        return '<a class="comment-reply-link" href="'
            . $this->esc('/' . ltrim($slug, '/') . '?replytocom=' . $commentId . '#respond') . '">'
            . $this->esc($label) . '</a>';
    }
}
