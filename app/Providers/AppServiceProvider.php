<?php

namespace App\Providers;

use App\Events\ProductOutOfStock;
use App\Listeners\LogLowStock;
use App\Listeners\NotifyPurchasingDept;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use App\Events\UserLoggedIn;
use App\Events\UserRegistered;
use App\Listeners\LogSuccessfulLogin;
use App\Listeners\SendWelcomeNotification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

use App\Models\Product;
use App\Observers\ProductObserver;

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
        
        // message stock
        Event::listen(LogLowStock::class, NotifyPurchasingDept::class);

        //Seguridad
        Event::listen(LogSuccessfulLogin::class);

        //Bienvenida
        Event::listen( SendWelcomeNotification::class);

        RateLimiter::for('login', function (Request $request) {

            return Limit::perMinute(5)->by($request->ip())->response(function () {
                    return response()->json([
                        'message' => 'Demasiados intentos. Por seguridad, espera un minuto.'
                    ], Response::HTTP_TOO_MANY_REQUESTS); // 
                });
        });
        Product::observe(ProductObserver::class);
            
    }
    
}
