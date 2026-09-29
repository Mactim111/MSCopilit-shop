<?php

namespace App\Providers;

use App\Models\ProductVariant;
use App\Models\Review;
use App\Observers\ProductVariantObserver;
use App\Observers\ReviewObserver;
use Illuminate\Support\ServiceProvider;

class CatalogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        ProductVariant::observe(ProductVariantObserver::class);
        Review::observe(ReviewObserver::class);
    }
}
