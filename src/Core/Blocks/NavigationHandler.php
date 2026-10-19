<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

/**
 * core/navigation — renders stored links, inline children, or a page-list fallback.
 */
final class NavigationHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        // Inline children present: keep the rendered markup (links stored in template).
        if (trim($content) !== '') {
            return '<div' . $this->wrapper($block) . '><ul class="wp-block-navigation__container">'
                . $content . '</ul></div>';
        }

        // `ref` — resolve a stored navigation menu.
        $ref = (int) ($attributes['ref'] ?? 0);

        if ($ref > 0) {
            $menus = $this->context($block)->get('navigation_repository');

            if (is_object($menus) && method_exists($menus, 'getItems')) {
                $items = $menus->getItems($ref);

                if (is_array($items) && $items !== []) {
                    return '<div' . $this->wrapper($block) . '><ul class="wp-block-navigation__container">'
                        . $this->renderItems($block, $items) . '</ul></div>';
                }
            }
        }

        // Fallback: page list.
        $fallback = new PageListHandler();

        return $fallback->render(['fallback' => true], '', $block);
    }

    /**
     * @param list<array<string, mixed>> $items
     */
    private function renderItems(BlockInstance $block, array $items): string
    {
        $html = '';

        foreach ($items as $item) {
            $label = $this->esc((string) ($item['label'] ?? $item['title'] ?? ''));
            $url = $this->esc((string) ($item['url'] ?? $item['link'] ?? '/'));
            $children = is_array($item['children'] ?? null) ? $item['children'] : [];

            $sub = '';

            if ($children !== []) {
                $sub = '<ul class="wp-block-navigation__submenu-container">'
                    . $this->renderItems($block, $children) . '</ul>';
            }

            $html .= '<li class="wp-block-navigation-item">'
                . '<a href="' . $url . '" class="wp-block-navigation-item__content"><span>' . $label . '</span></a>'
                . $sub . '</li>';
        }

        return $html;
    }
}
