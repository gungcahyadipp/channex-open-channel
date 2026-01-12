# Channex Open Channel PHP Package

PHP package untuk integrasi dengan Channex Open Channel API. Package ini mendukung penggunaan standalone (plain PHP 8.2+) maupun dengan Laravel framework.

## Requirements

- PHP 8.2 atau lebih tinggi
- Guzzle HTTP Client

## Installation

```bash
composer require gungcahyadipp/channex-open-channel
```

### Laravel Setup

Package ini menggunakan Laravel auto-discovery, jadi service provider akan otomatis terdaftar.

Publish config file:

```bash
php artisan vendor:publish --tag=channex-config
```

**Publish semua file sekaligus (config, controller, handlers, routes):**

```bash
php artisan vendor:publish --tag=channex
```

**Atau publish terpisah:**

```bash
# Hanya config
php artisan vendor:publish --tag=channex-config

# Hanya controller
php artisan vendor:publish --tag=channex-controller

# Hanya handlers  
php artisan vendor:publish --tag=channex-handlers

# Hanya routes
php artisan vendor:publish --tag=channex-routes
```

**Setelah publish routes, tambahkan ke `routes/api.php`:**

```php
require __DIR__ . '/channex.php';
```

Tambahkan ke file `.env`:

```env
# API Key untuk mengirim request ke Channex (push booking, request sync)
CHANNEX_API_KEY=open_channel_api_key

# API Key untuk validasi request yang masuk dari Channex (opsional)
CHANNEX_INBOUND_API_KEY=your-inbound-api-key

# Provider code Anda (gunakan OpenChannel untuk development)
CHANNEX_PROVIDER_CODE=OpenChannel

# Environment: staging atau production
CHANNEX_ENVIRONMENT=staging

# Custom endpoint URLs (opsional, kosongkan untuk menggunakan default)
CHANNEX_API_ENDPOINT=
CHANNEX_SECURE_ENDPOINT=
```

## Configuration

| Config Key | Description | Default |
|------------|-------------|---------|
| `api_key` | API key untuk mengirim request ke Channex | `open_channel_api_key` |
| `inbound_api_key` | API key untuk validasi request dari Channex | `null` |
| `provider_code` | Provider code unik dari Channex | `OpenChannel` |
| `environment` | `staging` atau `production` | `staging` |
| `endpoints.api` | Custom API endpoint (opsional) | `null` (auto) |
| `endpoints.secure` | Custom secure endpoint (opsional) | `null` (auto) |

### Default Endpoints

| Environment | API Endpoint | Secure Endpoint |
|-------------|--------------|-----------------|
| staging | `https://staging.channex.io/api/v1` | `https://secure-staging.channex.io/api/v1` |
| production | `https://app.channex.io/api/v1` | `https://secure.channex.io/api/v1` |

## Usage

### Standalone PHP

```php
<?php

use GungCahyadiPP\ChannexOpenChannel\ChannexConfig;
use GungCahyadiPP\ChannexOpenChannel\ChannexClient;
use GungCahyadiPP\ChannexOpenChannel\DTOs\Booking;
use GungCahyadiPP\ChannexOpenChannel\DTOs\Customer;
use GungCahyadiPP\ChannexOpenChannel\DTOs\Room;
use GungCahyadiPP\ChannexOpenChannel\DTOs\Occupancy;
use GungCahyadiPP\ChannexOpenChannel\DTOs\RoomDay;

// Initialize config
$config = new ChannexConfig(
    apiKey: 'your-api-key',
    providerCode: 'YOUR_PROVIDER_CODE',
    environment: 'staging',
    inboundApiKey: 'your-inbound-api-key', // optional
    customApiEndpoint: null, // optional override
    customSecureEndpoint: null, // optional override
);

// Create client
$client = new ChannexClient($config);

// Create booking
$booking = new Booking(
    status: Booking::STATUS_NEW,
    hotelCode: 'HOTEL123',
    arrivalDate: '2024-05-09',
    departureDate: '2024-05-10',
    currency: 'USD',
    customer: new Customer(
        surname: 'Doe',
        name: 'John',
        mail: 'john@example.com',
        phone: '+6281234567890'
    ),
    rooms: [
        new Room(
            roomTypeCode: 'ROOM001',
            occupancy: new Occupancy(adults: 2, children: 0, infants: 0),
            days: [
                new RoomDay(
                    date: '2024-05-09',
                    price: '100.00',
                    ratePlanCode: 'RATE001'
                )
            ]
        )
    ]
);

// Push booking to Channex
$response = $client->pushBooking($booking);

// Request full sync
$response = $client->requestFullSync('HOTEL123');
```

### With Laravel

```php
use GungCahyadiPP\ChannexOpenChannel\Laravel\Facades\Channex;
use GungCahyadiPP\ChannexOpenChannel\DTOs\Booking;
use GungCahyadiPP\ChannexOpenChannel\DTOs\Customer;
use GungCahyadiPP\ChannexOpenChannel\DTOs\Room;
use GungCahyadiPP\ChannexOpenChannel\DTOs\Occupancy;
use GungCahyadiPP\ChannexOpenChannel\DTOs\RoomDay;

// Push booking
$booking = new Booking(/* ... */);
$response = Channex::pushBooking($booking);

// Request full sync
Channex::requestFullSync('HOTEL123');
```

## Implementing Channex Endpoints

Channex akan mengirim request ke endpoint Anda dengan header `api-key`. Anda perlu mengimplementasikan 3 endpoint:

### 1. Test Connection

