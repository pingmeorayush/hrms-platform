<?php

namespace App\Modules\ReportingAnalytics\Controllers;

use App\Modules\Platform\Shared\Http\ApiResponse;
use App\Modules\ReportingAnalytics\Requests\ListSavedReportViewsRequest;
use App\Modules\ReportingAnalytics\Requests\StoreSavedReportViewRequest;
use App\Modules\ReportingAnalytics\Requests\UpdateSavedReportViewRequest;
use App\Modules\ReportingAnalytics\Resources\SavedReportViewResource;
use App\Modules\ReportingAnalytics\Services\ReportingSavedViewService;
use Illuminate\Http\JsonResponse;

class SavedReportViewController
{
    public function __construct(private readonly ReportingSavedViewService $reportingSavedViewService) {}

    public function index(ListSavedReportViewsRequest $request): JsonResponse
    {
        $views = $this->reportingSavedViewService->searchViews($request->user(), $request->validated());

        $payload = ApiResponse::success('Saved report views loaded successfully.', [
            'items' => SavedReportViewResource::collection($views->items()),
            'meta' => [
                'page' => $views->currentPage(),
                'per_page' => $views->perPage(),
                'total' => $views->total(),
                'last_page' => $views->lastPage(),
            ],
        ]);

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreSavedReportViewRequest $request): JsonResponse
    {
        $view = $this->reportingSavedViewService->createView($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Saved report view created successfully.',
            new SavedReportViewResource($view),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(int $savedReportViewId): JsonResponse
    {
        $view = $this->reportingSavedViewService->showView(request()->user(), $savedReportViewId);

        $payload = ApiResponse::success(
            'Saved report view loaded successfully.',
            new SavedReportViewResource($view),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateSavedReportViewRequest $request, int $savedReportViewId): JsonResponse
    {
        $view = $this->reportingSavedViewService->updateView($request->user(), $savedReportViewId, $request->validated());

        $payload = ApiResponse::success(
            'Saved report view updated successfully.',
            new SavedReportViewResource($view),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function destroy(int $savedReportViewId): JsonResponse
    {
        $view = $this->reportingSavedViewService->archiveView(request()->user(), $savedReportViewId);

        $payload = ApiResponse::success(
            'Saved report view archived successfully.',
            new SavedReportViewResource($view),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
