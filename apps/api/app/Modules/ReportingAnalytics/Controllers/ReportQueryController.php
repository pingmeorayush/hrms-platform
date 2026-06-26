<?php

namespace App\Modules\ReportingAnalytics\Controllers;

use App\Modules\Platform\Shared\Http\ApiResponse;
use App\Modules\ReportingAnalytics\Requests\QueryReportDatasetRequest;
use App\Modules\ReportingAnalytics\Services\ReportingQueryService;
use Illuminate\Http\JsonResponse;

class ReportQueryController
{
    public function __construct(private readonly ReportingQueryService $reportingQueryService) {}

    public function show(QueryReportDatasetRequest $request, string $datasetKey): JsonResponse
    {
        $result = $this->reportingQueryService->query($request->user(), $datasetKey, $request->validated());

        $payload = ApiResponse::success('Reporting dataset rows loaded successfully.', $result);

        return response()->json($payload['body'], $payload['status']);
    }
}
