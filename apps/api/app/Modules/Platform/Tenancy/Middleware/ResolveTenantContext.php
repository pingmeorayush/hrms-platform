<?php

namespace App\Modules\Platform\Tenancy\Middleware;

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

        if (! $user) {
            return $next($request);
        }

        $user->loadMissing('company');
        $company = $user->company;

        if (! $user->is_active || ! $company || ! $company->isActive()) {
            return $this->blockedResponse();
        }

        $previousContext = app(TenantContext::class);
        $previousTimezone = (string) config('app.timezone');

        app()->instance(TenantContext::class, TenantContext::fromCompany($company));
        config(['app.timezone' => $company->timezone]);
        date_default_timezone_set($company->timezone);

        try {
            return $next($request);
        } finally {
            app()->instance(TenantContext::class, $previousContext);
            config(['app.timezone' => $previousTimezone]);
            date_default_timezone_set($previousTimezone);
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
