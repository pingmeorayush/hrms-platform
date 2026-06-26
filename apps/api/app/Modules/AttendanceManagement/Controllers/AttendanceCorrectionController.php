<?php

namespace App\Modules\AttendanceManagement\Controllers;

use App\Models\AttendanceCorrection;
use App\Modules\AttendanceManagement\Requests\ListAttendanceCorrectionsRequest;
use App\Modules\AttendanceManagement\Requests\StoreAttendanceCorrectionRequest;
use App\Modules\AttendanceManagement\Requests\UpdateAttendanceCorrectionRequest;
use App\Modules\AttendanceManagement\Resources\AttendanceCorrectionResource;
use App\Modules\AttendanceManagement\Services\AttendanceCorrectionService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class AttendanceCorrectionController
{
    public function __construct(private readonly AttendanceCorrectionService $attendanceCorrectionService) {}

    public function index(ListAttendanceCorrectionsRequest $request): JsonResponse
    {
        $corrections = $this->attendanceCorrectionService->search($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Attendance corrections loaded successfully.',
            [
                'items' => AttendanceCorrectionResource::collection($corrections->items()),
                'meta' => [
                    'page' => $corrections->currentPage(),
                    'per_page' => $corrections->perPage(),
                    'total' => $corrections->total(),
                    'last_page' => $corrections->lastPage(),
                ],
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreAttendanceCorrectionRequest $request): JsonResponse
    {
        $correction = $this->attendanceCorrectionService->create($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Attendance correction submitted successfully.',
            new AttendanceCorrectionResource($correction),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateAttendanceCorrectionRequest $request, int $attendanceCorrectionId): JsonResponse
    {
        $correction = AttendanceCorrection::query()->findOrFail($attendanceCorrectionId);
        $correction = $this->attendanceCorrectionService->decide($correction, $request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Attendance correction updated successfully.',
            new AttendanceCorrectionResource($correction),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
