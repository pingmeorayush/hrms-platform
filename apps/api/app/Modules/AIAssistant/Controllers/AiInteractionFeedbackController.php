<?php

namespace App\Modules\AIAssistant\Controllers;

use App\Modules\AIAssistant\Requests\StoreAiInteractionFeedbackRequest;
use App\Modules\AIAssistant\Services\AiAssistantService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class AiInteractionFeedbackController
{
    public function __construct(private readonly AiAssistantService $aiAssistantService) {}

    public function store(StoreAiInteractionFeedbackRequest $request, int $interactionId): JsonResponse
    {
        $payload = ApiResponse::success(
            'AI interaction feedback recorded successfully.',
            $this->aiAssistantService->recordInteractionFeedback($request->user(), $interactionId, $request->validated()),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
