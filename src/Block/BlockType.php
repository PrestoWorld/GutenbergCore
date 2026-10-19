<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Block;

/**
 * A registered block type: metadata + optional render callback.
 *
 * Mirrors WP_Block_Type but as a focused value/service object.
 */
final class BlockType
{
    public ?string $title = null;

    public ?string $category = null;

    public ?string $description = null;

    /** @var array<string, array<string, mixed>> */
    public array $attributes = [];

    /** @var array<string, mixed> */
    public array $supports = [];

    /** @var list<string> */
    public array $usesContext = [];

    /** @var array<string, string> context name => attribute name */
    public array $providesContext = [];

    /** @var list<string> */
    public array $styleHandles = [];

    /** @var list<string> */
    public array $viewScriptHandles = [];

    public bool $skipInnerBlocks = false;

    /** @var (\Closure(array<string, mixed>, string, mixed): string)|null */
    private $renderCallback;

    /**
     * @param array<string, mixed> $settings
     * @param (\Closure(array<string, mixed> $attributes, string $content, mixed $block): string)|null $renderCallback
     */
    public function __construct(
        public readonly string $name,
        array $settings = [],
        ?\Closure $renderCallback = null,
    ) {
        $this->title = isset($settings['title']) ? (string) $settings['title'] : null;
        $this->category = isset($settings['category']) ? (string) $settings['category'] : null;
        $this->description = isset($settings['description']) ? (string) $settings['description'] : null;
        $this->attributes = is_array($settings['attributes'] ?? null) ? $settings['attributes'] : [];
        $this->supports = is_array($settings['supports'] ?? null) ? $settings['supports'] : [];
        $this->usesContext = is_array($settings['uses_context'] ?? null) ? array_values($settings['uses_context']) : [];
        $this->providesContext = is_array($settings['provides_context'] ?? null) ? $settings['provides_context'] : [];
        $this->styleHandles = is_array($settings['style_handles'] ?? null) ? array_values($settings['style_handles']) : [];
        $this->viewScriptHandles = is_array($settings['view_script_handles'] ?? null) ? array_values($settings['view_script_handles']) : [];
        $this->skipInnerBlocks = (bool) ($settings['skip_inner_blocks'] ?? false);
        $this->renderCallback = $renderCallback;
    }

    public function isDynamic(): bool
    {
        return $this->renderCallback !== null;
    }

    /**
     * @param (\Closure(array<string, mixed> $attributes, string $content, mixed $block): string) $callback
     */
    public function setRenderCallback(\Closure $callback): void
    {
        $this->renderCallback = $callback;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function render(array $attributes, string $content, mixed $block): string
    {
        if ($this->renderCallback === null) {
            throw new \PrestoWorld\Gutenberg\Exceptions\UnhandledBlockException(
                sprintf('Block type "%s" is not dynamic but a render callback was requested.', $this->name)
            );
        }

        return ($this->renderCallback)($attributes, $content, $block);
    }

    /**
     * Applies schema defaults and coerces types — port of
     * WP_Block_Type::prepare_attributes_for_render().
     *
     * @param array<string, mixed> $rawAttributes
     * @return array<string, mixed>
     */
    public function parseAttributes(array $rawAttributes): array
    {
        $prepared = [];

        foreach ($this->attributes as $name => $schema) {
            $hasValue = array_key_exists($name, $rawAttributes);

            if ($hasValue && $rawAttributes[$name] === null) {
                $hasValue = false;
            }

            if (!$hasValue) {
                $prepared[$name] = $schema['default'] ?? null;
                continue;
            }

            $prepared[$name] = $this->coerce($rawAttributes[$name], $schema['type'] ?? '');
        }

        // Keep unknown attributes (e.g. metadata) — bindings rely on them.
        foreach ($rawAttributes as $name => $value) {
            if (!array_key_exists($name, $prepared)) {
                $prepared[$name] = $value;
            }
        }

        return $prepared;
    }

    private function coerce(mixed $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => (bool) $value,
            'number', 'integer' => is_numeric($value) ? $value + 0 : $value,
            'string' => is_scalar($value) ? (string) $value : $value,
            'array' => is_array($value) ? $value : [],
            'object' => is_array($value) || is_object($value) ? $value : [],
            default => $value,
        };
    }
}
