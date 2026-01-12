<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel\DTOs;

class Guest
{
    public function __construct(
        public ?string $name = null,
        public ?string $surname = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'surname' => $this->surname,
        ], fn($value) => $value !== null);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            surname: $data['surname'] ?? null,
        );
    }
}
