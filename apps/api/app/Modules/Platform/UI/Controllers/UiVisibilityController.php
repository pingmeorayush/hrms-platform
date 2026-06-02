<?php

namespace App\Modules\Platform\UI\Controllers;

use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UiVisibilityController
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        $navigation = collect(config('ui_visibility.navigation'))
            ->map(fn (array $item): array => $this->shapeItem($user, $item))
            ->values();

        $actionGroups = collect(config('ui_visibility.action_groups'))
            ->map(function (array $group) use ($user): array {
                $actions = collect($group['actions'])
                    ->map(fn (array $action): array => $this->shapeItem($user, $action))
                    ->values();

                return [
                    'id' => $group['id'],
                    'title' => $group['title'],
                    'description' => $group['description'],
                    'actions' => $actions->all(),
                    'visible_count' => $actions->where('visible', true)->count(),
                    'hidden_count' => $actions->where('visible', false)->count(),
                ];
            })
            ->values();

        $payload = ApiResponse::success(
            'UI visibility contract loaded successfully.',
            [
                'navigation' => $navigation->all(),
                'action_groups' => $actionGroups->all(),
                'meta' => [
                    'visible_navigation_count' => $navigation->where('visible', true)->count(),
                    'hidden_navigation_count' => $navigation->where('visible', false)->count(),
                    'backend_enforcement_note' => 'This contract is advisory for rendering only. Backend permission checks remain the source of truth.',
                ],
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }

    private function shapeItem(object $user, array $item): array
    {
        $requiredPermissions = $item['required_permissions'] ?? [];
        $match = $item['match'] ?? 'all';

        $visible = $requiredPermissions === []
            || ($match === 'any'
                ? collect($requiredPermissions)->contains(fn (string $permission): bool => $user->can($permission))
                : collect($requiredPermissions)->every(fn (string $permission): bool => $user->can($permission)));

        return [
            'id' => $item['id'],
            'label' => $item['label'],
            'href' => $item['href'] ?? null,
            'description' => $item['description'] ?? null,
            'required_permissions' => $requiredPermissions,
            'match' => $match,
            'visible' => $visible,
        ];
    }
}
