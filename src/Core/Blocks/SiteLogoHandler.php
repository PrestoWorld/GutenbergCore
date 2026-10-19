<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class SiteLogoHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $logo = $this->context($block)->get('site_logo');

        if (!is_array($logo) || !isset($logo['url'])) {
            return '';
        }

        $width = isset($attributes['width']) && is_numeric($attributes['width']) ? (int) $attributes['width'] : null;
        $sizeAttr = $width !== null ? ' width="' . $width . '"' : '';
        $className = ($attributes['shouldSyncIcon'] ?? false) ? ' is-style-default' : '';

        return sprintf(
            '<div%s><img src="%s" alt="%s"%s /></div>',
            $this->wrapper($block, ['class' => trim('wp-block-site-logo' . $className)]),
            $this->esc((string) $logo['url']),
            $this->esc((string) ($logo['alt'] ?? '')),
            $sizeAttr
        );
    }
}
