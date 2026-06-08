<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'key' => 'workflow.task_assigned.in_app',
                'name' => 'Workflow Task Assigned',
                'category' => 'workflow',
                'channel' => 'in_app',
                'subject' => null,
                'content' => 'A {{workflow_name}} approval task has been assigned to you for {{reference_type}} {{reference_id}}.',
                'variables' => ['workflow_name', 'reference_type', 'reference_id', 'approval_link', 'company_name'],
            ],
            [
                'key' => 'workflow.completed.in_app',
                'name' => 'Workflow Completed',
                'category' => 'workflow',
                'channel' => 'in_app',
                'subject' => null,
                'content' => 'Your {{workflow_name}} workflow for {{reference_type}} {{reference_id}} has been approved and completed.',
                'variables' => ['workflow_name', 'reference_type', 'reference_id', 'company_name'],
            ],
            [
                'key' => 'workflow.rejected.in_app',
                'name' => 'Workflow Rejected',
                'category' => 'workflow',
                'channel' => 'in_app',
                'subject' => null,
                'content' => 'Your {{workflow_name}} workflow for {{reference_type}} {{reference_id}} was {{decision}}.',
                'variables' => ['workflow_name', 'reference_type', 'reference_id', 'decision', 'company_name'],
            ],
            [
                'key' => 'system.announcement.in_app',
                'name' => 'System Announcement',
                'category' => 'system',
                'channel' => 'in_app',
                'subject' => null,
                'content' => '{{message}}',
                'variables' => ['message', 'company_name'],
            ],
        ];

        foreach ($templates as $template) {
            NotificationTemplate::query()->updateOrCreate(
                [
                    'company_id' => null,
                    'key' => $template['key'],
                    'channel' => $template['channel'],
                ],
                $template + ['status' => 'active'],
            );
        }
    }
}
