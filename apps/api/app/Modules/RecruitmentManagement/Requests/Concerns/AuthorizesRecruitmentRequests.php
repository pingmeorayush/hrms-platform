<?php

namespace App\Modules\RecruitmentManagement\Requests\Concerns;

use App\Models\Interview;
use App\Models\User;

trait AuthorizesRecruitmentRequests
{
    protected function recruitmentUser(): ?User
    {
        $user = $this->user();

        return $user instanceof User ? $user : null;
    }

    protected function canAccessRecruitmentWorkspace(): bool
    {
        $user = $this->recruitmentUser();

        return $user?->canAny([
            'recruitment.view',
            'recruitment.manage',
            'recruitment.approve',
            'recruitment.interview',
        ]) ?? false;
    }

    protected function canManageRecruitment(): bool
    {
        return $this->recruitmentUser()?->can('recruitment.manage') ?? false;
    }

    protected function canApproveRecruitment(): bool
    {
        $user = $this->recruitmentUser();

        return $user?->canAny(['recruitment.manage', 'recruitment.approve']) ?? false;
    }

    protected function canAccessEmployeeWorkspace(): bool
    {
        $user = $this->recruitmentUser();

        return $user?->canAny(['employee.view', 'employee.manage']) ?? false;
    }

    protected function canManageEmployees(): bool
    {
        return $this->recruitmentUser()?->can('employee.manage') ?? false;
    }

    protected function canUpdateRequisitionAction(): bool
    {
        $action = $this->input('action');

        if (! is_string($action) || $action === '') {
            return $this->canManageRecruitment();
        }

        return in_array($action, ['approve', 'reject', 'request_changes'], true)
            ? $this->canApproveRecruitment()
            : $this->canManageRecruitment();
    }

    protected function canUpdateOfferAction(): bool
    {
        $action = $this->input('action');

        if (! is_string($action) || $action === '') {
            return $this->canManageRecruitment();
        }

        return in_array($action, ['approve', 'reject', 'request_changes'], true)
            ? $this->canApproveRecruitment()
            : $this->canManageRecruitment();
    }

    protected function canSubmitInterviewFeedback(): bool
    {
        if ($this->canManageRecruitment()) {
            return true;
        }

        $user = $this->recruitmentUser();
        $interviewId = $this->route('interviewId');

        if (! $user || ! is_numeric($interviewId)) {
            return false;
        }

        return Interview::query()
            ->where('company_id', $user->company_id)
            ->whereKey((int) $interviewId)
            ->where('interviewer_user_id', $user->id)
            ->exists();
    }
}
