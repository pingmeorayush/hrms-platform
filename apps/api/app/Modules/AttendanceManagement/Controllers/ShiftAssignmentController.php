<?php

namespace App\Modules\AttendanceManagement\Controllers;

use App\Models\ShiftAssignment;
use App\Modules\AttendanceManagement\Requests\StoreShiftAssignmentRequest;
use App\Modules\AttendanceManagement\Requests\UpdateShiftAssignmentRequest;
use App\Modules\AttendanceManagement\Resources\ShiftAssignmentResource;
use App\Modules\AttendanceManagement\Services\AttendanceSchedulingService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftAssignmentController
{
    public function __construct(private readonly AttendanceSchedulingService $attendanceSchedulingService) {}

    public function index(Request $request): JsonResponse
    {
        $assignments = ShiftAssignment::query()
            ->with(['shift', 'employee', 'department', 'location'])
            ->when(
                $request->filled('assignment_type'),
                fn (Builder $query) => $query->where('assignment_type', $request->string('assignment_type')->toString()),
            )
            ->when(
                $request->filled('employee_id'),
                fn (Builder $query) => $query->where('employee_id', $request->integer('employee_id')),
            )
            ->when(
                $request->filled('department_id'),
                fn (Builder $query) => $query->where('department_id', $request->integer('department_id')),
            )
            ->when(
                $request->filled('location_id'),
                fn (Builder $query) => $query->where('location_id', $request->integer('location_id')),
            )
            ->orderByDesc('effective_from')
            ->get();

        $payload = ApiResponse::success(
            'Shift assignments loaded successfully.',
            ShiftAssignmentResource::collection($assignments),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreShiftAssignmentRequest $request): JsonResponse
    {
        $assignment = $this->attendanceSchedulingService->createShiftAssignment($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Shift assignment created successfully.',
            new ShiftAssignmentResource($assignment),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateShiftAssignmentRequest $request, int $shiftAssignmentId): JsonResponse
    {
        $assignment = ShiftAssignment::query()->findOrFail($shiftAssignmentId);
        $assignment = $this->attendanceSchedulingService->updateShiftAssignment(
            $request->user(),
            $assignment,
            $request->validated(),
        );

        $payload = ApiResponse::success(
            'Shift assignment updated successfully.',
            new ShiftAssignmentResource($assignment),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
