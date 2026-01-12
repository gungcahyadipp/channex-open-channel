<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel\DTOs;

class Room
{
    /**
     * @param string $roomTypeCode Room type code
     * @param Occupancy $occupancy Room occupancy
     * @param RoomDay[] $days Daily pricing breakdown
     * @param int|null $index Room index for service association
     * @param Guest[] $guests Guest information
     * @param array|null $meta Additional metadata
     */
    public function __construct(
        public string $roomTypeCode,
        public Occupancy $occupancy,
        public array $days,
        public ?int $index = null,
        public array $guests = [],
        public ?array $meta = null,
    ) {}

    public function toArray(): array
    {
        $data = [
            'room_type_code' => $this->roomTypeCode,
            'occupancy' => $this->occupancy->toArray(),
            'days' => array_map(fn(RoomDay $day) => $day->toArray(), $this->days),
        ];

        if ($this->index !== null) {
            $data['index'] = $this->index;
        }

        if (!empty($this->guests)) {
            $data['guests'] = array_map(fn(Guest $guest) => $guest->toArray(), $this->guests);
        }

        if ($this->meta !== null) {
            $data['meta'] = $this->meta;
        }

        return $data;
    }

    public static function fromArray(array $data): self
    {
        $days = array_map(fn($day) => RoomDay::fromArray($day), $data['days']);
        $guests = isset($data['guests'])
            ? array_map(fn($guest) => Guest::fromArray($guest), $data['guests'])
            : [];

        return new self(
            roomTypeCode: $data['room_type_code'],
            occupancy: Occupancy::fromArray($data['occupancy']),
            days: $days,
            index: $data['index'] ?? null,
            guests: $guests,
            meta: $data['meta'] ?? null,
        );
    }
}
