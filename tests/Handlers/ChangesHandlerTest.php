<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel\Tests\Handlers;

use GungCahyadiPP\ChannexOpenChannel\DTOs\AvailabilityChange;
use GungCahyadiPP\ChannexOpenChannel\DTOs\RestrictionChange;
use GungCahyadiPP\ChannexOpenChannel\Handlers\AbstractChangesHandler;
use PHPUnit\Framework\TestCase;

class ChangesHandlerTest extends TestCase
{
    public function test_handles_availability_changes(): void
    {
        $processedData = new \stdClass();
        $processedData->availability = null;
        $processedData->restrictions = null;

        $handler = new class($processedData) extends AbstractChangesHandler {
            public function __construct(private \stdClass $data)
            {
                parent::__construct(null);
            }

            protected function processAvailabilityChanges(string $hotelCode, array $changes): void
            {
                $this->data->availability = [
                    'hotelCode' => $hotelCode,
                    'changes' => $changes,
                ];
            }

            protected function processRestrictionChanges(string $hotelCode, array $changes): void
            {
                $this->data->restrictions = [
                    'hotelCode' => $hotelCode,
                    'changes' => $changes,
                ];
            }
        };

        $payload = [
            'data' => [
                [
                    'type' => 'changes_notification',
                    'attributes' => [
                        'request_id' => 'uuid-123',
                        'hotel_code' => 'HOTEL123',
                        'changes' => [
                            [
                                'type' => 'availability_changes',
                                'attributes' => [
                                    'room_type_id' => 'room_1',
                                    'rate_plan_id' => 'rate_1',
                                    'date_from' => '2024-01-01',
                                    'date_to' => '2024-01-05',
                                    'availability' => 10,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $handler->handle($payload);

        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('unique_id', $response);
        $this->assertNotNull($processedData->availability);
        $this->assertEquals('HOTEL123', $processedData->availability['hotelCode']);
        $this->assertCount(1, $processedData->availability['changes']);
        $this->assertInstanceOf(AvailabilityChange::class, $processedData->availability['changes'][0]);
    }

    public function test_handles_restriction_changes(): void
    {
        $processedData = new \stdClass();
        $processedData->availability = null;
        $processedData->restrictions = null;

        $handler = new class($processedData) extends AbstractChangesHandler {
            public function __construct(private \stdClass $data)
            {
                parent::__construct(null);
            }

            protected function processAvailabilityChanges(string $hotelCode, array $changes): void
            {
                $this->data->availability = [
                    'hotelCode' => $hotelCode,
                    'changes' => $changes,
                ];
            }

            protected function processRestrictionChanges(string $hotelCode, array $changes): void
            {
                $this->data->restrictions = [
                    'hotelCode' => $hotelCode,
                    'changes' => $changes,
                ];
            }
        };

        $payload = [
            'data' => [
                [
                    'type' => 'changes_notification',
                    'attributes' => [
                        'hotel_code' => 'HOTEL123',
                        'changes' => [
                            [
                                'type' => 'restriction_changes',
                                'attributes' => [
                                    'rate_plan_id' => 'rate_1',
                                    'room_type_id' => 'room_1',
                                    'date_from' => '2024-01-01',
                                    'date_to' => '2024-01-05',
                                    'rates' => [
                                        ['rate' => '100.00', 'currency' => 'USD'],
                                    ],
                                    'stop_sell' => false,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $handler->handle($payload);

        $this->assertTrue($response['success']);
        $this->assertNotNull($processedData->restrictions);
        $this->assertEquals('HOTEL123', $processedData->restrictions['hotelCode']);
        $this->assertInstanceOf(RestrictionChange::class, $processedData->restrictions['changes'][0]);
    }

    public function test_parse_request_valid_json(): void
    {
        $handler = new class extends AbstractChangesHandler {
            protected function processAvailabilityChanges(string $hotelCode, array $changes): void {}
            protected function processRestrictionChanges(string $hotelCode, array $changes): void {}
        };

        $json = '{"data": []}';
        $payload = $handler->parseRequest($json);

        $this->assertEquals(['data' => []], $payload);
    }

    public function test_parse_request_invalid_json_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $handler = new class extends AbstractChangesHandler {
            protected function processAvailabilityChanges(string $hotelCode, array $changes): void {}
            protected function processRestrictionChanges(string $hotelCode, array $changes): void {}
        };

        $handler->parseRequest('invalid json');
    }

    public function test_returns_error_on_exception(): void
    {
        $handler = new class extends AbstractChangesHandler {
            protected function processAvailabilityChanges(string $hotelCode, array $changes): void
            {
                throw new \RuntimeException('Database error');
            }

            protected function processRestrictionChanges(string $hotelCode, array $changes): void {}
        };

        $payload = [
            'data' => [
                [
                    'type' => 'changes_notification',
                    'attributes' => [
                        'hotel_code' => 'HOTEL123',
                        'changes' => [
                            ['type' => 'availability_changes', 'attributes' => [
                                'room_type_id' => 'r1',
                                'rate_plan_id' => 'rp1',
                                'date_from' => '2024-01-01',
                                'date_to' => '2024-01-02',
                                'availability' => 5,
                            ]],
                        ],
                    ],
                ],
            ],
        ];

        $response = $handler->handle($payload);

        $this->assertFalse($response['success']);
        $this->assertEquals('Database error', $response['error']);
    }
}
