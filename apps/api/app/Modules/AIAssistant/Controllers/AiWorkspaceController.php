<?php

namespace App\Modules\AIAssistant\Controllers;

use App\Modules\AIAssistant\Services\AiAssistantService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiWorkspaceController
{
    public function __construct(private readonly AiAssistantService $aiAssistantService) {}

    public function show(Request $request): JsonResponse
    {
        $payload = ApiResponse::success(
            'AI assistant workspace loaded successfully.',
            $this->aiAssistantService->workspace($request->user()),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
