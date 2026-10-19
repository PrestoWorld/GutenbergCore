<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Rendering;

use PrestoWorld\Gutenberg\Block\BlockInstance;
use PrestoWorld\Gutenberg\Block\Type\BlockTypeRegistry;
use PrestoWorld\Gutenberg\Bindings\BindingsProcessor;
use PrestoWorld\Gutenberg\Contracts\BlockRendererInterface;
use PrestoWorld\Gutenberg\Contracts\HookManagerInterface;
use PrestoWorld\Gutenberg\Parser\Node\BlockNode;
use PrestoWorld\Gutenberg\Parser\Node\FreeformNode;
use PrestoWorld\Gutenberg\Parser\Node\ParsedNodeInterface;
use PrestoWorld\Gutenberg\Supports\BlockSupports;

/**
 * Render pipeline (Pipeline pattern).
 *
 * Ordered stages per block, faithful to core's render_block():
 *
 *   pre_render_block → render_block_data → render_block_context
 *   → inner recursion → bindings HTML rewrite → render_callback
 *   → asset enqueue → render_block → render_block_{name}
 *
 * Filters run through the HookManager adapter so the host runtime's global
 * hooks (and any plugin hooked into them) keep working.
 */
final class BlockRenderer implements BlockRendererInterface
{
    private ?WrapperAttributes $wrapperAttributes;

    public function __construct(
        private readonly BlockTypeRegistry $blockTypes,
        private readonly HookManagerInterface $hooks,
        private readonly BindingsProcessor $bindings,
        private readonly RenderContext $renderContext = new RenderContext(),
        ?WrapperAttributes $wrapperAttributes = null,
    ) {
        $this->wrapperAttributes = $wrapperAttributes;
    }

    public function wrapper(): WrapperAttributes
    {
        return $this->wrapperAttributes ??= new WrapperAttributes(new BlockSupports());
    }

    public function blockTypes(): BlockTypeRegistry
    {
        return $this->blockTypes;
    }

    public function hooks(): HookManagerInterface
    {
        return $this->hooks;
    }

    public function renderContext(): RenderContext
    {
        return $this->renderContext;
    }

    public function render(array $blocks, array $context = []): string
    {
        $baseContext = array_merge($this->renderContext->baseBlockContext(), $context);
        $output = '';

        foreach ($blocks as $node) {
            $parsed = $this->normalize($node);

            if ($parsed === null) {
                continue;
            }

            if (($parsed['blockName'] ?? null) === null) {
                $output .= (string) ($parsed['innerHTML'] ?? '');
                continue;
            }

            $instance = new BlockInstance($parsed, $baseContext, $this, $this->blockTypes);
            $output .= $this->renderBlock($instance);
        }

        return $output;
    }

    /**
     * @param array{dynamic?: bool} $options
     */
    public function renderBlock(BlockInstance $block, array $options = []): string
    {
        $options += ['dynamic' => true];

        $parsed = $block->toParsedArray();
        $parentParsed = $block->parent?->toParsedArray();

        // Stage 1 — short-circuit.
        $preRender = $this->hooks->applyFilters('pre_render_block', null, $parsed, $parentParsed);
        if ($preRender !== null) {
            return (string) $preRender;
        }

        // Stage 2 — data filter (may mutate the parsed payload).
        $sourceBlock = $parsed;
        $filtered = $this->hooks->applyFilters('render_block_data', $parsed, $sourceBlock, $parentParsed);
        if (is_array($filtered)) {
            $block->parsedBlock = $filtered;
            if ($filtered !== $sourceBlock) {
                $block->refreshDependents();
            }
        }

        // Stage 3 — context filter.
        $context = $this->hooks->applyFilters('render_block_context', $block->context, $block->parsedBlock, $parentParsed);
        if (is_array($context) && $context !== $block->context) {
            $block->applyContext($context);
        }

        // Stage 4 — block bindings.
        $computedAttributes = $this->bindings->process($block);
        if ($computedAttributes !== []) {
            $block->attributes = array_merge($block->attributes, $computedAttributes);
        }

        // Stage 5 — assemble inner content (recursive).
        $blockContent = $this->renderInnerContent($block, $options);

        // Stage 6 — rewrite bound attributes into the HTML.
        foreach ($computedAttributes as $attributeName => $sourceValue) {
            if ($attributeName === 'metadata') {
                continue;
            }
            $blockContent = $this->bindings->replaceHtml($blockContent, (string) $attributeName, $sourceValue, $block);
        }

        // Stage 7 — dynamic render callback.
        $isDynamic = $options['dynamic']
            && $block->blockType !== null
            && $block->blockType->isDynamic();

        if ($isDynamic) {
            if (class_exists(\PrestoWorld\Gutenberg\Gutenberg::class)) {
                \PrestoWorld\Gutenberg\Gutenberg::setCurrentBlock($block);
            }

            try {
                $blockContent = $block->blockType->render($block->attributes, $blockContent, $block);
            } finally {
                if (class_exists(\PrestoWorld\Gutenberg\Gutenberg::class)) {
                    \PrestoWorld\Gutenberg\Gutenberg::setCurrentBlock($block->parent);
                }
            }
        }

        // Stage 8 — assets declared on the block type.
        $this->enqueueAssets($block);

        // Stage 9 — post-render filters.
        $blockContent = $this->hooks->applyFilters('render_block', $blockContent, $block->toParsedArray(), $block);

        $name = $block->name();
        if ($name !== '') {
            $blockContent = $this->hooks->applyFilters('render_block_' . $name, $blockContent, $block->toParsedArray(), $block);
        }

        return $blockContent;
    }

    /**
     * @param array{dynamic?: bool} $options
     */
    private function renderInnerContent(BlockInstance $block, array $options): string
    {
        if ($options['dynamic'] && $block->blockType?->skipInnerBlocks) {
            return '';
        }

        $index = 0;
        $output = '';

        foreach ($block->innerContent as $chunk) {
            if (is_string($chunk)) {
                $output .= $chunk;
                continue;
            }

            $inner = $block->innerBlocks[$index] ?? null;

            if ($inner instanceof BlockInstance) {
                $output .= $this->renderBlock($inner, $options);
            }

            ++$index;
        }

        return $output;
    }

    private function enqueueAssets(BlockInstance $block): void
    {
        $blockType = $block->blockType;

        if ($blockType === null) {
            return;
        }

        foreach ($blockType->styleHandles as $handle) {
            if (function_exists('wp_enqueue_style')) {
                \wp_enqueue_style($handle);
            }
        }

        foreach ($blockType->viewScriptHandles as $handle) {
            if (function_exists('wp_enqueue_script')) {
                \wp_enqueue_script($handle);
            }
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function normalize(mixed $node): ?array
    {
        if ($node instanceof ParsedNodeInterface) {
            return $node->toArray();
        }

        if ($node instanceof BlockNode || $node instanceof FreeformNode) {
            return $node->toArray();
        }

        if (is_array($node) && isset($node['innerBlocks']) || is_array($node) && isset($node['innerHTML'])) {
            return $node;
        }

        if (is_string($node)) {
            return ['blockName' => null, 'attrs' => [], 'innerBlocks' => [], 'innerHTML' => $node, 'innerContent' => [$node]];
        }

        return null;
    }
}
