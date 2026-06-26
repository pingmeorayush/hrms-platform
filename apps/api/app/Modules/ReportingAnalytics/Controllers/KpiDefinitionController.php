<?php

namespace App\Modules\ReportingAnalytics\Controllers;

use App\Modules\Platform\Shared\Http\ApiResponse;
use App\Modules\ReportingAnalytics\Requests\ListKpiDefinitionsRequest;
use App\Modules\ReportingAnalytics\Requests\StoreKpiDefinitionRequest;
use App\Modules\ReportingAnalytics\Requests\UpdateKpiDefinitionRequest;
use App\Modules\ReportingAnalytics\Resources\KpiDefinitionResource;
use App\Modules\ReportingAnalytics\Services\ReportingCatalogService;
use Illuminate\Http\JsonResponse;

class KpiDefinitionController
{
    public function __construct(private readonly ReportingCatalogService $reportingCatalogService) {}

    public function index(ListKpiDefinitionsRequest $request): JsonResponse
    {
        $kpis = $this->reportingCatalogService->searchKpis($request->user(), $request->validated());

        $payload = ApiResponse::success('Reporting KPIs loaded successfully.', [
            'items' => KpiDefinitionResource::collection($kpis->items()),
            'meta' => [
                'page' => $kpis->currentPage(),
                'per_page' => $kpis->perPage(),
                'total' => $kpis->total(),
                'last_page' => $kpis->lastPage(),
            ],
        ]);

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreKpiDefinitionRequest $request): JsonResponse
    {
        $kpi = $this->reportingCatalogService->createKpi($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Reporting KPI created successfully.',
            new KpiDefinitionResource($kpi),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(int $kpiDefinitionId): JsonResponse
    {
        $kpi = $this->reportingCatalogService->findKpiForView(request()->user(), $kpiDefinitionId);

        $payload = ApiResponse::success(
            'Reporting KPI loaded successfully.',
            new KpiDefinitionResource($kpi),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateKpiDefinitionRequest $request, int $kpiDefinitionId): JsonResponse
    {
        $kpi = $this->reportingCatalogService->findKpiForView($request->user(), $kpiDefinitionId);
        $kpi = $this->reportingCatalogService->updateKpi($request->user(), $kpi, $request->validated());

        $payload = ApiResponse::success(
            'Reporting KPI updated successfully.',
            new KpiDefinitionResource($kpi),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
