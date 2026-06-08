<?php

namespace App\Modules\PayrollManagement\Controllers;

use App\Models\PayrollAdjustment;
use App\Models\PayrollRun;
use App\Modules\PayrollManagement\Requests\ListPayrollAdjustmentsRequest;
use App\Modules\PayrollManagement\Requests\StorePayrollAdjustmentRequest;
use App\Modules\PayrollManagement\Requests\UpdatePayrollAdjustmentRequest;
use App\Modules\PayrollManagement\Resources\PayrollAdjustmentResource;
use App\Modules\PayrollManagement\Services\PayrollInputService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class PayrollAdjustmentController
{
    public function __construct(private readonly PayrollInputService $payrollInputService) {}

    public function index(ListPayrollAdjustmentsRequest $request, int $payrollRunId): JsonResponse
    {
        $run = PayrollRun::query()->findOrFail($payrollRunId);
        $adjustments = $this->payrollInputService->listAdjustments($run, $request->validated());

        $payload = ApiResponse::success(
            'Payroll adjustments loaded successfully.',
            PayrollAdjustmentResource::collection($adjustments),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StorePayrollAdjustmentRequest $request, int $payrollRunId): JsonResponse
    {
        $run = PayrollRun::query()->findOrFail($payrollRunId);
        $adjustment = $this->payrollInputService->createAdjustment($request->user(), $run, $request->validated());

        $payload = ApiResponse::success(
            'Payroll adjustment created successfully.',
            new PayrollAdjustmentResource($adjustment),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdatePayrollAdjustmentRequest $request, int $payrollRunId, int $payrollAdjustmentId): JsonResponse
    {
        $run = PayrollRun::query()->findOrFail($payrollRunId);
        $adjustment = PayrollAdjustment::query()
            ->where('payroll_run_id', $run->id)
            ->findOrFail($payrollAdjustmentId);

        $adjustment = $this->payrollInputService->updateAdjustment($request->user(), $run, $adjustment, $request->validated());

        $payload = ApiResponse::success(
            'Payroll adjustment updated successfully.',
            new PayrollAdjustmentResource($adjustment),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
