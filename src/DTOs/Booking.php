<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel\DTOs;

use GungCahyadiPP\ChannexOpenChannel\Exceptions\ValidationException;

class Booking
{
    public const STATUS_NEW = 'new';
    public const STATUS_MODIFIED = 'modified';
    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_COLLECT_OTA = 'ota';
    public const PAYMENT_COLLECT_PROPERTY = 'property';

    public const PAYMENT_TYPE_CREDIT_CARD = 'credit_card';
    public const PAYMENT_TYPE_BANK_TRANSFER = 'bank_transfer';

    /**
     * @param string $status Booking status: new, modified, cancelled
     * @param string $hotelCode Hotel code for property identification
     * @param string $arrivalDate Arrival date (YYYY-MM-DD)
     * @param string $departureDate Departure date (YYYY-MM-DD)
     * @param string $currency ISO 4217 3-alpha currency code
     * @param Customer $customer Customer information
     * @param Room[] $rooms Booking rooms
     * @param string|null $otaName OTA code if booking from third party
     * @param string|null $reservationId Unique booking ID
     * @param string|null $arrivalHour Arrival time (HH:MM)
     * @param string|null $paymentCollect Payment collection point: ota or property
     * @param string|null $paymentType Payment type: credit_card or bank_transfer
     * @param string|null $notes Guest notes or special requests
     * @param Guarantee|null $guarantee Credit card guarantee
     * @param Service[] $services Additional services/extras
     * @param Deposit[] $deposits Deposits/charges
     * @param array|null $meta Additional metadata
     */
    public function __construct(
        public string $status,
        public string $hotelCode,
        public string $arrivalDate,
        public string $departureDate,
        public string $currency,
        public Customer $customer,
        public array $rooms,
        public ?string $otaName = null,
        public ?string $reservationId = null,
        public ?string $arrivalHour = null,
        public ?string $paymentCollect = null,
        public ?string $paymentType = null,
        public ?string $notes = null,
        public ?Guarantee $guarantee = null,
        public array $services = [],
        public array $deposits = [],
        public ?array $meta = null,
    ) {}

    /**
     * @throws ValidationException
     */
    public function validate(): void
    {
        if (!in_array($this->status, [self::STATUS_NEW, self::STATUS_MODIFIED, self::STATUS_CANCELLED], true)) {
            throw new ValidationException('Invalid booking status. Must be: new, modified, or cancelled');
        }

        if (empty($this->hotelCode)) {
            throw new ValidationException('Hotel code is required');
        }

        if (empty($this->rooms)) {
            throw new ValidationException('At least one room is required');
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->arrivalDate)) {
            throw new ValidationException('Arrival date must be in YYYY-MM-DD format');
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->departureDate)) {
            throw new ValidationException('Departure date must be in YYYY-MM-DD format');
        }

        if (strlen($this->currency) !== 3) {
            throw new ValidationException('Currency must be a 3-character ISO 4217 code');
        }
    }

    public function toArray(): array
    {
        $data = [
            'status' => $this->status,
            'hotel_code' => $this->hotelCode,
            'arrival_date' => $this->arrivalDate,
            'departure_date' => $this->departureDate,
            'currency' => $this->currency,
            'customer' => $this->customer->toArray(),
            'rooms' => array_map(fn(Room $room) => $room->toArray(), $this->rooms),
        ];

        if ($this->otaName !== null) {
            $data['ota_name'] = $this->otaName;
        }

        if ($this->reservationId !== null) {
            $data['reservation_id'] = $this->reservationId;
        }

        if ($this->arrivalHour !== null) {
            $data['arrival_hour'] = $this->arrivalHour;
        }

        if ($this->paymentCollect !== null) {
            $data['payment_collect'] = $this->paymentCollect;
        }

        if ($this->paymentType !== null) {
            $data['payment_type'] = $this->paymentType;
        }

        if ($this->notes !== null) {
            $data['notes'] = $this->notes;
        }

        if ($this->guarantee !== null) {
            $data['guarantee'] = $this->guarantee->toArray();
        }

        if (!empty($this->services)) {
            $data['services'] = array_map(fn(Service $service) => $service->toArray(), $this->services);
        }

        if (!empty($this->deposits)) {
            $data['deposits'] = array_map(fn(Deposit $deposit) => $deposit->toArray(), $this->deposits);
        }

        if ($this->meta !== null) {
            $data['meta'] = $this->meta;
        }

        return $data;
    }

    public static function fromArray(array $data): self
    {
        $rooms = array_map(fn($room) => Room::fromArray($room), $data['rooms']);
        $services = isset($data['services'])
            ? array_map(fn($service) => Service::fromArray($service), $data['services'])
            : [];
        $deposits = isset($data['deposits'])
            ? array_map(fn($deposit) => Deposit::fromArray($deposit), $data['deposits'])
            : [];

        return new self(
            status: $data['status'],
            hotelCode: $data['hotel_code'],
            arrivalDate: $data['arrival_date'],
            departureDate: $data['departure_date'],
            currency: $data['currency'],
            customer: Customer::fromArray($data['customer']),
            rooms: $rooms,
            otaName: $data['ota_name'] ?? null,
            reservationId: $data['reservation_id'] ?? null,
            arrivalHour: $data['arrival_hour'] ?? null,
            paymentCollect: $data['payment_collect'] ?? null,
            paymentType: $data['payment_type'] ?? null,
            notes: $data['notes'] ?? null,
            guarantee: isset($data['guarantee']) ? Guarantee::fromArray($data['guarantee']) : null,
            services: $services,
            deposits: $deposits,
            meta: $data['meta'] ?? null,
        );
    }
}
