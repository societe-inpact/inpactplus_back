<?php

namespace App\Providers;

use App\Classes\Converters\MarathonConverter;
use App\Interfaces\ConverterInterface;
use Illuminate\Support\ServiceProvider;

class ConverterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            ConverterInterface::class,
            MarathonConverter::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
