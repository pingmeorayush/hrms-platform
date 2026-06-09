<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'name',
    'lifecycle_type',
    'title',
    'category',
    'task_type',
    'assignee_type',
    'requires_approval',
    'approval_workflow_key',
    'due_offset_days',
    'sort_order',
    'notes',
    'is_active',
    'created_by_user_id',
    'updated_by_user_id',
])]
class EmployeeLifecycleTaskTemplate extends Model
{
    use BelongsToCompany;

    public function tasks(): HasMany
    {
        return $this->hasMany(EmployeeOnboardingTask::class, 'template_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    protected function casts(): array
    {
        return [
            'requires_approval' => 'boolean',
            'due_offset_days' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
