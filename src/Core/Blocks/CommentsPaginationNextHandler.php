<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class CommentsPaginationNextHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $page = (int) ($this->context($block)->get('comments_page') ?? 1);
        $maxPage = (int) ($this->context($block)->get('comments_max_page') ?? 0);

        if ($maxPage < 1 || $page >= $maxPage) {
            return '';
        }

        $label = $this->stringAttr($attributes, 'label', 'Newer Comments');
        $baseUrl = (string) $this->context($block)->get('pagination_url', $_SERVER['REQUEST_URI'] ?? '/');
        $separator = str_contains($baseUrl, '?') ? '&' : '?';
        $url = $baseUrl . $separator . 'cpage=' . ($page + 1) . '#respond';

        return '<div class="wp-block-comments-pagination-next"><a rel="next" href="'
            . $this->esc($url) . '">' . $this->esc($label) . '</a></div>';
    }
}
