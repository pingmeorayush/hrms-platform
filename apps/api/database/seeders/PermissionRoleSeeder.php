<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionRoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'audit.view',
            'auth.manage_permissions',
            'auth.manage_roles',
            'auth.manage_users',
            'tenant.manage',
            'tenant.view',
            'organization.view',
            'organization.manage',
            'workflow.view',
            'workflow.create',
            'workflow.edit',
            'workflow.publish',
            'workflow.execute',
            'workflow.monitor',
            'workflow.admin',
            'notification.manage',
            'notification.view',
            'employee.view',
            'employee.manage',
            'employee.bank.view',
            'employee.bank.manage',
            'employee.approve',
            'leave.approve',
        ];

        $permissionModels = collect($permissions)
            ->mapWithKeys(fn (string $permission): array => [
                $permission => Permission::findOrCreate($permission, 'web'),
            ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roles = [
            'platform.super_admin' => $permissions,
            'platform.support' => ['tenant.view', 'auth.manage_users', 'notification.view', 'organization.view', 'employee.view'],
            'platform.auditor' => ['audit.view', 'notification.view', 'organization.view', 'employee.view', 'employee.bank.view'],
            'tenant.admin' => [
                'tenant.manage',
                'tenant.view',
                'auth.manage_permissions',
                'auth.manage_roles',
                'auth.manage_users',
                'organization.view',
                'organization.manage',
                'employee.view',
                'employee.manage',
                'employee.bank.view',
                'employee.bank.manage',
                'workflow.view',
                'workflow.create',
                'workflow.edit',
                'workflow.publish',
                'workflow.execute',
                'workflow.monitor',
                'notification.view',
                'notification.manage',
            ],
            'hr.admin' => [
                'organization.view',
                'employee.view',
                'employee.manage',
                'employee.bank.view',
                'employee.bank.manage',
                'workflow.view',
                'workflow.execute',
                'workflow.monitor',
                'employee.approve',
                'leave.approve',
                'notification.view',
            ],
            'manager' => ['organization.view', 'employee.view', 'workflow.view', 'workflow.execute', 'leave.approve', 'notification.view'],
            'employee' => ['notification.view'],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions(
                collect($rolePermissions)
                    ->map(fn (string $permission) => $permissionModels[$permission])
                    ->all(),
            );
        }

        $company = Company::firstOrCreate(
            ['slug' => 'phoenix-demo'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Phoenix Demo Company',
                'status' => 'active',
                'subscription_plan' => 'enterprise',
                'timezone' => 'Asia/Kolkata',
                'currency' => 'INR',
            ],
        );

        $admin = User::withoutGlobalScopes()->firstOrCreate(
            ['email' => 'admin@phoenixhrms.test'],
            [
                'company_id' => $company->id,
                'name' => 'Platform Admin',
                'password' => Hash::make('Password@12345'),
                'is_active' => true,
            ],
        );

        $admin->syncRoles(['platform.super_admin']);
    }
}
