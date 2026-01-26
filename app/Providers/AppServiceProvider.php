<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Ovde možemo registrovati dodatne servise, ako bude potrebe
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ne učitavaj Cloudinary prilikom Artisan komandi (npr. migrate, tinker)
        if (App::runningInConsole()) {
            return;
        }

        // Opcionalno: konfiguracija putem .env fajla
        Cloudinary::config([
            'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
            'api_key'    => env('CLOUDINARY_API_KEY'),
            'api_secret' => env('CLOUDINARY_API_SECRET'),
        ]);
    }
}
