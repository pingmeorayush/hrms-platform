<?php

namespace App\Modules\Platform\Admin\Controllers;

use App\Modules\Platform\Admin\Resources\PermissionResource;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;

class PermissionController
{
    public function index(): JsonResponse
    {
        $permissions = Permission::query()
            ->orderBy('name')
            ->get();

        $payload = ApiResponse::success(
            'Permissions loaded successfully.',
            PermissionResource::collection($permissions),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
