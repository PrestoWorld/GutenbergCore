<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class TermDescriptionHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $term = $this->context($block)->get('current_term');

        if (!is_array($term) || trim((string) ($term['description'] ?? '')) === '') {
            return '';
        }

        return '<div' . $this->wrapper($block) . '>' . wpautop((string) $term['description']) . '</div>';
    }
}
