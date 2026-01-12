<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel\Tests;

use GungCahyadiPP\ChannexOpenChannel\ChannexConfig;
use PHPUnit\Framework\TestCase;

class ChannexConfigTest extends TestCase
{
    public function test_can_create_config_with_required_params(): void
    {
        $config = new ChannexConfig(
            apiKey: 'test-api-key',
            providerCode: 'TestProvider',
        );

        $this->assertEquals('test-api-key', $config->getApiKey());
        $this->assertEquals('TestProvider', $config->getProviderCode());
        $this->assertEquals('staging', $config->getEnvironment());
    }

    public function test_staging_endpoints(): void
    {
        $config = new ChannexConfig(
            apiKey: 'test-key',
            providerCode: 'Test',
            environment: 'staging',
        );

        $this->assertEquals('https://staging.channex.io/api/v1', $config->getApiEndpoint());
        $this->assertEquals('https://secure-staging.channex.io/api/v1', $config->getSecureEndpoint());
        $this->assertFalse($config->isProduction());
    }

    public function test_production_endpoints(): void
    {
        $config = new ChannexConfig(
            apiKey: 'test-key',
            providerCode: 'Test',
            environment: 'production',
        );

        $this->assertEquals('https://app.channex.io/api/v1', $config->getApiEndpoint());
        $this->assertEquals('https://secure.channex.io/api/v1', $config->getSecureEndpoint());
        $this->assertTrue($config->isProduction());
    }

    public function test_custom_endpoints_override_defaults(): void
    {
        $config = new ChannexConfig(
            apiKey: 'test-key',
            providerCode: 'Test',
            environment: 'staging',
            customApiEndpoint: 'https://custom.api.com',
            customSecureEndpoint: 'https://custom.secure.com',
        );

        $this->assertEquals('https://custom.api.com', $config->getApiEndpoint());
        $this->assertEquals('https://custom.secure.com', $config->getSecureEndpoint());
    }

    public function test_inbound_api_key_validation_passes_when_not_set(): void
    {
        $config = new ChannexConfig(
            apiKey: 'test-key',
            providerCode: 'Test',
        );

        $this->assertTrue($config->validateInboundApiKey(null));
        $this->assertTrue($config->validateInboundApiKey('any-key'));
    }

    public function test_inbound_api_key_validation_checks_key(): void
    {
        $config = new ChannexConfig(
            apiKey: 'test-key',
            providerCode: 'Test',
            inboundApiKey: 'secret-inbound-key',
        );

        $this->assertTrue($config->validateInboundApiKey('secret-inbound-key'));
        $this->assertFalse($config->validateInboundApiKey('wrong-key'));
        $this->assertFalse($config->validateInboundApiKey(null));
    }

    public function test_throws_exception_for_invalid_environment(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ChannexConfig(
            apiKey: 'test-key',
            providerCode: 'Test',
            environment: 'invalid',
        );
    }
}
