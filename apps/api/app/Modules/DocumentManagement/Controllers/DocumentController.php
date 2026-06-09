<?php

namespace App\Modules\DocumentManagement\Controllers;

use App\Models\Document;
use App\Modules\DocumentManagement\Requests\ListDocumentRequest;
use App\Modules\DocumentManagement\Requests\StoreDocumentRequest;
use App\Modules\DocumentManagement\Resources\DocumentResource;
use App\Modules\DocumentManagement\Services\DocumentRepositoryService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController
{
    public function __construct(private readonly DocumentRepositoryService $documentRepositoryService) {}

    public function index(ListDocumentRequest $request): JsonResponse
    {
        $documents = $this->documentRepositoryService->listDocuments($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Documents loaded successfully.',
            DocumentResource::collection($documents),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $document = $this->documentRepositoryService->create(
            $request->user(),
            $request->file('file'),
            $request->validated(),
        );

        $payload = ApiResponse::success(
            'Document uploaded successfully.',
            new DocumentResource($document),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(Request $request, int $documentId): JsonResponse
    {
        $document = Document::query()->findOrFail($documentId);
        $document = $this->documentRepositoryService->showDocument($document, $request->user());

        $payload = ApiResponse::success(
            'Document loaded successfully.',
            new DocumentResource($document),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function download(Request $request, int $documentId): StreamedResponse
    {
        $document = Document::query()->findOrFail($documentId);

        return $this->documentRepositoryService->download($document, $request->user());
    }
}
