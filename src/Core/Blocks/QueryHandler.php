<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

/**
 * core/query — resolves the post collection and exposes it via block context
 * for post-template / query-title / query-pagination children.
 */
final class QueryHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $query = is_array($attributes['query'] ?? null) ? $attributes['query'] : [];

        $criteria = $this->buildQueryCriteria($query);

        $repository = $this->context($block)->get('post_repository');

        if ($repository === null || !method_exists($repository, 'find')) {
            throw new \PrestoWorld\Gutenberg\Exceptions\GutenbergException(
                'core/query requires a post_repository in the render context.'
            );
        }

        /** @var list<array<string, mixed>> $posts */
        $posts = $repository->find($criteria);

        // Pagination info for query-pagination children.
        $page = max(1, (int) ($query['page'] ?? 1));
        $total = 0;

        if (method_exists($repository, 'count')) {
            $total = (int) $repository->count($criteria);
        } else {
            $total = count($posts);
        }

        $this->context($block)->set('query_posts', $posts);
        $this->context($block)->set('query_total', $total);
        $this->context($block)->set('query_page', $page);
        $this->context($block)->set('query_per_page', $criteria['per_page']);

        // Expose query context to descendants via block context map in metadata.
        $block->context['query'] = $query;
        $block->context['queryId'] = $block->attributes['queryId'] ?? 0;

        return sprintf('<div%s>%s</div>', $this->wrapper($block), $content);
    }
}
