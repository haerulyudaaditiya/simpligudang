<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Category;
use App\Policies\CategoryPolicy;
use App\Models\Location;
use App\Policies\LocationPolicy;
use App\Models\Item;
use App\Policies\ItemPolicy;

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
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Location::class, LocationPolicy::class);
        Gate::policy(Item::class, ItemPolicy::class);
    }
}