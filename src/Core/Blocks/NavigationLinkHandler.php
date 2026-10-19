<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class NavigationLinkHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $label = $this->stringAttr($attributes, 'label');
        $url = $this->stringAttr($attributes, 'url', '#');

        if ($label === '') {
            // Resolve from an id reference (page/post).
            $id = (int) ($attributes['id'] ?? 0);

            if ($id > 0) {
                $resolver = $this->context($block)->get('post_resolver');

                if (is_callable($resolver) && ($resolved = $resolver($id)) !== null && is_array($resolved)) {
                    $label = (string) ($resolved['title'] ?? '');
                    $slug = (string) ($resolved['slug'] ?? '');
                    $url = '/' . ltrim($slug, '/');
                }
            }

            if ($label === '') {
                return '';
            }
        }

        $isTopLevel = (bool) ($attributes['isTopLevelLink'] ?? false);
        $classes = ['wp-block-navigation-item'];

        if ($isTopLevel) {
            $classes[] = 'has-child';
        }

        $rel = $this->stringAttr($attributes, 'rel');
        $target = !empty($attributes['opensInNewTab']) ? ' target="_blank" rel="noopener"' : '';
        $title = $this->stringAttr($attributes, 'title');

        $desc = $this->stringAttr($attributes, 'description');

        $html = '<li class="' . $this->esc(implode(' ', $classes)) . '">'
            . '<a href="' . $this->esc($url) . '" class="wp-block-navigation-item__content"'
            . ($rel !== '' ? ' rel="' . $this->esc($rel) . '"' : '')
            . $target
            . ($title !== '' ? ' title="' . $this->esc($title) . '"' : '')
            . '><span>' . $this->esc($label) . '</span></a>';

        if ($desc !== '') {
            $html .= '<span class="wp-block-navigation-item__description">' . $this->esc($desc) . '</span>';
        }

        $html .= '</li>';

        return $html;
    }
}
