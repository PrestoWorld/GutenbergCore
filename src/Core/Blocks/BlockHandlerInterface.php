<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

/**
 * Render handler for a single core block (Strategy).
 */
interface BlockHandlerInterface
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function render(array $attributes, string $content, BlockInstance $block): string;
}
