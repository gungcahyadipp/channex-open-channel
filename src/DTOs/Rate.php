<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel\DTOs;

class Rate
{
    public function __construct(
        public string $rate,
        public string $currency,
        public int $fractionSize = 2,
        public ?int $occupancy = null,
    ) {}

    public function toArray(): array
    {
        $data = [
            'rate' => $this->rate,
            'currency' => $this->currency,
            'fraction_size' => $this->fractionSize,
        ];

        if ($this->occupancy !== null) {
            $data['occupancy'] = $this->occupancy;
        }

        return $data;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            rate: $data['rate'],
            currency: $data['currency'],
            fractionSize: $data['fraction_size'] ?? 2,
            occupancy: $data['occupancy'] ?? null,
        );
    }

    /**
     * Get the rate as a float value
     */
    public function getAmount(): float
    {
        return (float) $this->rate;
    }
}
