<?php declare(strict_types = 1);

// odsl-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Models/LeaveBalance.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\LeaveBalance
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.1-8.4.4-3554fba04e0f4bc4f1bc9837de153c429fe4d42486e668a7c0734812fc28de88',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\LeaveBalance',
        'filename' => '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Models/LeaveBalance.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\LeaveBalance',
    'shortName' => 'LeaveBalance',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property int $id
 * @property int $company_id
 * @property int $employee_id
 * @property int $leave_type_id
 * @property int $leave_policy_id
 * @property int $policy_version
 * @property float $available_days
 * @property float $booked_days
 * @property float $used_days
 * @property float $accrued_days
 * @property float $carry_forward_days
 * @property float $projected_encashable_days
 * @property Carbon|null $current_period_start
 * @property Carbon|null $current_period_end
 * @property string|null $last_calculation_hash
 * @property string $status
 * @property-read Company|null $company
 * @property-read Employee|null $employee
 * @property-read LeaveType|null $leaveType
 * @property-read LeavePolicy|null $leavePolicy
 * @property-read EloquentCollection<int, LeaveBalanceEntry> $entries
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
            'code' => '[\'company_id\', \'employee_id\', \'leave_type_id\', \'leave_policy_id\', \'policy_version\', \'available_days\', \'booked_days\', \'used_days\', \'accrued_days\', \'carry_forward_days\', \'projected_encashable_days\', \'current_period_start\', \'current_period_end\', \'last_calculation_hash\', \'status\']',
            'attributes' => 
            array (
              'startLine' => 36,
              'endLine' => 52,
              'startTokenPos' => 51,
              'startFilePos' => 1214,
              'endTokenPos' => 98,
              'endFilePos' => 1553,
            ),
          ),
        ),
      ),
    ),
    'startLine' => 36,
    'endLine' => 110,
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
        'startLine' => 60,
        'endLine' => 63,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\LeaveBalance',
        'implementingClassName' => 'App\\Models\\LeaveBalance',
        'currentClassName' => 'App\\Models\\LeaveBalance',
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
        'startLine' => 68,
        'endLine' => 71,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\LeaveBalance',
        'implementingClassName' => 'App\\Models\\LeaveBalance',
        'currentClassName' => 'App\\Models\\LeaveBalance',
        'aliasName' => NULL,
      ),
      'leaveType' => 
      array (
        'name' => 'leaveType',
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
 * @return BelongsTo<LeaveType, $this>
 */',
        'startLine' => 76,
        'endLine' => 79,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\LeaveBalance',
        'implementingClassName' => 'App\\Models\\LeaveBalance',
        'currentClassName' => 'App\\Models\\LeaveBalance',
        'aliasName' => NULL,
      ),
      'leavePolicy' => 
      array (
        'name' => 'leavePolicy',
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
 * @return BelongsTo<LeavePolicy, $this>
 */',
        'startLine' => 84,
        'endLine' => 87,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\LeaveBalance',
        'implementingClassName' => 'App\\Models\\LeaveBalance',
        'currentClassName' => 'App\\Models\\LeaveBalance',
        'aliasName' => NULL,
      ),
      'entries' => 
      array (
        'name' => 'entries',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @return HasMany<LeaveBalanceEntry, $this>
 */',
        'startLine' => 92,
        'endLine' => 95,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\LeaveBalance',
        'implementingClassName' => 'App\\Models\\LeaveBalance',
        'currentClassName' => 'App\\Models\\LeaveBalance',
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
        'startLine' => 97,
        'endLine' => 109,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\LeaveBalance',
        'implementingClassName' => 'App\\Models\\LeaveBalance',
        'currentClassName' => 'App\\Models\\LeaveBalance',
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