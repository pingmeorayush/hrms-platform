<?php

namespace App\Modules\AttendanceManagement\Controllers;

use App\Models\HolidayCalendar;
use App\Modules\AttendanceManagement\Requests\StoreHolidayCalendarRequest;
use App\Modules\AttendanceManagement\Requests\UpdateHolidayCalendarRequest;
use App\Modules\AttendanceManagement\Resources\HolidayCalendarResource;
use App\Modules\AttendanceManagement\Services\AttendanceConfigurationService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class HolidayCalendarController
{
    public function __construct(private readonly AttendanceConfigurationService $attendanceConfigurationService) {}

    public function index(): JsonResponse
    {
        $calendars = HolidayCalendar::query()
            ->with(['location', 'department', 'holidays'])
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $payload = ApiResponse::success(
            'Holiday calendars loaded successfully.',
            HolidayCalendarResource::collection($calendars),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreHolidayCalendarRequest $request): JsonResponse
    {
        $calendar = $this->attendanceConfigurationService->createHolidayCalendar($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Holiday calendar created successfully.',
            new HolidayCalendarResource($calendar),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateHolidayCalendarRequest $request, int $holidayCalendarId): JsonResponse
    {
        $calendar = HolidayCalendar::query()->findOrFail($holidayCalendarId);
        $calendar = $this->attendanceConfigurationService->updateHolidayCalendar(
            $request->user(),
            $calendar,
            $request->validated(),
        );

        $payload = ApiResponse::success(
            'Holiday calendar updated successfully.',
            new HolidayCalendarResource($calendar),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
