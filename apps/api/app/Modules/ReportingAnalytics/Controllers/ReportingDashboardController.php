<?php

namespace App\Modules\ReportingAnalytics\Controllers;

use App\Modules\Platform\Shared\Http\ApiResponse;
use App\Modules\ReportingAnalytics\Requests\ShowReportingDashboardRequest;
use App\Modules\ReportingAnalytics\Services\ReportingDashboardService;
use Illuminate\Http\JsonResponse;

class ReportingDashboardController
{
    public function __construct(private readonly ReportingDashboardService $reportingDashboardService) {}

    public function show(ShowReportingDashboardRequest $request, string $dashboardKey): JsonResponse
    {
        $result = $this->reportingDashboardService->show(
            $request->user(),
            $dashboardKey,
            (bool) ($request->validated()['force_refresh'] ?? false),
        );

        $payload = ApiResponse::success('Reporting dashboard loaded successfully.', $result);

        return response()->json($payload['body'], $payload['status']);
    }
}
