<?php

namespace App\Modules\LeaveManagement\Controllers;

use App\Modules\LeaveManagement\Requests\ListLeaveBalancesRequest;
use App\Modules\LeaveManagement\Resources\LeaveBalanceEntryResource;
use App\Modules\LeaveManagement\Resources\LeaveBalanceResource;
use App\Modules\LeaveManagement\Services\LeaveBalanceService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class LeaveBalanceController
{
    public function __construct(private readonly LeaveBalanceService $leaveBalanceService) {}

    public function index(ListLeaveBalancesRequest $request): JsonResponse
    {
        $balances = $this->leaveBalanceService->listBalances($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Leave balances loaded successfully.',
            LeaveBalanceResource::collection($balances),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(ListLeaveBalancesRequest $request, int $employeeId): JsonResponse
    {
        $detail = $this->leaveBalanceService->showEmployeeBalances($request->user(), $employeeId, $request->validated());

        $payload = ApiResponse::success(
            'Leave balance detail loaded successfully.',
            [
                'employee' => $detail['employee'],
                'balances' => LeaveBalanceResource::collection($detail['balances']),
                'history' => LeaveBalanceEntryResource::collection($detail['history']),
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
