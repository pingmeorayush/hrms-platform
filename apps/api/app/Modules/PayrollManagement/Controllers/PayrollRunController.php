<?php

namespace App\Modules\PayrollManagement\Controllers;

use App\Models\PayrollRun;
use App\Modules\PayrollManagement\Requests\ListPayrollRunsRequest;
use App\Modules\PayrollManagement\Requests\PayrollRunActionRequest;
use App\Modules\PayrollManagement\Resources\PayrollRunResource;
use App\Modules\PayrollManagement\Services\PayrollControlService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class PayrollRunController
{
    public function __construct(private readonly PayrollControlService $payrollControlService) {}

    public function index(ListPayrollRunsRequest $request): JsonResponse
    {
        $runs = $this->payrollControlService->searchRuns($request->validated());

        $payload = ApiResponse::success(
            'Payroll runs loaded successfully.',
            [
                'items' => PayrollRunResource::collection($runs->getCollection()),
                'meta' => [
                    'page' => $runs->currentPage(),
                    'per_page' => $runs->perPage(),
                    'total' => $runs->total(),
                    'last_page' => $runs->lastPage(),
                ],
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(int $payrollRunId): JsonResponse
    {
        $run = PayrollRun::query()
            ->with(['payrollPeriod.payrollCalendar', 'items.employee'])
            ->findOrFail($payrollRunId);

        $payload = ApiResponse::success(
            'Payroll run loaded successfully.',
            new PayrollRunResource($run),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function calculate(PayrollRunActionRequest $request, int $payrollRunId): JsonResponse
    {
        $run = PayrollRun::query()->findOrFail($payrollRunId);
        $run = $this->payrollControlService->calculateRun($request->user(), $run);

        $payload = ApiResponse::success(
            'Payroll run calculated successfully.',
            new PayrollRunResource($run),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function approve(PayrollRunActionRequest $request, int $payrollRunId): JsonResponse
    {
        $run = PayrollRun::query()->findOrFail($payrollRunId);
        $run = $this->payrollControlService->approveRun($request->user(), $run, $request->validated());

        $payload = ApiResponse::success(
            'Payroll run approved successfully.',
            new PayrollRunResource($run),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function lock(PayrollRunActionRequest $request, int $payrollRunId): JsonResponse
    {
        $run = PayrollRun::query()->findOrFail($payrollRunId);
        $run = $this->payrollControlService->lockRun($request->user(), $run);

        $payload = ApiResponse::success(
            'Payroll run locked successfully.',
            new PayrollRunResource($run),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function reopen(PayrollRunActionRequest $request, int $payrollRunId): JsonResponse
    {
        $run = PayrollRun::query()->findOrFail($payrollRunId);
        $run = $this->payrollControlService->reopenRun($request->user(), $run, $request->validated());

        $payload = ApiResponse::success(
            'Payroll run reopened successfully.',
            new PayrollRunResource($run),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
