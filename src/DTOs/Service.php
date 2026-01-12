<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel\DTOs;

class Service
{
    public const TYPE_MEAL = 'Meal';
    public const TYPE_FEE = 'Fee';
    public const TYPE_EXTRA = 'Extra';

    public const PRICE_MODE_PER_STAY = 'Per stay';
    public const PRICE_MODE_PER_NIGHT = 'Per night';
    public const PRICE_MODE_PER_PERSON = 'Per person';
    public const PRICE_MODE_PER_PERSON_PER_NIGHT = 'Per person per night';

    public function __construct(
        public string $type,
        public string $totalPrice,
        public string $pricePerUnit,
        public string $priceMode,
        public int $persons,
        public int $nights,
        public string $name,
        public ?int $roomIndex = null,
        public ?string $applicableDate = null,
        public bool $excluded = false,
    ) {}

    public function toArray(): array
    {
        $data = [
            'type' => $this->type,
            'total_price' => $this->totalPrice,
            'price_per_unit' => $this->pricePerUnit,
            'price_mode' => $this->priceMode,
            'persons' => $this->persons,
            'nights' => $this->nights,
            'name' => $this->name,
        ];

        if ($this->roomIndex !== null) {
            $data['room_index'] = $this->roomIndex;
        }

        if ($this->applicableDate !== null) {
            $data['applicable_date'] = $this->applicableDate;
        }

        if ($this->excluded) {
            $data['excluded'] = $this->excluded;
        }

        return $data;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'],
            totalPrice: $data['total_price'],
            pricePerUnit: $data['price_per_unit'],
            priceMode: $data['price_mode'],
            persons: $data['persons'],
            nights: $data['nights'],
            name: $data['name'],
            roomIndex: $data['room_index'] ?? null,
            applicableDate: $data['applicable_date'] ?? null,
            excluded: $data['excluded'] ?? false,
        );
    }

    /**
     * Create a cancellation fee service
     */
    public static function createCancellationFee(string $amount, ?int $roomIndex = null, ?string $date = null): self
    {
        return new self(
            type: self::TYPE_FEE,
            totalPrice: $amount,
            pricePerUnit: $amount,
            priceMode: self::PRICE_MODE_PER_STAY,
            persons: 0,
            nights: 0,
            name: 'Cancellation Fee',
            roomIndex: $roomIndex,
            applicableDate: $date,
        );
    }
}
