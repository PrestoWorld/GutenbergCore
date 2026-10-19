<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

/**
 * core/comments — wrapper for the comments query loop + form.
 */
final class CommentsHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $post = $this->currentPost($block);

        if ($post === null) {
            return '';
        }

        $postId = (int) ($post['id'] ?? $post['ID'] ?? 0);

        $comments = $this->context($block)->get('comment_repository');

        if (is_object($comments) && method_exists($comments, 'find')) {
            $rows = $comments->find(['post_id' => $postId, 'order' => 'ASC', 'per_page' => 200]);
            $this->context($block)->set('post_comments', is_array($rows) ? $rows : []);
            $this->context($block)->set('post_comments_count', is_array($rows) ? count($rows) : 0);
        } else {
            $this->context($block)->set('post_comments', []);
            $this->context($block)->set('post_comments_count', 0);
        }

        $tagName = $this->stringAttr($attributes, 'tagName', 'div');

        return sprintf('<%1$s%2$s>%3$s</%1$s>', $tagName, $this->wrapper($block), $content);
    }
}
