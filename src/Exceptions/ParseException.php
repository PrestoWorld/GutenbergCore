<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Exceptions;

/**
 * Raised when block markup cannot be parsed into a block tree.
 */
class ParseException extends GutenbergException
{
    public static function badAttributeJson(string $snippet, string $reason): self
    {
        return new self(
            sprintf('Unable to decode block attribute JSON near "%s": %s', $snippet, $reason)
        );
    }
}
