<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel\DTOs;

class RatePlan
{
    public const SELL_MODE_PER_ROOM = 'per_room';
    public const SELL_MODE_PER_PERSON = 'per_person';

    public function __construct(
        public string $id,
        public string $title,
        public string $sellMode,
        public int $maxPersons,
        public string $currency,
        public bool $readOnly = false,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'sell_mode' => $this->sellMode,
            'max_persons' => $this->maxPersons,
            'currency' => $this->currency,
            'read_only' => $this->readOnly,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            title: $data['title'],
            sellMode: $data['sell_mode'],
            maxPersons: $data['max_persons'],
            currency: $data['currency'],
            readOnly: $data['read_only'] ?? false,
        );
    }

    public function isPerRoom(): bool
    {
        return $this->sellMode === self::SELL_MODE_PER_ROOM;
    }

    public function isPerPerson(): bool
    {
        return $this->sellMode === self::SELL_MODE_PER_PERSON;
    }
}
