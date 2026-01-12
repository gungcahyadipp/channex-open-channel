<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel\Laravel\Facades;

use GungCahyadiPP\ChannexOpenChannel\ChannexClient;
use GungCahyadiPP\ChannexOpenChannel\DTOs\Booking;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array pushBooking(Booking $booking)
 * @method static array requestFullSync(string $hotelCode)
 * @method static \GungCahyadiPP\ChannexOpenChannel\ChannexConfig getConfig()
 * 
 * @see \GungCahyadiPP\ChannexOpenChannel\ChannexClient
 */
class Channex extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return ChannexClient::class;
    }
}
