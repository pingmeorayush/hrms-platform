<?php

namespace App\Modules\AIAssistant\Controllers;

use App\Modules\AIAssistant\Requests\StoreAiRecommendationDecisionRequest;
use App\Modules\AIAssistant\Services\AiAssistantService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class AiRecommendationDecisionController
{
    public function __construct(private readonly AiAssistantService $aiAssistantService) {}

    public function store(StoreAiRecommendationDecisionRequest $request, int $recommendationId): JsonResponse
    {
        $payload = ApiResponse::success(
            'AI recommendation decision recorded successfully.',
            $this->aiAssistantService->recordRecommendationDecision($request->user(), $recommendationId, $request->validated()),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
