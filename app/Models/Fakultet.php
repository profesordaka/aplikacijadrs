<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Ne učitavaj Cloudinary u artisan komandama
        if (App::runningInConsole()) {
            return;
        }

        Cloudinary::config([
            'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
            'api_key' => env('CLOUDINARY_API_KEY'),
            'api_secret' => env('CLOUDINARY_API_SECRET'),
        ]);
    }
}

