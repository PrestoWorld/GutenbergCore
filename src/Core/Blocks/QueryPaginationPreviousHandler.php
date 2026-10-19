<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class QueryPaginationPreviousHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $page = (int) ($this->context($block)->get('query_page') ?? 1);

        if ($page <= 1) {
            return '';
        }

        $label = $this->stringAttr($attributes, 'label');

        if ($label === '') {
            $label = function_exists('__') ? \__('Previous', 'twentytwentyfive') : 'Previous';
        }

        $arrow = $this->stringAttr($attributes, 'arrow');
        $text = $this->esc($label);

        if ($arrow !== '') {
            $text = '← ' . $text;
        }

        $url = $this->paginationUrl($block, $page - 1);

        return sprintf('<div%s><a href="%s" rel="prev">%s</a></div>', $this->wrapper($block), $this->esc($url), $text);
    }

    private function paginationUrl(BlockInstance $block, int $page): string
    {
        $baseUrl = (string) $this->context($block)->get('pagination_url', $_SERVER['REQUEST_URI'] ?? '/');

        $separator = str_contains($baseUrl, '?') ? '&' : '?';

        return $baseUrl . $separator . 'paged=' . $page;
    }
}
