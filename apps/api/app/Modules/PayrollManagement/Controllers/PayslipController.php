<?php

namespace App\Modules\PayrollManagement\Controllers;

use App\Models\PayrollRun;
use App\Modules\PayrollManagement\Requests\ListPayslipsRequest;
use App\Modules\PayrollManagement\Resources\PayslipResource;
use App\Modules\PayrollManagement\Services\PayslipService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PayslipController
{
    public function __construct(private readonly PayslipService $payslipService) {}

    public function generate(Request $request, int $payrollRunId): JsonResponse
    {
        $run = PayrollRun::query()->findOrFail($payrollRunId);
        $payslips = $this->payslipService->generateForRun($request->user(), $run);

        $payload = ApiResponse::success(
            'Payslips generated successfully.',
            [
                'generated_count' => $payslips->count(),
                'items' => PayslipResource::collection($payslips),
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function index(ListPayslipsRequest $request): JsonResponse
    {
        $payslips = $this->payslipService->searchPayslips($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Payslips loaded successfully.',
            [
                'items' => PayslipResource::collection($payslips->getCollection()),
                'meta' => [
                    'page' => $payslips->currentPage(),
                    'per_page' => $payslips->perPage(),
                    'total' => $payslips->total(),
                    'last_page' => $payslips->lastPage(),
                ],
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(Request $request, int $payslipId): JsonResponse
    {
        $payslip = $this->payslipService->showPayslip($request->user(), $payslipId);

        $payload = ApiResponse::success(
            'Payslip loaded successfully.',
            new PayslipResource($payslip),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function download(Request $request, int $payslipId): Response
    {
        return $this->payslipService->downloadPayslip($request->user(), $payslipId);
    }
}
