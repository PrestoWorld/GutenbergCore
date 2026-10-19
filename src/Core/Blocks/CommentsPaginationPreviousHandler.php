<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class CommentsPaginationPreviousHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $page = (int) ($this->context($block)->get('comments_page') ?? 1);

        if ($page <= 1) {
            return '';
        }

        $label = $this->stringAttr($attributes, 'label', 'Older Comments');
        $url = $this->url($block, $page - 1);

        return '<div class="wp-block-comments-pagination-previous"><a rel="prev" href="'
            . $this->esc($url) . '">' . $this->esc($label) . '</a></div>';
    }

    private function url(BlockInstance $block, int $page): string
    {
        $baseUrl = (string) $this->context($block)->get('pagination_url', $_SERVER['REQUEST_URI'] ?? '/');
        $separator = str_contains($baseUrl, '?') ? '&' : '?';

        return $baseUrl . $separator . 'cpage=' . $page . '#respond';
    }
}
