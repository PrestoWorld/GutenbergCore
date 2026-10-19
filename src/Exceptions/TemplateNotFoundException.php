<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Exceptions;

/**
 * Raised when a theme template (or part) cannot be found on disk.
 */
class TemplateNotFoundException extends GutenbergException
{
    public static function named(string $name, string $directory): self
    {
        return new self(sprintf('Block theme template "%s" not found in "%s".', $name, $directory));
    }
}
