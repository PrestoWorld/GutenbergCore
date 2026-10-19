<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Compatibility;

/**
 * Post formats (aside/audio/chat/gallery/image/link/quote/status/video).
 * Format is read from the post row's `format` column when present.
 */
final class PostFormats
{
    /** @var array<string, string> slug => label */
    private const LABELS = [
        'aside' => 'Aside',
        'audio' => 'Audio',
        'chat' => 'Chat',
        'gallery' => 'Gallery',
        'image' => 'Image',
        'link' => 'Link',
        'quote' => 'Quote',
        'status' => 'Status',
        'video' => 'Video',
    ];

    /**
     * @param array<string, mixed>|null $post
     */
    public static function slug(?array $post): string|false
    {
        if ($post === null) {
            return false;
        }

        $slug = (string) ($post['format'] ?? $post['post_format'] ?? '');

        if ($slug === '' || $slug === 'standard') {
            return false;
        }

        return isset(self::LABELS[$slug]) ? $slug : false;
    }

    public static function label(string $slug): string
    {
        return self::LABELS[$slug] ?? ucfirst($slug);
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return self::LABELS;
    }
}
