<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Styles;

use PrestoWorld\Gutenberg\Contracts\RegistryInterface;

/**
 * Registry of block styles, keyed by block name → style name.
 *
 * Mirrors register_block_style()/WP_Block_Styles_Registry.
 */
final class BlockStylesRegistry implements RegistryInterface
{
    /** @var array<string, array<string, BlockStyle>> */
    private array $styles = [];

    public function register(string $name, mixed $definition): void
    {
        // Support "blockname/style" compound keys and settings arrays.
        if (is_array($definition)) {
            $styleName = (string) ($definition['name'] ?? '');
            $blockName = $name;

            if (str_contains($name, '/') && $styleName === '') {
                [$blockName, $styleName] = explode('/', $name, 2);
            }

            if ($styleName === '') {
                throw new \InvalidArgumentException('Block style requires a "name" key.');
            }

            $this->add($blockName, new BlockStyle(
                name: $styleName,
                label: (string) ($definition['label'] ?? $styleName),
                inlineStyle: isset($definition['inline_style']) ? (string) $definition['inline_style'] : null,
                isDefault: (bool) ($definition['is_default'] ?? false),
            ));

            return;
        }

        if ($definition instanceof BlockStyle) {
            $this->add($name, $definition);

            return;
        }

        throw new \InvalidArgumentException('Block style definition must be a BlockStyle or array.');
    }

    public function add(string $blockName, BlockStyle $style): void
    {
        $this->styles[$blockName][$style->name] = $style;
    }

    public function forBlock(string $blockName): array
    {
        return $this->styles[$blockName] ?? [];
    }

    public function has(string $name): bool
    {
        return str_contains($name, '/')
            && isset($this->styles[explode('/', $name, 2)[0]][explode('/', $name, 2)[1]]);
    }

    public function get(string $name): mixed
    {
        if (!str_contains($name, '/')) {
            return null;
        }

        [$block, $style] = explode('/', $name, 2);

        return $this->styles[$block][$style] ?? null;
    }

    public function all(): array
    {
        return $this->styles;
    }

    public function unregister(string $name): bool
    {
        if (!str_contains($name, '/')) {
            return false;
        }

        [$block, $style] = explode('/', $name, 2);

        if (!isset($this->styles[$block][$style])) {
            return false;
        }

        unset($this->styles[$block][$style]);

        return true;
    }
}
