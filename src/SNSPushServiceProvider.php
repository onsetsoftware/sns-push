<?php

namespace SNSPush;

use Illuminate\Support\ServiceProvider;

class SNSPushServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot()
    {
    }

    /**
     * Register bindings in the container.
     */
    public function register()
    {
        $this->app->singleton(SNSPush::class, function () {
            return new SNSPush(config('services.sns'));
        });
    }

    /**
     * Tell what services this package provides.
     *
     * @return array
     */
    public function provides()
    {
        return [SNSPush::class];
    }
}
