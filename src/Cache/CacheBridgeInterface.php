<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Cache;

/**
 * App-owned cache policy bridge (interface giữ trong vendor để vendor không
 * phải biết storage/config của host). Host đăng ký instance lúc boot —
 * nếu không đăng ký, caching bên trong gutenberg-core tự tắt.
 */
interface CacheBridgeInterface
{
    /** Cache namespace có đang được bật không (page/fragment/parse/hooks). */
    public function enabled(string $ns): bool;

    /** Salt thủ công cho namespace (dashboard Clear → key đổi). */
    public function salt(string $ns): string;

    /** Generation invalidation cho namespace (content save → key đổi). */
    public function generation(string $ns): string;

    /** Ghi counter (hit/miss/store/…) vào stats của host. */
    public function stat(string $ns, string $event): void;
}
