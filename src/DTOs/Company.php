<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel\DTOs;

class Company
{
    public function __construct(
        public ?string $title = null,
        public ?string $number = null,
        public ?string $numberType = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'number' => $this->number,
            'number_type' => $this->numberType,
        ], fn($value) => $value !== null);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            number: $data['number'] ?? null,
            numberType: $data['number_type'] ?? null,
        );
    }
}
