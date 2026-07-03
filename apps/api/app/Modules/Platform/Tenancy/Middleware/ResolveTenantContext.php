<?php

namespace App\Modules\Platform\Tenancy\Middleware;

use App\Models\Company;
use App\Models\User;
use App\Modules\Platform\Shared\Http\ApiResponse;
use App\Modules\Platform\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $next($request);
        }

        $user->loadMissing('company');
        $company = $user->company;

        if (! $user->is_active || ! $company instanceof Company || ! $company->isActive()) {
            return $this->blockedResponse();
        }

        $previousContext = app(TenantContext::class);
        $previousTimezone = (string) config('app.timezone');
        $previousLocale = app()->getLocale();

        app()->instance(TenantContext::class, TenantContext::fromCompany($company));
        config(['app.timezone' => $company->timezone]);
        config(['app.locale' => $company->language]);
        date_default_timezone_set($company->timezone);
        app()->setLocale($company->language);

        try {
            return $next($request);
        } finally {
            app()->instance(TenantContext::class, $previousContext);
            config(['app.timezone' => $previousTimezone]);
            config(['app.locale' => $previousLocale]);
            date_default_timezone_set($previousTimezone);
            app()->setLocale($previousLocale);
        }
    }

    private function blockedResponse(): JsonResponse
    {
        $payload = ApiResponse::error(
            'The tenant context is invalid or inactive.',
            ['tenant' => ['The authenticated user is not attached to an active tenant.']],
            403,
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
