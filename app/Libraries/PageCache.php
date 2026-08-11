<?php

namespace App\Libraries;

use CodeIgniter\Cache\CacheFactory;

class PageCache
{
    protected static ?self $instance = null;
    protected $cache;
    protected bool $enabled;
    protected int $ttl;

    public function __construct()
    {
        $db = \Config\Database::connect();
        $row = $db->table('settings')
                  ->whereIn('key', ['cache_enabled', 'cache_ttl'])
                  ->get()
                  ->getResultArray();

        $settings = [];
        foreach ($row as $r) {
            $settings[$r['key']] = $r['value'];
        }

        $this->enabled = filter_var($settings['cache_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $this->ttl     = (int) ($settings['cache_ttl'] ?? 3600);

        if ($this->enabled) {
            $this->cache = CacheFactory::getHandler(new \Config\Cache());
        }
    }

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function isEnabled(): bool
    {
        return $this->enabled && $this->cache !== null;
    }

    public function getKey(string $uri, string $locale = ''): string
    {
        $prefix = 'page_' . ($locale ?: 'default') . '_';
        return $prefix . md5(strtolower($uri));
    }

    public function get(string $uri, string $locale = ''): ?string
    {
        if (! $this->isEnabled()) {
            return null;
        }
        return $this->cache->get($this->getKey($uri, $locale));
    }

    public function save(string $uri, string $html, string $locale = ''): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }
        return $this->cache->save($this->getKey($uri, $locale), $html, $this->ttl);
    }

    public function delete(string $pattern = ''): int
    {
        if (! $this->isEnabled()) {
            return 0;
        }

        $deleted = 0;
        $info    = $this->cache->getCacheInfo();
        $keys    = $info['cache_list'] ?? [];

        foreach ($keys as $item) {
            $key = is_array($item) ? ($item['name'] ?? '') : (string) $item;
            if (str_starts_with($key, 'page_')) {
                if ($pattern === '' || str_contains($key, $pattern)) {
                    $this->cache->delete($key);
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    public function flush(): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }
        return $this->cache->clean();
    }
}
