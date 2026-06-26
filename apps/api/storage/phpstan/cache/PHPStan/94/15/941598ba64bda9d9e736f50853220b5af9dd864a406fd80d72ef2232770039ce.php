<?php declare(strict_types = 1);

// odsl-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Models/EmploymentHistory.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\EmploymentHistory
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.1-8.4.4-0b9182f7635366532ab4b66957ee9bf3af2b642ed4a83ac92ca80ee1fd85a238',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\EmploymentHistory',
        'filename' => '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Models/EmploymentHistory.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\EmploymentHistory',
    'shortName' => 'EmploymentHistory',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property int $id
 * @property int $company_id
 * @property int $employee_id
 * @property string $action
 * @property Carbon|null $effective_date
 * @property int|null $previous_department_id
 * @property int|null $department_id
 * @property int|null $previous_designation_id
 * @property int|null $designation_id
 * @property int|null $previous_manager_id
 * @property int|null $manager_id
 * @property int|null $previous_location_id
 * @property int|null $location_id
 * @property string|null $previous_employment_status
 * @property string $employment_status
 * @property int|null $changed_by_user_id
 * @property string|null $notes
 * @property array<string, mixed>|null $metadata
 * @property-read Employee|null $employee
 * @property-read User|null $actor
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
            'code' => '[\'company_id\', \'employee_id\', \'action\', \'effective_date\', \'previous_department_id\', \'department_id\', \'previous_designation_id\', \'designation_id\', \'previous_manager_id\', \'manager_id\', \'previous_location_id\', \'location_id\', \'previous_employment_status\', \'employment_status\', \'changed_by_user_id\', \'notes\', \'metadata\']',
            'attributes' => 
            array (
              'startLine' => 33,
              'endLine' => 51,
              'startTokenPos' => 37,
              'startFilePos' => 1054,
              'endTokenPos' => 90,
              'endFilePos' => 1439,
            ),
          ),
        ),
      ),
    ),
    'startLine' => 33,
    'endLine' => 79,
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
        'startLine' => 59,
        'endLine' => 62,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\EmploymentHistory',
        'implementingClassName' => 'App\\Models\\EmploymentHistory',
        'currentClassName' => 'App\\Models\\EmploymentHistory',
        'aliasName' => NULL,
      ),
      'actor' => 
      array (
        'name' => 'actor',
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
        'declaringClassName' => 'App\\Models\\EmploymentHistory',
        'implementingClassName' => 'App\\Models\\EmploymentHistory',
        'currentClassName' => 'App\\Models\\EmploymentHistory',
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
        'startLine' => 72,
        'endLine' => 78,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\EmploymentHistory',
        'implementingClassName' => 'App\\Models\\EmploymentHistory',
        'currentClassName' => 'App\\Models\\EmploymentHistory',
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