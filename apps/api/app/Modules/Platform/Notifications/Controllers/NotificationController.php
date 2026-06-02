<?php

namespace App\Modules\Platform\Notifications\Controllers;

use App\Models\NotificationRecord;
use App\Models\User;
use App\Modules\Platform\Notifications\Requests\StoreNotificationRequest;
use App\Modules\Platform\Notifications\Resources\NotificationResource;
use App\Modules\Platform\Notifications\Services\NotificationService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController
{
    public function __construct(private readonly NotificationService $notificationService) {}

    public function index(Request $request): JsonResponse
    {
        $notifications = NotificationRecord::query()
            ->where('user_id', $request->user()->id)
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->string('status')->toString()),
            )
            ->latest()
            ->paginate((int) min($request->integer('per_page', 25), 100));

        $payload = ApiResponse::success(
            'Notifications loaded successfully.',
            [
                'items' => NotificationResource::collection($notifications->getCollection()),
                'meta' => [
                    'page' => $notifications->currentPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                    'last_page' => $notifications->lastPage(),
                    'unread_count' => NotificationRecord::query()
                        ->where('user_id', $request->user()->id)
                        ->where('status', 'unread')
                        ->count(),
                ],
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreNotificationRequest $request): JsonResponse
    {
        $recipient = User::query()->findOrFail($request->integer('user_id'));

        $notification = $request->filled('template_key')
            ? $this->notificationService->sendTemplate(
                $request->string('template_key')->toString(),
                $recipient,
                $request->input('variables', []),
                [
                    'type' => $request->input('type'),
                    'channel' => $request->input('channel', 'in_app'),
                    'priority' => $request->input('priority', 'normal'),
                    'deep_link' => $request->input('deep_link'),
                ],
            )
            : $this->notificationService->sendDirect(
                $recipient,
                $request->validated(),
                $request->user(),
            );

        $payload = ApiResponse::success(
            'Notification created successfully.',
            new NotificationResource($notification),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function markRead(Request $request, NotificationRecord $notification): JsonResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 404);

        $notification = $this->notificationService->markRead($notification, $request->user());

        $payload = ApiResponse::success(
            'Notification marked as read.',
            new NotificationResource($notification),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function retry(Request $request, NotificationRecord $notification): JsonResponse
    {
        $notification = $this->notificationService->retry($notification, $request->user());

        $payload = ApiResponse::success(
            'Notification retry processed successfully.',
            new NotificationResource($notification),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
