<?php

namespace App\Modules\LeaveManagement\Controllers;

use App\Models\LeaveType;
use App\Modules\LeaveManagement\Requests\StoreLeaveTypeRequest;
use App\Modules\LeaveManagement\Requests\UpdateLeaveTypeRequest;
use App\Modules\LeaveManagement\Resources\LeaveTypeResource;
use App\Modules\LeaveManagement\Services\LeaveConfigurationService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class LeaveTypeController
{
    public function __construct(private readonly LeaveConfigurationService $leaveConfigurationService) {}

    public function index(): JsonResponse
    {
        $leaveTypes = LeaveType::query()->orderBy('name')->get();

        $payload = ApiResponse::success(
            'Leave types loaded successfully.',
            LeaveTypeResource::collection($leaveTypes),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreLeaveTypeRequest $request): JsonResponse
    {
        $leaveType = $this->leaveConfigurationService->createLeaveType($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Leave type created successfully.',
            new LeaveTypeResource($leaveType),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateLeaveTypeRequest $request, int $leaveTypeId): JsonResponse
    {
        $leaveType = LeaveType::query()->findOrFail($leaveTypeId);
        $leaveType = $this->leaveConfigurationService->updateLeaveType($request->user(), $leaveType, $request->validated());

        $payload = ApiResponse::success(
            'Leave type updated successfully.',
            new LeaveTypeResource($leaveType),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
