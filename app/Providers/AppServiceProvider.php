<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Si detectamos que viene por el tÃºnel de Cloudflare, adaptamos la URL de la app
        if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $tunnelUrl = 'https://' . $_SERVER['HTTP_X_FORWARDED_HOST'];
            config(['app.url' => $tunnelUrl]);
            \Illuminate\Support\Facades\URL::forceRootUrl($tunnelUrl);
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Vite::prefetch(concurrency: 3);
    }
}
