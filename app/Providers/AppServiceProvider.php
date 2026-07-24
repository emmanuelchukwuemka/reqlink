<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
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
        View::composer('dashboards.*', function ($view) {
            $activeAnnouncements = collect();

            if (Auth::check()) {
                $activeAnnouncements = \App\Models\Announcement::active()
                    ->where(function ($q) {
                        $q->whereNull('target_role')->orWhere('target_role', Auth::user()->role);
                    })
                    ->latest()
                    ->get();
            }

            $view->with('activeAnnouncements', $activeAnnouncements);
        });
    }
}
