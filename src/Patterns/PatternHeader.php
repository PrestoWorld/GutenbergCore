<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Patterns;

/**
 * Parses the docblock header carried by theme pattern files.
 *
 * Mirrors core's `_get_theme_block_pattern_header()`: the first matching
 * `Pattern Name:` (or legacy `Title:`) line anchors the header; every
 * `Key: value` line in that block becomes a field.
 */
final class PatternHeader
{
    /**
     * @return array<string, string> lower-cased keys => raw values
     */
    public function parse(string $fileContents): array
    {
        $pattern = '/^(?:[ \t]*<\?php)?[ \t\/*#@]*' . 'Pattern Name:\s*(.*)$/mi';

        if (!preg_match($pattern, $fileContents, $matches)) {
            $pattern = '/^(?:[ \t]*<\?php)?[ \t\/*#@]*' . 'Title:\s*(.*)$/mi';

            if (!preg_match($pattern, $fileContents, $matches)) {
                return [];
            }
        }

        $headerText = $matches[0];
        $headers = [];

        foreach (explode("\n", $headerText) as $line) {
            if (!preg_match('/[ \t\/*#@]*(?P<key>[A-Za-z0-9 _\-]+?):[ \t]*(?P<value>.*)/', trim($line), $m)) {
                continue;
            }

            $key = strtolower(trim($m['key']));
            $value = trim($m['value']);

            if ($key !== '' && $value !== '') {
                $headers[$key] = $value;
            }
        }

        return $headers;
    }
}
