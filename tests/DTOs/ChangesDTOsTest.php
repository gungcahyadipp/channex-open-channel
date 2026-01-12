<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel\Tests\DTOs;

use GungCahyadiPP\ChannexOpenChannel\DTOs\AvailabilityChange;
use GungCahyadiPP\ChannexOpenChannel\DTOs\Rate;
use GungCahyadiPP\ChannexOpenChannel\DTOs\RestrictionChange;
use PHPUnit\Framework\TestCase;

class ChangesDTOsTest extends TestCase
{
    public function test_availability_change_from_array(): void
    {
        $data = [
            'type' => 'availability_changes',
            'attributes' => [
                'room_type_id' => 'room_1',
                'rate_plan_id' => 'rate_1',
                'date_from' => '2024-01-01',
                'date_to' => '2024-01-05',
                'availability' => 10,
            ],
        ];

        $change = AvailabilityChange::fromArray($data);

        $this->assertEquals('room_1', $change->roomTypeId);
        $this->assertEquals('rate_1', $change->ratePlanId);
        $this->assertEquals('2024-01-01', $change->dateFrom);
        $this->assertEquals('2024-01-05', $change->dateTo);
        $this->assertEquals(10, $change->availability);
    }

    public function test_restriction_change_from_array(): void
    {
        $data = [
            'type' => 'restriction_changes',
            'attributes' => [
                'rate_plan_id' => 'rate_1',
                'room_type_id' => 'room_1',
                'date_from' => '2024-01-01',
                'date_to' => '2024-01-05',
                'rates' => [
                    ['rate' => '100.00', 'currency' => 'USD', 'fraction_size' => 2, 'occupancy' => 2],
                ],
                'stop_sell' => false,
                'closed_to_arrival' => true,
                'closed_to_departure' => false,
                'min_stay_arrival' => 2,
                'min_stay_through' => 1,
                'max_stay' => 7,
            ],
        ];

        $change = RestrictionChange::fromArray($data);

        $this->assertEquals('rate_1', $change->ratePlanId);
        $this->assertEquals('room_1', $change->roomTypeId);
        $this->assertFalse($change->stopSell);
        $this->assertTrue($change->closedToArrival);
        $this->assertEquals(2, $change->minStayArrival);
        $this->assertEquals(7, $change->maxStay);
        $this->assertCount(1, $change->rates);
    }

    public function test_restriction_change_get_rate_for_occupancy(): void
    {
        $change = new RestrictionChange(
            ratePlanId: 'rate_1',
            roomTypeId: 'room_1',
            dateFrom: '2024-01-01',
            dateTo: '2024-01-05',
            rates: [
                new Rate(rate: '150.00', currency: 'USD', occupancy: 3),
                new Rate(rate: '100.00', currency: 'USD', occupancy: 2),
                new Rate(rate: '80.00', currency: 'USD', occupancy: 1),
            ],
        );

        $this->assertEquals('100.00', $change->getRateForOccupancy(2)?->rate);
        $this->assertEquals('80.00', $change->getRateForOccupancy(1)?->rate);
        $this->assertNull($change->getRateForOccupancy(5));
        $this->assertEquals('150.00', $change->getRateForOccupancy()?->rate); // First rate
    }

    public function test_restriction_change_is_available(): void
    {
        $available = new RestrictionChange(
            ratePlanId: 'rate_1',
            roomTypeId: 'room_1',
            dateFrom: '2024-01-01',
            dateTo: '2024-01-02',
            stopSell: false
        );

        $unavailable = new RestrictionChange(
            ratePlanId: 'rate_1',
            roomTypeId: 'room_1',
            dateFrom: '2024-01-01',
            dateTo: '2024-01-02',
            stopSell: true
        );

        $this->assertTrue($available->isAvailable());
        $this->assertFalse($unavailable->isAvailable());
    }

    public function test_rate_get_amount(): void
    {
        $rate = new Rate(rate: '150.50', currency: 'USD');

        $this->assertEquals(150.50, $rate->getAmount());
    }
}
