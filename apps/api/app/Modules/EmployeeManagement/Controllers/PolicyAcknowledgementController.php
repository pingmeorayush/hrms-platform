<?php

namespace App\Modules\EmployeeManagement\Controllers;

use App\Models\PolicyAcknowledgement;
use App\Modules\EmployeeManagement\Requests\AcknowledgePolicyRequest;
use App\Modules\EmployeeManagement\Requests\StorePolicyAcknowledgementRequest;
use App\Modules\EmployeeManagement\Resources\PolicyAcknowledgementResource;
use App\Modules\EmployeeManagement\Services\PolicyAcknowledgementService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PolicyAcknowledgementController
{
    public function __construct(private readonly PolicyAcknowledgementService $policyAcknowledgementService) {}

    public function index(Request $request): JsonResponse
    {
        $acknowledgements = $this->policyAcknowledgementService->search(
            $request->user(),
            $request->only(['employee_id', 'status']),
        );

        $payload = ApiResponse::success(
            'Policy acknowledgements loaded successfully.',
            PolicyAcknowledgementResource::collection($acknowledgements),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StorePolicyAcknowledgementRequest $request): JsonResponse
    {
        $acknowledgements = $this->policyAcknowledgementService->assign($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Policy acknowledgements assigned successfully.',
            PolicyAcknowledgementResource::collection($acknowledgements),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function acknowledge(AcknowledgePolicyRequest $request, int $policyAcknowledgementId): JsonResponse
    {
        $acknowledgement = PolicyAcknowledgement::query()->findOrFail($policyAcknowledgementId);
        $acknowledgement = $this->policyAcknowledgementService->acknowledge(
            $acknowledgement,
            $request->user(),
            $request->validated(),
        );

        $payload = ApiResponse::success(
            'Policy acknowledgement recorded successfully.',
            new PolicyAcknowledgementResource($acknowledgement),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function download(Request $request, int $policyAcknowledgementId): StreamedResponse
    {
        $acknowledgement = PolicyAcknowledgement::query()->findOrFail($policyAcknowledgementId);

        return $this->policyAcknowledgementService->download($acknowledgement, $request->user());
    }
}
