<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core\Blocks;

use PrestoWorld\Gutenberg\Block\BlockInstance;
use PrestoWorld\Gutenberg\Block\Metadata\MetadataLoader;
use PrestoWorld\Gutenberg\Block\Type\BlockTypeFactory;
use PrestoWorld\Gutenberg\Block\Type\BlockTypeRegistry;
use PrestoWorld\Gutenberg\Exceptions\UnhandledBlockException;

/**
 * Registers every core block shipped with the package (metadata mirror).
 *
 * Resolution order per block:
 *   1. mapped handler class            → dynamic, server-rendered
 *   2. STATIC_OVERRIDES                → treated as static even if core has a callback
 *   3. core dynamic block, no handler  → throwing callback (fail loud)
 *   4. otherwise                       → static (markup passthrough)
 */
final class Registrar
{
    /**
     * slug => handler class (dynamic, needs server data).
     *
     * @var array<string, class-string<BlockHandlerInterface>>
     */
    public const HANDLERS = [
        'avatar' => AvatarHandler::class,
        'comment-author-name' => CommentAuthorNameHandler::class,
        'comment-content' => CommentContentHandler::class,
        'comment-date' => CommentDateHandler::class,
        'comment-edit-link' => CommentEditLinkHandler::class,
        'comment-reply-link' => CommentReplyLinkHandler::class,
        'comment-template' => CommentTemplateHandler::class,
        'comments' => CommentsHandler::class,
        'comments-pagination' => CommentsPaginationHandler::class,
        'comments-pagination-next' => CommentsPaginationNextHandler::class,
        'comments-pagination-numbers' => CommentsPaginationNumbersHandler::class,
        'comments-pagination-previous' => CommentsPaginationPreviousHandler::class,
        'comments-title' => CommentsTitleHandler::class,
        'navigation' => NavigationHandler::class,
        'navigation-link' => NavigationLinkHandler::class,
        'page-list' => PageListHandler::class,
        'pattern' => PatternHandler::class,
        'post-author-name' => PostAuthorNameHandler::class,
        'post-comments-form' => PostCommentsFormHandler::class,
        'post-content' => PostContentHandler::class,
        'post-date' => PostDateHandler::class,
        'post-excerpt' => PostExcerptHandler::class,
        'post-featured-image' => PostFeaturedImageHandler::class,
        'post-navigation-link' => PostNavigationLinkHandler::class,
        'post-template' => PostTemplateHandler::class,
        'post-terms' => PostTermsHandler::class,
        'post-title' => PostTitleHandler::class,
        'query' => QueryHandler::class,
        'query-no-results' => QueryNoResultsHandler::class,
        'query-pagination' => QueryPaginationHandler::class,
        'query-pagination-next' => QueryPaginationNextHandler::class,
        'query-pagination-numbers' => QueryPaginationNumbersHandler::class,
        'query-pagination-previous' => QueryPaginationPreviousHandler::class,
        'query-title' => QueryTitleHandler::class,
        'search' => SearchHandler::class,
        'site-logo' => SiteLogoHandler::class,
        'site-tagline' => SiteTaglineHandler::class,
        'site-title' => SiteTitleHandler::class,
        'social-link' => SocialLinkHandler::class,
        'template-part' => TemplatePartHandler::class,
        'term-description' => TermDescriptionHandler::class,
    ];

    /**
     * Core blocks that have a render callback upstream but whose serialized
     * markup is self-sufficient here — rendered statically on purpose.
     */
    public const STATIC_OVERRIDES = [
        'button', 'cover', 'details', 'file', 'gallery', 'heading', 'image',
        'list', 'media-text', 'paragraph', 'video', 'accordion', 'accordion-item',
        'icon', 'navigation-overlay-close', 'page-list-item', 'read-more',
    ];

    public static function register(
        BlockTypeRegistry $registry,
        MetadataLoader $metadata,
        ?string $metadataDir = null,
    ): void {
        $dir = $metadataDir ?? $metadata->dir();

        if (!is_dir($dir)) {
            throw UnhandledBlockException::forName('*', 'metadata directory missing: ' . $dir);
        }

        foreach (scandir($dir) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $file = $dir . '/' . $entry . '/block.json';

            if (!is_file($file)) {
                continue;
            }

            $decoded = json_decode((string) file_get_contents($file), true);

            if (!is_array($decoded) || !isset($decoded['name']) || !is_string($decoded['name'])) {
                continue;
            }

            $name = $decoded['name'];
            $slug = $entry;

            $callback = self::callbackFor($name, $slug, $decoded);

            $registry->register($name, BlockTypeFactory::fromMetadata($name, $decoded, $callback));
        }
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private static function callbackFor(string $name, string $slug, array $metadata): ?\Closure
    {
        if (isset(self::HANDLERS[$slug])) {
            $class = self::HANDLERS[$slug];

            return static function (array $attributes, string $content, BlockInstance $block) use ($class): string {
                return (new $class())->render($attributes, $content, $block);
            };
        }

        $isCoreDynamic = self::isDynamicInCore($slug);

        if ($isCoreDynamic && !in_array($slug, self::STATIC_OVERRIDES, true)) {
            return static function () use ($name): string {
                throw UnhandledBlockException::forName($name);
            };
        }

        return null;
    }

    /**
     * Dynamic core blocks (upstream render callback exists) — see the
     * package's Core/metadata mirror + wp-includes/blocks top-level files.
     *
     * @param list<string> $list
     */
    private static function isDynamicInCore(string $slug, ?array $list = null): bool
    {
        static $dynamic = null;

        if ($dynamic === null) {
            $dynamic = array_flip([
                'accordion', 'accordion-item', 'archives', 'avatar', 'block', 'breadcrumbs',
                'button', 'calendar', 'categories', 'comment-author-name', 'comment-content',
                'comment-date', 'comment-edit-link', 'comment-reply-link', 'comment-template',
                'comments', 'comments-pagination', 'comments-pagination-next',
                'comments-pagination-numbers', 'comments-pagination-previous', 'comments-title',
                'cover', 'details', 'file', 'footnotes', 'gallery', 'heading', 'home-link',
                'icon', 'image', 'latest-comments', 'latest-posts', 'legacy-widget', 'list',
                'loginout', 'media-text', 'navigation', 'navigation-link',
                'navigation-overlay-close', 'navigation-submenu', 'page-list', 'page-list-item',
                'paragraph', 'pattern', 'post-author', 'post-author-biography',
                'post-author-name', 'post-comments-count', 'post-comments-form',
                'post-comments-link', 'post-content', 'post-date', 'post-excerpt',
                'post-featured-image', 'post-navigation-link', 'post-template', 'post-terms',
                'post-time-to-read', 'post-title', 'query', 'query-no-results',
                'query-pagination', 'query-pagination-next', 'query-pagination-numbers',
                'query-pagination-previous', 'query-title', 'query-total', 'read-more', 'rss',
                'search', 'shortcode', 'site-logo', 'site-tagline', 'site-title', 'social-link',
                'tag-cloud', 'template-part', 'term-count', 'term-description', 'term-name',
                'term-template', 'video', 'widget-group',
            ]);
        }

        return isset($dynamic[$slug]);
    }
}
