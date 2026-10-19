<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

/**
 * core/query-no-results — renders children only when core/query returned nothing.
 */
final class QueryNoResultsHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $hasResults = $this->context($block)->get('query_has_results');

        if ($hasResults === null || $hasResults === true) {
            return '';
        }

        return $content;
    }
}
