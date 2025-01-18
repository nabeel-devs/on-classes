<?php

namespace App\Providers;

use App\Services\AgoraService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AgoraService::class, function ($app) {
            return new AgoraService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ini_set('upload_max_filesize', '100M');
        ini_set('post_max_size', '100M');
        ini_set('memory_limit', '256M');
    }
}
