<?php

namespace App\Modules\LeaveManagement\Controllers;

use App\Models\LeavePolicy;
use App\Modules\LeaveManagement\Requests\PreviewLeaveAccrualRequest;
use App\Modules\LeaveManagement\Resources\LeaveAccrualResource;
use App\Modules\LeaveManagement\Services\LeaveAccrualService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class LeaveAccrualController
{
    public function __construct(private readonly LeaveAccrualService $leaveAccrualService) {}

    public function preview(PreviewLeaveAccrualRequest $request, int $leavePolicyId): JsonResponse
    {
        $policy = LeavePolicy::query()
            ->with(['leaveType', 'applicableDepartment', 'applicableLocation'])
            ->findOrFail($leavePolicyId);

        $accrual = $this->leaveAccrualService->previewAccrual($request->user(), $policy, $request->validated());

        $payload = ApiResponse::success(
            'Leave accrual preview generated successfully.',
            new LeaveAccrualResource($accrual),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
