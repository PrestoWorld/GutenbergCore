<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Rendering;

/**
 * Per-render shared state (context base + current post tracking).
 *
 * Block render callbacks reach application services through this object,
 * e.g. `$context->get('post_repository')`.
 */
final class RenderContext
{
    /** @var array<string, mixed> */
    private array $global = [];

    /** @var array<string, mixed>|null */
    private ?array $currentPost = null;

    /**
     * @param array<string, mixed> $context
     */
    public function __construct(array $context = [])
    {
        $this->global = $context;
    }

    /**
     * @return array<string, mixed>
     */
    public function global(): array
    {
        return $this->global;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function merge(array $context): void
    {
        $this->global = array_merge($this->global, $context);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->global[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->global[$key] = $value;
    }

    /**
     * @param array<string, mixed>|null $post
     */
    public function setCurrentPost(?array $post): void
    {
        $this->currentPost = $post;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function currentPost(): ?array
    {
        return $this->currentPost;
    }

    /**
     * Context defaults injected at the root of every render.
     *
     * @return array<string, mixed>
     */
    public function baseBlockContext(): array
    {
        $context = [];

        if ($this->currentPost !== null) {
            $context['postId'] = $this->currentPost['id'] ?? $this->currentPost['ID'] ?? null;
            $context['postType'] = $this->currentPost['type'] ?? $this->currentPost['post_type'] ?? $this->currentPost['postType'] ?? 'post';
        }

        return $context;
    }
}
