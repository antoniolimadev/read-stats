<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
//
use \App\Goodreads\ApiRequest;

class AppServiceProvider extends ServiceProvider
{
    // makes it so content of this class is only loaded when is requested
    // this is irrelevant if boot method has any code
    // protected $defer = true;

    // assuming laravel has been booted
    public function boot()
    {
        Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // singleton always returns the same instance
        \App::singleton(ApiRequest::class, function () {
            return new ApiRequest((config('services.goodreads.key')));
        });
    }
}
