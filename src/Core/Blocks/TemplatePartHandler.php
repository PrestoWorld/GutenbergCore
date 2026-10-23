<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;
use PrestoWorld\Gutenberg\Exceptions\TemplateNotFoundException;

final class TemplatePartHandler extends AbstractHandler
{
    /**
     * Fragment-cache bridge do host đăng ký lúc boot (app owns policy:
     * enabled/TTL/salt/gen/stats — vendor chỉ gọi). Null → không cache.
     *
     * @var (callable(string, array<string, mixed>): ?string)|null
     */
    public static $cacheLookup = null;

    /**
     * @var (callable(string, array<string, mixed>, string): void)|null
     */
    public static $cacheStore = null;

    public function render(array $attributes, string $content, BlockInstance $block): string
    {
        $slug = $this->stringAttr($attributes, 'slug');

        if ($slug === '') {
            // An inline (unsaved) template part renders its inner content.
            return $content;
        }

        $lookup = self::$cacheLookup;
        if ($lookup !== null) {
            $cached = $lookup($slug, $attributes);
            if ($cached !== null) {
                return $cached;
            }
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

        $html = sprintf(
            '<div%s>%s</div>',
            $this->wrapper($block, $extra),
            $renderer->render($parser->parse($part->content))
        );

        $store = self::$cacheStore;
        if ($store !== null) {
            $store($slug, $attributes, $html);
        }

        return $html;
    }
}
