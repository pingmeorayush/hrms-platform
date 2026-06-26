<?php

namespace App\Modules\RecruitmentManagement\Controllers;

use App\Modules\Platform\Shared\Http\ApiResponse;
use App\Modules\RecruitmentManagement\Requests\ListInterviewsRequest;
use App\Modules\RecruitmentManagement\Requests\StoreInterviewFeedbackRequest;
use App\Modules\RecruitmentManagement\Requests\StoreInterviewRequest;
use App\Modules\RecruitmentManagement\Requests\UpdateInterviewRequest;
use App\Modules\RecruitmentManagement\Resources\InterviewResource;
use App\Modules\RecruitmentManagement\Services\InterviewService;
use Illuminate\Http\JsonResponse;

class InterviewController
{
    public function __construct(private readonly InterviewService $interviewService) {}

    public function index(ListInterviewsRequest $request): JsonResponse
    {
        $interviews = $this->interviewService->search($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Interviews loaded successfully.',
            [
                'items' => InterviewResource::collection($interviews->items()),
                'meta' => [
                    'page' => $interviews->currentPage(),
                    'per_page' => $interviews->perPage(),
                    'total' => $interviews->total(),
                    'last_page' => $interviews->lastPage(),
                ],
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreInterviewRequest $request): JsonResponse
    {
        $interview = $this->interviewService->schedule($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Interview scheduled successfully.',
            new InterviewResource($interview),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(int $interviewId): JsonResponse
    {
        $interview = $this->interviewService->findForView(request()->user(), $interviewId);

        $payload = ApiResponse::success(
            'Interview loaded successfully.',
            new InterviewResource($interview),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateInterviewRequest $request, int $interviewId): JsonResponse
    {
        $interview = $this->interviewService->cancel(
            $request->user(),
            $interviewId,
            (string) $request->validated('comment'),
        );

        $payload = ApiResponse::success(
            'Interview updated successfully.',
            new InterviewResource($interview),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function storeFeedback(StoreInterviewFeedbackRequest $request, int $interviewId): JsonResponse
    {
        $interview = $this->interviewService->submitFeedback($request->user(), $interviewId, $request->validated());

        $payload = ApiResponse::success(
            'Interview feedback submitted successfully.',
            new InterviewResource($interview),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
