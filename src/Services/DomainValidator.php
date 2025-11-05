<?php

declare(strict_types=1);

namespace ImageResizer\Services;

use ImageResizer\Config\Config;
use ImageResizer\Exceptions\InvalidDomainException;

class DomainValidator
{
    public function __construct(
        private readonly Config $config
    ) {
    }

    /**
     * @throws InvalidDomainException
     */
    public function validate(string $url): void
    {
        if (empty($url)) {
            throw new InvalidDomainException('URL cannot be empty');
        }

        $parsedUrl = parse_url($url);

        if ($parsedUrl === false || !isset($parsedUrl['host'])) {
            throw new InvalidDomainException('Invalid URL format');
        }

        $host = $parsedUrl['host'];
        $allowedDomains = $this->config->getAllowedDomains();

        if (!in_array($host, $allowedDomains, true)) {
            throw new InvalidDomainException("Domain not allowed: {$host}");
        }

        // Prevent localhost, private IPs, and other potentially unsafe targets (SSRF protection)
        if ($this->isDangerousHost($host)) {
            throw new InvalidDomainException("Potentially unsafe host: {$host}");
        }
    }

    private function isDangerousHost(string $host): bool
    {
        // Check for localhost variants
        $dangerousHosts = [
            'localhost',
            '127.0.0.1',
            '::1',
            '0.0.0.0',
        ];

        if (in_array(strtolower($host), $dangerousHosts, true)) {
            return true;
        }

        // Check for private IP ranges
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return !filter_var(
                $host,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );
        }

        return false;
    }

    public function isAllowed(string $url): bool
    {
        try {
            $this->validate($url);
            return true;
        } catch (InvalidDomainException) {
            return false;
        }
    }
}
