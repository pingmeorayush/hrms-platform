<?php

namespace App\Modules\ReportingAnalytics\Requests\Concerns;

use App\Models\User;

trait AuthorizesReportingRequests
{
    protected function reportingUser(): ?User
    {
        $user = $this->user();

        return $user instanceof User ? $user : null;
    }

    protected function canViewReportingWorkspace(): bool
    {
        $user = $this->reportingUser();

        return $user?->canAny(['reporting.view', 'reporting.manage', 'reporting.certify']) ?? false;
    }

    protected function canManageReportingCatalog(): bool
    {
        $user = $this->reportingUser();

        if (! $user) {
            return false;
        }

        $requestedCertificationStatus = $this->input('certification_status');

        if (is_string($requestedCertificationStatus) && $requestedCertificationStatus !== '' && $requestedCertificationStatus !== 'draft') {
            return $user->can('reporting.certify');
        }

        return $user->canAny(['reporting.manage', 'reporting.certify']);
    }
}
