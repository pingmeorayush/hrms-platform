<?php

namespace App\Modules\AttendanceManagement\Controllers;

use App\Modules\AttendanceManagement\Requests\ListAttendanceRecordsRequest;
use App\Modules\AttendanceManagement\Requests\RecalculateAttendanceRequest;
use App\Modules\AttendanceManagement\Requests\StoreAttendanceCheckInRequest;
use App\Modules\AttendanceManagement\Requests\StoreAttendanceCheckOutRequest;
use App\Modules\AttendanceManagement\Resources\AttendanceRecordResource;
use App\Modules\AttendanceManagement\Services\AttendanceCalculationService;
use App\Modules\AttendanceManagement\Services\AttendanceRecordService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class AttendanceRecordController
{
    public function __construct(
        private readonly AttendanceCalculationService $attendanceCalculationService,
        private readonly AttendanceRecordService $attendanceRecordService,
    ) {}

    public function index(ListAttendanceRecordsRequest $request): JsonResponse
    {
        $records = $this->attendanceRecordService->search($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Attendance records loaded successfully.',
            [
                'items' => AttendanceRecordResource::collection($records->items()),
                'meta' => [
                    'page' => $records->currentPage(),
                    'per_page' => $records->perPage(),
                    'total' => $records->total(),
                    'last_page' => $records->lastPage(),
                ],
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(int $attendanceRecordId): JsonResponse
    {
        $record = $this->attendanceRecordService->findForView(request()->user(), $attendanceRecordId);

        $payload = ApiResponse::success(
            'Attendance record loaded successfully.',
            new AttendanceRecordResource($record),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function checkIn(StoreAttendanceCheckInRequest $request): JsonResponse
    {
        $record = $this->attendanceRecordService->checkIn(
            $request->user(),
            $request->validated(),
            [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
        );

        $payload = ApiResponse::success(
            'Attendance check-in recorded successfully.',
            new AttendanceRecordResource($record),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function checkOut(StoreAttendanceCheckOutRequest $request): JsonResponse
    {
        $record = $this->attendanceRecordService->checkOut(
            $request->user(),
            $request->validated(),
            [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
        );

        $payload = ApiResponse::success(
            'Attendance check-out recorded successfully.',
            new AttendanceRecordResource($record),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function recalculate(RecalculateAttendanceRequest $request): JsonResponse
    {
        $summary = $this->attendanceCalculationService->recalculate($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Attendance records recalculated successfully.',
            $summary,
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
