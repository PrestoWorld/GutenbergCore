<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class PostTermsHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $post = $this->currentPost($block);

        if ($post === null) {
            return '';
        }

        $taxonomy = $this->stringAttr($attributes, 'term', 'category');
        $separator = $this->stringAttr($attributes, 'separator', ', ');
        $prefix = $this->stringAttr($attributes, 'prefix');
        $showTermCount = (bool) ($attributes['showTermCount'] ?? false);

        $terms = $this->fetchTerms($block, (int) ($post['id'] ?? $post['ID'] ?? 0), $taxonomy);

        if ($terms === []) {
            return '';
        }

        $links = [];

        foreach ($terms as $term) {
            $label = (string) ($term['name'] ?? '');
            $slug = (string) ($term['slug'] ?? '');
            $count = isset($term['count']) && $showTermCount ? ' (' . (int) $term['count'] . ')' : '';

            $links[] = '<a href="' . $this->esc('/' . $taxonomy . '/' . $slug) . '" rel="tag">'
                . $this->esc($label . $count) . '</a>';
        }

        $html = ($prefix !== '' ? $this->esc($prefix) . ' ' : '')
            . implode($this->esc($separator), $links);

        return '<div' . $this->wrapper($block) . '>' . $html . '</div>';
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchTerms(BlockInstance $block, int $postId, string $taxonomy): array
    {
        if ($postId <= 0) {
            return [];
        }

        $terms = $this->context($block)->get('term_repository');

        if (is_object($terms) && method_exists($terms, 'findForPost')) {
            $found = $terms->findForPost($postId, $taxonomy);

            return is_array($found) ? $found : [];
        }

        // Post rows may carry pre-resolved term lists.
        $post = $this->currentPost($block);

        if (is_array($post) && is_array($post[$taxonomy] ?? null)) {
            return $post[$taxonomy];
        }

        return [];
    }
}
