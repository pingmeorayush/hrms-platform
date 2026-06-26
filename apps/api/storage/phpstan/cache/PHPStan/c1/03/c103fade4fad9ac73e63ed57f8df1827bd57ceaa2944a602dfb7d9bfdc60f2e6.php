<?php declare(strict_types = 1);

// odsl-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Models/AttendanceRecord.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\AttendanceRecord
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.1-8.4.4-ff15c92c28b521517454fd4454928f6cdfa5090ce7227b77d5be37180e21245b',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\AttendanceRecord',
        'filename' => '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Models/AttendanceRecord.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\AttendanceRecord',
    'shortName' => 'AttendanceRecord',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property int $id
 * @property int $company_id
 * @property int $employee_id
 * @property int|null $shift_id
 * @property int|null $shift_roster_id
 * @property Carbon|null $attendance_date
 * @property Carbon|null $check_in_at
 * @property array<string, mixed>|null $check_in_metadata
 * @property Carbon|null $check_out_at
 * @property array<string, mixed>|null $check_out_metadata
 * @property Carbon|null $scheduled_start_at
 * @property Carbon|null $scheduled_end_at
 * @property Carbon|null $calculated_at
 * @property string|null $primary_status
 * @property-read Employee $employee
 * @property-read Shift|null $shift
 * @property-read ShiftRoster|null $shiftRoster
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
            'code' => '[\'company_id\', \'employee_id\', \'shift_id\', \'shift_roster_id\', \'attendance_date\', \'check_in_at\', \'check_in_channel\', \'check_in_ip_address\', \'check_in_user_agent\', \'check_in_metadata\', \'check_out_at\', \'check_out_channel\', \'check_out_ip_address\', \'check_out_user_agent\', \'check_out_metadata\', \'worked_minutes\', \'primary_status\', \'scheduled_start_at\', \'scheduled_end_at\', \'scheduled_work_minutes\', \'break_duration_minutes\', \'is_late\', \'late_minutes\', \'is_half_day\', \'overtime_minutes\', \'is_weekend\', \'is_holiday\', \'holiday_name\', \'is_early_departure\', \'early_departure_minutes\', \'calculated_at\', \'calculation_metadata\', \'created_by_user_id\', \'updated_by_user_id\']',
            'attributes' => 
            array (
              'startLine' => 31,
              'endLine' => 66,
              'startTokenPos' => 42,
              'startFilePos' => 1018,
              'endTokenPos' => 146,
              'endFilePos' => 1814,
            ),
          ),
        ),
      ),
    ),
    'startLine' => 31,
    'endLine' => 146,
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
        'startLine' => 74,
        'endLine' => 77,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\AttendanceRecord',
        'implementingClassName' => 'App\\Models\\AttendanceRecord',
        'currentClassName' => 'App\\Models\\AttendanceRecord',
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
        'startLine' => 82,
        'endLine' => 85,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\AttendanceRecord',
        'implementingClassName' => 'App\\Models\\AttendanceRecord',
        'currentClassName' => 'App\\Models\\AttendanceRecord',
        'aliasName' => NULL,
      ),
      'shift' => 
      array (
        'name' => 'shift',
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
 * @return BelongsTo<Shift, $this>
 */',
        'startLine' => 90,
        'endLine' => 93,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\AttendanceRecord',
        'implementingClassName' => 'App\\Models\\AttendanceRecord',
        'currentClassName' => 'App\\Models\\AttendanceRecord',
        'aliasName' => NULL,
      ),
      'shiftRoster' => 
      array (
        'name' => 'shiftRoster',
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
 * @return BelongsTo<ShiftRoster, $this>
 */',
        'startLine' => 98,
        'endLine' => 101,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\AttendanceRecord',
        'implementingClassName' => 'App\\Models\\AttendanceRecord',
        'currentClassName' => 'App\\Models\\AttendanceRecord',
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
        'startLine' => 106,
        'endLine' => 109,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\AttendanceRecord',
        'implementingClassName' => 'App\\Models\\AttendanceRecord',
        'currentClassName' => 'App\\Models\\AttendanceRecord',
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
        'startLine' => 114,
        'endLine' => 117,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\AttendanceRecord',
        'implementingClassName' => 'App\\Models\\AttendanceRecord',
        'currentClassName' => 'App\\Models\\AttendanceRecord',
        'aliasName' => NULL,
      ),
      'corrections' => 
      array (
        'name' => 'corrections',
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
 * @return HasMany<AttendanceCorrection, $this>
 */',
        'startLine' => 122,
        'endLine' => 125,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\AttendanceRecord',
        'implementingClassName' => 'App\\Models\\AttendanceRecord',
        'currentClassName' => 'App\\Models\\AttendanceRecord',
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
        'startLine' => 127,
        'endLine' => 145,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\AttendanceRecord',
        'implementingClassName' => 'App\\Models\\AttendanceRecord',
        'currentClassName' => 'App\\Models\\AttendanceRecord',
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