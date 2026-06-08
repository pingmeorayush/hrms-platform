<?php

namespace App\Modules\AttendanceManagement\Controllers;

use App\Modules\AttendanceManagement\Requests\ShowAttendanceOperationalWindowRequest;
use App\Modules\AttendanceManagement\Resources\AttendanceCorrectionResource;
use App\Modules\AttendanceManagement\Resources\AttendanceOperationalRecordResource;
use App\Modules\AttendanceManagement\Services\AttendanceOperationalReviewService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class AttendanceOperationalReviewController
{
    public function __construct(
        private readonly AttendanceOperationalReviewService $attendanceOperationalReviewService,
    ) {}

    public function overview(ShowAttendanceOperationalWindowRequest $request): JsonResponse
    {
        $review = $this->attendanceOperationalReviewService->overview($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Attendance operational review loaded successfully.',
            [
                'window_date' => $review['window_date'],
                'summary' => $review['summary'],
                'items' => AttendanceOperationalRecordResource::collection($review['items']),
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function pendingExceptions(ShowAttendanceOperationalWindowRequest $request): JsonResponse
    {
        $review = $this->attendanceOperationalReviewService->pendingExceptions($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Attendance pending exceptions loaded successfully.',
            [
                'window_date' => $review['window_date'],
                'summary' => $review['summary'],
                'attendance_items' => AttendanceOperationalRecordResource::collection($review['attendance_items']),
                'correction_items' => AttendanceCorrectionResource::collection($review['correction_items']),
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
