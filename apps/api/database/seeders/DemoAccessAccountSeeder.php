<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CostCenter;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoAccessAccountSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = 'Password@12345';

    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['slug' => 'phoenix-demo'],
            [
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'name' => 'Phoenix Demo Company',
                'status' => 'active',
                'subscription_plan' => 'enterprise',
                'timezone' => 'Asia/Kolkata',
                'currency' => 'INR',
            ],
        );

        $location = Location::query()->updateOrCreate(
            [
                'company_id' => $company->id,
                'code' => 'BLR-HQ',
            ],
            [
                'name' => 'Bengaluru Headquarters',
                'timezone' => 'Asia/Kolkata',
                'currency' => 'INR',
                'address_line_1' => 'Embassy Tech Village',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'country' => 'India',
                'postal_code' => '560103',
                'status' => 'active',
            ],
        );

        $costCenter = CostCenter::query()->updateOrCreate(
            [
                'company_id' => $company->id,
                'code' => 'CORP-100',
            ],
            [
                'name' => 'Corporate Operations',
                'description' => 'Default seeded cost center for role-based access walkthroughs.',
                'status' => 'active',
            ],
        );

        $departments = [
            'leadership' => Department::query()->updateOrCreate(
                ['company_id' => $company->id, 'code' => 'LEAD'],
                ['name' => 'Leadership', 'description' => 'Seeded enterprise leadership team.', 'status' => 'active'],
            ),
            'people' => Department::query()->updateOrCreate(
                ['company_id' => $company->id, 'code' => 'PEOPLE'],
                ['name' => 'People Operations', 'description' => 'Seeded HR and people operations team.', 'status' => 'active'],
            ),
            'engineering' => Department::query()->updateOrCreate(
                ['company_id' => $company->id, 'code' => 'ENG'],
                ['name' => 'Engineering', 'description' => 'Seeded product and engineering team.', 'status' => 'active'],
            ),
            'it' => Department::query()->updateOrCreate(
                ['company_id' => $company->id, 'code' => 'ITOPS'],
                ['name' => 'Technology Operations', 'description' => 'Seeded infrastructure and systems team.', 'status' => 'active'],
            ),
            'talent' => Department::query()->updateOrCreate(
                ['company_id' => $company->id, 'code' => 'TALENT'],
                ['name' => 'Talent Acquisition', 'description' => 'Seeded recruiting and interview operations team.', 'status' => 'active'],
            ),
            'learning' => Department::query()->updateOrCreate(
                ['company_id' => $company->id, 'code' => 'LND'],
                ['name' => 'Learning and Development', 'description' => 'Seeded learning operations team.', 'status' => 'active'],
            ),
        ];

        $designations = [
            'platform-admin' => $this->upsertDesignation($company->id, 'PLATFORM-ADMIN', 'Platform Administrator'),
            'platform-support' => $this->upsertDesignation($company->id, 'PLATFORM-SUPPORT', 'Platform Support Lead'),
            'platform-auditor' => $this->upsertDesignation($company->id, 'PLATFORM-AUDITOR', 'Platform Audit Lead'),
            'tenant-admin' => $this->upsertDesignation($company->id, 'TENANT-ADMIN', 'Tenant Administrator'),
            'hr-admin' => $this->upsertDesignation($company->id, 'HR-ADMIN', 'HR Administrator'),
            'engineering-manager' => $this->upsertDesignation($company->id, 'ENG-MANAGER', 'Engineering Manager'),
            'it-admin' => $this->upsertDesignation($company->id, 'IT-ADMIN', 'IT Administrator'),
            'learning-admin' => $this->upsertDesignation($company->id, 'LND-ADMIN', 'Learning Administrator'),
            'recruiter' => $this->upsertDesignation($company->id, 'RECRUITER', 'Talent Recruiter'),
            'interviewer' => $this->upsertDesignation($company->id, 'INTERVIEWER', 'Interview Panelist'),
            'employee' => $this->upsertDesignation($company->id, 'SWE-IC', 'Software Engineer'),
        ];

        $accounts = [
            [
                'email' => 'admin@phoenixhrms.test',
                'name' => 'Platform Admin',
                'role' => 'platform.super_admin',
                'employee' => [
                    'employee_code' => 'PHX-PLT-001',
                    'first_name' => 'Platform',
                    'last_name' => 'Admin',
                    'department' => 'leadership',
                    'designation' => 'platform-admin',
                ],
            ],
            [
                'email' => 'platform.support@phoenixhrms.test',
                'name' => 'Priya Support',
                'role' => 'platform.support',
                'employee' => [
                    'employee_code' => 'PHX-PLT-002',
                    'first_name' => 'Priya',
                    'last_name' => 'Support',
                    'department' => 'it',
                    'designation' => 'platform-support',
                ],
            ],
            [
                'email' => 'platform.auditor@phoenixhrms.test',
                'name' => 'Karan Auditor',
                'role' => 'platform.auditor',
                'employee' => [
                    'employee_code' => 'PHX-PLT-003',
                    'first_name' => 'Karan',
                    'last_name' => 'Auditor',
                    'department' => 'leadership',
                    'designation' => 'platform-auditor',
                ],
            ],
            [
                'email' => 'tenant.admin@phoenixhrms.test',
                'name' => 'Naina Kapoor',
                'role' => 'tenant.admin',
                'employee' => [
                    'employee_code' => 'PHX-TEN-001',
                    'first_name' => 'Naina',
                    'last_name' => 'Kapoor',
                    'department' => 'people',
                    'designation' => 'tenant-admin',
                ],
            ],
            [
                'email' => 'hr.admin@phoenixhrms.test',
                'name' => 'Sana Mehta',
                'role' => 'hr.admin',
                'employee' => [
                    'employee_code' => 'PHX-HR-001',
                    'first_name' => 'Sana',
                    'last_name' => 'Mehta',
                    'department' => 'people',
                    'designation' => 'hr-admin',
                ],
            ],
            [
                'email' => 'manager@phoenixhrms.test',
                'name' => 'Maya Sharma',
                'role' => 'manager',
                'employee' => [
                    'employee_code' => 'PHX-MGR-001',
                    'first_name' => 'Maya',
                    'last_name' => 'Sharma',
                    'department' => 'engineering',
                    'designation' => 'engineering-manager',
                ],
            ],
            [
                'email' => 'it.admin@phoenixhrms.test',
                'name' => 'Vikram Rao',
                'role' => 'it.admin',
                'employee' => [
                    'employee_code' => 'PHX-IT-001',
                    'first_name' => 'Vikram',
                    'last_name' => 'Rao',
                    'department' => 'it',
                    'designation' => 'it-admin',
                ],
            ],
            [
                'email' => 'learning.admin@phoenixhrms.test',
                'name' => 'Priya Nair',
                'role' => 'learning.admin',
                'employee' => [
                    'employee_code' => 'PHX-LND-001',
                    'first_name' => 'Priya',
                    'last_name' => 'Nair',
                    'department' => 'learning',
                    'designation' => 'learning-admin',
                ],
            ],
            [
                'email' => 'recruiter@phoenixhrms.test',
                'name' => 'Aisha Khan',
                'role' => 'recruiter',
                'employee' => [
                    'employee_code' => 'PHX-TA-001',
                    'first_name' => 'Aisha',
                    'last_name' => 'Khan',
                    'department' => 'talent',
                    'designation' => 'recruiter',
                ],
            ],
            [
                'email' => 'interviewer@phoenixhrms.test',
                'name' => 'Neeraj Gupta',
                'role' => 'interviewer',
                'employee' => [
                    'employee_code' => 'PHX-ENG-002',
                    'first_name' => 'Neeraj',
                    'last_name' => 'Gupta',
                    'department' => 'engineering',
                    'designation' => 'interviewer',
                    'manager_email' => 'manager@phoenixhrms.test',
                ],
            ],
            [
                'email' => 'employee@phoenixhrms.test',
                'name' => 'Arjun Verma',
                'role' => 'employee',
                'employee' => [
                    'employee_code' => 'PHX-ENG-001',
                    'first_name' => 'Arjun',
                    'last_name' => 'Verma',
                    'department' => 'engineering',
                    'designation' => 'employee',
                    'manager_email' => 'manager@phoenixhrms.test',
                ],
            ],
        ];

        $usersByEmail = [];

        foreach ($accounts as $account) {
            $user = User::withoutGlobalScopes()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'company_id' => $company->id,
                    'name' => $account['name'],
                    'password' => Hash::make(self::DEFAULT_PASSWORD),
                    'is_active' => true,
                    'requires_mfa' => false,
                    'mfa_method' => null,
                    'timezone' => 'Asia/Kolkata',
                    'currency' => 'INR',
                    'locale' => 'en-IN',
                    'language' => 'en',
                    'time_format' => '24h',
                ],
            );

            $user->syncRoles([$account['role']]);
            $usersByEmail[$account['email']] = $user;
        }

        $employeesByEmail = [];

        foreach ($accounts as $account) {
            if (! isset($account['employee'])) {
                continue;
            }

            $employee = $account['employee'];

            $employeesByEmail[$account['email']] = Employee::query()->updateOrCreate(
                [
                    'company_id' => $company->id,
                    'email' => $account['email'],
                ],
                [
                    'employee_code' => $employee['employee_code'],
                    'first_name' => $employee['first_name'],
                    'last_name' => $employee['last_name'],
                    'date_of_joining' => '2024-04-01',
                    'employment_type' => 'full_time',
                    'employment_status' => 'active',
                    'department_id' => $departments[$employee['department']]->id,
                    'designation_id' => $designations[$employee['designation']]->id,
                    'location_id' => $location->id,
                    'cost_center_id' => $costCenter->id,
                    'manager_id' => null,
                    'user_id' => $usersByEmail[$account['email']]->id,
                ],
            );
        }

        foreach ($accounts as $account) {
            $managerEmail = $account['employee']['manager_email'] ?? null;

            if (! $managerEmail || ! isset($employeesByEmail[$account['email']], $employeesByEmail[$managerEmail])) {
                continue;
            }

            $employee = $employeesByEmail[$account['email']];
            $manager = $employeesByEmail[$managerEmail];

            if ($employee->manager_id !== $manager->id) {
                $employee->forceFill(['manager_id' => $manager->id])->save();
            }
        }
    }

    private function upsertDesignation(int $companyId, string $code, string $name): Designation
    {
        return Designation::query()->updateOrCreate(
            [
                'company_id' => $companyId,
                'code' => $code,
            ],
            [
                'name' => $name,
                'description' => sprintf('Seeded designation for %s.', $name),
                'status' => 'active',
            ],
        );
    }
}
