<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel\Tests\DTOs;

use GungCahyadiPP\ChannexOpenChannel\DTOs\RatePlan;
use GungCahyadiPP\ChannexOpenChannel\DTOs\RoomType;
use PHPUnit\Framework\TestCase;

class MappingDTOsTest extends TestCase
{
    public function test_rate_plan_to_array(): void
    {
        $ratePlan = new RatePlan(
            id: 'rate_1',
            title: 'Best Available Rate',
            sellMode: RatePlan::SELL_MODE_PER_ROOM,
            maxPersons: 2,
            currency: 'USD',
            readOnly: false
        );

        $array = $ratePlan->toArray();

        $this->assertEquals('rate_1', $array['id']);
        $this->assertEquals('Best Available Rate', $array['title']);
        $this->assertEquals('per_room', $array['sell_mode']);
        $this->assertEquals(2, $array['max_persons']);
        $this->assertEquals('USD', $array['currency']);
        $this->assertFalse($array['read_only']);
    }

    public function test_rate_plan_sell_mode_helpers(): void
    {
        $perRoom = new RatePlan(
            id: '1',
            title: 'Test',
            sellMode: RatePlan::SELL_MODE_PER_ROOM,
            maxPersons: 2,
            currency: 'USD'
        );

        $perPerson = new RatePlan(
            id: '2',
            title: 'Test',
            sellMode: RatePlan::SELL_MODE_PER_PERSON,
            maxPersons: 2,
            currency: 'USD'
        );

        $this->assertTrue($perRoom->isPerRoom());
        $this->assertFalse($perRoom->isPerPerson());
        $this->assertTrue($perPerson->isPerPerson());
        $this->assertFalse($perPerson->isPerRoom());
    }

    public function test_room_type_to_array(): void
    {
        $roomType = new RoomType(id: 'room_1', title: 'Standard Room');
        $roomType->addRatePlan(new RatePlan(
            id: 'rate_1',
            title: 'BAR',
            sellMode: 'per_room',
            maxPersons: 2,
            currency: 'USD'
        ));

        $array = $roomType->toArray();

        $this->assertEquals('room_1', $array['id']);
        $this->assertEquals('Standard Room', $array['title']);
        $this->assertCount(1, $array['rate_plans']);
    }

    public function test_room_type_from_array(): void
    {
        $data = [
            'id' => 'room_1',
            'title' => 'Deluxe Room',
            'rate_plans' => [
                [
                    'id' => 'rate_1',
                    'title' => 'Standard Rate',
                    'sell_mode' => 'per_room',
                    'max_persons' => 3,
                    'currency' => 'EUR',
                    'read_only' => true,
                ],
            ],
        ];

        $roomType = RoomType::fromArray($data);

        $this->assertEquals('room_1', $roomType->id);
        $this->assertEquals('Deluxe Room', $roomType->title);
        $this->assertCount(1, $roomType->ratePlans);
        $this->assertTrue($roomType->ratePlans[0]->readOnly);
    }
}
