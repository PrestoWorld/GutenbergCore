<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Block\Type;

use PrestoWorld\Gutenberg\Block\BlockName;
use PrestoWorld\Gutenberg\Block\BlockType;
use PrestoWorld\Gutenberg\Contracts\RegistryInterface;

/**
 * Singleton-style registry of block types (Registry pattern).
 */
final class BlockTypeRegistry implements RegistryInterface
{
    /** @var array<string, BlockType> */
    private array $types = [];

    public function register(string $name, mixed $definition): void
    {
        if ($definition instanceof BlockType) {
            $this->types[BlockName::from($name)->full()] = $definition;

            return;
        }

        if (is_array($definition)) {
            $this->types[BlockName::from($name)->full()] = new BlockType($name, $definition);

            return;
        }

        throw new \InvalidArgumentException(sprintf(
            'Block type definition for "%s" must be a BlockType instance or settings array.',
            $name
        ));
    }

    public function has(string $name): bool
    {
        return isset($this->types[BlockName::from($name)->full()]);
    }

    public function get(string $name): ?BlockType
    {
        return $this->types[BlockName::from($name)->full()] ?? null;
    }

    public function all(): array
    {
        return $this->types;
    }

    public function unregister(string $name): bool
    {
        $full = BlockName::from($name)->full();

        if (!isset($this->types[$full])) {
            return false;
        }

        unset($this->types[$full]);

        return true;
    }
}
