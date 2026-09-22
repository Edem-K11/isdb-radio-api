<?php

namespace App\Providers;

use App\Models\Episode;
use App\Models\StreamSetting;
use App\Observers\EpisodeObserver;
use App\Observers\StreamSettingObserver;
use Illuminate\Support\Facades\URL;
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
        Episode::observe(EpisodeObserver::class);
        StreamSetting::observe(StreamSettingObserver::class);

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
