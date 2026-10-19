<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Templates;

use PrestoWorld\Gutenberg\Contracts\TemplateSourceInterface;
use PrestoWorld\Gutenberg\Exceptions\TemplateNotFoundException;

/**
 * Template repository + simplified WP template hierarchy (Repository + Strategy).
 *
 * Resolution order is intentionally small but explicit: the exact requested
 * name first, then the WP block-theme fallbacks, finally `index`.
 */
final class TemplateRepository
{
    /** @var array<string, list<string>> */
    private const FALLBACKS = [
        'single' => ['index'],
        'page' => ['index'],
        'home' => ['index'],
        'front-page' => ['home', 'index'],
        'archive' => ['index'],
        'search' => ['index'],
        '404' => ['index'],
        'index' => [],
    ];

    public function __construct(
        private readonly TemplateSourceInterface $source,
    ) {
    }

    public function find(string $slug): ?Template
    {
        return $this->source->findTemplate($slug);
    }

    public function resolve(string $slug): Template
    {
        $template = $this->find($slug);

        if ($template !== null) {
            return $template;
        }

        foreach (self::FALLBACKS[$slug] ?? ['index'] as $fallback) {
            $template = $this->find($fallback);

            if ($template !== null) {
                return $template;
            }
        }

        $all = $this->source->allTemplates();

        if ($all === []) {
            throw TemplateNotFoundException::named($slug, '(theme has no templates/*.html)');
        }

        // Last resort: first available template.
        return reset($all);
    }

    public function part(string $slug): TemplatePart
    {
        $part = $this->source->findPart($slug);

        if ($part === null) {
            throw TemplateNotFoundException::named('part:' . $slug, '(theme has no matching parts/*.html)');
        }

        return $part;
    }

    public function source(): TemplateSourceInterface
    {
        return $this->source;
    }
}
