<?php

namespace App\Modules\PayrollManagement\Controllers;

use App\Models\PayrollPeriod;
use App\Modules\PayrollManagement\Requests\ListPayrollPeriodsRequest;
use App\Modules\PayrollManagement\Requests\StorePayrollPeriodRequest;
use App\Modules\PayrollManagement\Resources\PayrollPeriodResource;
use App\Modules\PayrollManagement\Resources\PayrollRunResource;
use App\Modules\PayrollManagement\Services\PayrollControlService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class PayrollPeriodController
{
    public function __construct(private readonly PayrollControlService $payrollControlService) {}

    public function index(ListPayrollPeriodsRequest $request): JsonResponse
    {
        $periods = $this->payrollControlService->searchPeriods($request->validated());

        $payload = ApiResponse::success(
            'Payroll periods loaded successfully.',
            [
                'items' => PayrollPeriodResource::collection($periods->getCollection()),
                'meta' => [
                    'page' => $periods->currentPage(),
                    'per_page' => $periods->perPage(),
                    'total' => $periods->total(),
                    'last_page' => $periods->lastPage(),
                ],
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StorePayrollPeriodRequest $request): JsonResponse
    {
        $period = $this->payrollControlService->createPeriod($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Payroll period created successfully.',
            new PayrollPeriodResource($period),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(int $payrollPeriodId): JsonResponse
    {
        $period = PayrollPeriod::query()
            ->with(['payrollCalendar', 'latestRun'])
            ->findOrFail($payrollPeriodId);

        $payload = ApiResponse::success(
            'Payroll period loaded successfully.',
            [
                'period' => new PayrollPeriodResource($period),
                'prerequisites' => $this->payrollControlService->previewPrerequisites($period),
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function open(int $payrollPeriodId): JsonResponse
    {
        $period = PayrollPeriod::query()->findOrFail($payrollPeriodId);
        $period = $this->payrollControlService->openPeriod(request()->user(), $period);

        $payload = ApiResponse::success(
            'Payroll period opened successfully.',
            new PayrollPeriodResource($period),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function prepare(int $payrollPeriodId): JsonResponse
    {
        $period = PayrollPeriod::query()->findOrFail($payrollPeriodId);
        $result = $this->payrollControlService->preparePeriod(request()->user(), $period);

        $payload = ApiResponse::success(
            'Payroll period prepared successfully.',
            [
                'period' => new PayrollPeriodResource($result['period']),
                'prerequisites' => $result['prerequisites'],
                'run' => new PayrollRunResource($result['run']),
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function close(int $payrollPeriodId): JsonResponse
    {
        $period = PayrollPeriod::query()->findOrFail($payrollPeriodId);
        $period = $this->payrollControlService->closePeriod(request()->user(), $period);

        $payload = ApiResponse::success(
            'Payroll period closed successfully.',
            new PayrollPeriodResource($period),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
