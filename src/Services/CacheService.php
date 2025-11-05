<?php

declare(strict_types=1);

namespace ImageResizer\Services;

use ImageResizer\Config\Config;

class CacheService
{
    public function __construct(
        private readonly Config $config
    ) {
    }

    public function generateKey(
        string $url,
        ?int $width,
        ?int $height,
        int $quality,
        ?string $crop
    ): string {
        $data = implode('_', [
            $url,
            (string)$width,
            (string)$height,
            (string)$quality,
            (string)$crop
        ]);

        return hash('sha256', $data);
    }

    public function getCachePath(string $cacheKey): string
    {
        return $this->config->getCacheDirectory() . $cacheKey;
    }

    public function has(string $cacheKey): bool
    {
        $cachePath = $this->getCachePath($cacheKey);

        if (!file_exists($cachePath)) {
            return false;
        }

        return $this->isValid($cacheKey);
    }

    public function isValid(string $cacheKey): bool
    {
        $cachePath = $this->getCachePath($cacheKey);

        if (!file_exists($cachePath)) {
            return false;
        }

        $age = $this->getFileAge($cachePath);
        return $age !== false && $age <= $this->config->getCacheLifetime();
    }

    public function get(string $cacheKey): string|false
    {
        if (!$this->has($cacheKey)) {
            return false;
        }

        return file_get_contents($this->getCachePath($cacheKey));
    }

    public function put(string $cacheKey, string $data): bool
    {
        $cachePath = $this->getCachePath($cacheKey);
        return file_put_contents($cachePath, $data) !== false;
    }

    public function delete(string $cacheKey): bool
    {
        $cachePath = $this->getCachePath($cacheKey);

        if (!file_exists($cachePath)) {
            return false;
        }

        return unlink($cachePath);
    }

    public function clear(): int
    {
        $cacheDir = $this->config->getCacheDirectory();
        $files = glob($cacheDir . '*');
        $count = 0;

        if ($files === false) {
            return 0;
        }

        foreach ($files as $file) {
            if (is_file($file) && unlink($file)) {
                $count++;
            }
        }

        return $count;
    }

    private function getFileAge(string $filePath): int|false
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $mtime = filemtime($filePath);
        if ($mtime === false) {
            return false;
        }

        return time() - $mtime;
    }
}
