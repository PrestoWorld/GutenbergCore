<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;
use PrestoWorld\Gutenberg\Exceptions\GutenbergException;
use PrestoWorld\Gutenberg\Rendering\RenderContext;

/**
 * Shared helpers for core block handlers.
 */
abstract class AbstractHandler implements BlockHandlerInterface
{
    protected function context(BlockInstance $block): RenderContext
    {
        return $block->renderer()->renderContext();
    }

    /**
     * Resolve the post a dynamic block should read, if any.
     *
     * @return array<string, mixed>|null
     */
    protected function currentPost(BlockInstance $block): ?array
    {
        $post = $block->context['post'] ?? null;

        if (is_array($post)) {
            return $post;
        }

        $postId = $block->context['postId'] ?? null;

        if ($postId === null || !is_numeric($postId)) {
            $post = $this->context($block)->currentPost();

            return is_array($post) ? $post : null;
        }

        $resolver = $this->context($block)->get('post_resolver');

        if (is_callable($resolver)) {
            $resolved = $resolver((int) $postId);

            return is_array($resolved) ? $resolved : null;
        }

        $repository = $this->context($block)->get('post_repository');

        if (is_object($repository) && method_exists($repository, 'find')) {
            $rows = $repository->find(['id' => (int) $postId, 'per_page' => 1]);

            return is_array($rows[0] ?? null) ? $rows[0] : null;
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function repository(BlockInstance $block): array
    {
        $repository = $this->context($block)->get('post_repository');

        if (!is_object($repository)) {
            throw new GutenbergException(
                'No post_repository is available in the render context; '
                . 'wire PrestoWorld\\Modules\\Schema\\PostRepository into the Gutenberg kernel.'
            );
        }

        return ['object' => $repository];
    }

    protected function stringAttr(array $attributes, string $key, string $default = ''): string
    {
        $value = $attributes[$key] ?? null;

        return is_scalar($value) ? (string) $value : $default;
    }

    /**
     * Build PostRepository criteria from a core/query `query` attribute array.
     *
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    protected function buildQueryCriteria(array $query): array
    {
        $criteria = [
            'post_type' => !empty($query['postType']) ? (array) $query['postType'] : ['post', 'page'],
            'status' => 'publish',
            'order' => strtoupper((string) ($query['order'] ?? 'DESC')),
            'orderby' => $query['orderBy'] ?? 'date',
            'per_page' => max(1, (int) ($query['perPage'] ?? 10)),
            'offset' => max(0, (int) ($query['offset'] ?? 0)),
        ];

        if (!empty($query['search'])) {
            $criteria['search'] = (string) $query['search'];
        }

        if (!empty($query['author'])) {
            $criteria['author_id'] = (int) $query['author'];
        }

        return $criteria;
    }

    /**
     * Resolve the post collection for a `query` attribute array via the
     * post_repository in the render context. Returns [] when unavailable.
     *
     * @param array<string, mixed> $query
     * @return list<array<string, mixed>>
     */
    protected function resolveQueryPosts(BlockInstance $block, array $query): array
    {
        $repository = $this->context($block)->get('post_repository');

        if (!is_object($repository) || !method_exists($repository, 'find')) {
            return [];
        }

        /** @var list<array<string, mixed>> $posts */
        $posts = $repository->find($this->buildQueryCriteria($query));

        return $posts;
    }

    protected function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    protected function wrapper(BlockInstance $block, array $extra = []): string
    {
        return $block->renderer()->wrapper()->build($block, $extra);
    }
}
