<?php declare(strict_types = 1);

// odsl-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Models/AttendanceCorrection.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\AttendanceCorrection
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.1-8.4.4-294dbcb334c9ba60c351e7e3f198c4815267f7083b15ee56ce6d5bdee274d2f9',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\AttendanceCorrection',
        'filename' => '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Models/AttendanceCorrection.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\AttendanceCorrection',
    'shortName' => 'AttendanceCorrection',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property int $id
 * @property int $company_id
 * @property int $attendance_record_id
 * @property int $employee_id
 * @property int|null $workflow_instance_id
 * @property int $requested_by_user_id
 * @property int|null $latest_action_by_user_id
 * @property string $status
 * @property string $reason
 * @property array<string, mixed>|null $original_values
 * @property array<string, string>|null $corrected_values
 * @property array<string, mixed>|null $applied_values
 * @property string|null $decision_comment
 * @property Carbon|null $approved_at
 * @property Carbon|null $rejected_at
 * @property-read AttendanceRecord|null $attendanceRecord
 * @property-read Employee|null $employee
 * @property-read WorkflowInstance|null $workflowInstance
 * @property-read User|null $requester
 * @property-read User|null $latestActor
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
            'code' => '[\'company_id\', \'attendance_record_id\', \'employee_id\', \'workflow_instance_id\', \'requested_by_user_id\', \'latest_action_by_user_id\', \'status\', \'reason\', \'original_values\', \'corrected_values\', \'applied_values\', \'decision_comment\', \'approved_at\', \'rejected_at\']',
            'attributes' => 
            array (
              'startLine' => 33,
              'endLine' => 48,
              'startTokenPos' => 37,
              'startFilePos' => 1121,
              'endTokenPos' => 81,
              'endFilePos' => 1435,
            ),
          ),
        ),
      ),
    ),
    'startLine' => 33,
    'endLine' => 103,
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
        'startLine' => 53,
        'endLine' => 62,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\AttendanceCorrection',
        'implementingClassName' => 'App\\Models\\AttendanceCorrection',
        'currentClassName' => 'App\\Models\\AttendanceCorrection',
        'aliasName' => NULL,
      ),
      'attendanceRecord' => 
      array (
        'name' => 'attendanceRecord',
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
 * @return BelongsTo<AttendanceRecord, $this>
 */',
        'startLine' => 67,
        'endLine' => 70,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\AttendanceCorrection',
        'implementingClassName' => 'App\\Models\\AttendanceCorrection',
        'currentClassName' => 'App\\Models\\AttendanceCorrection',
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
        'startLine' => 75,
        'endLine' => 78,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\AttendanceCorrection',
        'implementingClassName' => 'App\\Models\\AttendanceCorrection',
        'currentClassName' => 'App\\Models\\AttendanceCorrection',
        'aliasName' => NULL,
      ),
      'workflowInstance' => 
      array (
        'name' => 'workflowInstance',
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
 * @return BelongsTo<WorkflowInstance, $this>
 */',
        'startLine' => 83,
        'endLine' => 86,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\AttendanceCorrection',
        'implementingClassName' => 'App\\Models\\AttendanceCorrection',
        'currentClassName' => 'App\\Models\\AttendanceCorrection',
        'aliasName' => NULL,
      ),
      'requester' => 
      array (
        'name' => 'requester',
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
        'startLine' => 91,
        'endLine' => 94,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\AttendanceCorrection',
        'implementingClassName' => 'App\\Models\\AttendanceCorrection',
        'currentClassName' => 'App\\Models\\AttendanceCorrection',
        'aliasName' => NULL,
      ),
      'latestActor' => 
      array (
        'name' => 'latestActor',
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
        'startLine' => 99,
        'endLine' => 102,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\AttendanceCorrection',
        'implementingClassName' => 'App\\Models\\AttendanceCorrection',
        'currentClassName' => 'App\\Models\\AttendanceCorrection',
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