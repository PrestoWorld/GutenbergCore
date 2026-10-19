<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Exceptions;

/**
 * Raised when code asks the registry for a block that was never registered.
 */
class BlockNotRegisteredException extends GutenbergException
{
    public static function forName(string $blockName): self
    {
        return new self(sprintf(
            'Block "%s" is not registered. Register it with register_block_type() '
            . 'or provide a render handler in PrestoWorld\Gutenberg\Core\Blocks\Registrar.',
            $blockName
        ));
    }
}
