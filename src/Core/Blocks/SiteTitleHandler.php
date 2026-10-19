<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class SiteTitleHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $title = (string) $this->context($block)->get('site_title', get_option('blogname') ?: 'PrestoWorld');
        $tag = $this->stringAttr($attributes, 'level', '0') === '0' ? 'p' : 'h' . (int) $this->stringAttr($attributes, 'level', '1');
        $href = (string) $this->context($block)->get('site_url', '/');
        $isLink = (bool) ($attributes['isLink'] ?? true);

        $text = $isLink
            ? '<a href="' . $this->esc($href) . '">' . $this->esc($title) . '</a>'
            : $this->esc($title);

        return sprintf('<%1$s%2$s>%3$s</%1$s>', $tag, $this->wrapper($block), $text);
    }
}
