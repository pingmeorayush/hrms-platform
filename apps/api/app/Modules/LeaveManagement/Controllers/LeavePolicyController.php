<?php

namespace App\Modules\LeaveManagement\Controllers;

use App\Models\LeavePolicy;
use App\Modules\LeaveManagement\Requests\StoreLeavePolicyRequest;
use App\Modules\LeaveManagement\Requests\UpdateLeavePolicyRequest;
use App\Modules\LeaveManagement\Resources\LeavePolicyResource;
use App\Modules\LeaveManagement\Services\LeaveConfigurationService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class LeavePolicyController
{
    public function __construct(private readonly LeaveConfigurationService $leaveConfigurationService) {}

    public function index(): JsonResponse
    {
        $policies = LeavePolicy::query()
            ->with(['leaveType', 'applicableDepartment', 'applicableLocation'])
            ->orderBy('leave_type_id')
            ->orderBy('applicable_department_id')
            ->orderBy('applicable_location_id')
            ->get();

        $payload = ApiResponse::success(
            'Leave policies loaded successfully.',
            LeavePolicyResource::collection($policies),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreLeavePolicyRequest $request): JsonResponse
    {
        $policy = $this->leaveConfigurationService->createLeavePolicy($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Leave policy created successfully.',
            new LeavePolicyResource($policy),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateLeavePolicyRequest $request, int $leavePolicyId): JsonResponse
    {
        $policy = LeavePolicy::query()->findOrFail($leavePolicyId);
        $policy = $this->leaveConfigurationService->updateLeavePolicy($request->user(), $policy, $request->validated());

        $payload = ApiResponse::success(
            'Leave policy updated successfully.',
            new LeavePolicyResource($policy),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
