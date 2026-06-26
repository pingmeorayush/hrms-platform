<?php

namespace App\Modules\LeaveManagement\Controllers;

use App\Modules\LeaveManagement\Requests\ListLeaveRequestsRequest;
use App\Modules\LeaveManagement\Requests\StoreLeaveRequestRequest;
use App\Modules\LeaveManagement\Requests\UpdateLeaveRequestRequest;
use App\Modules\LeaveManagement\Resources\LeaveRequestResource;
use App\Modules\LeaveManagement\Services\LeaveRequestService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class LeaveRequestController
{
    public function __construct(private readonly LeaveRequestService $leaveRequestService) {}

    public function index(ListLeaveRequestsRequest $request): JsonResponse
    {
        $requests = $this->leaveRequestService->search($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Leave requests loaded successfully.',
            [
                'items' => LeaveRequestResource::collection($requests->items()),
                'meta' => [
                    'page' => $requests->currentPage(),
                    'per_page' => $requests->perPage(),
                    'total' => $requests->total(),
                    'last_page' => $requests->lastPage(),
                ],
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreLeaveRequestRequest $request): JsonResponse
    {
        $leaveRequest = $this->leaveRequestService->submit($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Leave request submitted successfully.',
            new LeaveRequestResource($leaveRequest),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(int $leaveRequestId): JsonResponse
    {
        $leaveRequest = $this->leaveRequestService->findForView(request()->user(), $leaveRequestId);

        $payload = ApiResponse::success(
            'Leave request loaded successfully.',
            new LeaveRequestResource($leaveRequest),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateLeaveRequestRequest $request, int $leaveRequestId): JsonResponse
    {
        $leaveRequest = $this->leaveRequestService->update(
            $request->user(),
            $leaveRequestId,
            $request->validated(),
        );

        $payload = ApiResponse::success(
            'Leave request updated successfully.',
            new LeaveRequestResource($leaveRequest),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
