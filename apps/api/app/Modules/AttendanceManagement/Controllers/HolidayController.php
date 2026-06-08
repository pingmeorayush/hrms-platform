<?php

namespace App\Modules\AttendanceManagement\Controllers;

use App\Models\Holiday;
use App\Models\HolidayCalendar;
use App\Modules\AttendanceManagement\Requests\StoreHolidayRequest;
use App\Modules\AttendanceManagement\Requests\UpdateHolidayRequest;
use App\Modules\AttendanceManagement\Resources\HolidayResource;
use App\Modules\AttendanceManagement\Services\AttendanceConfigurationService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class HolidayController
{
    public function __construct(private readonly AttendanceConfigurationService $attendanceConfigurationService) {}

    public function store(StoreHolidayRequest $request, int $holidayCalendarId): JsonResponse
    {
        $holidayCalendar = HolidayCalendar::query()->findOrFail($holidayCalendarId);
        $holiday = $this->attendanceConfigurationService->createHoliday(
            $request->user(),
            $holidayCalendar,
            $request->validated(),
        );

        $payload = ApiResponse::success(
            'Holiday created successfully.',
            new HolidayResource($holiday),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateHolidayRequest $request, int $holidayCalendarId, int $holidayId): JsonResponse
    {
        $holidayCalendar = HolidayCalendar::query()->findOrFail($holidayCalendarId);
        $holiday = Holiday::query()
            ->whereBelongsTo($holidayCalendar, 'holidayCalendar')
            ->findOrFail($holidayId);

        $holiday = $this->attendanceConfigurationService->updateHoliday(
            $request->user(),
            $holidayCalendar,
            $holiday,
            $request->validated(),
        );

        $payload = ApiResponse::success(
            'Holiday updated successfully.',
            new HolidayResource($holiday),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
