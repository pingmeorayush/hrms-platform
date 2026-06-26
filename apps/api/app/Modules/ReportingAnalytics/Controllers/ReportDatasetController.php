<?php

namespace App\Modules\ReportingAnalytics\Controllers;

use App\Modules\Platform\Shared\Http\ApiResponse;
use App\Modules\ReportingAnalytics\Requests\ListReportDatasetsRequest;
use App\Modules\ReportingAnalytics\Requests\StoreReportDatasetRequest;
use App\Modules\ReportingAnalytics\Requests\UpdateReportDatasetRequest;
use App\Modules\ReportingAnalytics\Resources\ReportDatasetResource;
use App\Modules\ReportingAnalytics\Services\ReportingCatalogService;
use Illuminate\Http\JsonResponse;

class ReportDatasetController
{
    public function __construct(private readonly ReportingCatalogService $reportingCatalogService) {}

    public function index(ListReportDatasetsRequest $request): JsonResponse
    {
        $datasets = $this->reportingCatalogService->searchDatasets($request->user(), $request->validated());

        $payload = ApiResponse::success('Reporting datasets loaded successfully.', [
            'items' => ReportDatasetResource::collection($datasets->items()),
            'meta' => [
                'page' => $datasets->currentPage(),
                'per_page' => $datasets->perPage(),
                'total' => $datasets->total(),
                'last_page' => $datasets->lastPage(),
            ],
        ]);

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreReportDatasetRequest $request): JsonResponse
    {
        $dataset = $this->reportingCatalogService->createDataset($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Reporting dataset created successfully.',
            new ReportDatasetResource($dataset),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(int $reportDatasetId): JsonResponse
    {
        $dataset = $this->reportingCatalogService->findDatasetForView(request()->user(), $reportDatasetId);

        $payload = ApiResponse::success(
            'Reporting dataset loaded successfully.',
            new ReportDatasetResource($dataset),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateReportDatasetRequest $request, int $reportDatasetId): JsonResponse
    {
        $dataset = $this->reportingCatalogService->findDatasetForView($request->user(), $reportDatasetId);
        $dataset = $this->reportingCatalogService->updateDataset($request->user(), $dataset, $request->validated());

        $payload = ApiResponse::success(
            'Reporting dataset updated successfully.',
            new ReportDatasetResource($dataset),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
