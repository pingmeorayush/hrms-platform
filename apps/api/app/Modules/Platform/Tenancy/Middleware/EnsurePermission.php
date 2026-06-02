<?php

namespace App\Modules\Platform\Tenancy\Middleware;

use App\Modules\Platform\Audit\Services\AuditLogger;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Request $request, Closure $next, string $permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->response('Unauthenticated.', 401);
        }

        $required = collect(explode('|', $permissions))
            ->filter()
            ->values();

        if ($required->contains(fn (string $permission): bool => $user->can($permission))) {
            return $next($request);
        }

        $this->auditLogger->record(
            eventType: 'auth.permission.denied',
            actor: $user,
            metadata: [
                'required_permissions' => $required->all(),
                'path' => $request->path(),
                'method' => $request->method(),
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return $this->response('You do not have permission to perform this action.', 403);
    }

    private function response(string $message, int $status): JsonResponse
    {
        $payload = ApiResponse::error($message, [], $status);

        return response()->json($payload['body'], $payload['status']);
    }
}
