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
use App\Models\Log;
use App\Policies\LogPolicy;
use App\Models\BorrowRequest;
use App\Policies\BorrowRequestPolicy;

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
        Gate::policy(Item::class, ItemPolicy::class);
        Gate::policy(Log::class, LogPolicy::class);
        Gate::policy(BorrowRequest::class, BorrowRequestPolicy::class);
    }
}