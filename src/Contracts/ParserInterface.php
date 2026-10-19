<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Contracts;

/**
 * Parses serialized block markup into a tree of nodes.
 */
interface ParserInterface
{
    /**
     * @return list<\PrestoWorld\Gutenberg\Parser\Node\ParsedNodeInterface>
     */
    public function parse(string $content): array;
}
