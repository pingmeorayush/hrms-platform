<?php

namespace App\Modules\ReportingAnalytics\Controllers;

use App\Modules\Platform\Shared\Http\ApiResponse;
use App\Modules\ReportingAnalytics\Requests\ListReportExportsRequest;
use App\Modules\ReportingAnalytics\Requests\StoreReportExportRequest;
use App\Modules\ReportingAnalytics\Resources\ReportExportResource;
use App\Modules\ReportingAnalytics\Services\ReportingExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController
{
    public function __construct(private readonly ReportingExportService $reportingExportService) {}

    public function index(ListReportExportsRequest $request): JsonResponse
    {
        $exports = $this->reportingExportService->searchExports($request->user(), $request->validated());

        $payload = ApiResponse::success('Reporting exports loaded successfully.', [
            'items' => ReportExportResource::collection($exports->items()),
            'meta' => [
                'page' => $exports->currentPage(),
                'per_page' => $exports->perPage(),
                'total' => $exports->total(),
                'last_page' => $exports->lastPage(),
            ],
        ]);

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreReportExportRequest $request): JsonResponse
    {
        $export = $this->reportingExportService->requestExport($request->user(), $request->validated());

        $message = $export->status === 'queued'
            ? 'Report export queued successfully.'
            : 'Report export generated successfully.';

        $payload = ApiResponse::success(
            $message,
            new ReportExportResource($export),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(Request $request, int $reportExportId): JsonResponse
    {
        $export = $this->reportingExportService->showExport($request->user(), $reportExportId);

        $payload = ApiResponse::success(
            'Reporting export loaded successfully.',
            new ReportExportResource($export),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function process(Request $request, int $reportExportId): JsonResponse
    {
        $export = $this->reportingExportService->processExport($request->user(), $reportExportId);
        $message = $export->status === 'completed'
            ? 'Report export processed successfully.'
            : 'Report export processing failed.';

        $payload = ApiResponse::success(
            $message,
            new ReportExportResource($export),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function download(Request $request, int $reportExportId): StreamedResponse
    {
        return $this->reportingExportService->downloadExport($request->user(), $reportExportId);
    }
}
