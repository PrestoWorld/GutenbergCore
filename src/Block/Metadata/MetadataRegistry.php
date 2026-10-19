<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Block\Metadata;

use PrestoWorld\Gutenberg\Block\BlockName;

/**
 * Lazily-resolved block.json metadata store (like WP_Block_Metadata_Registry).
 *
 * Metadata is keyed by block name; a resolver callback loads it on demand
 * from the package's Core/metadata mirror of wp-includes/blocks.
 */
final class MetadataRegistry
{
    /** @var array<string, array<string, mixed>> */
    private array $resolved = [];

    /** @var array<string, true> */
    private array $attempted = [];

    /** @var (callable(string): ?array<string, mixed>)|null */
    private $loader;

    /**
     * @param (callable(string $blockName): ?array<string, mixed>)|null $loader
     */
    public function __construct(?callable $loader = null)
    {
        $this->loader = $loader;
    }

    /**
     * @param (callable(string $blockName): ?array<string, mixed>) $loader
     */
    public function setLoader(callable $loader): void
    {
        $this->loader = $loader;
    }

    public function add(string $name, array $metadata): void
    {
        $this->resolved[BlockName::from($name)->full()] = $metadata;
    }

    public function has(string $name): bool
    {
        $full = BlockName::from($name)->full();

        return isset($this->resolved[$full]) || $this->resolve($full) !== null;
    }

    public function get(string $name): ?array
    {
        $full = BlockName::from($name)->full();

        if (isset($this->resolved[$full])) {
            return $this->resolved[$full];
        }

        return $this->resolve($full);
    }

    private function resolve(string $full): ?array
    {
        if (isset($this->attempted[$full])) {
            return null;
        }

        $this->attempted[$full] = true;

        if ($this->loader === null) {
            return null;
        }

        $metadata = ($this->loader)($full);

        if (is_array($metadata)) {
            $this->resolved[$full] = $metadata;
        }

        return $metadata;
    }
}
