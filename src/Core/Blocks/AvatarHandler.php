<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class AvatarHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $commentId = (int) ($attributes['id'] ?? $block->context['commentId'] ?? 0);
        $userId = (int) ($attributes['userId'] ?? 0);

        $size = (int) ($attributes['size'] ?? 48);
        $url = '';
        $name = '';

        $comment = $this->context($block)->get('current_comment');

        if (is_array($comment)) {
            $commentId = $commentId ?: (int) ($comment['id'] ?? 0);
            $userId = $userId ?: (int) ($comment['user_id'] ?? 0);
            $name = (string) ($comment['author'] ?? '');
        }

        if ($commentId > 0) {
            $comments = $this->context($block)->get('comment_repository');

            if (is_object($comments) && method_exists($comments, 'find')) {
                $rows = $comments->find(['id' => $commentId]);
                $row = is_array($rows[0] ?? null) ? $rows[0] : null;

                if ($row !== null) {
                    $userId = $userId ?: (int) ($row['user_id'] ?? 0);
                    $name = $name ?: (string) ($row['author'] ?? '');
                    $url = (string) ($row['avatar_url'] ?? '');
                }
            }
        }

        if ($url === '' && $userId > 0) {
            $users = $this->context($block)->get('user_repository');

            if (is_object($users) && method_exists($users, 'find')) {
                $rows = $users->find(['id' => $userId]);
                $row = is_array($rows[0] ?? null) ? $rows[0] : null;
                $url = (string) ($row['avatar_url'] ?? '');
                $name = $name ?: (string) ($row['display_name'] ?? '');
            }
        }

        if ($url === '' && function_exists('get_avatar_url')) {
            $url = (string) \get_avatar_url($userId > 0 ? $userId : ($name !== '' ? $name : 'anonymous'), ['size' => $size]);
        }

        if ($url === '') {
            return '';
        }

        $shape = $this->stringAttr($attributes, 'shape', 'circle');

        return sprintf(
            '<img alt="%s" src="%s" style="width:%dpx;height:%dpx" class="wp-block-avatar__image is-%s-shape" />',
            $this->esc($name),
            $this->esc($url),
            $size,
            $size,
            $this->esc($shape)
        );
    }
}
