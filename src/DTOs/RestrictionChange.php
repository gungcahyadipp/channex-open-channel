<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel\DTOs;

class RestrictionChange
{
    /**
     * @param Rate[] $rates
     */
    public function __construct(
        public string $ratePlanId,
        public string $roomTypeId,
        public string $dateFrom,
        public string $dateTo,
        public array $rates = [],
        public bool $stopSell = false,
        public bool $closedToArrival = false,
        public bool $closedToDeparture = false,
        public int $minStayArrival = 1,
        public int $minStayThrough = 1,
        public int $maxStay = 0,
    ) {}

    public function toArray(): array
    {
        return [
            'type' => 'restriction_changes',
            'attributes' => [
                'rate_plan_id' => $this->ratePlanId,
                'room_type_id' => $this->roomTypeId,
                'date_from' => $this->dateFrom,
                'date_to' => $this->dateTo,
                'rates' => array_map(fn(Rate $rate) => $rate->toArray(), $this->rates),
                'stop_sell' => $this->stopSell,
                'closed_to_arrival' => $this->closedToArrival,
                'closed_to_departure' => $this->closedToDeparture,
                'min_stay_arrival' => $this->minStayArrival,
                'min_stay_through' => $this->minStayThrough,
                'max_stay' => $this->maxStay,
            ],
        ];
    }

    public static function fromArray(array $data): self
    {
        $attributes = $data['attributes'] ?? $data;

        $rates = isset($attributes['rates'])
            ? array_map(fn($rate) => Rate::fromArray($rate), $attributes['rates'])
            : [];

        return new self(
            ratePlanId: $attributes['rate_plan_id'],
            roomTypeId: $attributes['room_type_id'],
            dateFrom: $attributes['date_from'],
            dateTo: $attributes['date_to'],
            rates: $rates,
            stopSell: $attributes['stop_sell'] ?? false,
            closedToArrival: $attributes['closed_to_arrival'] ?? false,
            closedToDeparture: $attributes['closed_to_departure'] ?? false,
            minStayArrival: $attributes['min_stay_arrival'] ?? 1,
            minStayThrough: $attributes['min_stay_through'] ?? 1,
            maxStay: $attributes['max_stay'] ?? 0,
        );
    }

    /**
     * Check if the inventory is open for sale
     */
    public function isAvailable(): bool
    {
        return !$this->stopSell;
    }

    /**
     * Get the rate for a specific occupancy, or the first rate if not specified
     */
    public function getRateForOccupancy(?int $occupancy = null): ?Rate
    {
        if (empty($this->rates)) {
            return null;
        }

        if ($occupancy === null) {
            return $this->rates[0];
        }

        foreach ($this->rates as $rate) {
            if ($rate->occupancy === $occupancy) {
                return $rate;
            }
        }

        return null;
    }
}
