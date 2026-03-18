<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Queries\UserRepository;
use App\Repositories\Contracts\ProfileRepositoryInterface;
use App\Repositories\Queries\ProfileRepository;
use App\Repositories\Contracts\GalleryRepositoryInterface;
use App\Repositories\Queries\GalleryRepository;
use App\Repositories\Contracts\DirectoryRepositoryInterface;
use App\Repositories\Queries\DirectoryRepository;
use App\Repositories\Contracts\JobCategoryRepositoryInterface;
use App\Repositories\Queries\JobRepository;
use App\Repositories\Contracts\JobSuggestionRepositoryInterface;
use App\Repositories\Queries\JobSuggestionRepository;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Queries\BookingRepository;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use App\Repositories\Queries\ReviewRepository;
use App\Repositories\Contracts\BookmarkRepositoryInterface;
use App\Repositories\Queries\BookmarkRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );

        $this->app->bind(
            ProfileRepositoryInterface::class,
            ProfileRepository::class
        );

        $this->app->bind(
            GalleryRepositoryInterface::class,
            GalleryRepository::class
        );

        $this->app->bind(
            DirectoryRepositoryInterface::class,
            DirectoryRepository::class
        );

        $this->app->bind(
            JobCategoryRepositoryInterface::class,
            JobCategoryRepository::class
        );

        $this->app->bind(
            JobSuggestionRepositoryInterface::class,
            JobSuggestionRepository::class
        );

        $this->app->bind(
            BookingRepositoryInterface::class,
            BookingRepository::class
        );

        $this->app->bind(
            ReviewRepositoryInterface::class,
            ReviewRepository::class
        );

        $this->app->bind(
            BookmarkRepositoryInterface::class,
            BookmarkRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
