<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class CommentAuthorNameHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $comment = $this->comment($block);

        if ($comment === null) {
            return '';
        }

        $name = $this->esc((string) ($comment['author'] ?? ''));

        if ($name === '') {
            return '';
        }

        $url = (string) ($comment['author_url'] ?? '');

        if ($url !== '' && ($attributes['isLink'] ?? true)) {
            $name = '<a href="' . $this->esc($url) . '" rel="external nofollow ugc">' . $name . '</a>';
        }

        return '<b class="wp-block-comment-author-name">' . $name . '</b>';
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function comment(BlockInstance $block): ?array
    {
        $comment = $this->context($block)->get('current_comment');

        if (is_array($comment)) {
            return $comment;
        }

        $comment = $block->context['comment'] ?? null;

        return is_array($comment) ? $comment : null;
    }
}
