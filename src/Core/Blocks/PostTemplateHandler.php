<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

/**
 * core/post-template — iterates the query result set set by core/query.
 */
final class PostTemplateHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $posts = $this->context($block)->get('query_posts');

        if (!is_array($posts)) {
            return '';
        }

        $tagName = $this->stringAttr($attributes, 'tagName', 'div');
        $layout = is_array($attributes['layout'] ?? null) ? $attributes['layout'] : [];

        $extra = [];

        if (!empty($layout['type']) && $layout['type'] === 'grid') {
            if (!empty($layout['minimumColumnWidth'])) {
                $extra['style'] = 'grid-template-columns:repeat(auto-fill,minmax('
                    . (float) $layout['minimumColumnWidth'] . 'ch,1fr))';
            } elseif (!empty($layout['columnCount'])) {
                $extra['style'] = 'grid-template-columns:repeat(' . (int) $layout['columnCount'] . ',1fr)';
            }
        }

        if ($posts === []) {
            $this->context($block)->set('query_has_results', false);

            return '';
        }

        $this->context($block)->set('query_has_results', true);

        $renderer = $block->renderer();
        $wrapperAttrs = $renderer->wrapper()->build($block, $extra);
        $out = [];

        foreach ($posts as $post) {
            $postId = (int) ($post['id'] ?? $post['ID'] ?? 0);
            $postType = (string) ($post['type'] ?? $post['post_type'] ?? $post['postType'] ?? 'post');

            $this->context($block)->set('current_post', $post);

            $block->context['postId'] = $postId;
            $block->context['postType'] = $postType;
            $block->context['post'] = $post;
            $block->availableContext['postId'] = $postId;
            $block->availableContext['postType'] = $postType;
            $block->availableContext['post'] = $post;

            foreach ($block->innerBlocks as $child) {
                $child->recontextualize($block->availableContext);
            }

            $out[] = $this->renderInner($block, $renderer);
        }

        $this->context($block)->set('current_post', $this->context($block)->currentPost());

        return sprintf('<%1$s%2$s>%3$s</%1$s>', $tagName, $wrapperAttrs, implode('', $out));
    }

    /**
     * Re-render inner content for the current post context.
     *
     * @return string
     */
    private function renderInner(BlockInstance $block, \PrestoWorld\Gutenberg\Rendering\BlockRenderer $renderer): string
    {
        $index = 0;
        $output = '';

        foreach ($block->innerContent as $chunk) {
            if (is_string($chunk)) {
                $output .= $chunk;
                continue;
            }

            $inner = $block->innerBlocks[$index] ?? null;

            if ($inner instanceof BlockInstance) {
                $output .= $renderer->renderBlock($inner);
            }

            ++$index;
        }

        return $output;
    }
}
