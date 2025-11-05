<?php

declare(strict_types=1);

namespace ImageResizer\Config;

use RuntimeException;

class Config
{
    /** @var array<string, mixed> */
    private array $config;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        if (empty($config)) {
            $this->loadFromEnv();
        } else {
            $this->config = $config;
        }

        $this->validate();
    }

    private function loadFromEnv(): void
    {
        $this->config = [
            'allowed_domains' => $this->parseAllowedDomains($_ENV['ALLOWED_DOMAINS'] ?? ''),
            'cache_directory' => $_ENV['CACHE_DIRECTORY'] ?? '../cache/',
            'cache_lifetime' => (int)($_ENV['CACHE_LIFETIME'] ?? 3600),
            'default_quality' => (int)($_ENV['DEFAULT_QUALITY'] ?? 75),
            'max_file_size' => (int)($_ENV['MAX_FILE_SIZE'] ?? 10485760), // 10MB default
            'request_timeout' => (int)($_ENV['REQUEST_TIMEOUT'] ?? 30),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function parseAllowedDomains(string $domains): array
    {
        if (empty($domains)) {
            return [];
        }

        return array_filter(
            array_map('trim', explode(',', $domains)),
            fn($domain) => !empty($domain)
        );
    }

    private function validate(): void
    {
        if (empty($this->config['allowed_domains'])) {
            throw new RuntimeException('ALLOWED_DOMAINS must be configured');
        }

        if (empty($this->config['cache_directory'])) {
            throw new RuntimeException('CACHE_DIRECTORY must be configured');
        }

        if ($this->config['cache_lifetime'] <= 0) {
            throw new RuntimeException('CACHE_LIFETIME must be greater than 0');
        }

        $cacheDir = $this->getCacheDirectory();
        if (!is_dir($cacheDir) && !mkdir($cacheDir, 0755, true)) {
            throw new RuntimeException("Cache directory does not exist and could not be created: {$cacheDir}");
        }

        if (!is_writable($cacheDir)) {
            throw new RuntimeException("Cache directory is not writable: {$cacheDir}");
        }
    }

    /**
     * @return array<int, string>
     */
    public function getAllowedDomains(): array
    {
        return $this->config['allowed_domains'];
    }

    public function getCacheDirectory(): string
    {
        $dir = $this->config['cache_directory'];

        // Normalize path
        if (!str_starts_with($dir, '/')) {
            $dir = dirname(__DIR__, 2) . '/' . $dir;
        }

        return rtrim($dir, '/') . '/';
    }

    public function getCacheLifetime(): int
    {
        return $this->config['cache_lifetime'];
    }

    public function getDefaultQuality(): int
    {
        return $this->config['default_quality'];
    }

    public function getMaxFileSize(): int
    {
        return $this->config['max_file_size'];
    }

    public function getRequestTimeout(): int
    {
        return $this->config['request_timeout'];
    }
}
