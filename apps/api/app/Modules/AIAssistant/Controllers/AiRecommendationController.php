<?php

namespace App\Modules\AIAssistant\Controllers;

use App\Modules\AIAssistant\Requests\StoreAiRecommendationRequest;
use App\Modules\AIAssistant\Services\AiAssistantService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class AiRecommendationController
{
    public function __construct(private readonly AiAssistantService $aiAssistantService) {}

    public function store(StoreAiRecommendationRequest $request): JsonResponse
    {
        $payload = ApiResponse::success(
            'AI recommendation generated successfully.',
            $this->aiAssistantService->generateRecommendation($request->user(), $request->validated()),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
