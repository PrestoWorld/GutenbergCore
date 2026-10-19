<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class CommentContentHandler extends AbstractHandler
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

        $raw = (string) ($comment['content'] ?? $comment['comment_content'] ?? '');

        if (trim($raw) === '') {
            return '';
        }

        return '<div class="wp-block-comment-content">' . wpautop($raw) . '</div>';
    }
}
