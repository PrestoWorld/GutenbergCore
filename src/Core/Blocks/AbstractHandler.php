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

    protected function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    protected function wrapper(BlockInstance $block, array $extra = []): string
    {
        return $block->renderer()->wrapper()->build($block, $extra);
    }
}
