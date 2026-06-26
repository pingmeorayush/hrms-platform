<?php

namespace App\Providers;

use App\Modules\AttendanceManagement\Listeners\SyncAttendanceCorrectionWorkflowState;
use App\Modules\EmployeeManagement\Listeners\SyncEmployeeLifecycleTaskWorkflowState;
use App\Modules\LeaveManagement\Listeners\SyncLeaveRequestWorkflowState;
use App\Modules\Platform\Workflow\Events\WorkflowInstanceTransitioned;
use App\Modules\Platform\Workflow\Events\WorkflowTaskAssigned;
use App\Modules\Platform\Workflow\Listeners\SendWorkflowInstanceTransitionNotification;
use App\Modules\Platform\Workflow\Listeners\SendWorkflowTaskAssignedNotification;
use App\Modules\RecruitmentManagement\Listeners\SyncJobRequisitionWorkflowState;
use App\Modules\RecruitmentManagement\Listeners\SyncOfferWorkflowState;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        WorkflowTaskAssigned::class => [
            SendWorkflowTaskAssignedNotification::class,
        ],
        WorkflowInstanceTransitioned::class => [
            SendWorkflowInstanceTransitionNotification::class,
            SyncAttendanceCorrectionWorkflowState::class,
            SyncEmployeeLifecycleTaskWorkflowState::class,
            SyncLeaveRequestWorkflowState::class,
            SyncJobRequisitionWorkflowState::class,
            SyncOfferWorkflowState::class,
        ],
    ];
}
