<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * この名前空間をルートコントローラーに適用します。
     *
     * @var string|null
     */
    protected $namespace = 'App\\Http\\Controllers';

    /**
     * アプリケーションのルートを定義します。
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * ルートのマッピングを行います。
     */
    public function map()
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }

    /**
     * Web ルートを定義します。
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * API ルートを定義します。
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }
}
