<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';
    protected $adminNamespace = 'App\Http\Controllers\Admin';
    protected $apiNamespace = 'App\Http\Controllers\Api';
    protected $currentDomain;

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //当前访问页入口
        $this->currentDomain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "";

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        //$this->mapApiRoutes();

        //$this->mapWebRoutes();

        if (env('APP_URL') == $this->currentDomain) {
            $this->mapWebRoutes();
        } elseif (env('API_URL') == $this->currentDomain) {
            $this->mapApiRoutes();
        } elseif (env('ADMIN_URL') == $this->currentDomain) {
            $this->mapAdminRoutes();
        } else {
            $this->mapWebRoutes();
        }
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * 后台管理系统路由
     */
    protected function mapAdminRoutes()
    {
        Route::middleware('admin')
            ->namespace($this->adminNamespace)
            ->group(base_path('routes/admin.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::middleware('api')
            ->namespace($this->apiNamespace)
            ->group(base_path('routes/api.php'));
    }
}
