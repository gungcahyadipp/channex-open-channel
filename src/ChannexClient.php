<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel;

use GungCahyadiPP\ChannexOpenChannel\DTOs\Booking;
use GungCahyadiPP\ChannexOpenChannel\Exceptions\ApiException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ChannexClient
{
    private Client $httpClient;

    public function __construct(
        private ChannexConfig $config,
        ?Client $httpClient = null,
    ) {
        $this->httpClient = $httpClient ?? new Client([
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'api-key' => $this->config->getApiKey(),
            ],
        ]);
    }

    /**
     * Push a booking to Channex
     * 
     * @param Booking $booking The booking to push
     * @return array The response from Channex
     * @throws ApiException
     */
    public function pushBooking(Booking $booking): array
    {
        $endpoint = $this->config->getSecureEndpoint() . '/channel_webhooks/open_channel/new_booking';

        $payload = [
            'booking' => array_merge(
                ['provider_code' => $this->config->getProviderCode()],
                $booking->toArray()
            ),
        ];

        return $this->sendRequest('POST', $endpoint, $payload);
    }

    /**
     * Request a full sync from Channex
     * 
     * @param string $hotelCode The hotel code to sync
     * @return array The response from Channex
     * @throws ApiException
     */
    public function requestFullSync(string $hotelCode): array
    {
        $endpoint = $this->config->getApiEndpoint() . '/channel_webhooks/open_channel/request_full_sync';

        $payload = [
            'provider_code' => $this->config->getProviderCode(),
            'hotel_code' => $hotelCode,
        ];

        return $this->sendRequest('POST', $endpoint, $payload);
    }

    /**
     * Send HTTP request to Channex API
     * 
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $payload Request payload
     * @return array Response data
     * @throws ApiException
     */
    private function sendRequest(string $method, string $endpoint, array $payload): array
    {
        try {
            $response = $this->httpClient->request($method, $endpoint, [
                'json' => $payload,
            ]);

            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ApiException('Invalid JSON response from Channex API');
            }

            return $data;
        } catch (GuzzleException $e) {
            throw new ApiException(
                sprintf('Channex API request failed: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get the configuration
     */
    public function getConfig(): ChannexConfig
    {
        return $this->config;
    }
}
