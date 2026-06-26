<?php declare(strict_types = 1);

// odsl-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Models/Payslip.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\Payslip
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.1-8.4.4-af07a8594f7d56796f6a79b8013645adff8ed4654b054ed3e4bf55886267a40d',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\Payslip',
        'filename' => '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Models/Payslip.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\Payslip',
    'shortName' => 'Payslip',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property int $id
 * @property int $company_id
 * @property int $payroll_run_id
 * @property int $payroll_period_id
 * @property int $payroll_item_id
 * @property int $employee_id
 * @property int $employee_compensation_id
 * @property string $slip_number
 * @property string $status
 * @property string $currency
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property Carbon|null $payroll_date
 * @property string $file_name
 * @property float|string $gross_salary
 * @property float|string $total_earnings
 * @property float|string $total_deductions
 * @property float|string $net_salary
 * @property float|string $employer_cost
 * @property array<int, array<string, mixed>>|null $earnings_breakdown
 * @property array<int, array<string, mixed>>|null $deductions_breakdown
 * @property array<int, array<string, mixed>>|null $employer_contribution_breakdown
 * @property array<string, mixed>|null $employee_snapshot
 * @property array<string, mixed>|null $company_snapshot
 * @property string $rendered_format
 * @property string $rendered_content
 * @property string $checksum_sha256
 * @property Carbon|null $generated_at
 * @property-read Company|null $company
 * @property-read PayrollRun|null $payrollRun
 * @property-read PayrollPeriod|null $payrollPeriod
 * @property-read PayrollItem|null $payrollItem
 * @property-read Employee|null $employee
 * @property-read EmployeeCompensation|null $employeeCompensation
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */',
    'attributes' => 
    array (
      0 => 
      array (
        'name' => 'Illuminate\\Database\\Eloquent\\Attributes\\Fillable',
        'isRepeated' => false,
        'arguments' => 
        array (
          0 => 
          array (
            'code' => '[\'company_id\', \'payroll_run_id\', \'payroll_period_id\', \'payroll_item_id\', \'employee_id\', \'employee_compensation_id\', \'slip_number\', \'status\', \'currency\', \'start_date\', \'end_date\', \'payroll_date\', \'file_name\', \'gross_salary\', \'total_earnings\', \'total_deductions\', \'net_salary\', \'employer_cost\', \'earnings_breakdown\', \'deductions_breakdown\', \'employer_contribution_breakdown\', \'employee_snapshot\', \'company_snapshot\', \'rendered_format\', \'rendered_content\', \'checksum_sha256\', \'generated_at\', \'created_by_user_id\', \'updated_by_user_id\']',
            'attributes' => 
            array (
              'startLine' => 49,
              'endLine' => 79,
              'startTokenPos' => 37,
              'startFilePos' => 1819,
              'endTokenPos' => 126,
              'endFilePos' => 2469,
            ),
          ),
        ),
      ),
    ),
    'startLine' => 49,
    'endLine' => 167,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Illuminate\\Database\\Eloquent\\Model',
    'implementsClassNames' => 
    array (
    ),
    'traitClassNames' => 
    array (
      0 => 'App\\Modules\\Platform\\Tenancy\\Concerns\\BelongsToCompany',
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
    ),
    'immediateMethods' => 
    array (
      'company' => 
      array (
        'name' => 'company',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @return BelongsTo<Company, $this>
 */',
        'startLine' => 87,
        'endLine' => 90,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Payslip',
        'implementingClassName' => 'App\\Models\\Payslip',
        'currentClassName' => 'App\\Models\\Payslip',
        'aliasName' => NULL,
      ),
      'payrollRun' => 
      array (
        'name' => 'payrollRun',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @return BelongsTo<PayrollRun, $this>
 */',
        'startLine' => 95,
        'endLine' => 98,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Payslip',
        'implementingClassName' => 'App\\Models\\Payslip',
        'currentClassName' => 'App\\Models\\Payslip',
        'aliasName' => NULL,
      ),
      'payrollPeriod' => 
      array (
        'name' => 'payrollPeriod',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @return BelongsTo<PayrollPeriod, $this>
 */',
        'startLine' => 103,
        'endLine' => 106,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Payslip',
        'implementingClassName' => 'App\\Models\\Payslip',
        'currentClassName' => 'App\\Models\\Payslip',
        'aliasName' => NULL,
      ),
      'payrollItem' => 
      array (
        'name' => 'payrollItem',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @return BelongsTo<PayrollItem, $this>
 */',
        'startLine' => 111,
        'endLine' => 114,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Payslip',
        'implementingClassName' => 'App\\Models\\Payslip',
        'currentClassName' => 'App\\Models\\Payslip',
        'aliasName' => NULL,
      ),
      'employee' => 
      array (
        'name' => 'employee',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @return BelongsTo<Employee, $this>
 */',
        'startLine' => 119,
        'endLine' => 122,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Payslip',
        'implementingClassName' => 'App\\Models\\Payslip',
        'currentClassName' => 'App\\Models\\Payslip',
        'aliasName' => NULL,
      ),
      'employeeCompensation' => 
      array (
        'name' => 'employeeCompensation',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @return BelongsTo<EmployeeCompensation, $this>
 */',
        'startLine' => 127,
        'endLine' => 130,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Payslip',
        'implementingClassName' => 'App\\Models\\Payslip',
        'currentClassName' => 'App\\Models\\Payslip',
        'aliasName' => NULL,
      ),
      'createdBy' => 
      array (
        'name' => 'createdBy',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @return BelongsTo<User, $this>
 */',
        'startLine' => 135,
        'endLine' => 138,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Payslip',
        'implementingClassName' => 'App\\Models\\Payslip',
        'currentClassName' => 'App\\Models\\Payslip',
        'aliasName' => NULL,
      ),
      'updatedBy' => 
      array (
        'name' => 'updatedBy',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @return BelongsTo<User, $this>
 */',
        'startLine' => 143,
        'endLine' => 146,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Payslip',
        'implementingClassName' => 'App\\Models\\Payslip',
        'currentClassName' => 'App\\Models\\Payslip',
        'aliasName' => NULL,
      ),
      'casts' => 
      array (
        'name' => 'casts',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 148,
        'endLine' => 166,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Payslip',
        'implementingClassName' => 'App\\Models\\Payslip',
        'currentClassName' => 'App\\Models\\Payslip',
        'aliasName' => NULL,
      ),
    ),
    'traitsData' => 
    array (
      'aliases' => 
      array (
      ),
      'modifiers' => 
      array (
      ),
      'precedences' => 
      array (
      ),
      'hashes' => 
      array (
      ),
    ),
  ),
));