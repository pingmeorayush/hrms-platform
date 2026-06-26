<?php declare(strict_types = 1);

// odsl-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Models/AttendancePolicy.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\AttendancePolicy
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.1-8.4.4-9c9645440fe6b0deded395ff9f4fc5d5017eeb381642e3a2de86173444ee32a0',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\AttendancePolicy',
        'filename' => '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Models/AttendancePolicy.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\AttendancePolicy',
    'shortName' => 'AttendancePolicy',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property int|null $working_hours_minutes
 * @property int|null $grace_minutes
 * @property int|null $late_after_minutes
 * @property int|null $half_day_minutes
 * @property bool $overtime_eligible
 * @property int|null $overtime_after_minutes
 * @property array<string, mixed>|null $weekend_rule
 * @property bool $work_from_home_allowed
 * @property bool $enforce_geofence
 * @property int|null $allowed_radius_meters
 * @property string $status
 * @property-read Company|null $company
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
            'code' => '[\'company_id\', \'name\', \'working_hours_minutes\', \'grace_minutes\', \'late_after_minutes\', \'half_day_minutes\', \'overtime_eligible\', \'overtime_after_minutes\', \'weekend_rule\', \'work_from_home_allowed\', \'enforce_geofence\', \'allowed_radius_meters\', \'status\']',
            'attributes' => 
            array (
              'startLine' => 27,
              'endLine' => 41,
              'startTokenPos' => 32,
              'startFilePos' => 825,
              'endTokenPos' => 73,
              'endFilePos' => 1129,
            ),
          ),
        ),
      ),
    ),
    'startLine' => 27,
    'endLine' => 63,
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
        'startLine' => 49,
        'endLine' => 52,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\AttendancePolicy',
        'implementingClassName' => 'App\\Models\\AttendancePolicy',
        'currentClassName' => 'App\\Models\\AttendancePolicy',
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
        'startLine' => 54,
        'endLine' => 62,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\AttendancePolicy',
        'implementingClassName' => 'App\\Models\\AttendancePolicy',
        'currentClassName' => 'App\\Models\\AttendancePolicy',
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