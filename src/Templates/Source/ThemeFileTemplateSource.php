<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Templates\Source;

use PrestoWorld\Gutenberg\Contracts\TemplateSourceInterface;
use PrestoWorld\Gutenberg\Templates\Template;
use PrestoWorld\Gutenberg\Templates\TemplatePart;

/**
 * Reads templates and parts straight from the theme directory.
 */
final class ThemeFileTemplateSource implements TemplateSourceInterface
{
    /** @var array<string, Template>|null */
    private ?array $templates = null;

    /** @var array<string, TemplatePart>|null */
    private ?array $parts = null;

    /**
     * @param array<string, array<string, mixed>> $partMeta areas/titles from theme.json templateParts
     */
    public function __construct(
        private readonly string $themeDir,
        private readonly array $partMeta = [],
    ) {
    }

    public function findTemplate(string $slug): ?Template
    {
        return $this->templates()[$slug] ?? null;
    }

    public function allTemplates(): array
    {
        return $this->templates();
    }

    public function findPart(string $slug): ?TemplatePart
    {
        return $this->parts()[$slug] ?? null;
    }

    public function allParts(): array
    {
        return $this->parts();
    }

    /**
     * @return array<string, Template>
     */
    private function templates(): array
    {
        if ($this->templates !== null) {
            return $this->templates;
        }

        $dir = rtrim($this->themeDir, '/') . '/templates';
        $this->templates = [];

        foreach (glob($dir . '/*.html') ?: [] as $file) {
            $slug = basename($file, '.html');
            $this->templates[$slug] = new Template(
                slug: $slug,
                content: (string) file_get_contents($file),
                sourceFile: $file,
            );
        }

        return $this->templates;
    }

    /**
     * @return array<string, TemplatePart>
     */
    private function parts(): array
    {
        if ($this->parts !== null) {
            return $this->parts;
        }

        $dir = rtrim($this->themeDir, '/') . '/parts';
        $this->parts = [];

        foreach (glob($dir . '/*.html') ?: [] as $file) {
            $slug = basename($file, '.html');
            $meta = $this->partMeta[$slug] ?? [];

            $this->parts[$slug] = new TemplatePart(
                slug: $slug,
                content: (string) file_get_contents($file),
                title: isset($meta['title']) ? (string) $meta['title'] : null,
                area: isset($meta['area']) ? (string) $meta['area'] : null,
                sourceFile: $file,
            );
        }

        return $this->parts;
    }
}
