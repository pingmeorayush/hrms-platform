<?php

namespace App\Modules\PerformanceManagement\Controllers;

use App\Modules\PerformanceManagement\Requests\ListPerformanceCompetenciesRequest;
use App\Modules\PerformanceManagement\Requests\StorePerformanceCompetencyRequest;
use App\Modules\PerformanceManagement\Requests\UpdatePerformanceCompetencyRequest;
use App\Modules\PerformanceManagement\Resources\PerformanceCompetencyResource;
use App\Modules\PerformanceManagement\Services\PerformanceConfigurationService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class PerformanceCompetencyController
{
    public function __construct(private readonly PerformanceConfigurationService $performanceConfigurationService) {}

    public function index(ListPerformanceCompetenciesRequest $request): JsonResponse
    {
        $competencies = $this->performanceConfigurationService->searchCompetencies($request->user(), $request->validated());

        $payload = ApiResponse::success('Performance competencies loaded successfully.', [
            'items' => PerformanceCompetencyResource::collection($competencies->items()),
            'meta' => [
                'page' => $competencies->currentPage(),
                'per_page' => $competencies->perPage(),
                'total' => $competencies->total(),
                'last_page' => $competencies->lastPage(),
            ],
        ]);

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StorePerformanceCompetencyRequest $request): JsonResponse
    {
        $competency = $this->performanceConfigurationService->createCompetency($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Performance competency created successfully.',
            new PerformanceCompetencyResource($competency),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(int $performanceCompetencyId): JsonResponse
    {
        $competency = $this->performanceConfigurationService->findCompetencyForView(request()->user(), $performanceCompetencyId);

        $payload = ApiResponse::success(
            'Performance competency loaded successfully.',
            new PerformanceCompetencyResource($competency),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdatePerformanceCompetencyRequest $request, int $performanceCompetencyId): JsonResponse
    {
        $competency = $this->performanceConfigurationService->findCompetencyForView($request->user(), $performanceCompetencyId);
        $competency = $this->performanceConfigurationService->updateCompetency($request->user(), $competency, $request->validated());

        $payload = ApiResponse::success(
            'Performance competency updated successfully.',
            new PerformanceCompetencyResource($competency),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
