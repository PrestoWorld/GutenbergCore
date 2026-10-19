<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Block\Type;

use PrestoWorld\Gutenberg\Block\BlockName;
use PrestoWorld\Gutenberg\Block\BlockType;

/**
 * Factory: builds a BlockType from block.json metadata (Factory pattern).
 */
final class BlockTypeFactory
{
    /**
     * @param array<string, mixed> $metadata
     * @param (\Closure|null) $renderCallback
     */
    public static function fromMetadata(string $blockName, array $metadata, ?\Closure $renderCallback = null): BlockType
    {
        $name = BlockName::from($blockName)->full();

        $settings = [
            'title' => $metadata['title'] ?? null,
            'category' => $metadata['category'] ?? null,
            'description' => $metadata['description'] ?? null,
            'attributes' => self::normalizeAttributes($metadata['attributes'] ?? []),
            'supports' => $metadata['supports'] ?? [],
            'uses_context' => $metadata['usesContext'] ?? [],
            'provides_context' => $metadata['providesContext'] ?? [],
            'style_handles' => [],
            'view_script_handles' => [],
            'skip_inner_blocks' => (bool) ($metadata['skipInnerBlocks'] ?? false),
        ];

        $type = new BlockType($name, array_filter($settings, static fn ($v): bool => $v !== null));

        if ($renderCallback !== null) {
            $type->setRenderCallback($renderCallback);
        }

        return $type;
    }

    /**
     * @param array<mixed> $attributes
     * @return array<string, array<string, mixed>>
     */
    private static function normalizeAttributes(array $attributes): array
    {
        $normalized = [];

        foreach ($attributes as $name => $schema) {
            if (!is_string($name) || !is_array($schema)) {
                continue;
            }

            $normalized[$name] = $schema;
        }

        return $normalized;
    }
}
