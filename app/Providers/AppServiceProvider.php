<?php

namespace App\Providers;

use App\Models\Lead;
use App\Support\RfqCart;
use Illuminate\Support\Facades\View;
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
        View::composer('layouts.layout', function ($view): void {
            $view->with('rfqCount', app(RfqCart::class)->count());
        });

        View::composer('layouts.admin', function ($view): void {
            $view->with('leadBaruCount', Lead::query()->baru()->count());
        });
    }
}
