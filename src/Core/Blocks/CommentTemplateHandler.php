<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

/**
 * core/comment-template — iterates the comment list for the current post.
 */
final class CommentTemplateHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $comments = $this->context($block)->get('post_comments');

        if (!is_array($comments) || $comments === []) {
            return '';
        }

        $tagName = $this->stringAttr($attributes, 'tagName', 'ol');
        $wrapperAttrs = $block->renderer()->wrapper()->build($block);
        $out = [];
        $base = $block->availableContext;

        foreach ($comments as $comment) {
            $commentId = (int) ($comment['id'] ?? $comment['comment_ID'] ?? 0);

            $block->context['commentId'] = $commentId;
            $block->availableContext = array_merge($base, [
                'commentId' => $commentId,
                'comment' => $comment,
            ]);

            foreach ($block->innerBlocks as $child) {
                $child->recontextualize($block->availableContext);
            }

            $this->context($block)->set('current_comment', $comment);
            $out[] = $this->renderInner($block);
        }

        $block->availableContext = $base;
        $this->context($block)->set('current_comment', null);

        return sprintf('<%1$s%2$s>%3$s</%1$s>', $tagName, $wrapperAttrs, implode('', $out));
    }

    private function renderInner(BlockInstance $block): string
    {
        $renderer = $block->renderer();
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
