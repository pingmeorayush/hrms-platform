<?php

namespace App\Modules\RecruitmentManagement\Controllers;

use App\Modules\Platform\Shared\Http\ApiResponse;
use App\Modules\RecruitmentManagement\Requests\ListJobRequisitionsRequest;
use App\Modules\RecruitmentManagement\Requests\StoreJobRequisitionRequest;
use App\Modules\RecruitmentManagement\Requests\UpdateJobRequisitionRequest;
use App\Modules\RecruitmentManagement\Resources\JobRequisitionResource;
use App\Modules\RecruitmentManagement\Services\JobRequisitionService;
use Illuminate\Http\JsonResponse;

class JobRequisitionController
{
    public function __construct(private readonly JobRequisitionService $jobRequisitionService) {}

    public function index(ListJobRequisitionsRequest $request): JsonResponse
    {
        $requisitions = $this->jobRequisitionService->search($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Job requisitions loaded successfully.',
            [
                'items' => JobRequisitionResource::collection($requisitions->items()),
                'meta' => [
                    'page' => $requisitions->currentPage(),
                    'per_page' => $requisitions->perPage(),
                    'total' => $requisitions->total(),
                    'last_page' => $requisitions->lastPage(),
                ],
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreJobRequisitionRequest $request): JsonResponse
    {
        $requisition = $this->jobRequisitionService->create($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Job requisition created successfully.',
            new JobRequisitionResource($requisition),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(int $jobRequisitionId): JsonResponse
    {
        $requisition = $this->jobRequisitionService->findForView(request()->user(), $jobRequisitionId);

        $payload = ApiResponse::success(
            'Job requisition loaded successfully.',
            new JobRequisitionResource($requisition),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateJobRequisitionRequest $request, int $jobRequisitionId): JsonResponse
    {
        $requisition = $this->jobRequisitionService->update(
            $request->user(),
            $jobRequisitionId,
            $request->validated(),
        );

        $payload = ApiResponse::success(
            'Job requisition updated successfully.',
            new JobRequisitionResource($requisition),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
