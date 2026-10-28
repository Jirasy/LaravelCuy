<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Selama pengembangan, query berulang (N+1 / lazy loading) dicatat di storage/logs supaya ketahuan.
        // Hanya dicatat, halaman tidak error. Di production tidak aktif.
        Model::preventLazyLoading(! $this->app->isProduction());
        Model::handleLazyLoadingViolationUsing(function (Model $model, string $relation) {
            logger()->warning('Lazy loading terdeteksi: '.$model::class.'::'.$relation);
        });

        // Maksimal 5 percobaan login per menit untuk tiap kombinasi email + IP
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by(mb_strtolower((string) $request->input('email')) . '|' . $request->ip())
                ->response(function (Request $request, array $headers) {
                    return redirect()->route('login')
                        ->withErrors(['email' => 'Terlalu banyak percobaan login. Coba lagi dalam 1 menit.'])
                        ->withInput($request->only('email'));
                });
        });
    }
}