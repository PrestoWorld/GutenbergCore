<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class CommentsTitleHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $count = (int) ($this->context($block)->get('post_comments_count') ?? 0);

        if ($count === 0) {
            return '';
        }

        $post = $this->context($block)->get('current_post');
        $postTitle = is_array($post) ? (string) ($post['title'] ?? '') : '';

        $text = $count === 1
            ? sprintf('One Reply to "%s"', $this->esc($postTitle))
            : sprintf('%d Replies to "%s"', $count, $this->esc($postTitle));

        $level = (int) ($attributes['level'] ?? 2);
        $tag = $level >= 1 && $level <= 6 ? 'h' . $level : 'p';

        return sprintf('<%1$s%2$s>%3$s</%1$s>', $tag, $this->wrapper($block), $text);
    }
}
