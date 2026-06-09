<?php

namespace App\Modules\DocumentManagement\Controllers;

use App\Models\DocumentCategory;
use App\Modules\DocumentManagement\Requests\ListDocumentCategoryRequest;
use App\Modules\DocumentManagement\Requests\StoreDocumentCategoryRequest;
use App\Modules\DocumentManagement\Requests\UpdateDocumentCategoryRequest;
use App\Modules\DocumentManagement\Resources\DocumentCategoryResource;
use App\Modules\DocumentManagement\Services\DocumentCategoryService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class DocumentCategoryController
{
    public function __construct(private readonly DocumentCategoryService $documentCategoryService) {}

    public function index(ListDocumentCategoryRequest $request): JsonResponse
    {
        $categories = $this->documentCategoryService->listCategories($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Document categories loaded successfully.',
            DocumentCategoryResource::collection($categories),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreDocumentCategoryRequest $request): JsonResponse
    {
        $category = $this->documentCategoryService->create($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Document category created successfully.',
            new DocumentCategoryResource($category),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateDocumentCategoryRequest $request, int $documentCategoryId): JsonResponse
    {
        $category = DocumentCategory::query()->findOrFail($documentCategoryId);
        $category = $this->documentCategoryService->update($request->user(), $category, $request->validated());

        $payload = ApiResponse::success(
            'Document category updated successfully.',
            new DocumentCategoryResource($category),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
