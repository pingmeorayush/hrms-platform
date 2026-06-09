<?php

namespace App\Modules\AssetManagement\Controllers;

use App\Models\Asset;
use App\Modules\AssetManagement\Requests\AssignAssetRequest;
use App\Modules\AssetManagement\Requests\IssueAssetRequest;
use App\Modules\AssetManagement\Requests\ListAssetRequest;
use App\Modules\AssetManagement\Requests\ReturnAssetRequest;
use App\Modules\AssetManagement\Requests\StoreAssetRequest;
use App\Modules\AssetManagement\Resources\AssetResource;
use App\Modules\AssetManagement\Services\AssetCatalogService;
use App\Modules\AssetManagement\Services\AssetLifecycleService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class AssetController
{
    public function __construct(
        private readonly AssetCatalogService $assetCatalogService,
        private readonly AssetLifecycleService $assetLifecycleService,
    ) {}

    public function index(ListAssetRequest $request): JsonResponse
    {
        $assets = $this->assetCatalogService->listAssets($request->validated(), $request->user());

        $payload = ApiResponse::success(
            'Assets loaded successfully.',
            AssetResource::collection($assets),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreAssetRequest $request): JsonResponse
    {
        $asset = $this->assetCatalogService->createAsset($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Asset created successfully.',
            new AssetResource($asset),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(int $assetId): JsonResponse
    {
        $asset = $this->assetCatalogService->showAsset($assetId);

        $payload = ApiResponse::success(
            'Asset loaded successfully.',
            new AssetResource($asset),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function assign(AssignAssetRequest $request, int $assetId): JsonResponse
    {
        $asset = Asset::query()->findOrFail($assetId);
        $asset = $this->assetLifecycleService->assignAsset($request->user(), $asset, $request->validated());

        $payload = ApiResponse::success(
            'Asset assigned successfully.',
            new AssetResource($asset),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function issue(IssueAssetRequest $request, int $assetId): JsonResponse
    {
        $asset = Asset::query()->findOrFail($assetId);
        $asset = $this->assetLifecycleService->issueAsset($request->user(), $asset, $request->validated());

        $payload = ApiResponse::success(
            'Asset issued successfully.',
            new AssetResource($asset),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function return(ReturnAssetRequest $request, int $assetId): JsonResponse
    {
        $asset = Asset::query()->findOrFail($assetId);
        $asset = $this->assetLifecycleService->returnAsset($request->user(), $asset, $request->validated());

        $payload = ApiResponse::success(
            'Asset returned successfully.',
            new AssetResource($asset),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
