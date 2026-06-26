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

        $navigation = array_map(
            fn (array $item): array => $this->shapeItem($user, $item),
            $this->normalizeItems(config('ui_visibility.navigation')),
        );

        $actionGroups = array_map(function (array $group) use ($user): array {
            $actions = array_map(
                fn (array $action): array => $this->shapeItem($user, $action),
                $this->normalizeItems($group['actions'] ?? []),
            );

            return [
                'id' => (string) $group['id'],
                'title' => (string) $group['title'],
                'description' => isset($group['description']) ? (string) $group['description'] : null,
                'actions' => $actions,
                'visible_count' => $this->countVisible($actions, true),
                'hidden_count' => $this->countVisible($actions, false),
            ];
        }, $this->normalizeItems(config('ui_visibility.action_groups')));

        $payload = ApiResponse::success(
            'UI visibility contract loaded successfully.',
            [
                'navigation' => $navigation,
                'action_groups' => $actionGroups,
                'meta' => [
                    'visible_navigation_count' => $this->countVisible($navigation, true),
                    'hidden_navigation_count' => $this->countVisible($navigation, false),
                    'backend_enforcement_note' => 'This contract is advisory for rendering only. Backend permission checks remain the source of truth.',
                ],
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array{
     *   id: string,
     *   label: string,
     *   href: string|null,
     *   description: string|null,
     *   required_permissions: list<string>,
     *   match: string,
     *   visible: bool
     * }
     */
    private function shapeItem(object $user, array $item): array
    {
        $requiredPermissions = $this->normalizePermissionList($item['required_permissions'] ?? []);
        $match = isset($item['match']) ? (string) $item['match'] : 'all';

        $visible = $requiredPermissions === []
            || ($match === 'any'
                ? $this->hasAnyPermission($user, $requiredPermissions)
                : $this->hasAllPermissions($user, $requiredPermissions));

        return [
            'id' => (string) $item['id'],
            'label' => (string) $item['label'],
            'href' => isset($item['href']) ? (string) $item['href'] : null,
            'description' => isset($item['description']) ? (string) $item['description'] : null,
            'required_permissions' => $requiredPermissions,
            'match' => $match,
            'visible' => $visible,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function normalizeItems(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        $normalized = [];
        foreach ($items as $item) {
            if (is_array($item)) {
                $normalized[] = $item;
            }
        }

        return $normalized;
    }

    /**
     * @return list<string>
     */
    private function normalizePermissionList(mixed $permissions): array
    {
        if (! is_array($permissions)) {
            return [];
        }

        $normalized = [];
        foreach ($permissions as $permission) {
            $value = trim((string) $permission);
            if ($value !== '') {
                $normalized[] = $value;
            }
        }

        return array_values(array_unique($normalized));
    }

    /**
     * @param  list<string>  $permissions
     */
    private function hasAnyPermission(object $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<string>  $permissions
     */
    private function hasAllPermissions(object $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (! $user->can($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<array{visible: bool}>  $items
     */
    private function countVisible(array $items, bool $visible): int
    {
        $count = 0;

        foreach ($items as $item) {
            if ($item['visible'] === $visible) {
                $count++;
            }
        }

        return $count;
    }
}
