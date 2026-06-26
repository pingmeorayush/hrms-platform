<?php

namespace App\Modules\Platform\Audit\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Modules\Platform\Tenancy\TenantContext;

class AuditLogger
{
    /**
     * @param  array<string|int, mixed>  $metadata
     */
    public function record(
        string $eventType,
        ?User $actor = null,
        array $metadata = [],
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?string $entityType = null,
        ?string $entityId = null,
    ): AuditLog {
        $tenantContext = app(TenantContext::class);

        return AuditLog::withoutGlobalScopes()->create([
            'company_id' => $actor->company_id ?? $tenantContext->companyId,
            'user_id' => $actor?->id,
            'event_type' => $eventType,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
