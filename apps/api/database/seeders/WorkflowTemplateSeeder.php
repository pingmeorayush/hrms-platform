<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowStage;
use App\Models\WorkflowVersion;
use Illuminate\Database\Seeder;

class WorkflowTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->where('slug', 'phoenix-demo')->first();
        $admin = User::withoutGlobalScopes()->where('email', 'admin@phoenixhrms.test')->first();

        if (! $company || ! $admin) {
            return;
        }

        $templates = [
            [
                'key' => 'leave-approval',
                'name' => 'Leave Approval Workflow',
                'module' => 'leave',
                'description' => 'Sequential leave approval through manager and HR.',
                'stages' => [
                    [
                        'key' => 'manager_approval',
                        'name' => 'Manager Approval',
                        'sequence' => 1,
                        'approver_type' => 'employee_manager',
                        'approver_value' => 'employee_manager',
                        'available_actions' => ['approve', 'reject', 'request_changes'],
                        'sla_hours' => 24,
                    ],
                    [
                        'key' => 'hr_approval',
                        'name' => 'HR Approval',
                        'sequence' => 2,
                        'approver_type' => 'role',
                        'approver_value' => 'hr.admin',
                        'available_actions' => ['approve', 'reject'],
                        'sla_hours' => 24,
                    ],
                ],
            ],
            [
                'key' => 'employee-approval',
                'name' => 'Employee Approval Workflow',
                'module' => 'employee',
                'description' => 'Sequential employee approval through HR and tenant administration.',
                'stages' => [
                    [
                        'key' => 'hr_review',
                        'name' => 'HR Review',
                        'sequence' => 1,
                        'approver_type' => 'role',
                        'approver_value' => 'hr.admin',
                        'available_actions' => ['approve', 'reject', 'request_changes'],
                        'sla_hours' => 24,
                    ],
                    [
                        'key' => 'tenant_admin_approval',
                        'name' => 'Tenant Admin Approval',
                        'sequence' => 2,
                        'approver_type' => 'role',
                        'approver_value' => 'tenant.admin',
                        'available_actions' => ['approve', 'reject'],
                        'sla_hours' => 24,
                    ],
                ],
            ],
            [
                'key' => 'attendance-correction-approval',
                'name' => 'Attendance Correction Approval Workflow',
                'module' => 'attendance',
                'description' => 'Sequential attendance correction approval through the employee manager and HR.',
                'stages' => [
                    [
                        'key' => 'manager_review',
                        'name' => 'Manager Review',
                        'sequence' => 1,
                        'approver_type' => 'employee_manager',
                        'approver_value' => 'employee_manager',
                        'available_actions' => ['approve', 'reject', 'request_changes'],
                        'sla_hours' => 24,
                    ],
                    [
                        'key' => 'hr_review',
                        'name' => 'HR Review',
                        'sequence' => 2,
                        'approver_type' => 'role',
                        'approver_value' => 'hr.admin',
                        'available_actions' => ['approve', 'reject'],
                        'sla_hours' => 24,
                    ],
                ],
            ],
            [
                'key' => 'employee-offboarding-clearance',
                'name' => 'Employee Offboarding Clearance Workflow',
                'module' => 'employee',
                'description' => 'Sequential offboarding clearance through the employee manager and HR.',
                'stages' => [
                    [
                        'key' => 'manager_clearance',
                        'name' => 'Manager Clearance',
                        'sequence' => 1,
                        'approver_type' => 'employee_manager',
                        'approver_value' => 'employee_manager',
                        'available_actions' => ['approve', 'reject', 'request_changes'],
                        'sla_hours' => 24,
                    ],
                    [
                        'key' => 'hr_clearance',
                        'name' => 'HR Clearance',
                        'sequence' => 2,
                        'approver_type' => 'role',
                        'approver_value' => 'hr.admin',
                        'available_actions' => ['approve', 'reject'],
                        'sla_hours' => 24,
                    ],
                ],
            ],
            [
                'key' => 'recruitment-requisition-approval',
                'name' => 'Recruitment Requisition Approval Workflow',
                'module' => 'recruitment',
                'description' => 'Sequential requisition approval through the assigned hiring manager and HR.',
                'stages' => [
                    [
                        'key' => 'hiring_manager_review',
                        'name' => 'Hiring Manager Review',
                        'sequence' => 1,
                        'approver_type' => 'payload_user',
                        'approver_value' => 'hiring_manager_user_id',
                        'available_actions' => ['approve', 'reject', 'request_changes'],
                        'sla_hours' => 48,
                    ],
                    [
                        'key' => 'hr_review',
                        'name' => 'HR Review',
                        'sequence' => 2,
                        'approver_type' => 'role',
                        'approver_value' => 'hr.admin',
                        'available_actions' => ['approve', 'reject', 'request_changes'],
                        'sla_hours' => 48,
                    ],
                ],
            ],
            [
                'key' => 'recruitment-offer-approval',
                'name' => 'Recruitment Offer Approval Workflow',
                'module' => 'recruitment',
                'description' => 'Sequential offer approval through the assigned hiring manager and HR.',
                'stages' => [
                    [
                        'key' => 'hiring_manager_review',
                        'name' => 'Hiring Manager Review',
                        'sequence' => 1,
                        'approver_type' => 'payload_user',
                        'approver_value' => 'hiring_manager_user_id',
                        'available_actions' => ['approve', 'reject', 'request_changes'],
                        'sla_hours' => 48,
                    ],
                    [
                        'key' => 'hr_review',
                        'name' => 'HR Review',
                        'sequence' => 2,
                        'approver_type' => 'role',
                        'approver_value' => 'hr.admin',
                        'available_actions' => ['approve', 'reject', 'request_changes'],
                        'sla_hours' => 48,
                    ],
                ],
            ],
        ];

        foreach ($templates as $template) {
            $definition = WorkflowDefinition::withoutGlobalScopes()->updateOrCreate(
                [
                    'company_id' => $company->id,
                    'key' => $template['key'],
                ],
                [
                    'name' => $template['name'],
                    'module' => $template['module'],
                    'description' => $template['description'],
                    'is_template' => true,
                    'status' => 'published',
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                ],
            );

            $version = WorkflowVersion::query()->updateOrCreate(
                [
                    'workflow_definition_id' => $definition->id,
                    'version' => 1,
                ],
                [
                    'status' => 'published',
                    'definition' => [
                        'module' => $template['module'],
                        'stages' => $template['stages'],
                    ],
                    'created_by' => $admin->id,
                    'published_at' => now(),
                ],
            );

            foreach ($template['stages'] as $stageData) {
                WorkflowStage::query()->updateOrCreate(
                    [
                        'workflow_version_id' => $version->id,
                        'key' => $stageData['key'],
                    ],
                    $stageData,
                );
            }

            $definition->forceFill([
                'active_version_id' => $version->id,
            ])->save();
        }
    }
}
