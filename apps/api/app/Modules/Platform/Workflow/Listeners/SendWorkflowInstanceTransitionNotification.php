<?php

namespace App\Modules\Platform\Workflow\Listeners;

use App\Modules\Platform\Notifications\Services\NotificationService;
use App\Modules\Platform\Workflow\Events\WorkflowInstanceTransitioned;

class SendWorkflowInstanceTransitionNotification
{
    public function __construct(private readonly NotificationService $notificationService) {}

    public function handle(WorkflowInstanceTransitioned $event): void
    {
        $instance = $event->instance->loadMissing('definition', 'starter');

        if (! $instance->starter) {
            return;
        }

        if ($event->transition === 'completed') {
            $this->notificationService->sendTemplate(
                'workflow.completed.in_app',
                $instance->starter,
                [
                    'workflow_name' => $instance->definition->name,
                    'reference_type' => $instance->reference_type,
                    'reference_id' => $instance->reference_id,
                ],
                [
                    'deep_link' => '/workflows/'.$instance->id,
                ],
            );

            return;
        }

        if (! in_array($event->transition, ['reject', 'request_changes'], true)) {
            return;
        }

        $this->notificationService->sendTemplate(
            'workflow.rejected.in_app',
            $instance->starter,
            [
                'workflow_name' => $instance->definition->name,
                'reference_type' => $instance->reference_type,
                'reference_id' => $instance->reference_id,
                'decision' => str_replace('_', ' ', $event->transition),
            ],
            [
                'deep_link' => '/workflows/'.$instance->id,
                'priority' => $event->transition === 'reject' ? 'high' : 'normal',
            ],
        );
    }
}
