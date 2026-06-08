<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addTenantCompositeUniqueIndexes();
        $this->addWorkflowDefinitionActiveVersionIndex();
        $this->addAuditLogHistoryIndex();
        $this->addNotificationTemplateFallbackUniqueness();

        if ($this->supportsAlterTableForeignKeys()) {
            $this->addTenantCompositeForeignKeys();
            $this->addWorkflowDefinitionActiveVersionForeignKey();
        }
    }

    public function down(): void
    {
        if ($this->supportsAlterTableForeignKeys()) {
            $this->dropWorkflowDefinitionActiveVersionForeignKey();
            $this->dropTenantCompositeForeignKeys();
        }

        $this->dropNotificationTemplateFallbackUniqueness();
        $this->dropAuditLogHistoryIndex();
        $this->dropWorkflowDefinitionActiveVersionIndex();
        $this->dropTenantCompositeUniqueIndexes();
    }

    private function addTenantCompositeUniqueIndexes(): void
    {
        foreach ($this->tenantCompositeUniqueIndexes() as $index) {
            Schema::table($index['table'], function (Blueprint $table) use ($index): void {
                $table->unique($index['columns'], $index['name']);
            });
        }
    }

    private function dropTenantCompositeUniqueIndexes(): void
    {
        foreach (array_reverse($this->tenantCompositeUniqueIndexes()) as $index) {
            Schema::table($index['table'], function (Blueprint $table) use ($index): void {
                $table->dropUnique($index['name']);
            });
        }
    }

    private function addTenantCompositeForeignKeys(): void
    {
        foreach ($this->tenantCompositeForeignKeys() as $definition) {
            Schema::table($definition['table'], function (Blueprint $table) use ($definition): void {
                $foreign = $table->foreign($definition['columns'], $definition['name'])
                    ->references($definition['references'])
                    ->on($definition['on'])
                    ->cascadeOnUpdate();

                match ($definition['onDelete']) {
                    'cascade' => $foreign->cascadeOnDelete(),
                    'restrict' => $foreign->restrictOnDelete(),
                    'set null' => $foreign->nullOnDelete(),
                    default => null,
                };
            });
        }
    }

    private function dropTenantCompositeForeignKeys(): void
    {
        foreach (array_reverse($this->tenantCompositeForeignKeys()) as $definition) {
            Schema::table($definition['table'], function (Blueprint $table) use ($definition): void {
                $table->dropForeign($definition['name']);
            });
        }
    }

    private function addWorkflowDefinitionActiveVersionIndex(): void
    {
        Schema::table('workflow_definitions', function (Blueprint $table): void {
            $table->index('active_version_id', 'workflow_definitions_active_version_idx');
        });
    }

    private function dropWorkflowDefinitionActiveVersionIndex(): void
    {
        Schema::table('workflow_definitions', function (Blueprint $table): void {
            $table->dropIndex('workflow_definitions_active_version_idx');
        });
    }

    private function addWorkflowDefinitionActiveVersionForeignKey(): void
    {
        Schema::table('workflow_definitions', function (Blueprint $table): void {
            $table->foreign('active_version_id', 'workflow_definitions_active_version_fk')
                ->references('id')
                ->on('workflow_versions')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    private function dropWorkflowDefinitionActiveVersionForeignKey(): void
    {
        Schema::table('workflow_definitions', function (Blueprint $table): void {
            $table->dropForeign('workflow_definitions_active_version_fk');
        });
    }

    private function addAuditLogHistoryIndex(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->index(
                ['company_id', 'entity_type', 'entity_id', 'created_at', 'id'],
                'audit_logs_company_entity_created_idx',
            );
        });
    }

    private function dropAuditLogHistoryIndex(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->dropIndex('audit_logs_company_entity_created_idx');
        });
    }

    private function addNotificationTemplateFallbackUniqueness(): void
    {
        $driver = DB::getDriverName();

        if ($this->isMySqlFamily()) {
            Schema::table('notification_templates', function (Blueprint $table): void {
                $table->unsignedBigInteger('tenant_scope_company_id')
                    ->storedAs('coalesce(company_id, 0)');
            });

            Schema::table('notification_templates', function (Blueprint $table): void {
                $table->unique(
                    ['tenant_scope_company_id', 'key', 'channel'],
                    'notification_templates_scope_key_channel_unique',
                );
            });

            return;
        }

        if (in_array($driver, ['pgsql', 'sqlite'], true)) {
            DB::statement(
                'CREATE UNIQUE INDEX notification_templates_global_key_channel_unique
                ON notification_templates ("key", "channel")
                WHERE company_id IS NULL'
            );
        }
    }

    private function dropNotificationTemplateFallbackUniqueness(): void
    {
        $driver = DB::getDriverName();

        if ($this->isMySqlFamily()) {
            Schema::table('notification_templates', function (Blueprint $table): void {
                $table->dropUnique('notification_templates_scope_key_channel_unique');
            });

            Schema::table('notification_templates', function (Blueprint $table): void {
                $table->dropColumn('tenant_scope_company_id');
            });

            return;
        }

        if (in_array($driver, ['pgsql', 'sqlite'], true)) {
            DB::statement('DROP INDEX notification_templates_global_key_channel_unique');
        }
    }

    private function supportsAlterTableForeignKeys(): bool
    {
        return DB::getDriverName() !== 'sqlite';
    }

    private function isMySqlFamily(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }

    /**
     * @return array<int, array{name:string, table:string, columns:array<int, string>}>
     */
    private function tenantCompositeUniqueIndexes(): array
    {
        return [
            ['table' => 'users', 'columns' => ['company_id', 'id'], 'name' => 'users_company_id_id_unique'],
            ['table' => 'departments', 'columns' => ['company_id', 'id'], 'name' => 'departments_company_id_id_unique'],
            ['table' => 'designations', 'columns' => ['company_id', 'id'], 'name' => 'designations_company_id_id_unique'],
            ['table' => 'locations', 'columns' => ['company_id', 'id'], 'name' => 'locations_company_id_id_unique'],
            ['table' => 'cost_centers', 'columns' => ['company_id', 'id'], 'name' => 'cost_centers_company_id_id_unique'],
            ['table' => 'employees', 'columns' => ['company_id', 'id'], 'name' => 'employees_company_id_id_unique'],
            ['table' => 'workflow_definitions', 'columns' => ['company_id', 'id'], 'name' => 'workflow_definitions_company_id_id_unique'],
            ['table' => 'workflow_instances', 'columns' => ['company_id', 'id'], 'name' => 'workflow_instances_company_id_id_unique'],
            ['table' => 'holiday_calendars', 'columns' => ['company_id', 'id'], 'name' => 'holiday_calendars_company_id_id_unique'],
            ['table' => 'shifts', 'columns' => ['company_id', 'id'], 'name' => 'shifts_company_id_id_unique'],
            ['table' => 'shift_rosters', 'columns' => ['company_id', 'id'], 'name' => 'shift_rosters_company_id_id_unique'],
            ['table' => 'attendance_records', 'columns' => ['company_id', 'id'], 'name' => 'attendance_records_company_id_id_unique'],
            ['table' => 'leave_types', 'columns' => ['company_id', 'id'], 'name' => 'leave_types_company_id_id_unique'],
            ['table' => 'leave_policies', 'columns' => ['company_id', 'id'], 'name' => 'leave_policies_company_id_id_unique'],
            ['table' => 'leave_accruals', 'columns' => ['company_id', 'id'], 'name' => 'leave_accruals_company_id_id_unique'],
            ['table' => 'leave_balances', 'columns' => ['company_id', 'id'], 'name' => 'leave_balances_company_id_id_unique'],
        ];
    }

    /**
     * @return array<int, array{
     *   table:string,
     *   columns:array<int, string>,
     *   references:array<int, string>,
     *   on:string,
     *   onDelete:string,
     *   name:string
     * }>
     */
    private function tenantCompositeForeignKeys(): array
    {
        return [
            ['table' => 'employees', 'columns' => ['company_id', 'department_id'], 'references' => ['company_id', 'id'], 'on' => 'departments', 'onDelete' => 'restrict', 'name' => 'employees_company_department_fk'],
            ['table' => 'employees', 'columns' => ['company_id', 'designation_id'], 'references' => ['company_id', 'id'], 'on' => 'designations', 'onDelete' => 'restrict', 'name' => 'employees_company_designation_fk'],
            ['table' => 'employees', 'columns' => ['company_id', 'manager_id'], 'references' => ['company_id', 'id'], 'on' => 'employees', 'onDelete' => 'set null', 'name' => 'employees_company_manager_fk'],
            ['table' => 'employees', 'columns' => ['company_id', 'location_id'], 'references' => ['company_id', 'id'], 'on' => 'locations', 'onDelete' => 'set null', 'name' => 'employees_company_location_fk'],
            ['table' => 'employees', 'columns' => ['company_id', 'cost_center_id'], 'references' => ['company_id', 'id'], 'on' => 'cost_centers', 'onDelete' => 'set null', 'name' => 'employees_company_cost_center_fk'],
            ['table' => 'employees', 'columns' => ['company_id', 'user_id'], 'references' => ['company_id', 'id'], 'on' => 'users', 'onDelete' => 'set null', 'name' => 'employees_company_user_fk'],
            ['table' => 'employment_histories', 'columns' => ['company_id', 'employee_id'], 'references' => ['company_id', 'id'], 'on' => 'employees', 'onDelete' => 'cascade', 'name' => 'employment_histories_company_employee_fk'],
            ['table' => 'employment_histories', 'columns' => ['company_id', 'previous_department_id'], 'references' => ['company_id', 'id'], 'on' => 'departments', 'onDelete' => 'set null', 'name' => 'employment_histories_prev_department_fk'],
            ['table' => 'employment_histories', 'columns' => ['company_id', 'department_id'], 'references' => ['company_id', 'id'], 'on' => 'departments', 'onDelete' => 'set null', 'name' => 'employment_histories_department_fk'],
            ['table' => 'employment_histories', 'columns' => ['company_id', 'previous_designation_id'], 'references' => ['company_id', 'id'], 'on' => 'designations', 'onDelete' => 'set null', 'name' => 'employment_histories_prev_designation_fk'],
            ['table' => 'employment_histories', 'columns' => ['company_id', 'designation_id'], 'references' => ['company_id', 'id'], 'on' => 'designations', 'onDelete' => 'set null', 'name' => 'employment_histories_designation_fk'],
            ['table' => 'employment_histories', 'columns' => ['company_id', 'previous_manager_id'], 'references' => ['company_id', 'id'], 'on' => 'employees', 'onDelete' => 'set null', 'name' => 'employment_histories_prev_manager_fk'],
            ['table' => 'employment_histories', 'columns' => ['company_id', 'manager_id'], 'references' => ['company_id', 'id'], 'on' => 'employees', 'onDelete' => 'set null', 'name' => 'employment_histories_manager_fk'],
            ['table' => 'employment_histories', 'columns' => ['company_id', 'previous_location_id'], 'references' => ['company_id', 'id'], 'on' => 'locations', 'onDelete' => 'set null', 'name' => 'employment_histories_prev_location_fk'],
            ['table' => 'employment_histories', 'columns' => ['company_id', 'location_id'], 'references' => ['company_id', 'id'], 'on' => 'locations', 'onDelete' => 'set null', 'name' => 'employment_histories_location_fk'],
            ['table' => 'employee_contacts', 'columns' => ['company_id', 'employee_id'], 'references' => ['company_id', 'id'], 'on' => 'employees', 'onDelete' => 'cascade', 'name' => 'employee_contacts_company_employee_fk'],
            ['table' => 'employee_addresses', 'columns' => ['company_id', 'employee_id'], 'references' => ['company_id', 'id'], 'on' => 'employees', 'onDelete' => 'cascade', 'name' => 'employee_addresses_company_employee_fk'],
            ['table' => 'employee_emergency_contacts', 'columns' => ['company_id', 'employee_id'], 'references' => ['company_id', 'id'], 'on' => 'employees', 'onDelete' => 'cascade', 'name' => 'employee_emergency_contacts_employee_fk'],
            ['table' => 'employee_bank_accounts', 'columns' => ['company_id', 'employee_id'], 'references' => ['company_id', 'id'], 'on' => 'employees', 'onDelete' => 'cascade', 'name' => 'employee_bank_accounts_company_employee_fk'],
            ['table' => 'employee_documents', 'columns' => ['company_id', 'employee_id'], 'references' => ['company_id', 'id'], 'on' => 'employees', 'onDelete' => 'cascade', 'name' => 'employee_documents_company_employee_fk'],
            ['table' => 'employee_onboarding_tasks', 'columns' => ['company_id', 'employee_id'], 'references' => ['company_id', 'id'], 'on' => 'employees', 'onDelete' => 'cascade', 'name' => 'employee_onboarding_tasks_employee_fk'],
            ['table' => 'workflow_instances', 'columns' => ['company_id', 'workflow_definition_id'], 'references' => ['company_id', 'id'], 'on' => 'workflow_definitions', 'onDelete' => 'restrict', 'name' => 'workflow_instances_definition_fk'],
            ['table' => 'workflow_instances', 'columns' => ['company_id', 'started_by_user_id'], 'references' => ['company_id', 'id'], 'on' => 'users', 'onDelete' => 'set null', 'name' => 'workflow_instances_started_by_fk'],
            ['table' => 'workflow_tasks', 'columns' => ['company_id', 'workflow_instance_id'], 'references' => ['company_id', 'id'], 'on' => 'workflow_instances', 'onDelete' => 'cascade', 'name' => 'workflow_tasks_instance_fk'],
            ['table' => 'workflow_tasks', 'columns' => ['company_id', 'assigned_to_user_id'], 'references' => ['company_id', 'id'], 'on' => 'users', 'onDelete' => 'set null', 'name' => 'workflow_tasks_assigned_user_fk'],
            ['table' => 'workflow_tasks', 'columns' => ['company_id', 'acted_by_user_id'], 'references' => ['company_id', 'id'], 'on' => 'users', 'onDelete' => 'set null', 'name' => 'workflow_tasks_acted_user_fk'],
            ['table' => 'workflow_tasks', 'columns' => ['company_id', 'delegated_to_user_id'], 'references' => ['company_id', 'id'], 'on' => 'users', 'onDelete' => 'set null', 'name' => 'workflow_tasks_delegated_user_fk'],
            ['table' => 'notifications', 'columns' => ['company_id', 'user_id'], 'references' => ['company_id', 'id'], 'on' => 'users', 'onDelete' => 'cascade', 'name' => 'notifications_company_user_fk'],
            ['table' => 'holiday_calendars', 'columns' => ['company_id', 'location_id'], 'references' => ['company_id', 'id'], 'on' => 'locations', 'onDelete' => 'set null', 'name' => 'holiday_calendars_location_fk'],
            ['table' => 'holiday_calendars', 'columns' => ['company_id', 'department_id'], 'references' => ['company_id', 'id'], 'on' => 'departments', 'onDelete' => 'set null', 'name' => 'holiday_calendars_department_fk'],
            ['table' => 'holidays', 'columns' => ['company_id', 'holiday_calendar_id'], 'references' => ['company_id', 'id'], 'on' => 'holiday_calendars', 'onDelete' => 'cascade', 'name' => 'holidays_calendar_fk'],
            ['table' => 'shift_assignments', 'columns' => ['company_id', 'shift_id'], 'references' => ['company_id', 'id'], 'on' => 'shifts', 'onDelete' => 'cascade', 'name' => 'shift_assignments_shift_fk'],
            ['table' => 'shift_assignments', 'columns' => ['company_id', 'employee_id'], 'references' => ['company_id', 'id'], 'on' => 'employees', 'onDelete' => 'cascade', 'name' => 'shift_assignments_employee_fk'],
            ['table' => 'shift_assignments', 'columns' => ['company_id', 'department_id'], 'references' => ['company_id', 'id'], 'on' => 'departments', 'onDelete' => 'cascade', 'name' => 'shift_assignments_department_fk'],
            ['table' => 'shift_assignments', 'columns' => ['company_id', 'location_id'], 'references' => ['company_id', 'id'], 'on' => 'locations', 'onDelete' => 'cascade', 'name' => 'shift_assignments_location_fk'],
            ['table' => 'shift_rosters', 'columns' => ['company_id', 'employee_id'], 'references' => ['company_id', 'id'], 'on' => 'employees', 'onDelete' => 'cascade', 'name' => 'shift_rosters_employee_fk'],
            ['table' => 'shift_rosters', 'columns' => ['company_id', 'shift_id'], 'references' => ['company_id', 'id'], 'on' => 'shifts', 'onDelete' => 'cascade', 'name' => 'shift_rosters_shift_fk'],
            ['table' => 'attendance_records', 'columns' => ['company_id', 'employee_id'], 'references' => ['company_id', 'id'], 'on' => 'employees', 'onDelete' => 'cascade', 'name' => 'attendance_records_employee_fk'],
            ['table' => 'attendance_records', 'columns' => ['company_id', 'shift_id'], 'references' => ['company_id', 'id'], 'on' => 'shifts', 'onDelete' => 'set null', 'name' => 'attendance_records_shift_fk'],
            ['table' => 'attendance_records', 'columns' => ['company_id', 'shift_roster_id'], 'references' => ['company_id', 'id'], 'on' => 'shift_rosters', 'onDelete' => 'set null', 'name' => 'attendance_records_roster_fk'],
            ['table' => 'attendance_corrections', 'columns' => ['company_id', 'attendance_record_id'], 'references' => ['company_id', 'id'], 'on' => 'attendance_records', 'onDelete' => 'cascade', 'name' => 'attendance_corrections_record_fk'],
            ['table' => 'attendance_corrections', 'columns' => ['company_id', 'employee_id'], 'references' => ['company_id', 'id'], 'on' => 'employees', 'onDelete' => 'cascade', 'name' => 'attendance_corrections_employee_fk'],
            ['table' => 'attendance_corrections', 'columns' => ['company_id', 'workflow_instance_id'], 'references' => ['company_id', 'id'], 'on' => 'workflow_instances', 'onDelete' => 'set null', 'name' => 'attendance_corrections_workflow_fk'],
            ['table' => 'attendance_corrections', 'columns' => ['company_id', 'requested_by_user_id'], 'references' => ['company_id', 'id'], 'on' => 'users', 'onDelete' => 'cascade', 'name' => 'attendance_corrections_request_user_fk'],
            ['table' => 'attendance_corrections', 'columns' => ['company_id', 'latest_action_by_user_id'], 'references' => ['company_id', 'id'], 'on' => 'users', 'onDelete' => 'set null', 'name' => 'attendance_corrections_actor_user_fk'],
            ['table' => 'leave_policies', 'columns' => ['company_id', 'leave_type_id'], 'references' => ['company_id', 'id'], 'on' => 'leave_types', 'onDelete' => 'cascade', 'name' => 'leave_policies_type_fk'],
            ['table' => 'leave_policies', 'columns' => ['company_id', 'applicable_department_id'], 'references' => ['company_id', 'id'], 'on' => 'departments', 'onDelete' => 'set null', 'name' => 'leave_policies_department_fk'],
            ['table' => 'leave_policies', 'columns' => ['company_id', 'applicable_location_id'], 'references' => ['company_id', 'id'], 'on' => 'locations', 'onDelete' => 'set null', 'name' => 'leave_policies_location_fk'],
            ['table' => 'leave_accruals', 'columns' => ['company_id', 'employee_id'], 'references' => ['company_id', 'id'], 'on' => 'employees', 'onDelete' => 'cascade', 'name' => 'leave_accruals_employee_fk'],
            ['table' => 'leave_accruals', 'columns' => ['company_id', 'leave_policy_id'], 'references' => ['company_id', 'id'], 'on' => 'leave_policies', 'onDelete' => 'cascade', 'name' => 'leave_accruals_policy_fk'],
            ['table' => 'leave_accruals', 'columns' => ['company_id', 'leave_type_id'], 'references' => ['company_id', 'id'], 'on' => 'leave_types', 'onDelete' => 'cascade', 'name' => 'leave_accruals_type_fk'],
            ['table' => 'leave_encashments', 'columns' => ['company_id', 'leave_accrual_id'], 'references' => ['company_id', 'id'], 'on' => 'leave_accruals', 'onDelete' => 'cascade', 'name' => 'leave_encashments_accrual_fk'],
            ['table' => 'leave_encashments', 'columns' => ['company_id', 'employee_id'], 'references' => ['company_id', 'id'], 'on' => 'employees', 'onDelete' => 'cascade', 'name' => 'leave_encashments_employee_fk'],
            ['table' => 'leave_encashments', 'columns' => ['company_id', 'leave_policy_id'], 'references' => ['company_id', 'id'], 'on' => 'leave_policies', 'onDelete' => 'cascade', 'name' => 'leave_encashments_policy_fk'],
            ['table' => 'leave_encashments', 'columns' => ['company_id', 'leave_type_id'], 'references' => ['company_id', 'id'], 'on' => 'leave_types', 'onDelete' => 'cascade', 'name' => 'leave_encashments_type_fk'],
            ['table' => 'leave_balances', 'columns' => ['company_id', 'employee_id'], 'references' => ['company_id', 'id'], 'on' => 'employees', 'onDelete' => 'cascade', 'name' => 'leave_balances_employee_fk'],
            ['table' => 'leave_balances', 'columns' => ['company_id', 'leave_type_id'], 'references' => ['company_id', 'id'], 'on' => 'leave_types', 'onDelete' => 'cascade', 'name' => 'leave_balances_type_fk'],
            ['table' => 'leave_balances', 'columns' => ['company_id', 'leave_policy_id'], 'references' => ['company_id', 'id'], 'on' => 'leave_policies', 'onDelete' => 'set null', 'name' => 'leave_balances_policy_fk'],
            ['table' => 'leave_balance_entries', 'columns' => ['company_id', 'leave_balance_id'], 'references' => ['company_id', 'id'], 'on' => 'leave_balances', 'onDelete' => 'cascade', 'name' => 'leave_balance_entries_balance_fk'],
            ['table' => 'leave_balance_entries', 'columns' => ['company_id', 'employee_id'], 'references' => ['company_id', 'id'], 'on' => 'employees', 'onDelete' => 'cascade', 'name' => 'leave_balance_entries_employee_fk'],
            ['table' => 'leave_balance_entries', 'columns' => ['company_id', 'leave_type_id'], 'references' => ['company_id', 'id'], 'on' => 'leave_types', 'onDelete' => 'cascade', 'name' => 'leave_balance_entries_type_fk'],
            ['table' => 'leave_balance_entries', 'columns' => ['company_id', 'leave_policy_id'], 'references' => ['company_id', 'id'], 'on' => 'leave_policies', 'onDelete' => 'set null', 'name' => 'leave_balance_entries_policy_fk'],
            ['table' => 'leave_requests', 'columns' => ['company_id', 'employee_id'], 'references' => ['company_id', 'id'], 'on' => 'employees', 'onDelete' => 'cascade', 'name' => 'leave_requests_employee_fk'],
            ['table' => 'leave_requests', 'columns' => ['company_id', 'leave_type_id'], 'references' => ['company_id', 'id'], 'on' => 'leave_types', 'onDelete' => 'cascade', 'name' => 'leave_requests_type_fk'],
            ['table' => 'leave_requests', 'columns' => ['company_id', 'leave_policy_id'], 'references' => ['company_id', 'id'], 'on' => 'leave_policies', 'onDelete' => 'cascade', 'name' => 'leave_requests_policy_fk'],
            ['table' => 'leave_requests', 'columns' => ['company_id', 'workflow_instance_id'], 'references' => ['company_id', 'id'], 'on' => 'workflow_instances', 'onDelete' => 'set null', 'name' => 'leave_requests_workflow_fk'],
            ['table' => 'leave_requests', 'columns' => ['company_id', 'requested_by_user_id'], 'references' => ['company_id', 'id'], 'on' => 'users', 'onDelete' => 'cascade', 'name' => 'leave_requests_requested_user_fk'],
            ['table' => 'leave_requests', 'columns' => ['company_id', 'department_id'], 'references' => ['company_id', 'id'], 'on' => 'departments', 'onDelete' => 'set null', 'name' => 'leave_requests_department_fk'],
            ['table' => 'leave_requests', 'columns' => ['company_id', 'location_id'], 'references' => ['company_id', 'id'], 'on' => 'locations', 'onDelete' => 'set null', 'name' => 'leave_requests_location_fk'],
        ];
    }
};
