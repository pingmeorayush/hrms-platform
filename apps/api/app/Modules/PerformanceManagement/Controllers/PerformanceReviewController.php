<?php

namespace App\Modules\PerformanceManagement\Controllers;

use App\Modules\PerformanceManagement\Requests\CalibratePerformanceReviewRequest;
use App\Modules\PerformanceManagement\Requests\FinalizePerformanceReviewRequest;
use App\Modules\PerformanceManagement\Requests\ListPerformanceReviewsRequest;
use App\Modules\PerformanceManagement\Requests\ReopenPerformanceReviewRequest;
use App\Modules\PerformanceManagement\Requests\StorePerformanceReviewRequest;
use App\Modules\PerformanceManagement\Requests\SubmitPerformanceReviewRequest;
use App\Modules\PerformanceManagement\Requests\UpdatePerformanceReviewRequest;
use App\Modules\PerformanceManagement\Resources\PerformanceReviewResource;
use App\Modules\PerformanceManagement\Services\PerformanceReviewExecutionService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerformanceReviewController
{
    public function __construct(private readonly PerformanceReviewExecutionService $performanceReviewExecutionService) {}

    public function index(ListPerformanceReviewsRequest $request): JsonResponse
    {
        $reviews = $this->performanceReviewExecutionService->searchReviews($request->user(), $request->validated());

        $payload = ApiResponse::success('Performance reviews loaded successfully.', [
            'items' => PerformanceReviewResource::collection($reviews->items()),
            'meta' => [
                'page' => $reviews->currentPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
                'last_page' => $reviews->lastPage(),
            ],
        ]);

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StorePerformanceReviewRequest $request): JsonResponse
    {
        $review = $this->performanceReviewExecutionService->createReview($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Performance review created successfully.',
            new PerformanceReviewResource($review),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(int $performanceReviewId): JsonResponse
    {
        $review = $this->performanceReviewExecutionService->findForView(request()->user(), $performanceReviewId);

        $payload = ApiResponse::success(
            'Performance review loaded successfully.',
            new PerformanceReviewResource($review),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdatePerformanceReviewRequest $request, int $performanceReviewId): JsonResponse
    {
        $review = $this->performanceReviewExecutionService->findForView($request->user(), $performanceReviewId);
        $review = $this->performanceReviewExecutionService->updateReview($request->user(), $review, $request->validated());

        $payload = ApiResponse::success(
            'Performance review updated successfully.',
            new PerformanceReviewResource($review),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function submit(SubmitPerformanceReviewRequest $request, int $performanceReviewId): JsonResponse
    {
        $review = $this->performanceReviewExecutionService->findForView($request->user(), $performanceReviewId);
        $review = $this->performanceReviewExecutionService->submitReview($request->user(), $review, $request->validated());

        $payload = ApiResponse::success(
            'Performance review submission saved successfully.',
            new PerformanceReviewResource($review),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function calibrate(CalibratePerformanceReviewRequest $request, int $performanceReviewId): JsonResponse
    {
        $review = $this->performanceReviewExecutionService->findForView($request->user(), $performanceReviewId);
        $review = $this->performanceReviewExecutionService->calibrateReview($request->user(), $review, $request->validated());

        $payload = ApiResponse::success(
            'Performance review calibrated successfully.',
            new PerformanceReviewResource($review),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function finalize(FinalizePerformanceReviewRequest $request, int $performanceReviewId): JsonResponse
    {
        $review = $this->performanceReviewExecutionService->findForView($request->user(), $performanceReviewId);
        $review = $this->performanceReviewExecutionService->finalizeReview($request->user(), $review, $request->validated());

        $payload = ApiResponse::success(
            'Performance review finalized successfully.',
            new PerformanceReviewResource($review),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function publish(Request $request, int $performanceReviewId): JsonResponse
    {
        $review = $this->performanceReviewExecutionService->findForView($request->user(), $performanceReviewId);
        $review = $this->performanceReviewExecutionService->publishReview($request->user(), $review);

        $payload = ApiResponse::success(
            'Performance review published successfully.',
            new PerformanceReviewResource($review),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function reopen(ReopenPerformanceReviewRequest $request, int $performanceReviewId): JsonResponse
    {
        $review = $this->performanceReviewExecutionService->findForView($request->user(), $performanceReviewId);
        $review = $this->performanceReviewExecutionService->reopenReview($request->user(), $review, $request->validated()['reason']);

        $payload = ApiResponse::success(
            'Performance review reopened successfully.',
            new PerformanceReviewResource($review),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
