<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Contracts\UserRepositoryInterface::class,
            \App\Repositories\Queries\UserRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\ProfileRepositoryInterface::class,
            \App\Repositories\Queries\ProfileRepository::class
        );

        $this->app->bind(
            \App\Services\Contracts\ProfileServiceInterface::class,
            \App\Services\ProfileService::class
        );

        $this->app->bind(
            \App\Repositories\Queries\ProfileRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\GalleryRepositoryInterface::class,
            \App\Repositories\Queries\GalleryRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\DirectoryRepositoryInterface::class,
            \App\Repositories\Queries\DirectoryRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\JobCategoryRepositoryInterface::class,
            \App\Repositories\Queries\JobCategoryRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\JobSuggestionRepositoryInterface::class,
            \App\Repositories\Queries\JobSuggestionRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\BookingRepositoryInterface::class,
            \App\Repositories\Queries\BookingRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\ReviewRepositoryInterface::class,
            \App\Repositories\Queries\ReviewRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\BookmarkRepositoryInterface::class,
            \App\Repositories\Queries\BookmarkRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\AvailabilityRepositoryInterface::class,
            \App\Repositories\Queries\AvailabilityRepository::class
        );

        $this->app->bind(
            \App\Services\Contracts\CreditServiceInterface::class,
            \App\Services\CreditService::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\TransactionRepositoryInterface::class,
            \App\Repositories\Queries\TransactionRepository::class
        );

        $this->app->bind(
            \App\Services\Contracts\PaymentServiceInterface::class,
            \App\Services\PaymentService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Booking::class, BookingPolicy::class);
    }
}
