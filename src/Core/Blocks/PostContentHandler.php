<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class PostContentHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $post = $this->currentPost($block);

        if ($post === null) {
            return '';
        }

        $raw = (string) ($post['content'] ?? $post['post_content'] ?? '');

        if (trim($raw) === '') {
            return '';
        }

        // Render the stored block markup through the pipeline.
        if (function_exists('do_blocks')) {
            $rendered = \do_blocks($raw);
        } else {
            $parser = $this->context($block)->get('block_parser');
            $rendered = $parser !== null
                ? $block->renderer()->render($parser->parse($raw))
                : $raw;
        }

        return sprintf('<div%s>%s</div>', $this->wrapper($block), $rendered);
    }
}
