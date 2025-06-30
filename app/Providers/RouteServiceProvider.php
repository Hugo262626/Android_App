<?php


namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Le chemin à la "home" route de ton application.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Définir les services du provider de routes.
     *
     * @return void
     */
        public function boot()
    {
        $this->routes(function () {
            Route::prefix('api')->group(function () {
                Route::post('/login', [AuthController::class, 'login']);
                Route::post('/register', [AuthController::class, 'register']);
                Route::middleware(['api', 'jwt.auth'])->group(base_path('routes/api.php'));
            });

            Route::middleware('web')->group(base_path('routes/web.php'));
        });
    }
    protected $middlewareAliases = [
        'jwt.auth' => \App\Http\Middleware\JWTAuthMiddleware::class,
    ];

    /**
     * Configurer la limitation de taux des requêtes.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
