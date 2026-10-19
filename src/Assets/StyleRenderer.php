<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Assets;

use PrestoWorld\Gutenberg\ThemeJson\Css\ValueResolver;

/**
 * Renders queued styles + inline CSS into <link>/<style> markup.
 */
final class StyleRenderer
{
    public function __construct(
        private readonly StyleQueue $queue,
        private readonly ValueResolver $resolver = new ValueResolver(),
    ) {
    }

    /**
     * @param list<string> $inlineCssBlocks additional inline <style> blocks
     */
    public function render(array $inlineCssBlocks = []): string
    {
        $out = '';

        foreach ($this->queue->ordered() as $asset) {
            if ($asset->src !== '') {
                $url = $this->resolver->resolve($asset->src);

                if (str_starts_with($url, '/') || str_contains($url, '://')) {
                    $query = $asset->version !== null && $asset->version !== false
                        ? (str_contains($url, '?') ? '&' : '?') . 'ver=' . rawurlencode((string) $asset->version)
                        : '';

                    $out .= sprintf(
                        '<link rel="stylesheet" href="%s" media="%s" />' . "\n",
                        htmlspecialchars($url . $query, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                        htmlspecialchars($asset->media, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                    );
                }
            }

            if ($asset->inlineCss !== null && trim($asset->inlineCss) !== '') {
                $out .= '<style id="' . htmlspecialchars($asset->handle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '-inline-css">'
                    . "\n" . trim($asset->inlineCss) . "\n</style>\n";
            }
        }

        foreach ($inlineCssBlocks as $css) {
            if (trim((string) $css) === '') {
                continue;
            }

            $out .= '<style id="gutenberg-inline-css">' . "\n" . trim((string) $css) . "\n</style>\n";
        }

        return $out;
    }
}
