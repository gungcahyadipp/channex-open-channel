<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel\Tests\DTOs;

use GungCahyadiPP\ChannexOpenChannel\DTOs\Booking;
use GungCahyadiPP\ChannexOpenChannel\DTOs\Customer;
use GungCahyadiPP\ChannexOpenChannel\DTOs\Occupancy;
use GungCahyadiPP\ChannexOpenChannel\DTOs\Room;
use GungCahyadiPP\ChannexOpenChannel\DTOs\RoomDay;
use GungCahyadiPP\ChannexOpenChannel\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;

class BookingTest extends TestCase
{
    private function createValidBooking(): Booking
    {
        return new Booking(
            status: Booking::STATUS_NEW,
            hotelCode: 'HOTEL123',
            arrivalDate: '2024-05-09',
            departureDate: '2024-05-10',
            currency: 'USD',
            customer: new Customer(surname: 'Doe', name: 'John'),
            rooms: [
                new Room(
                    roomTypeCode: 'ROOM001',
                    occupancy: new Occupancy(adults: 2),
                    days: [
                        new RoomDay(date: '2024-05-09', price: '100.00', ratePlanCode: 'RATE001'),
                    ]
                )
            ]
        );
    }

    public function test_can_create_valid_booking(): void
    {
        $booking = $this->createValidBooking();

        $this->assertEquals('new', $booking->status);
        $this->assertEquals('HOTEL123', $booking->hotelCode);
        $this->assertEquals('USD', $booking->currency);
    }

    public function test_booking_to_array(): void
    {
        $booking = $this->createValidBooking();
        $array = $booking->toArray();

        $this->assertEquals('new', $array['status']);
        $this->assertEquals('HOTEL123', $array['hotel_code']);
        $this->assertEquals('2024-05-09', $array['arrival_date']);
        $this->assertEquals('2024-05-10', $array['departure_date']);
        $this->assertEquals('USD', $array['currency']);
        $this->assertArrayHasKey('customer', $array);
        $this->assertArrayHasKey('rooms', $array);
        $this->assertCount(1, $array['rooms']);
    }

    public function test_booking_validation_passes_for_valid_data(): void
    {
        $booking = $this->createValidBooking();
        $booking->validate();

        $this->assertTrue(true); // No exception thrown
    }

    public function test_booking_validation_fails_for_invalid_status(): void
    {
        $this->expectException(ValidationException::class);

        $booking = new Booking(
            status: 'invalid_status',
            hotelCode: 'HOTEL123',
            arrivalDate: '2024-05-09',
            departureDate: '2024-05-10',
            currency: 'USD',
            customer: new Customer(surname: 'Doe'),
            rooms: [
                new Room(
                    roomTypeCode: 'ROOM001',
                    occupancy: new Occupancy(adults: 1),
                    days: [new RoomDay(date: '2024-05-09', price: '100', ratePlanCode: 'RATE1')]
                )
            ]
        );

        $booking->validate();
    }

    public function test_booking_validation_fails_for_empty_rooms(): void
    {
        $this->expectException(ValidationException::class);

        $booking = new Booking(
            status: 'new',
            hotelCode: 'HOTEL123',
            arrivalDate: '2024-05-09',
            departureDate: '2024-05-10',
            currency: 'USD',
            customer: new Customer(surname: 'Doe'),
            rooms: []
        );

        $booking->validate();
    }

    public function test_booking_from_array(): void
    {
        $data = [
            'status' => 'new',
            'hotel_code' => 'HOTEL123',
            'arrival_date' => '2024-05-09',
            'departure_date' => '2024-05-10',
            'currency' => 'USD',
            'customer' => [
                'surname' => 'Doe',
                'name' => 'John',
            ],
            'rooms' => [
                [
                    'room_type_code' => 'ROOM001',
                    'occupancy' => ['adults' => 2, 'children' => 0, 'infants' => 0],
                    'days' => [
                        ['date' => '2024-05-09', 'price' => '100.00', 'rate_plan_code' => 'RATE001'],
                    ],
                ],
            ],
        ];

        $booking = Booking::fromArray($data);

        $this->assertEquals('new', $booking->status);
        $this->assertEquals('HOTEL123', $booking->hotelCode);
        $this->assertEquals('John', $booking->customer->name);
        $this->assertCount(1, $booking->rooms);
    }
}
