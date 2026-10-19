<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Contracts;

use PrestoWorld\Gutenberg\Patterns\Pattern;

/**
 * Source of block patterns (Strategy pattern) — e.g. theme /patterns/*.php.
 */
interface PatternSourceInterface
{
    /**
     * @return list<Pattern>
     */
    public function discover(): array;
}
