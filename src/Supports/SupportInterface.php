<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Supports;

use PrestoWorld\Gutenberg\Block\BlockInstance;

/**
 * A block support (Strategy) contributing classes/styles to the wrapper.
 */
interface SupportInterface
{
    /**
     * @return list<string>
     */
    public function classes(BlockInstance $block): array;

    /**
     * @return list<string> CSS declarations, e.g. "line-height: 1.4"
     */
    public function styles(BlockInstance $block): array;
}
