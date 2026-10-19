<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Contracts;

use PrestoWorld\Gutenberg\Block\BlockInstance;

/**
 * Strategy for producing the final HTML of a single block instance.
 */
interface RenderHandlerInterface
{
    public function render(BlockInstance $block): string;
}
