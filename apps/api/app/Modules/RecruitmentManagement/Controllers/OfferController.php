<?php

namespace App\Modules\RecruitmentManagement\Controllers;

use App\Modules\Platform\Shared\Http\ApiResponse;
use App\Modules\RecruitmentManagement\Requests\ListOffersRequest;
use App\Modules\RecruitmentManagement\Requests\StoreOfferRequest;
use App\Modules\RecruitmentManagement\Requests\UpdateOfferRequest;
use App\Modules\RecruitmentManagement\Resources\OfferResource;
use App\Modules\RecruitmentManagement\Services\OfferService;
use Illuminate\Http\JsonResponse;

class OfferController
{
    public function __construct(private readonly OfferService $offerService) {}

    public function index(ListOffersRequest $request): JsonResponse
    {
        $offers = $this->offerService->search($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Offers loaded successfully.',
            [
                'items' => OfferResource::collection($offers->items()),
                'meta' => [
                    'page' => $offers->currentPage(),
                    'per_page' => $offers->perPage(),
                    'total' => $offers->total(),
                    'last_page' => $offers->lastPage(),
                ],
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreOfferRequest $request): JsonResponse
    {
        $offer = $this->offerService->create($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Offer created successfully.',
            new OfferResource($offer),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(int $offerId): JsonResponse
    {
        $offer = $this->offerService->findForView(request()->user(), $offerId);

        $payload = ApiResponse::success(
            'Offer loaded successfully.',
            new OfferResource($offer),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateOfferRequest $request, int $offerId): JsonResponse
    {
        $offer = $this->offerService->update($request->user(), $offerId, $request->validated());

        $payload = ApiResponse::success(
            'Offer updated successfully.',
            new OfferResource($offer),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
