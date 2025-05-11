<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        \App\Models\File::class => \App\Policies\FilePolicy::class,
        \App\Models\PracticeRecord::class => \App\Policies\PracticeRecordPolicy::class,
    ];
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
        if ($this->app->environment('production')) {
            \URL::forceScheme('https');
        }

        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
