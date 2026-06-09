<?php

namespace App\Modules\Platform\Auth\Controllers;

use App\Modules\Platform\Auth\Requests\ForgotPasswordRequest;
use App\Modules\Platform\Auth\Requests\LoginRequest;
use App\Modules\Platform\Auth\Requests\ResetPasswordRequest;
use App\Modules\Platform\Auth\Requests\VerifyMfaRequest;
use App\Modules\Platform\Auth\Resources\AuthenticatedUserResource;
use App\Modules\Platform\Auth\Services\AuthService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController
{
    public function __construct(private readonly AuthService $authService) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
            deviceName: $request->string('device_name')->toString() ?: 'web',
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        $message = ($result['mfa_required'] ?? false)
            ? 'MFA verification is required to complete sign in.'
            : 'Login successful.';

        $payload = ApiResponse::success($message, $result);

        return response()->json($payload['body'], $payload['status']);
    }

    public function verifyMfa(VerifyMfaRequest $request): JsonResponse
    {
        $result = $this->authService->verifyMfa(
            email: $request->string('email')->toString(),
            code: $request->string('code')->toString(),
            deviceName: $request->string('device_name')->toString() ?: 'web',
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        $payload = ApiResponse::success('MFA verification successful.', $result);

        return response()->json($payload['body'], $payload['status']);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->sendPasswordResetLink(
            email: $request->string('email')->toString(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        $payload = ApiResponse::success(
            'If the account exists, a password reset link has been sent.',
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->authService->resetPassword(
            payload: $request->validated(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        $payload = ApiResponse::success('Password reset successful.');

        return response()->json($payload['body'], $payload['status']);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout(
            user: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        $payload = ApiResponse::success('Logout successful.');

        return response()->json($payload['body'], $payload['status']);
    }

    public function me(Request $request): JsonResponse
    {
        $payload = ApiResponse::success(
            'Authenticated user loaded successfully.',
            new AuthenticatedUserResource($request->user()->loadMissing('roles', 'permissions', 'company', 'employee')),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
