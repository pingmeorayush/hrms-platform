<?php

namespace App\Modules\PayrollManagement\Controllers;

use App\Models\PayrollRun;
use App\Modules\PayrollManagement\Requests\ListPayrollInputsRequest;
use App\Modules\PayrollManagement\Resources\PayrollInputResource;
use App\Modules\PayrollManagement\Services\PayrollInputService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class PayrollInputController
{
    public function __construct(private readonly PayrollInputService $payrollInputService) {}

    public function index(ListPayrollInputsRequest $request, int $payrollRunId): JsonResponse
    {
        $run = PayrollRun::query()->findOrFail($payrollRunId);
        $result = $this->payrollInputService->listInputs($run, $request->validated());

        $payload = ApiResponse::success(
            'Payroll inputs loaded successfully.',
            [
                'run' => [
                    'id' => $run->id,
                    'status' => $run->status,
                    'input_summary' => $run->input_summary ?? [],
                    'inputs_generated_at' => $run->inputs_generated_at?->toIso8601String(),
                ],
                'items' => PayrollInputResource::collection($result),
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
