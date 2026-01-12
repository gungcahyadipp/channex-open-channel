<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel\Handlers;

use GungCahyadiPP\ChannexOpenChannel\ChannexConfig;
use GungCahyadiPP\ChannexOpenChannel\DTOs\AvailabilityChange;
use GungCahyadiPP\ChannexOpenChannel\DTOs\RestrictionChange;

/**
 * Abstract handler for changes endpoint
 * 
 * Implement this handler to process inventory changes from Channex.
 * 
 * Expected endpoint: POST {your-endpoint}/changes/
 * Header: api-key (optional, for validation)
 */
abstract class AbstractChangesHandler
{
    public function __construct(
        protected ?ChannexConfig $config = null,
    ) {}

    /**
     * Process availability changes
     * 
     * @param string $hotelCode The hotel code
     * @param AvailabilityChange[] $changes Array of availability changes
     * @return void
     */
    abstract protected function processAvailabilityChanges(string $hotelCode, array $changes): void;

    /**
     * Process restriction changes (rates, stop sell, min stay, etc.)
     * 
     * @param string $hotelCode The hotel code
     * @param RestrictionChange[] $changes Array of restriction changes
     * @return void
     */
    abstract protected function processRestrictionChanges(string $hotelCode, array $changes): void;

    /**
     * Generate a unique ID for tracking this request
     * 
     * @return string Unique identifier
     */
    protected function generateUniqueId(): string
    {
        return uniqid('channex_', true);
    }

    /**
     * Handle the changes notification request
     * 
     * @param array $payload The raw request payload from Channex
     * @param string|null $apiKey The API key from request header (optional)
     * @return array The response array
     */
    public function handle(array $payload, ?string $apiKey = null): array
    {
        $uniqueId = $this->generateUniqueId();

        // Validate API key if config is provided
        if ($this->config !== null && !$this->config->validateInboundApiKey($apiKey)) {
            return [
                'success' => false,
                'unique_id' => $uniqueId,
                'error' => 'Unauthorized',
            ];
        }

        try {
            foreach ($payload['data'] ?? [] as $notification) {
                if (($notification['type'] ?? '') !== 'changes_notification') {
                    continue;
                }

                $attributes = $notification['attributes'] ?? [];
                $hotelCode = $attributes['hotel_code'] ?? '';
                $changes = $attributes['changes'] ?? [];

                $availabilityChanges = [];
                $restrictionChanges = [];

                foreach ($changes as $change) {
                    $type = $change['type'] ?? '';

                    if ($type === 'availability_changes') {
                        $availabilityChanges[] = AvailabilityChange::fromArray($change);
                    } elseif ($type === 'restriction_changes') {
                        $restrictionChanges[] = RestrictionChange::fromArray($change);
                    }
                }

                if (!empty($availabilityChanges)) {
                    $this->processAvailabilityChanges($hotelCode, $availabilityChanges);
                }

                if (!empty($restrictionChanges)) {
                    $this->processRestrictionChanges($hotelCode, $restrictionChanges);
                }
            }

            return [
                'success' => true,
                'unique_id' => $uniqueId,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'unique_id' => $uniqueId,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Parse the raw request body (JSON) into an array
     * 
     * @param string $rawBody Raw JSON body
     * @return array Parsed payload
     */
    public function parseRequest(string $rawBody): array
    {
        $payload = json_decode($rawBody, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON payload');
        }

        return $payload;
    }
}
