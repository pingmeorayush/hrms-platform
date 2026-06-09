<?php

namespace App\Modules\AssetManagement\Controllers;

use App\Models\AssetCategory;
use App\Modules\AssetManagement\Requests\ListAssetCategoryRequest;
use App\Modules\AssetManagement\Requests\StoreAssetCategoryRequest;
use App\Modules\AssetManagement\Requests\UpdateAssetCategoryRequest;
use App\Modules\AssetManagement\Resources\AssetCategoryResource;
use App\Modules\AssetManagement\Services\AssetCatalogService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class AssetCategoryController
{
    public function __construct(private readonly AssetCatalogService $assetCatalogService) {}

    public function index(ListAssetCategoryRequest $request): JsonResponse
    {
        $categories = $this->assetCatalogService->listCategories($request->validated(), $request->user());

        $payload = ApiResponse::success(
            'Asset categories loaded successfully.',
            AssetCategoryResource::collection($categories),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreAssetCategoryRequest $request): JsonResponse
    {
        $category = $this->assetCatalogService->createCategory($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Asset category created successfully.',
            new AssetCategoryResource($category),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateAssetCategoryRequest $request, int $assetCategoryId): JsonResponse
    {
        $category = AssetCategory::query()->findOrFail($assetCategoryId);
        $category = $this->assetCatalogService->updateCategory($request->user(), $category, $request->validated());

        $payload = ApiResponse::success(
            'Asset category updated successfully.',
            new AssetCategoryResource($category),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
