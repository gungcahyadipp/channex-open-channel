<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel\Handlers;

use GungCahyadiPP\ChannexOpenChannel\ChannexConfig;
use GungCahyadiPP\ChannexOpenChannel\DTOs\RoomType;

/**
 * Abstract handler for mapping_details endpoint
 * 
 * Implement this handler to provide room and rate mapping information to Channex.
 * 
 * Expected endpoint: GET {your-endpoint}/mapping_details/?hotel_code={HOTEL_CODE}
 * Header: api-key (optional, for validation)
 */
abstract class AbstractMappingDetailsHandler
{
    public function __construct(
        protected ?ChannexConfig $config = null,
    ) {}

    /**
     * Get room types with rate plans for the given hotel
     * 
     * @param string $hotelCode The hotel code
     * @return RoomType[] Array of room types with their rate plans
     */
    abstract protected function getRoomTypes(string $hotelCode): array;

    /**
     * Handle the mapping details request
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

        $roomTypes = $this->getRoomTypes($hotelCode);

        return [
            'data' => [
                'type' => 'mapping_details',
                'attributes' => [
                    'room_types' => array_map(
                        fn(RoomType $roomType) => $roomType->toArray(),
                        $roomTypes
                    ),
                ],
            ],
        ];
    }
}
