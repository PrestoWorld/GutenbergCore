<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\ThemeJson;

/**
 * Layered theme.json data model (Builder-style merge of origins).
 */
final class ThemeJson
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private array $data = [],
    ) {
    }

    public static function fromFile(string $path): self
    {
        $file = is_dir($path) ? rtrim($path, '/') . '/theme.json' : $path;

        if (!is_file($file)) {
            return new self();
        }

        $decoded = json_decode((string) file_get_contents($file), true);

        return new self(is_array($decoded) ? $decoded : []);
    }

    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * Dot-path getter, e.g. get('settings.color.palette').
     */
    public function get(string $path, mixed $default = null): mixed
    {
        $data = $this->data;

        foreach (explode('.', $path) as $key) {
            if (!is_array($data) || !array_key_exists($key, $data)) {
                return $default;
            }

            $data = $data[$key];
        }

        return $data;
    }

    /**
     * Overlay another layer on top of this one (theme over defaults, …).
     */
    public function merge(self $overlay): self
    {
        return new self(self::mergeArrays($this->data, $overlay->data));
    }

    /**
     * @param array<string, mixed> $base
     * @param array<string, mixed> $overlay
     * @return array<string, mixed>
     */
    private static function mergeArrays(array $base, array $overlay): array
    {
        foreach ($overlay as $key => $value) {
            if (
                is_array($value)
                && isset($base[$key])
                && is_array($base[$key])
                && self::isAssoc($value)
                && self::isAssoc($base[$key])
            ) {
                $base[$key] = self::mergeArrays($base[$key], $value);
                continue;
            }

            $base[$key] = $value;
        }

        return $base;
    }

    private static function isAssoc(array $array): bool
    {
        if ($array === []) {
            return true;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }
}
