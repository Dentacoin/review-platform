<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Observers\VoxQuestionObserver;
use App\Models\VoxQuestion;

class AppServiceProvider extends ServiceProvider {
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        VoxQuestion::observe(VoxQuestionObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        //
    }
}
