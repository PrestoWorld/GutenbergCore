<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Cache;

/**
 * Static holder cho CacheBridgeInterface — BlockSerializer và
 * TemplatePartHandler đọc qua đây. Bridge null → caching tắt (default
 * an toàn cho test/CLI standalone).
 */
final class CacheBridge
{
    private static ?CacheBridgeInterface $instance = null;

    public static function set(?CacheBridgeInterface $bridge): void
    {
        self::$instance = $bridge;
    }

    public static function get(): ?CacheBridgeInterface
    {
        return self::$instance;
    }

    public static function enabled(string $ns): bool
    {
        return self::$instance !== null && self::$instance->enabled($ns);
    }

    public static function stat(string $ns, string $event): void
    {
        self::$instance?->stat($ns, $event);
    }
}
