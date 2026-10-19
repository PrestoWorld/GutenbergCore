<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Bindings;

use PrestoWorld\Gutenberg\Block\BlockInstance;

/**
 * Processes `metadata.bindings` on a block instance and rewrites the
 * rendered HTML afterwards (port of WP_Block::process_block_bindings +
 * replace_html, without the HTML API dependency).
 */
final class BindingsProcessor
{
    public function __construct(
        private readonly BindingsRegistry $registry,
    ) {
    }

    /**
     * @return array<string, mixed> computed attributes from bound sources
     */
    public function process(BlockInstance $block): array
    {
        $computed = [];

        $bindings = $block->parsedBlock['attrs']['metadata']['bindings'] ?? null;

        if (!is_array($bindings) || $bindings === []) {
            return $computed;
        }

        $supported = $this->supportedAttributes($block);

        foreach ($bindings as $attributeName => $binding) {
            if (!is_string($attributeName) || !in_array($attributeName, $supported, true)) {
                continue;
            }

            if (!is_array($binding) || !isset($binding['source']) || !is_string($binding['source'])) {
                continue;
            }

            $source = $this->registry->get($binding['source']);

            if ($source === null) {
                continue;
            }

            foreach ($source->usesContext as $contextName) {
                if (array_key_exists($contextName, $block->availableContext)) {
                    $block->context[$contextName] = $block->availableContext[$contextName];
                }
            }

            $args = is_array($binding['args'] ?? null) ? $binding['args'] : [];
            $value = $source->getValue($args, $block, $attributeName);

            if ($value !== null) {
                $computed[$attributeName] = $value;
            }
        }

        return $computed;
    }

    /**
     * Replace a bound attribute value inside the rendered HTML.
     *
     * @param mixed $value
     */
    public function replaceHtml(string $content, string $attributeName, mixed $value, BlockInstance $block): string
    {
        $schema = $block->blockType?->attributes[$attributeName] ?? null;

        if (!is_array($schema) || !isset($schema['source'])) {
            return $content;
        }

        $source = (string) $schema['source'];

        if ($source === 'html' || $source === 'rich-text') {
            $selector = isset($schema['selector']) && is_string($schema['selector'])
                ? $schema['selector']
                : null;

            return $this->replaceRichText($content, $selector, (string) $value);
        }

        if ($source === 'attribute') {
            $tag = isset($schema['selector']) && is_string($schema['selector']) ? $schema['selector'] : null;
            $attribute = isset($schema['attribute']) && is_string($schema['attribute']) ? $schema['attribute'] : null;

            if ($attribute === null) {
                return $content;
            }

            return $this->setAttribute($content, $tag, $attribute, (string) $value);
        }

        return $content;
    }

    /**
     * @return list<string>
     */
    private function supportedAttributes(BlockInstance $block): array
    {
        $blockType = $block->blockType;

        if ($blockType === null) {
            return [];
        }

        $supported = [];

        foreach ($blockType->attributes as $name => $schema) {
            if (is_array($schema) && isset($schema['source'])) {
                $supported[] = $name;
            }
        }

        return $supported;
    }

    private function replaceRichText(string $content, ?string $selector, string $value): string
    {
        $dom = $this->loadFragment($content);

        if ($dom === null) {
            return $content;
        }

        $candidates = $selector !== null && $selector !== ''
            ? array_filter(array_map('trim', explode(',', $selector)))
            : [];

        $target = null;

        foreach ($dom->getElementsByTagName('*') as $element) {
            /** @var \DOMElement $element */
            $tag = strtolower($element->tagName);

            if ($candidates === []) {
                $target = $element;
                break;
            }

            foreach ($candidates as $candidate) {
                if (strcasecmp($tag, $candidate) === 0) {
                    $target = $element;
                    break 2;
                }
            }
        }

        if ($target === null) {
            return $content;
        }

        while ($target->firstChild !== null) {
            $target->removeChild($target->firstChild);
        }

        $fragment = $this->loadFragment('<div id="__binding_root">' . $value . '</div>');

        if ($fragment !== null) {
            $root = $fragment->getElementById('__binding_root');

            if ($root !== null) {
                foreach (iterator_to_array($root->childNodes) as $child) {
                    $target->appendChild($dom->importNode($child, true));
                }
            }
        } else {
            $target->appendChild($dom->createTextNode($value));
        }

        return $this->saveFragment($dom, $content);
    }

    private function setAttribute(string $content, ?string $tag, string $attribute, string $value): string
    {
        $dom = $this->loadFragment($content);

        if ($dom === null) {
            return $content;
        }

        $target = null;

        foreach ($dom->getElementsByTagName('*') as $element) {
            /** @var \DOMElement $element */
            if ($tag === null || $tag === '' || strcasecmp($element->tagName, $tag) === 0) {
                $target = $element;
                break;
            }
        }

        if ($target === null) {
            return $content;
        }

        $target->setAttribute($attribute, $value);

        return $this->saveFragment($dom, $content);
    }

    private function loadFragment(string $html): ?\DOMDocument
    {
        if (trim($html) === '') {
            return null;
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);

        $loaded = $dom->loadHTML(
            '<?xml encoding="UTF-8"><div id="__fragment_root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOWARNING | LIBXML_NOERROR
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $loaded ? $dom : null;
    }

    private function saveFragment(\DOMDocument $dom, string $original): string
    {
        $root = $dom->getElementById('__fragment_root');

        if ($root === null) {
            return $original;
        }

        $html = '';

        foreach (iterator_to_array($root->childNodes) as $child) {
            $html .= $dom->saveHTML($child);
        }

        return $html;
    }
}
