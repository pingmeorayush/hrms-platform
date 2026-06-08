<?php

namespace App\Modules\PayrollManagement\Controllers;

use App\Models\PayrollCalendar;
use App\Modules\PayrollManagement\Requests\StorePayrollCalendarRequest;
use App\Modules\PayrollManagement\Requests\UpdatePayrollCalendarRequest;
use App\Modules\PayrollManagement\Resources\PayrollCalendarResource;
use App\Modules\PayrollManagement\Services\PayrollControlService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class PayrollCalendarController
{
    public function __construct(private readonly PayrollControlService $payrollControlService) {}

    public function index(): JsonResponse
    {
        $calendars = PayrollCalendar::query()
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $payload = ApiResponse::success(
            'Payroll calendars loaded successfully.',
            PayrollCalendarResource::collection($calendars),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StorePayrollCalendarRequest $request): JsonResponse
    {
        $calendar = $this->payrollControlService->createCalendar($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Payroll calendar created successfully.',
            new PayrollCalendarResource($calendar),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdatePayrollCalendarRequest $request, int $payrollCalendarId): JsonResponse
    {
        $calendar = PayrollCalendar::query()->findOrFail($payrollCalendarId);
        $calendar = $this->payrollControlService->updateCalendar($request->user(), $calendar, $request->validated());

        $payload = ApiResponse::success(
            'Payroll calendar updated successfully.',
            new PayrollCalendarResource($calendar),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
