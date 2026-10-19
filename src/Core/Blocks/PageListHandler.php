<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

/**
 * core/page-list — hierarchical list of published pages.
 */
final class PageListHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $repository = $this->context($block)->get('post_repository');

        if ($repository === null || !method_exists($repository, 'find')) {
            return '';
        }

        /** @var list<array<string, mixed>> $pages */
        $pages = $repository->find([
            'post_type' => ['page'],
            'status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC',
            'per_page' => 500,
        ]);

        if ($pages === []) {
            return '';
        }

        $byParent = [];

        foreach ($pages as $page) {
            $parentId = (int) ($page['parent_id'] ?? $page['post_parent'] ?? 0);
            $byParent[$parentId][] = $page;
        }

        $tree = $this->renderLevel($byParent, 0);

        if ($tree === '') {
            return '';
        }

        $wrapper = $this->wrapper($block);

        if (($block->name() === 'core/navigation') || !empty($attributes['fallback'])) {
            // Navigation fallback output: no extra wrapper — navigation provides it.
            return $tree;
        }

        return '<ul' . $wrapper . '>' . $tree . '</ul>';
    }

    /**
     * @param array<int, list<array<string, mixed>>> $byParent
     */
    private function renderLevel(array $byParent, int $parentId): string
    {
        $html = '';

        foreach ($byParent[$parentId] ?? [] as $page) {
            $id = (int) ($page['id'] ?? $page['ID'] ?? 0);
            $slug = (string) ($page['slug'] ?? $page['post_name'] ?? '');
            $title = (string) ($page['title'] ?? $page['post_title'] ?? '');

            $children = $this->renderLevel($byParent, $id);

            $html .= '<li class="wp-block-pages-list__item'
                . ($children !== '' ? ' has-child' : '') . '">'
                . '<a class="wp-block-pages-list__item__link" href="' . $this->esc('/' . ltrim($slug, '/')) . '">'
                . $this->esc($title) . '</a>';

            if ($children !== '') {
                $html .= '<ul class="wp-block-pages-list__item__submenu-container">' . $children . '</ul>';
            }

            $html .= '</li>';
        }

        return $html;
    }
}
