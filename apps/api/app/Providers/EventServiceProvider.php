<?php

namespace App\Providers;

use App\Modules\AttendanceManagement\Listeners\SyncAttendanceCorrectionWorkflowState;
use App\Modules\LeaveManagement\Listeners\SyncLeaveRequestWorkflowState;
use App\Modules\Platform\Workflow\Events\WorkflowInstanceTransitioned;
use App\Modules\Platform\Workflow\Events\WorkflowTaskAssigned;
use App\Modules\Platform\Workflow\Listeners\SendWorkflowInstanceTransitionNotification;
use App\Modules\Platform\Workflow\Listeners\SendWorkflowTaskAssignedNotification;
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
            SyncLeaveRequestWorkflowState::class,
        ],
    ];
}
