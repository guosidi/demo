<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer('layouts.navigation', 'App\Http\ViewComposers\AdminMenuComposer'); //后台左侧菜单
        view()->composer('layouts.bar', 'App\Http\ViewComposers\AdminBarComposer'); //后台面包屑导航
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
