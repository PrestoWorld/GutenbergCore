<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Exceptions;

/**
 * Raised when a dynamic block is hit at render time but has no handler yet.
 *
 * This is intentional: an unimplemented block must FAIL LOUDLY so the gap is
 * discovered and fixed instead of silently producing broken markup.
 */
class UnhandledBlockException extends GutenbergException
{
    public static function forName(string $blockName, string $reason = ''): self
    {
        $message = sprintf(
            'Dynamic block "%s" has no render handler in PrestoWorld\Gutenberg. '
            . 'Implement a handler class in PrestoWorld\\Gutenberg\\Core\\Blocks\\ and register it.',
            $blockName
        );

        if ($reason !== '') {
            $message .= ' Reason: ' . $reason;
        }

        return new self($message);
    }
}
