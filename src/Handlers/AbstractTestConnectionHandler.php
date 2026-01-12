<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel\Handlers;

use GungCahyadiPP\ChannexOpenChannel\ChannexConfig;

/**
 * Abstract handler for test_connection endpoint
 * 
 * Implement this handler to respond to Channex test connection requests.
 * 
 * Expected endpoint: GET {your-endpoint}/test_connection/?hotel_code={HOTEL_CODE}
 * Header: api-key (optional, for validation)
 */
abstract class AbstractTestConnectionHandler
{
    public function __construct(
        protected ?ChannexConfig $config = null,
    ) {}

    /**
     * Validate if the hotel code exists and is valid
     * 
     * @param string $hotelCode The hotel code to validate
     * @return bool True if the hotel code is valid
     */
    abstract protected function validateHotelCode(string $hotelCode): bool;

    /**
     * Handle the test connection request
     * 
     * @param string $hotelCode The hotel code from the request
     * @param string|null $apiKey The API key from request header (optional)
     * @return array The response array
     */
    public function handle(string $hotelCode, ?string $apiKey = null): array
    {
        // Validate API key if config is provided
        if ($this->config !== null && !$this->config->validateInboundApiKey($apiKey)) {
            return ['success' => false, 'error' => 'Unauthorized'];
        }

        if ($this->validateHotelCode($hotelCode)) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Invalid hotel code'];
    }
}
