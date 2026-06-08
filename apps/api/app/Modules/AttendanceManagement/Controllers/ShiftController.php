<?php

namespace App\Modules\AttendanceManagement\Controllers;

use App\Models\Shift;
use App\Modules\AttendanceManagement\Requests\StoreShiftRequest;
use App\Modules\AttendanceManagement\Requests\UpdateShiftRequest;
use App\Modules\AttendanceManagement\Resources\ShiftResource;
use App\Modules\AttendanceManagement\Services\AttendanceSchedulingService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class ShiftController
{
    public function __construct(private readonly AttendanceSchedulingService $attendanceSchedulingService) {}

    public function index(): JsonResponse
    {
        $shifts = Shift::query()->orderBy('name')->get();

        $payload = ApiResponse::success(
            'Shifts loaded successfully.',
            ShiftResource::collection($shifts),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreShiftRequest $request): JsonResponse
    {
        $shift = $this->attendanceSchedulingService->createShift($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Shift created successfully.',
            new ShiftResource($shift),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateShiftRequest $request, int $shiftId): JsonResponse
    {
        $shift = Shift::query()->findOrFail($shiftId);
        $shift = $this->attendanceSchedulingService->updateShift($request->user(), $shift, $request->validated());

        $payload = ApiResponse::success(
            'Shift updated successfully.',
            new ShiftResource($shift),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
