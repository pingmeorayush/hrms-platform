<?php

namespace App\Modules\RecruitmentManagement\Controllers;

use App\Modules\Platform\Shared\Http\ApiResponse;
use App\Modules\RecruitmentManagement\Requests\ListRecruitmentHireHandoffsRequest;
use App\Modules\RecruitmentManagement\Requests\StoreRecruitmentHireHandoffRequest;
use App\Modules\RecruitmentManagement\Resources\RecruitmentHireHandoffResource;
use App\Modules\RecruitmentManagement\Services\RecruitmentHireHandoffService;
use Illuminate\Http\JsonResponse;

class RecruitmentHireHandoffController
{
    public function __construct(private readonly RecruitmentHireHandoffService $handoffService) {}

    public function index(ListRecruitmentHireHandoffsRequest $request): JsonResponse
    {
        $handoffs = $this->handoffService->search($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Recruitment hire handoffs loaded successfully.',
            [
                'items' => RecruitmentHireHandoffResource::collection($handoffs->items()),
                'meta' => [
                    'page' => $handoffs->currentPage(),
                    'per_page' => $handoffs->perPage(),
                    'total' => $handoffs->total(),
                    'last_page' => $handoffs->lastPage(),
                ],
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreRecruitmentHireHandoffRequest $request, int $offerId): JsonResponse
    {
        $handoff = $this->handoffService->createFromAcceptedOffer($request->user(), $offerId, $request->validated());

        $payload = ApiResponse::success(
            'Recruitment hire handoff created successfully.',
            new RecruitmentHireHandoffResource($handoff),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(int $handoffId): JsonResponse
    {
        $handoff = $this->handoffService->findForView(request()->user(), $handoffId);

        $payload = ApiResponse::success(
            'Recruitment hire handoff loaded successfully.',
            new RecruitmentHireHandoffResource($handoff),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
