<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Exceptions;

/**
 * Raised when theme code calls a WordPress API that this engine has not
 * implemented yet. The message names the missing API and where it was used,
 * so the gap can be closed instead of being silently swallowed.
 */
class MissingApiException extends GutenbergException
{
    public static function forFunction(string $function, string $context = ''): self
    {
        $message = sprintf(
            'WordPress API function "%s()" is not implemented by prestoworld/gutenberg-core.',
            $function
        );

        if ($context !== '') {
            $message .= ' Called from: ' . $context;
        }

        $message .= ' Implement it in PrestoWorld\\Gutenberg\\Compatibility\\functions.php.';

        return new self($message);
    }
}
