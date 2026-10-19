<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;
use PrestoWorld\Gutenberg\Exceptions\TemplateNotFoundException;

final class TemplatePartHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $slug = $this->stringAttr($attributes, 'slug');

        if ($slug === '') {
            // An inline (unsaved) template part renders its inner content.
            return $content;
        }

        $repository = $this->context($block)->get('template_repository');

        if ($repository === null) {
            throw TemplateNotFoundException::named('part:' . $slug, 'no template repository in render context');
        }

        try {
            $part = $repository->part($slug);
        } catch (TemplateNotFoundException) {
            // Fall back to the inner content stored in the template markup.
            return $content;
        }

        $parser = $this->context($block)->get('block_parser');

        if ($parser === null) {
            throw TemplateNotFoundException::named('part:' . $slug, 'no block_parser in render context');
        }

        $renderer = $block->renderer();

        $extra = [];
        if ($part->area !== null) {
            $extra['data-area'] = $part->area;
        }

        return sprintf(
            '<div%s>%s</div>',
            $this->wrapper($block, $extra),
            $renderer->render($parser->parse($part->content))
        );
    }
}
