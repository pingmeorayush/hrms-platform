<?php

namespace App\Modules\OrganizationManagement\Controllers;

use App\Modules\OrganizationManagement\Requests\UpdateCompanyProfileRequest;
use App\Modules\OrganizationManagement\Resources\CompanyProfileResource;
use App\Modules\OrganizationManagement\Services\OrganizationStructureService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyProfileController
{
    public function __construct(private readonly OrganizationStructureService $organizationStructureService) {}

    public function show(Request $request): JsonResponse
    {
        $payload = ApiResponse::success(
            'Company profile loaded successfully.',
            new CompanyProfileResource($request->user()->company),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateCompanyProfileRequest $request): JsonResponse
    {
        $company = $this->organizationStructureService->updateCompanyProfile(
            $request->user(),
            $request->user()->company,
            $request->validated(),
        );

        $payload = ApiResponse::success(
            'Company profile updated successfully.',
            new CompanyProfileResource($company),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
