<?php

namespace App\Modules\AttendanceManagement\Controllers;

use App\Modules\AttendanceManagement\Requests\UpdateAttendancePolicyRequest;
use App\Modules\AttendanceManagement\Resources\AttendancePolicyResource;
use App\Modules\AttendanceManagement\Services\AttendanceConfigurationService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class AttendancePolicyController
{
    public function __construct(private readonly AttendanceConfigurationService $attendanceConfigurationService) {}

    public function show(): JsonResponse
    {
        $policy = $this->attendanceConfigurationService->getOrCreatePolicy();

        $payload = ApiResponse::success(
            'Attendance policy loaded successfully.',
            new AttendancePolicyResource($policy),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateAttendancePolicyRequest $request): JsonResponse
    {
        $policy = $this->attendanceConfigurationService->getOrCreatePolicy();
        $policy = $this->attendanceConfigurationService->updatePolicy($request->user(), $policy, $request->validated());

        $payload = ApiResponse::success(
            'Attendance policy updated successfully.',
            new AttendancePolicyResource($policy),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
