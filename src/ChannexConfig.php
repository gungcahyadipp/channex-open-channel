<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel;

class ChannexConfig
{
    public const ENV_STAGING = 'staging';
    public const ENV_PRODUCTION = 'production';

    private const DEFAULT_ENDPOINTS = [
        self::ENV_STAGING => [
            'api' => 'https://staging.channex.io/api/v1',
            'secure' => 'https://secure-staging.channex.io/api/v1',
        ],
        self::ENV_PRODUCTION => [
            'api' => 'https://app.channex.io/api/v1',
            'secure' => 'https://secure.channex.io/api/v1',
        ],
    ];

    public function __construct(
        private string $apiKey,
        private string $providerCode,
        private string $environment = self::ENV_STAGING,
        private ?string $inboundApiKey = null,
        private ?string $customApiEndpoint = null,
        private ?string $customSecureEndpoint = null,
    ) {
        if (!in_array($environment, [self::ENV_STAGING, self::ENV_PRODUCTION], true)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid environment "%s". Must be "staging" or "production".', $environment)
            );
        }
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getInboundApiKey(): ?string
    {
        return $this->inboundApiKey;
    }

    public function getProviderCode(): string
    {
        return $this->providerCode;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function getApiEndpoint(): string
    {
        return $this->customApiEndpoint ?? self::DEFAULT_ENDPOINTS[$this->environment]['api'];
    }

    public function getSecureEndpoint(): string
    {
        return $this->customSecureEndpoint ?? self::DEFAULT_ENDPOINTS[$this->environment]['secure'];
    }

    public function isProduction(): bool
    {
        return $this->environment === self::ENV_PRODUCTION;
    }

    /**
     * Validate inbound request API key from Channex
     */
    public function validateInboundApiKey(?string $apiKey): bool
    {
        if (empty($this->inboundApiKey)) {
            return true; // No validation if not configured
        }

        return $apiKey === $this->inboundApiKey;
    }
}
