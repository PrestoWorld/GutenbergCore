<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Assets;

/**
 * A stylesheet asset.
 */
final class StyleAsset
{
    /**
     * @param list<string> $dependencies
     */
    public function __construct(
        public readonly string $handle,
        public readonly string $src = '',
        public readonly array $dependencies = [],
        public readonly string|bool|null $version = null,
        public readonly string $media = 'all',
        public readonly ?string $inlineCss = null,
    ) {
    }
}
