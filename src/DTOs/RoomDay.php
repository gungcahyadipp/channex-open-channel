<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel\DTOs;

class RoomDay
{
    public function __construct(
        public string $date,
        public string|int $price,
        public string $ratePlanCode,
    ) {}

    public function toArray(): array
    {
        return [
            'date' => $this->date,
            'price' => (string) $this->price,
            'rate_plan_code' => $this->ratePlanCode,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            date: $data['date'],
            price: $data['price'],
            ratePlanCode: $data['rate_plan_code'],
        );
    }
}
