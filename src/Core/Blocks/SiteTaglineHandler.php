<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class SiteTaglineHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $tagline = (string) $this->context($block)->get('site_tagline', get_option('blogdescription') ?: '');

        if ($tagline === '') {
            return '';
        }

        return '<p' . $this->wrapper($block) . '>' . $this->esc($tagline) . '</p>';
    }
}
