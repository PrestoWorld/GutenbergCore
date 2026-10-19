<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

/**
 * core/post-comments-form — comment form markup for the current post.
 */
final class PostCommentsFormHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $post = $this->currentPost($block);

        if ($post === null) {
            return '';
        }

        $slug = (string) ($post['slug'] ?? $post['post_name'] ?? '');
        $postId = (int) ($post['id'] ?? 0);

        $labelName = function_exists('__') ? \__('Name', 'twentytwentyfive') : 'Name';
        $labelEmail = function_exists('__') ? \__('Email', 'twentytwentyfive') : 'Email';
        $labelComment = function_exists('__') ? \__('Comment', 'twentytwentyfive') : 'Comment';
        $submit = function_exists('__') ? \__('Post Comment', 'twentytwentyfive') : 'Post Comment';

        return sprintf(
            '<form class="comment-form wp-block-post-comments-form" action="%s" method="post" id="respond">'
            . '<p class="comment-notes"><label for="author">%s</label>'
            . '<input id="author" name="author" type="text" required /></p>'
            . '<p class="comment-notes"><label for="email">%s</label>'
            . '<input id="email" name="email" type="email" required /></p>'
            . '<p class="comment-form-comment"><label for="comment">%s</label>'
            . '<textarea id="comment" name="comment" rows="6" required></textarea></p>'
            . '<input type="hidden" name="comment_post_ID" value="%d" />'
            . '<p class="form-submit"><button type="submit" name="%s" class="wp-element-button">%s</button></p>'
            . '</form>',
            $this->esc('/' . ltrim($slug, '/')),
            $this->esc($labelName),
            $this->esc($labelEmail),
            $this->esc($labelComment),
            $postId,
            'submit',
            $this->esc($submit)
        );
    }
}
