<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class QueryPaginationNextHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $page = (int) ($this->context($block)->get('query_page') ?? 1);
        $total = (int) ($this->context($block)->get('query_total') ?? 0);
        $perPage = max(1, (int) ($this->context($block)->get('query_per_page') ?? 10));
        $maxPage = (int) ceil($total / $perPage);

        if ($maxPage < 1 || $page >= $maxPage) {
            return '';
        }

        $label = $this->stringAttr($attributes, 'label');

        if ($label === '') {
            $label = function_exists('__') ? \__('Next', 'twentytwentyfive') : 'Next';
        }

        $arrow = $this->stringAttr($attributes, 'arrow');
        $text = $this->esc($label);

        if ($arrow !== '') {
            $text .= ' →';
        }

        $baseUrl = (string) $this->context($block)->get('pagination_url', $_SERVER['REQUEST_URI'] ?? '/');
        $separator = str_contains($baseUrl, '?') ? '&' : '?';
        $url = $baseUrl . $separator . 'paged=' . ($page + 1);

        return sprintf('<div%s><a href="%s" rel="next">%s</a></div>', $this->wrapper($block), $this->esc($url), $text);
    }
}
