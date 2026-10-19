<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class CommentsPaginationNumbersHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $page = (int) ($this->context($block)->get('comments_page') ?? 1);
        $maxPage = (int) ($this->context($block)->get('comments_max_page') ?? 0);

        if ($maxPage <= 1) {
            return '';
        }

        $baseUrl = (string) $this->context($block)->get('pagination_url', $_SERVER['REQUEST_URI'] ?? '/');
        $separator = str_contains($baseUrl, '?') ? '&' : '?';
        $links = [];

        for ($i = 1; $i <= $maxPage; $i++) {
            if ($i === $page) {
                $links[] = '<span aria-current="page" class="page-numbers current">' . $i . '</span>';
                continue;
            }

            $links[] = '<a class="page-numbers" href="'
                . $this->esc($baseUrl . $separator . 'cpage=' . $i . '#respond') . '">'
                . $i . '</a>';
        }

        return '<div class="wp-block-comments-pagination-numbers">' . implode('', $links) . '</div>';
    }
}
