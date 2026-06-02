<?php

namespace App\Modules\Platform\Workflow\Listeners;

use App\Modules\Platform\Notifications\Services\NotificationService;
use App\Modules\Platform\Workflow\Events\WorkflowTaskAssigned;

class SendWorkflowTaskAssignedNotification
{
    public function __construct(private readonly NotificationService $notificationService) {}

    public function handle(WorkflowTaskAssigned $event): void
    {
        $task = $event->task->loadMissing('instance.definition', 'assignee');

        if (! $task->assignee) {
            return;
        }

        $this->notificationService->sendTemplate(
            'workflow.task_assigned.in_app',
            $task->assignee,
            [
                'workflow_name' => $task->instance->definition->name,
                'reference_type' => $task->instance->reference_type,
                'reference_id' => $task->instance->reference_id,
                'approval_link' => '/tasks/'.$task->id,
            ],
            [
                'deep_link' => '/tasks/'.$task->id,
            ],
        );
    }
}
