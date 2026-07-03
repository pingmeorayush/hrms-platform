<?php

namespace App\Modules\GlobalizationLocalization\Controllers;

use App\Modules\GlobalizationLocalization\Requests\UpdateLocalizationPreferencesRequest;
use App\Modules\GlobalizationLocalization\Services\LocalizationService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocalizationController
{
    public function __construct(private readonly LocalizationService $localizationService) {}

    public function show(Request $request): JsonResponse
    {
        $payload = ApiResponse::success(
            'Localization configuration loaded successfully.',
            $this->localizationService->configurationForUser($request->user()),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function updatePreferences(UpdateLocalizationPreferencesRequest $request): JsonResponse
    {
        $payload = ApiResponse::success(
            'Regional preferences updated successfully.',
            $this->localizationService->updatePreferences(
                $request->user(),
                $request->validated(),
                $request->ip(),
                $request->userAgent(),
            ),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
