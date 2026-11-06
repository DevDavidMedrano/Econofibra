<?php

namespace App\Providers;

use App\Http\Support\ContactPhones;
use Illuminate\Contracts\View\View as ViewContract;
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
        View::composer(
            ['partials.footer', 'pages.contact.index'],
            fn (ViewContract $view) => $view->with('contactPhones', ContactPhones::configured(config('site.phones'))),
        );
    }
}
