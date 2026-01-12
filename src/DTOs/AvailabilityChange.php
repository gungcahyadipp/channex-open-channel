<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel\DTOs;

class AvailabilityChange
{
    public function __construct(
        public string $roomTypeId,
        public string $ratePlanId,
        public string $dateFrom,
        public string $dateTo,
        public int $availability,
    ) {}

    public function toArray(): array
    {
        return [
            'type' => 'availability_changes',
            'attributes' => [
                'room_type_id' => $this->roomTypeId,
                'rate_plan_id' => $this->ratePlanId,
                'date_from' => $this->dateFrom,
                'date_to' => $this->dateTo,
                'availability' => $this->availability,
            ],
        ];
    }

    public static function fromArray(array $data): self
    {
        $attributes = $data['attributes'] ?? $data;

        return new self(
            roomTypeId: $attributes['room_type_id'],
            ratePlanId: $attributes['rate_plan_id'],
            dateFrom: $attributes['date_from'],
            dateTo: $attributes['date_to'],
            availability: $attributes['availability'],
        );
    }
}
