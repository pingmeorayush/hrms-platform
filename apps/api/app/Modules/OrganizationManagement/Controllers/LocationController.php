<?php

namespace App\Modules\OrganizationManagement\Controllers;

use App\Models\Location;
use App\Modules\OrganizationManagement\Requests\StoreLocationRequest;
use App\Modules\OrganizationManagement\Requests\UpdateLocationRequest;
use App\Modules\OrganizationManagement\Resources\LocationResource;
use App\Modules\OrganizationManagement\Services\OrganizationStructureService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class LocationController
{
    public function __construct(private readonly OrganizationStructureService $organizationStructureService) {}

    public function index(): JsonResponse
    {
        $locations = Location::query()->orderBy('name')->get();

        $payload = ApiResponse::success(
            'Locations loaded successfully.',
            LocationResource::collection($locations),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreLocationRequest $request): JsonResponse
    {
        $location = $this->organizationStructureService->createMasterRecord(
            $request->user(),
            Location::class,
            $request->validated(),
            'organization.location.created',
            'location',
        );

        $payload = ApiResponse::success(
            'Location created successfully.',
            new LocationResource($location),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateLocationRequest $request, int $locationId): JsonResponse
    {
        $location = Location::query()->findOrFail($locationId);

        $location = $this->organizationStructureService->updateMasterRecord(
            $request->user(),
            $location,
            $request->validated(),
            'organization.location.updated',
            'location',
            [
                'code',
                'name',
                'timezone',
                'currency',
                'address_line_1',
                'address_line_2',
                'city',
                'state',
                'country',
                'postal_code',
                'status',
            ],
        );

        $payload = ApiResponse::success(
            'Location updated successfully.',
            new LocationResource($location),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
