<?php

namespace App\Modules\EmployeeManagement\Services;

use App\Models\Company;
use App\Modules\EmployeeManagement\Resources\EmployeeResource;
use App\Modules\Platform\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EmployeeDirectoryBenchmarkService
{
    public function __construct(private readonly EmployeeDirectoryService $employeeDirectoryService) {}

    /**
     * @return array{
     *     employee_count:int,
     *     iterations:int,
     *     seed_duration_ms:float,
     *     scenarios:array<int, array{
     *         name:string,
     *         result_count:int,
     *         durations_ms:array<int, float>,
     *         average_ms:float,
     *         p95_ms:float
     *     }>,
     *     worst_p95_ms:float,
     *     nfr_limit_ms:float,
     *     within_nfr:bool
     * }
     */
    public function run(int $employeeCount = 100000, int $iterations = 5, bool $cleanup = true): array
    {
        if ($employeeCount < 2) {
            throw new RuntimeException('Employee benchmark requires at least 2 employees.');
        }

        DB::connection()->disableQueryLog();

        $context = $this->createBenchmarkDataset($employeeCount);
        $previousTenantContext = app(TenantContext::class);

        app()->instance(TenantContext::class, TenantContext::fromCompany($context['company']));

        try {
            $scenarios = [
                [
                    'name' => 'email_prefix_search',
                    'filters' => ['search' => 'benchmark.target', 'per_page' => 25],
                ],
                [
                    'name' => 'full_name_prefix_search',
                    'filters' => ['search' => 'Benchmark Target', 'per_page' => 25],
                ],
                [
                    'name' => 'indexed_filtered_listing',
                    'filters' => [
                        'employment_status' => 'active',
                        'department_id' => $context['target_department_id'],
                        'designation_id' => $context['target_designation_id'],
                        'manager_id' => $context['manager_id'],
                        'per_page' => 25,
                    ],
                ],
            ];

            $results = array_map(
                fn (array $scenario): array => $this->runScenario($scenario['name'], $scenario['filters'], $iterations),
                $scenarios,
            );

            $worstP95 = max(array_column($results, 'p95_ms'));

            return [
                'employee_count' => $employeeCount,
                'iterations' => $iterations,
                'seed_duration_ms' => $context['seed_duration_ms'],
                'scenarios' => $results,
                'worst_p95_ms' => $worstP95,
                'nfr_limit_ms' => 1000.0,
                'within_nfr' => $worstP95 <= 1000.0,
            ];
        } finally {
            app()->instance(TenantContext::class, $previousTenantContext);

            if ($cleanup) {
                $this->cleanupBenchmarkDataset($context['company']->id);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     name:string,
     *     result_count:int,
     *     durations_ms:array<int, float>,
     *     average_ms:float,
     *     p95_ms:float
     * }
     */
    private function runScenario(string $name, array $filters, int $iterations): array
    {
        $this->executeQuery($filters);

        $durations = [];
        $resultCount = 0;

        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            [$durationMs, $resultCount] = $this->executeQuery($filters);
            $durations[] = $durationMs;
        }

        return [
            'name' => $name,
            'result_count' => $resultCount,
            'durations_ms' => $durations,
            'average_ms' => round(array_sum($durations) / count($durations), 2),
            'p95_ms' => round($this->percentile($durations, 0.95), 2),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{0: float, 1: int}
     */
    private function executeQuery(array $filters): array
    {
        $start = hrtime(true);
        $employees = $this->employeeDirectoryService->search($filters);
        EmployeeResource::collection($employees->items())->resolve();
        $durationMs = (hrtime(true) - $start) / 1_000_000;

        return [round($durationMs, 2), $employees->total()];
    }

    /**
     * @return array{
     *     company:Company,
     *     manager_id:int,
     *     target_department_id:int,
     *     target_designation_id:int,
     *     seed_duration_ms:float
     * }
     */
    private function createBenchmarkDataset(int $employeeCount): array
    {
        $startedAt = hrtime(true);

        $context = DB::transaction(function () use ($employeeCount): array {
            $company = Company::withoutGlobalScopes()->create([
                'uuid' => (string) str()->uuid(),
                'name' => 'Employee Directory Benchmark Tenant',
                'slug' => 'employee-directory-benchmark-'.str()->lower((string) str()->random(10)),
                'status' => 'active',
                'subscription_plan' => 'enterprise',
                'timezone' => 'Asia/Kolkata',
                'currency' => 'INR',
            ]);

            $timestamp = now();

            $departmentId = DB::table('departments')->insertGetId([
                'company_id' => $company->id,
                'code' => 'BENCH-DEP-A',
                'name' => 'Benchmark Department A',
                'description' => null,
                'status' => 'active',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            $targetDepartmentId = DB::table('departments')->insertGetId([
                'company_id' => $company->id,
                'code' => 'BENCH-DEP-B',
                'name' => 'Benchmark Department B',
                'description' => null,
                'status' => 'active',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            $designationId = DB::table('designations')->insertGetId([
                'company_id' => $company->id,
                'code' => 'BENCH-DSG-A',
                'name' => 'Benchmark Designation A',
                'description' => null,
                'status' => 'active',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            $targetDesignationId = DB::table('designations')->insertGetId([
                'company_id' => $company->id,
                'code' => 'BENCH-DSG-B',
                'name' => 'Benchmark Designation B',
                'description' => null,
                'status' => 'active',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            $locationId = DB::table('locations')->insertGetId([
                'company_id' => $company->id,
                'code' => 'BENCH-LOC',
                'name' => 'Benchmark Location',
                'timezone' => 'Asia/Kolkata',
                'currency' => 'INR',
                'address_line_1' => null,
                'address_line_2' => null,
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'country' => 'India',
                'postal_code' => '560001',
                'status' => 'active',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            $costCenterId = DB::table('cost_centers')->insertGetId([
                'company_id' => $company->id,
                'code' => 'BENCH-CC',
                'name' => 'Benchmark Cost Center',
                'description' => null,
                'status' => 'active',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            $managerId = DB::table('employees')->insertGetId($this->employeeRow(
                companyId: $company->id,
                employeeCode: 'BENCH-MGR-00001',
                firstName: 'Benchmark',
                lastName: 'Manager',
                email: 'benchmark.manager@phoenixhrms.test',
                departmentId: $departmentId,
                designationId: $designationId,
                managerId: null,
                locationId: $locationId,
                costCenterId: $costCenterId,
            ));

            DB::table('employees')->insert($this->employeeRow(
                companyId: $company->id,
                employeeCode: 'BENCH-TARGET-00001',
                firstName: 'Benchmark',
                lastName: 'Target',
                email: 'benchmark.target@phoenixhrms.test',
                departmentId: $targetDepartmentId,
                designationId: $targetDesignationId,
                managerId: $managerId,
                locationId: $locationId,
                costCenterId: $costCenterId,
            ));

            $remainingEmployees = $employeeCount - 2;
            $this->insertBulkBenchmarkEmployees(
                companyId: $company->id,
                departmentId: $departmentId,
                designationId: $designationId,
                locationId: $locationId,
                costCenterId: $costCenterId,
                employeeCount: $remainingEmployees,
                timestamp: $timestamp,
            );

            return [
                'company' => $company,
                'manager_id' => $managerId,
                'target_department_id' => $targetDepartmentId,
                'target_designation_id' => $targetDesignationId,
            ];
        }, 1);

        $seedDurationMs = (hrtime(true) - $startedAt) / 1_000_000;

        return [
            'company' => $context['company'],
            'manager_id' => $context['manager_id'],
            'target_department_id' => $context['target_department_id'],
            'target_designation_id' => $context['target_designation_id'],
            'seed_duration_ms' => round($seedDurationMs, 2),
        ];
    }

    private function insertBulkBenchmarkEmployees(
        int $companyId,
        int $departmentId,
        int $designationId,
        int $locationId,
        int $costCenterId,
        int $employeeCount,
        mixed $timestamp,
    ): void {
        if ($employeeCount <= 0) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            DB::statement(
                <<<'SQL'
                WITH digits(digit) AS (
                    VALUES (0), (1), (2), (3), (4), (5), (6), (7), (8), (9)
                ),
                sequence(value) AS (
                    SELECT generated.value
                    FROM (
                        SELECT
                            ones.digit
                            + (tens.digit * 10)
                            + (hundreds.digit * 100)
                            + (thousands.digit * 1000)
                            + (ten_thousands.digit * 10000)
                            + 1 AS value
                        FROM digits ones
                        CROSS JOIN digits tens
                        CROSS JOIN digits hundreds
                        CROSS JOIN digits thousands
                        CROSS JOIN digits ten_thousands
                    ) generated
                    ORDER BY generated.value
                    LIMIT :employee_count
                )
                INSERT INTO employees (
                    company_id,
                    employee_code,
                    first_name,
                    middle_name,
                    last_name,
                    email,
                    phone,
                    date_of_birth,
                    gender,
                    marital_status,
                    date_of_joining,
                    employment_type,
                    employment_status,
                    department_id,
                    designation_id,
                    manager_id,
                    location_id,
                    cost_center_id,
                    user_id,
                    termination_reason,
                    terminated_at,
                    created_at,
                    updated_at
                )
                SELECT
                    :company_id,
                    printf('BENCH-%05d', value),
                    'Employee' || value,
                    NULL,
                    'Directory' || value,
                    'employee.' || printf('%05d', value) || '@phoenixhrms.test',
                    NULL,
                    '1990-01-01',
                    NULL,
                    NULL,
                    '2024-01-01',
                    'full_time',
                    CASE WHEN value % 7 = 0 THEN 'probation' ELSE 'active' END,
                    :department_id,
                    :designation_id,
                    NULL,
                    :location_id,
                    :cost_center_id,
                    NULL,
                    NULL,
                    NULL,
                    :created_at,
                    :updated_at
                FROM sequence
                SQL,
                [
                    'employee_count' => $employeeCount,
                    'company_id' => $companyId,
                    'department_id' => $departmentId,
                    'designation_id' => $designationId,
                    'location_id' => $locationId,
                    'cost_center_id' => $costCenterId,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ],
            );

            return;
        }

        $batch = [];

        for ($sequence = 1; $sequence <= $employeeCount; $sequence++) {
            $batch[] = [
                'company_id' => $companyId,
                'employee_code' => sprintf('BENCH-%05d', $sequence),
                'first_name' => 'Employee'.$sequence,
                'middle_name' => null,
                'last_name' => 'Directory'.$sequence,
                'email' => sprintf('employee.%05d@phoenixhrms.test', $sequence),
                'phone' => null,
                'date_of_birth' => '1990-01-01',
                'gender' => null,
                'marital_status' => null,
                'date_of_joining' => '2024-01-01',
                'employment_type' => 'full_time',
                'employment_status' => $sequence % 7 === 0 ? 'probation' : 'active',
                'department_id' => $departmentId,
                'designation_id' => $designationId,
                'manager_id' => null,
                'location_id' => $locationId,
                'cost_center_id' => $costCenterId,
                'user_id' => null,
                'termination_reason' => null,
                'terminated_at' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            if (count($batch) === 5000) {
                DB::table('employees')->insert($batch);
                $batch = [];
            }
        }

        if ($batch !== []) {
            DB::table('employees')->insert($batch);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function employeeRow(
        int $companyId,
        string $employeeCode,
        string $firstName,
        string $lastName,
        string $email,
        int $departmentId,
        int $designationId,
        ?int $managerId,
        int $locationId,
        int $costCenterId,
    ): array {
        return [
            'company_id' => $companyId,
            'employee_code' => $employeeCode,
            'first_name' => $firstName,
            'middle_name' => null,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => null,
            'date_of_birth' => '1990-01-01',
            'gender' => null,
            'marital_status' => null,
            'date_of_joining' => '2024-01-01',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'department_id' => $departmentId,
            'designation_id' => $designationId,
            'manager_id' => $managerId,
            'location_id' => $locationId,
            'cost_center_id' => $costCenterId,
            'user_id' => null,
            'termination_reason' => null,
            'terminated_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function cleanupBenchmarkDataset(int $companyId): void
    {
        DB::table('employees')->where('company_id', $companyId)->delete();
        DB::table('cost_centers')->where('company_id', $companyId)->delete();
        DB::table('locations')->where('company_id', $companyId)->delete();
        DB::table('designations')->where('company_id', $companyId)->delete();
        DB::table('departments')->where('company_id', $companyId)->delete();
        DB::table('companies')->where('id', $companyId)->delete();
    }

    /**
     * @param  array<int, float>  $values
     */
    private function percentile(array $values, float $percentile): float
    {
        sort($values);

        $index = (int) ceil((count($values) * $percentile)) - 1;
        $index = max(0, min($index, count($values) - 1));

        return $values[$index];
    }
}
