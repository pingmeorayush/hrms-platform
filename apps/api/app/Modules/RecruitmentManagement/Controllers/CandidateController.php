<?php

namespace App\Modules\RecruitmentManagement\Controllers;

use App\Modules\Platform\Shared\Http\ApiResponse;
use App\Modules\RecruitmentManagement\Requests\ListCandidatesRequest;
use App\Modules\RecruitmentManagement\Requests\StoreCandidateRequest;
use App\Modules\RecruitmentManagement\Requests\StoreCandidateResumeRequest;
use App\Modules\RecruitmentManagement\Requests\StoreCandidateStageTransitionRequest;
use App\Modules\RecruitmentManagement\Requests\UpdateCandidateRequest;
use App\Modules\RecruitmentManagement\Resources\CandidateResource;
use App\Modules\RecruitmentManagement\Resources\CandidateResumeResource;
use App\Modules\RecruitmentManagement\Services\CandidateService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CandidateController
{
    public function __construct(private readonly CandidateService $candidateService) {}

    public function index(ListCandidatesRequest $request): JsonResponse
    {
        $candidates = $this->candidateService->search($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Candidates loaded successfully.',
            [
                'items' => CandidateResource::collection($candidates->items()),
                'meta' => [
                    'page' => $candidates->currentPage(),
                    'per_page' => $candidates->perPage(),
                    'total' => $candidates->total(),
                    'last_page' => $candidates->lastPage(),
                ],
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreCandidateRequest $request): JsonResponse
    {
        $candidate = $this->candidateService->create($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Candidate created successfully.',
            new CandidateResource($candidate),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(int $candidateId): JsonResponse
    {
        $candidate = $this->candidateService->findForView(request()->user(), $candidateId);

        $payload = ApiResponse::success(
            'Candidate loaded successfully.',
            new CandidateResource($candidate),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateCandidateRequest $request, int $candidateId): JsonResponse
    {
        $candidate = $this->candidateService->update($request->user(), $candidateId, $request->validated());

        $payload = ApiResponse::success(
            'Candidate updated successfully.',
            new CandidateResource($candidate),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function storeResume(StoreCandidateResumeRequest $request, int $candidateId): JsonResponse
    {
        $resume = $this->candidateService->uploadResume(
            $request->user(),
            $candidateId,
            $request->file('file'),
            $request->validated(),
        );

        $payload = ApiResponse::success(
            'Candidate resume uploaded successfully.',
            new CandidateResumeResource($resume),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function downloadResume(int $candidateId, int $candidateResumeId): StreamedResponse
    {
        return $this->candidateService->downloadResume(request()->user(), $candidateId, $candidateResumeId);
    }

    public function transitionStage(StoreCandidateStageTransitionRequest $request, int $candidateId): JsonResponse
    {
        $candidate = $this->candidateService->transitionStage($request->user(), $candidateId, $request->validated());

        $payload = ApiResponse::success(
            'Candidate stage updated successfully.',
            new CandidateResource($candidate),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
