<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\GrpcClientManager;
use App\Services\DocumentService;

class GrpcServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GrpcClientManager::class, function () {
            return new GrpcClientManager();
        });

        $this->app->singleton(DocumentService::class, function ($app) {
            return new DocumentService($app->make(GrpcClientManager::class));
        });
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/grpc.php', 'grpc');
    }
}