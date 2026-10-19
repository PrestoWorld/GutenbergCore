<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Parser;

/**
 * One block-delimiter token found in the document.
 */
final class Token
{
    public const NO_MORE = 'no-more-tokens';
    public const VOID = 'void-block';
    public const OPENER = 'block-opener';
    public const CLOSER = 'block-closer';

    public function __construct(
        public readonly string $type,
        public readonly ?string $name = null,
        public readonly array $attributes = [],
        public readonly int $start = 0,
        public readonly int $length = 0,
    ) {
    }
}