```php
use GungCahyadiPP\ChannexOpenChannel\ChannexConfig;
use GungCahyadiPP\ChannexOpenChannel\Handlers\AbstractTestConnectionHandler;

class MyTestConnectionHandler extends AbstractTestConnectionHandler
{
    protected function validateHotelCode(string $hotelCode): bool
    {
        return Property::where('channex_code', $hotelCode)->exists();
    }
}

// In your controller (Laravel)
public function testConnection(Request $request)
{
    $config = app(ChannexConfig::class); // or create manually
    $handler = new MyTestConnectionHandler($config);
    
    $response = $handler->handle(
        $request->query('hotel_code'),
        $request->header('api-key')
    );
    
    $statusCode = $response['status_code'] ?? ($response['success'] ? 200 : 400);
    return response()->json($response, $statusCode);
}
```

### 2. Mapping Details

```php
use GungCahyadiPP\ChannexOpenChannel\ChannexConfig;
use GungCahyadiPP\ChannexOpenChannel\Handlers\AbstractMappingDetailsHandler;
use GungCahyadiPP\ChannexOpenChannel\DTOs\RoomType;
use GungCahyadiPP\ChannexOpenChannel\DTOs\RatePlan;

class MyMappingDetailsHandler extends AbstractMappingDetailsHandler
{
    protected function getRoomTypes(string $hotelCode): array
    {
        $property = Property::where('channex_code', $hotelCode)->first();
        
        return $property->roomTypes->map(function ($room) {
            $roomType = new RoomType(
                id: (string) $room->id,
                title: $room->name
            );
            
            foreach ($room->ratePlans as $rate) {
                $roomType->addRatePlan(new RatePlan(
                    id: (string) $rate->id,
                    title: $rate->name,
                    sellMode: RatePlan::SELL_MODE_PER_ROOM,
                    maxPersons: $room->max_occupancy,
                    currency: 'USD',
                    readOnly: false
                ));
            }
            
            return $roomType;
        })->toArray();
    }
}

// In your controller
public function mappingDetails(Request $request)
{
    $config = app(ChannexConfig::class);
    $handler = new MyMappingDetailsHandler($config);
    
    $response = $handler->handle(
        $request->query('hotel_code'),
        $request->header('api-key')
    );
    
    $statusCode = isset($response['status_code']) ? $response['status_code'] : 200;
    return response()->json($response, $statusCode);
}
```

### 3. Changes Handler

```php
use GungCahyadiPP\ChannexOpenChannel\ChannexConfig;
use GungCahyadiPP\ChannexOpenChannel\Handlers\AbstractChangesHandler;
use GungCahyadiPP\ChannexOpenChannel\DTOs\AvailabilityChange;
use GungCahyadiPP\ChannexOpenChannel\DTOs\RestrictionChange;

class MyChangesHandler extends AbstractChangesHandler
{
    protected function processAvailabilityChanges(string $hotelCode, array $changes): void
    {
        foreach ($changes as $change) {
            /** @var AvailabilityChange $change */
            Availability::updateOrCreate(
                [
                    'room_type_id' => $change->roomTypeId,
                    'date' => $change->dateFrom,
                ],
                [
                    'available' => $change->availability,
                ]
            );
        }
    }

    protected function processRestrictionChanges(string $hotelCode, array $changes): void
    {
        foreach ($changes as $change) {
            /** @var RestrictionChange $change */
            $rate = $change->getRateForOccupancy();
            
            Restriction::updateOrCreate(
                [
                    'rate_plan_id' => $change->ratePlanId,
                    'date' => $change->dateFrom,
                ],
                [
                    'rate' => $rate?->getAmount(),
                    'stop_sell' => $change->stopSell,
                    'closed_to_arrival' => $change->closedToArrival,
                    'closed_to_departure' => $change->closedToDeparture,
                    'min_stay_arrival' => $change->minStayArrival,
                    'min_stay_through' => $change->minStayThrough,
                    'max_stay' => $change->maxStay,
                ]
            );
        }
    }
}

// In your controller
public function changes(Request $request)
{
    $config = app(ChannexConfig::class);
    $handler = new MyChangesHandler($config);
    
    $payload = $handler->parseRequest($request->getContent());
    $response = $handler->handle($payload, $request->header('api-key'));
    
    $statusCode = $response['status_code'] ?? ($response['success'] ? 200 : 400);
    return response()->json($response, $statusCode);
}
```

## API Endpoints

Anda perlu membuat endpoint berikut di aplikasi Anda:

| Method | Endpoint | Handler | Header |
|--------|----------|---------|--------|
| GET | `/api/test_connection/` | `AbstractTestConnectionHandler` | `api-key` |
| GET | `/api/mapping_details/` | `AbstractMappingDetailsHandler` | `api-key` |
| POST | `/api/changes/` | `AbstractChangesHandler` | `api-key` |

## DTOs Reference

### Booking DTOs

| Class | Description |
|-------|-------------|
| `Booking` | Main booking object |
| `Customer` | Customer information |
| `Company` | Customer company info |
| `Room` | Booking room |
| `RoomDay` | Daily room pricing |
| `Occupancy` | Room occupancy |
| `Guest` | Guest information |
| `Guarantee` | Credit card details |
| `Service` | Extra services/fees |
| `Deposit` | Payment deposits |

### Mapping DTOs

| Class | Description |
|-------|-------------|
| `RoomType` | Room type for mapping |
| `RatePlan` | Rate plan for mapping |

### Changes DTOs

| Class | Description |
|-------|-------------|
| `AvailabilityChange` | Availability update from Channex |
| `RestrictionChange` | Rate/restriction update from Channex |
| `Rate` | Rate information |

## License

MIT License
