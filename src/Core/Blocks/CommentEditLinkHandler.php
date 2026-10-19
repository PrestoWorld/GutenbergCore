<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class CommentEditLinkHandler extends AbstractHandler
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
        $userId = (int) ($comment['user_id'] ?? 0);
        $currentUserId = (int) ($this->context($block)->get('current_user_id') ?? 0);

        if ($commentId <= 0 || $currentUserId <= 0 || $userId !== $currentUserId) {
            return '';
        }

        $label = $this->stringAttr($attributes, 'label', 'Edit');

        $post = $this->currentPost($block);
        $slug = is_array($post) ? (string) ($post['slug'] ?? '') : '';

        return '<a class="comment-edit-link" href="'
            . $this->esc('/' . ltrim($slug, '/') . '#comment-' . $commentId) . '">'
            . $this->esc($label) . '</a>';
    }
}
