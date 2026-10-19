<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;

final class QueryTitleHandler extends AbstractHandler
{
    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $query = $block->context['query'] ?? (is_array($attributes['query'] ?? null) ? $attributes['query'] : []);
        $type = $this->stringAttr($attributes, 'queryType', 'archive');

        if ($type === 'search') {
            $text = sprintf(
                function_exists('__') ? \__('Results for: %s', 'twentytwentyfive') : 'Results for: %s',
                '<span>' . $this->esc((string) ($_GET['s'] ?? '')) . '</span>'
            );
        } else {
            // Archive title derived from query filters.
            $text = $this->stringAttr($attributes, 'level');

            $postType = is_array($query) && !empty($query['postType'])
                ? (string) reset((array) $query['postType'])
                : 'post';

            $postTypes = $this->context($block)->get('post_type_labels');

            if (is_array($postTypes) && isset($postTypes[$postType])) {
                $label = is_array($postTypes[$postType])
                    ? (string) ($postTypes[$postType]['label'] ?? $postType)
                    : (string) $postTypes[$postType];
            } else {
                $label = ucfirst(str_replace(['-', '_'], ' ', $postType));
            }

            $text = $label;
        }

        $level = (int) ($attributes['level'] ?? 2);
        $tag = $level >= 1 && $level <= 6 ? 'h' . $level : 'p';

        return sprintf('<%1$s%2$s>%3$s</%1$s>', $tag, $this->wrapper($block), $text);
    }
}
