<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class PostAuthorNameHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $post = $this->currentPost($block);

        if ($post === null) {
            return '';
        }

        $authorId = (int) ($post['author_id'] ?? $post['post_author'] ?? 0);
        $name = (string) ($post['author_name'] ?? $post['post_author_name'] ?? '');

        if ($name === '' && $authorId > 0) {
            $users = $this->context($block)->get('user_repository');

            if (is_object($users) && method_exists($users, 'find')) {
                $rows = $users->find(['id' => $authorId]);
                $name = (string) ($rows[0]['display_name'] ?? $rows[0]['name'] ?? '');
            }
        }

        if ($name === '' && $authorId > 0 && function_exists('get_userdata')) {
            $user = \get_userdata($authorId);
            $name = is_object($user) ? (string) ($user->display_name ?? '') : '';
        }

        if ($name === '') {
            $name = (string) $this->context($block)->get('site_title', 'PrestoWorld');
        }

        $prefix = $this->stringAttr($attributes, 'prefix');
        $text = $prefix !== '' ? $this->esc($prefix) . ' ' : '';

        if (($attributes['isLink'] ?? true) && $authorId > 0) {
            $text .= '<a href="' . $this->esc('/author/' . $authorId) . '">' . $this->esc($name) . '</a>';
        } else {
            $text .= $this->esc($name);
        }

        return '<span' . $this->wrapper($block) . '>' . $text . '</span>';
    }
}
