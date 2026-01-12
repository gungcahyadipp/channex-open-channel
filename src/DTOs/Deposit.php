<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel\DTOs;

class Deposit
{
    public function __construct(
        public string|int $amount,
        public string $currency,
        public string $type,
        public ?string $chargedAt = null,
        public ?string $notes = null,
        public ?array $providerMeta = null,
    ) {}

    public function toArray(): array
    {
        $data = [
            'amount' => (string) $this->amount,
            'currency' => $this->currency,
            'type' => $this->type,
        ];

        if ($this->chargedAt !== null) {
            $data['charged_at'] = $this->chargedAt;
        }

        if ($this->notes !== null) {
            $data['notes'] = $this->notes;
        }

        if ($this->providerMeta !== null) {
            $data['provider_meta'] = $this->providerMeta;
        }

        return $data;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            amount: $data['amount'],
            currency: $data['currency'],
            type: $data['type'],
            chargedAt: $data['charged_at'] ?? null,
            notes: $data['notes'] ?? null,
            providerMeta: $data['provider_meta'] ?? null,
        );
    }
}
