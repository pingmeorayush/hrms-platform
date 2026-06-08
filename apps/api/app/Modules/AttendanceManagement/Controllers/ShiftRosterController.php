<?php

namespace App\Modules\AttendanceManagement\Controllers;

use App\Models\ShiftRoster;
use App\Modules\AttendanceManagement\Requests\StoreShiftRosterRequest;
use App\Modules\AttendanceManagement\Requests\UpdateShiftRosterRequest;
use App\Modules\AttendanceManagement\Resources\ShiftRosterResource;
use App\Modules\AttendanceManagement\Services\AttendanceSchedulingService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftRosterController
{
    public function __construct(private readonly AttendanceSchedulingService $attendanceSchedulingService) {}

    public function index(Request $request): JsonResponse
    {
        $rosters = ShiftRoster::query()
            ->with(['employee', 'shift'])
            ->when(
                $request->filled('employee_id'),
                fn (Builder $query) => $query->where('employee_id', $request->integer('employee_id')),
            )
            ->when(
                $request->filled('date_from'),
                fn (Builder $query) => $query->where('work_date', '>=', $request->string('date_from')->toString()),
            )
            ->when(
                $request->filled('date_to'),
                fn (Builder $query) => $query->where(
                    'work_date',
                    '<',
                    Carbon::parse($request->string('date_to')->toString())->addDay()->toDateString(),
                ),
            )
            ->orderBy('work_date')
            ->orderBy('employee_id')
            ->get();

        $payload = ApiResponse::success(
            'Shift rosters loaded successfully.',
            ShiftRosterResource::collection($rosters),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreShiftRosterRequest $request): JsonResponse
    {
        $rosters = $this->attendanceSchedulingService->createRosters($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Shift rosters scheduled successfully.',
            ShiftRosterResource::collection($rosters),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateShiftRosterRequest $request, int $shiftRosterId): JsonResponse
    {
        $roster = ShiftRoster::query()->findOrFail($shiftRosterId);
        $roster = $this->attendanceSchedulingService->updateRoster($request->user(), $roster, $request->validated());

        $payload = ApiResponse::success(
            'Shift roster updated successfully.',
            new ShiftRosterResource($roster),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
