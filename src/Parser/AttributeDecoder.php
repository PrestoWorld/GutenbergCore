<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Parser;

use PrestoWorld\Gutenberg\Exceptions\ParseException;

/**
 * Decodes the JSON attribute payload of a block delimiter.
 */
final class AttributeDecoder
{
    /**
     * @return array<string, mixed>
     */
    public function decode(string $json): array
    {
        $trimmed = trim($json);

        if ($trimmed === '' || $trimmed === '{}') {
            return [];
        }

        try {
            $decoded = json_decode($trimmed, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw ParseException::badAttributeJson(
                mb_substr($trimmed, 0, 80),
                $e->getMessage()
            );
        }

        if (!is_array($decoded)) {
            return [];
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }
}
