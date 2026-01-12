<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel\DTOs;

class Occupancy
{
    public function __construct(
        public int $adults = 1,
        public int $children = 0,
        public int $infants = 0,
    ) {}

    public function toArray(): array
    {
        return [
            'adults' => $this->adults,
            'children' => $this->children,
            'infants' => $this->infants,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            adults: $data['adults'] ?? 1,
            children: $data['children'] ?? 0,
            infants: $data['infants'] ?? 0,
        );
    }

    public function getTotalGuests(): int
    {
        return $this->adults + $this->children + $this->infants;
    }
}
