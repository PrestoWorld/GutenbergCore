<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class PatternHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $slug = $this->stringAttr($attributes, 'slug');

        if ($slug === '') {
            // A pattern stored inline in the markup.
            return $content;
        }

        $registry = $this->context($block)->get('pattern_registry');
        $parser = $this->context($block)->get('block_parser');

        if ($registry === null || $parser === null) {
            throw new \PrestoWorld\Gutenberg\Exceptions\GutenbergException(sprintf(
                'Pattern block references "%s" but no pattern_registry/block_parser is wired into the render context.',
                $slug
            ));
        }

        $pattern = $registry->get($slug);

        if ($pattern === null) {
            // Pattern may be a hidden theme file not yet registered — no output.
            return '';
        }

        return $block->renderer()->render($parser->parse($pattern->content));
    }
}
