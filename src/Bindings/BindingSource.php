<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Bindings;

/**
 * A block-bindings source (Strategy): resolves a value for a bound attribute.
 */
final class BindingSource
{
    /** @var list<string> */
    public readonly array $usesContext;

    /**
     * @param array{label?: string, uses_context?: list<string>, get_value_callback?: callable} $settings
     */
    public function __construct(
        public readonly string $name,
        array $settings = [],
    ) {
        $this->usesContext = is_array($settings['uses_context'] ?? null) ? array_values($settings['uses_context']) : [];

        $callback = $settings['get_value_callback'] ?? null;

        if (!is_callable($callback)) {
            throw new \InvalidArgumentException(sprintf(
                'Block bindings source "%s" requires a callable get_value_callback.',
                $name
            ));
        }

        $this->callback = $callback;
        $this->label = isset($settings['label']) ? (string) $settings['label'] : null;
    }

    public readonly ?string $label;

    /** @var callable */
    private $callback;

    /**
     * @param array<string, mixed> $args
     * @return mixed null when the source has no value for the context
     */
    public function getValue(array $args, mixed $block, string $attributeName): mixed
    {
        return ($this->callback)($args, $block, $attributeName);
    }
}
