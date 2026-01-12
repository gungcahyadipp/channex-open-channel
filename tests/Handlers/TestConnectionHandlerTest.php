<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel\Tests\Handlers;

use GungCahyadiPP\ChannexOpenChannel\ChannexConfig;
use GungCahyadiPP\ChannexOpenChannel\Handlers\AbstractTestConnectionHandler;
use PHPUnit\Framework\TestCase;

class TestConnectionHandlerTest extends TestCase
{
    private function createHandler(bool $hotelExists = true, ?ChannexConfig $config = null): AbstractTestConnectionHandler
    {
        return new class($hotelExists, $config) extends AbstractTestConnectionHandler {
            public function __construct(
                private bool $hotelExists,
                ?ChannexConfig $config = null,
            ) {
                parent::__construct($config);
            }

            protected function validateHotelCode(string $hotelCode): bool
            {
                return $this->hotelExists && !empty($hotelCode);
            }
        };
    }

    public function test_returns_success_for_valid_hotel(): void
    {
        $handler = $this->createHandler(hotelExists: true);
        $response = $handler->handle('HOTEL123');

        $this->assertTrue($response['success']);
    }

    public function test_returns_error_for_invalid_hotel(): void
    {
        $handler = $this->createHandler(hotelExists: false);
        $response = $handler->handle('INVALID');

        $this->assertFalse($response['success']);
        $this->assertEquals('Invalid hotel code', $response['error']);
    }

    public function test_validates_api_key_when_config_provided(): void
    {
        $config = new ChannexConfig(
            apiKey: 'test',
            providerCode: 'Test',
            inboundApiKey: 'secret-key',
        );

        $handler = $this->createHandler(hotelExists: true, config: $config);

        // Valid API key
        $response = $handler->handle('HOTEL123', 'secret-key');
        $this->assertTrue($response['success']);

        // Invalid API key
        $response = $handler->handle('HOTEL123', 'wrong-key');
        $this->assertFalse($response['success']);
        $this->assertEquals('Unauthorized', $response['error']);
    }

    public function test_skips_api_key_validation_when_no_config(): void
    {
        $handler = $this->createHandler(hotelExists: true, config: null);
        $response = $handler->handle('HOTEL123', 'any-key');

        $this->assertTrue($response['success']);
    }
}
