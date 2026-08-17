<?php

namespace App\Providers;

use App\Services\AI\AIManager;
use App\Services\AI\AIResponseValidator;
use App\Services\Document\DocumentExtractorManager;
use App\Services\Rps\RpsDraftValidator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AIManager::class, function ($app) {
            return new AIManager(
                $app->make(AIResponseValidator::class),
                $app->make(RpsDraftValidator::class),
            );
        });

        $this->app->singleton(DocumentExtractorManager::class, function () {
            return new DocumentExtractorManager;
        });

        $this->app->singleton(\App\Services\Term\ActiveTerm::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
