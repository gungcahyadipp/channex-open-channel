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
        public ?bool $stopSell = null,
        public ?bool $closedToArrival = null,
        public ?bool $closedToDeparture = null,
        public ?int $minStayArrival = null,
        public ?int $minStayThrough = null,
        public ?int $maxStay = null,
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
            // array_key_exists (bukan ??) SENGAJA dipakai di bawah ini: field-field restriction
            // ini nullable karena Channex bisa mengirim payload yang HANYA berisi `rates` (update
            // harga murni) tanpa field stop_sell/CTA/CTD/min_stay sama sekali. `??` tidak bisa
            // membedakan "key tidak ada di payload" dari "key ada dengan nilai false/0" — makanya
            // sebelumnya field ini selalu default ke false/1/0 walau Channex tidak pernah
            // mengirimnya, dan consumer yang mengecek `!== null` jadi selalu true (dead code),
            // menimpa ulang stop_sell/CTA/CTD yang sudah benar dari perubahan sebelumnya di batch
            // yang sama. JANGAN kembalikan ke `??` dengan default non-null.
            stopSell: array_key_exists('stop_sell', $attributes) ? (bool) $attributes['stop_sell'] : null,
            closedToArrival: array_key_exists('closed_to_arrival', $attributes) ? (bool) $attributes['closed_to_arrival'] : null,
            closedToDeparture: array_key_exists('closed_to_departure', $attributes) ? (bool) $attributes['closed_to_departure'] : null,
            minStayArrival: array_key_exists('min_stay_arrival', $attributes) ? (int) $attributes['min_stay_arrival'] : null,
            minStayThrough: array_key_exists('min_stay_through', $attributes) ? (int) $attributes['min_stay_through'] : null,
            maxStay: array_key_exists('max_stay', $attributes) ? (int) $attributes['max_stay'] : null,
        );
    }

    /**
     * Check if the inventory is open for sale.
     *
     * Returns null (unknown) when this change didn't carry a stop_sell value at all —
     * callers must treat null as "no change to make", not as "available".
     */
    public function isAvailable(): ?bool
    {
        return $this->stopSell === null ? null : !$this->stopSell;
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
