<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class QueryPaginationNumbersHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $page = (int) ($this->context($block)->get('query_page') ?? 1);
        $total = (int) ($this->context($block)->get('query_total') ?? 0);
        $perPage = max(1, (int) ($this->context($block)->get('query_per_page') ?? 10));
        $maxPage = (int) ceil($total / $perPage);

        if ($maxPage <= 1) {
            return '';
        }

        $baseUrl = (string) $this->context($block)->get('pagination_url', $_SERVER['REQUEST_URI'] ?? '/');
        $separator = str_contains($baseUrl, '?') ? '&' : '?';
        $links = [];

        $start = max(1, $page - 2);
        $end = min($maxPage, $page + 2);

        if ($start > 1) {
            $links[] = $this->link($baseUrl, $separator, 1, false);
            if ($start > 2) {
                $links[] = '<span class="page-numbers dots">…</span>';
            }
        }

        for ($i = $start; $i <= $end; $i++) {
            $links[] = $this->link($baseUrl, $separator, $i, $i === $page);
        }

        if ($end < $maxPage) {
            if ($end < $maxPage - 1) {
                $links[] = '<span class="page-numbers dots">…</span>';
            }
            $links[] = $this->link($baseUrl, $separator, $maxPage, false);
        }

        return '<div' . $this->wrapper($block) . '>' . implode('', $links) . '</div>';
    }

    private function link(string $baseUrl, string $separator, int $page, bool $current): string
    {
        if ($current) {
            return '<span aria-current="page" class="page-numbers current">' . $page . '</span>';
        }

        return '<a class="page-numbers" href="' . $this->esc($baseUrl . $separator . 'paged=' . $page) . '">'
            . $page . '</a>';
    }
}
