<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel\Laravel;

use GungCahyadiPP\ChannexOpenChannel\ChannexClient;
use GungCahyadiPP\ChannexOpenChannel\ChannexConfig;
use Illuminate\Support\ServiceProvider;

class ChannexServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/channex.php',
            'channex'
        );

        $this->app->singleton(ChannexConfig::class, function ($app) {
            $config = $app['config']['channex'];

            return new ChannexConfig(
                apiKey: $config['api_key'] ?? '',
                providerCode: $config['provider_code'] ?? '',
                environment: $config['environment'] ?? ChannexConfig::ENV_STAGING,
                inboundApiKey: $config['inbound_api_key'] ?? null,
                customApiEndpoint: $config['endpoints']['api'] ?? null,
                customSecureEndpoint: $config['endpoints']['secure'] ?? null,
            );
        });

        $this->app->singleton(ChannexClient::class, function ($app) {
            return new ChannexClient(
                $app->make(ChannexConfig::class)
            );
        });

        $this->app->alias(ChannexClient::class, 'channex');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__ . '/../../config/channex.php' => config_path('channex.php'),
            ], 'channex-config');

            // Publish controller
            $this->publishes([
                __DIR__ . '/../../stubs/Controllers/ChannexController.php.stub' => app_path('Http/Controllers/ChannexController.php'),
            ], 'channex-controller');

            // Publish handlers
            $this->publishes([
                __DIR__ . '/../../stubs/Handlers/Channex/TestConnectionHandler.php.stub' => app_path('Handlers/Channex/TestConnectionHandler.php'),
                __DIR__ . '/../../stubs/Handlers/Channex/MappingDetailsHandler.php.stub' => app_path('Handlers/Channex/MappingDetailsHandler.php'),
                __DIR__ . '/../../stubs/Handlers/Channex/ChangesHandler.php.stub' => app_path('Handlers/Channex/ChangesHandler.php'),
            ], 'channex-handlers');

            // Publish routes
            $this->publishes([
                __DIR__ . '/../../stubs/routes/channex.php.stub' => base_path('routes/channex.php'),
            ], 'channex-routes');

            // Publish all at once
            $this->publishes([
                __DIR__ . '/../../config/channex.php' => config_path('channex.php'),
                __DIR__ . '/../../stubs/Controllers/ChannexController.php.stub' => app_path('Http/Controllers/ChannexController.php'),
                __DIR__ . '/../../stubs/Handlers/Channex/TestConnectionHandler.php.stub' => app_path('Handlers/Channex/TestConnectionHandler.php'),
                __DIR__ . '/../../stubs/Handlers/Channex/MappingDetailsHandler.php.stub' => app_path('Handlers/Channex/MappingDetailsHandler.php'),
                __DIR__ . '/../../stubs/Handlers/Channex/ChangesHandler.php.stub' => app_path('Handlers/Channex/ChangesHandler.php'),
                __DIR__ . '/../../stubs/routes/channex.php.stub' => base_path('routes/channex.php'),
            ], 'channex');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<string>
     */
    public function provides(): array
    {
        return [
            ChannexConfig::class,
            ChannexClient::class,
            'channex',
        ];
    }
}
