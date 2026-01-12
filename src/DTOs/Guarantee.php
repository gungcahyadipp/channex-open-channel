<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel\DTOs;

class Guarantee
{
    public function __construct(
        public string $expirationDate,
        public string $cvv,
        public string $cardholderName,
        public string $cardType,
        public string $cardNumber,
        public ?array $meta = null,
    ) {}

    public function toArray(): array
    {
        $data = [
            'expiration_date' => $this->expirationDate,
            'cvv' => $this->cvv,
            'cardholder_name' => $this->cardholderName,
            'card_type' => $this->cardType,
            'card_number' => $this->cardNumber,
        ];

        if ($this->meta !== null) {
            $data['meta'] = $this->meta;
        }

        return $data;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            expirationDate: $data['expiration_date'],
            cvv: $data['cvv'],
            cardholderName: $data['cardholder_name'],
            cardType: $data['card_type'],
            cardNumber: $data['card_number'],
            meta: $data['meta'] ?? null,
        );
    }

    /**
     * Create a virtual card guarantee with additional metadata
     */
    public static function createVirtualCard(
        string $expirationDate,
        string $cvv,
        string $cardholderName,
        string $cardType,
        string $cardNumber,
        string $currencyCode,
        int $currentBalance,
        int $decimalPlaces,
        string $effectiveDate,
        string $virtualCardExpirationDate,
    ): self {
        return new self(
            expirationDate: $expirationDate,
            cvv: $cvv,
            cardholderName: $cardholderName,
            cardType: $cardType,
            cardNumber: $cardNumber,
            meta: [
                'virtual_card_currency_code' => $currencyCode,
                'virtual_card_current_balance' => $currentBalance,
                'virtual_card_decimal_places' => $decimalPlaces,
                'virtual_card_effective_date' => $effectiveDate,
                'virtual_card_expiration_date' => $virtualCardExpirationDate,
            ],
        );
    }
}
