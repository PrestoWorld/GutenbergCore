<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Patterns\Source;

use PrestoWorld\Gutenberg\Contracts\PatternSourceInterface;
use PrestoWorld\Gutenberg\Patterns\Pattern;
use PrestoWorld\Gutenberg\Patterns\PatternHeader;

/**
 * Discovers patterns from `{theme}/patterns/*.php` (Strategy: theme files).
 *
 * Pattern PHP is executed in an output-buffered include so the embedded
 * `<?php echo esc_html_e(...) ?>` snippets produce their HTML — the same
 * contract as core's file-based patterns.
 */
final class ThemeFilePatternSource implements PatternSourceInterface
{
    private PatternHeader $headerParser;

    public function __construct(
        private readonly string $themeDir,
        private readonly ?string $defaultNamespace = null,
        ?PatternHeader $headerParser = null,
    ) {
        $this->headerParser = $headerParser ?? new PatternHeader();
    }

    public function discover(): array
    {
        $dir = rtrim($this->themeDir, '/') . '/patterns';

        if (!is_dir($dir)) {
            return [];
        }

        $files = glob($dir . '/*.php') ?: [];
        $patterns = [];

        foreach ($files as $file) {
            $pattern = $this->load($file);

            if ($pattern !== null) {
                $patterns[] = $pattern;
            }
        }

        return $patterns;
    }

    private function load(string $file): ?Pattern
    {
        $contents = (string) file_get_contents($file);
        $headers = $this->headerParser->parse($contents);

        if (!isset($headers['title'])) {
            return null;
        }

        $slug = $headers['slug']
            ?? (($this->defaultNamespace ?? basename($this->themeDir)) . '/' . basename($file, '.php'));

        return new Pattern(
            slug: $slug,
            title: $headers['title'],
            content: $this->evaluate($file),
            categories: $this->splitList($headers['categories'] ?? ''),
            blockTypes: $this->splitList($headers['block types'] ?? ''),
            viewportWidth: isset($headers['viewport width']) && is_numeric($headers['viewport width'])
                ? (int) $headers['viewport width']
                : null,
            inserter: strtolower($headers['inserter'] ?? 'yes') !== 'no',
            description: $headers['description'] ?? null,
            keywords: $this->splitList($headers['keywords'] ?? ''),
            templateTypes: $this->splitList($headers['template types'] ?? ''),
            postTypes: $this->splitList($headers['post types'] ?? ''),
            sourceFile: $file,
        );
    }

    private function evaluate(string $file): string
    {
        ob_start();

        try {
            (static function (string $__patternFile): void {
                include $__patternFile;
            })($file);
        } finally {
            $html = (string) ob_get_clean();
        }

        return $html;
    }

    /**
     * @return list<string>
     */
    private function splitList(string $value): array
    {
        if (trim($value) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }
}
