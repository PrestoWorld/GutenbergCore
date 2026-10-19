<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class SearchHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $label = $this->stringAttr($attributes, 'label', function_exists('__') ? \__('Search', 'twentytwentyfive') : 'Search');
        $placeholder = $this->stringAttr($attributes, 'placeholder', $label . '…');
        $buttonLabel = $this->stringAttr($attributes, 'buttonText', function_exists('__') ? \__('Search', 'twentytwentyfive') : 'Search');
        $action = (string) $this->context($block)->get('search_url', '/search');

        return sprintf(
            '<form role="search" method="get" action="%s"%s>'
            . '<label class="wp-block-search__label screen-reader-text" for="wp-block-search-%d">%s</label>'
            . '<input class="wp-block-search__input" id="wp-block-search-%d" type="search" name="s" placeholder="%s" value="%s" />'
            . '<button type="submit" class="wp-block-search__button wp-element-button">%s</button>'
            . '</form>',
            $this->esc($action),
            $this->wrapper($block),
            $block->attributes['queryId'] ?? 0,
            $this->esc($label),
            $block->attributes['queryId'] ?? 0,
            $this->esc($placeholder),
            $this->esc((string) ($_GET['s'] ?? '')),
            $this->esc($buttonLabel)
        );
    }
}
