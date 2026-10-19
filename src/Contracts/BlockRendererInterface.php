<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Contracts;

/**
 * Renders a parsed block tree into HTML.
 */
interface BlockRendererInterface
{
    /**
     * @param array<int, array<string, mixed>|\PrestoWorld\Gutenberg\Parser\Node\ParsedNodeInterface> $blocks
     * @param array<string, mixed> $context
     */
    public function render(array $blocks, array $context = []): string;
}
