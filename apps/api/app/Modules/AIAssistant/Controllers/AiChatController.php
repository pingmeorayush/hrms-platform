<?php

namespace App\Modules\AIAssistant\Controllers;

use App\Modules\AIAssistant\Requests\StoreAiChatRequest;
use App\Modules\AIAssistant\Services\AiAssistantService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class AiChatController
{
    public function __construct(private readonly AiAssistantService $aiAssistantService) {}

    public function store(StoreAiChatRequest $request): JsonResponse
    {
        $payload = ApiResponse::success(
            'AI assistant response generated successfully.',
            $this->aiAssistantService->chat($request->user(), $request->validated()),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
