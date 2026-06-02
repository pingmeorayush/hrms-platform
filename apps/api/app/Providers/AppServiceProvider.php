<?php

namespace App\Providers;

use App\Modules\Platform\Tenancy\TenantContext;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class, fn () => TenantContext::empty());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();
        ResetPassword::createUrlUsing(function (object $user, string $token): string {
            $baseUrl = rtrim((string) env('FRONTEND_URL', 'http://localhost:5173'), '/');

            return $baseUrl.'/reset-password?token='.$token.'&email='.urlencode($user->email);
        });

        RateLimiter::for('auth-login', function (Request $request): Limit {
            $email = Str::lower((string) $request->input('email'));

            return Limit::perMinute(10)->by($email.'|'.$request->ip());
        });

        RateLimiter::for('auth-sensitive', fn (Request $request): Limit => Limit::perMinute(20)->by($request->ip()));
        RateLimiter::for('api-general', fn (Request $request): Limit => Limit::perMinute(100)->by($request->ip()));
    }
}
