<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class CommentsPaginationHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $count = (int) ($this->context($block)->get('post_comments_count') ?? 0);
        $perPage = max(1, (int) ($this->context($block)->get('comments_per_page') ?? 25));

        if ($count <= $perPage) {
            return '';
        }

        $this->context($block)->set('comments_max_page', (int) ceil($count / $perPage));
        $this->context($block)->set(
            'comments_page',
            max(1, (int) ($_GET['cpage'] ?? 1))
        );

        return '<div' . $this->wrapper($block) . '>' . $content . '</div>';
    }
}
