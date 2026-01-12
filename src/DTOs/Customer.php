<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel\DTOs;

class Customer
{
    public function __construct(
        public string $surname,
        public ?string $name = null,
        public ?string $country = null,
        public ?string $city = null,
        public ?string $address = null,
        public ?string $zip = null,
        public ?string $mail = null,
        public ?string $phone = null,
        public ?string $language = null,
        public ?Company $company = null,
        public ?array $meta = null,
    ) {}

    public function toArray(): array
    {
        $data = array_filter([
            'name' => $this->name,
            'surname' => $this->surname,
            'country' => $this->country,
            'city' => $this->city,
            'address' => $this->address,
            'zip' => $this->zip,
            'mail' => $this->mail,
            'phone' => $this->phone,
            'language' => $this->language,
        ], fn($value) => $value !== null);

        if ($this->company !== null) {
            $data['company'] = $this->company->toArray();
        }

        if ($this->meta !== null) {
            $data['meta'] = $this->meta;
        }

        return $data;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            surname: $data['surname'],
            name: $data['name'] ?? null,
            country: $data['country'] ?? null,
            city: $data['city'] ?? null,
            address: $data['address'] ?? null,
            zip: $data['zip'] ?? null,
            mail: $data['mail'] ?? null,
            phone: $data['phone'] ?? null,
            language: $data['language'] ?? null,
            company: isset($data['company']) ? Company::fromArray($data['company']) : null,
            meta: $data['meta'] ?? null,
        );
    }
}
