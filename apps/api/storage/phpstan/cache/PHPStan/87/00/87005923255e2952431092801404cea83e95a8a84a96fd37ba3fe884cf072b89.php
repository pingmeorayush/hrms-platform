<?php declare(strict_types = 1);

// odsl-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Models/LearningAssignmentTarget.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\LearningAssignmentTarget
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.1-8.4.4-023e0528d1bc551c9e65117abcc6c3df1994592f2cf73cd584ef44ff9367f732',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\LearningAssignmentTarget',
        'filename' => '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Models/LearningAssignmentTarget.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\LearningAssignmentTarget',
    'shortName' => 'LearningAssignmentTarget',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property int $id
 * @property int $company_id
 * @property int $learning_assignment_id
 * @property int $learning_item_id
 * @property int $employee_id
 * @property int $assigned_by_user_id
 * @property Carbon|null $assigned_on
 * @property Carbon|null $due_on
 * @property string $status
 * @property int $completion_progress_percent
 * @property Carbon|null $completed_at
 * @property int|null $completed_by_user_id
 * @property string|null $completion_notes
 * @property array<string, mixed>|null $completion_evidence
 * @property Carbon|null $renewal_due_on
 * @property-read Company|null $company
 * @property-read LearningAssignment|null $assignment
 * @property-read LearningItem|null $item
 * @property-read Employee|null $employee
 * @property-read User|null $assignedBy
 * @property-read User|null $completedBy
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
            'code' => '[\'company_id\', \'learning_assignment_id\', \'learning_item_id\', \'employee_id\', \'assigned_by_user_id\', \'assigned_on\', \'due_on\', \'status\', \'completion_progress_percent\', \'completed_at\', \'completed_by_user_id\', \'completion_notes\', \'completion_evidence\', \'renewal_due_on\']',
            'attributes' => 
            array (
              'startLine' => 34,
              'endLine' => 49,
              'startTokenPos' => 37,
              'startFilePos' => 1114,
              'endTokenPos' => 81,
              'endFilePos' => 1437,
            ),
          ),
        ),
      ),
    ),
    'startLine' => 34,
    'endLine' => 113,
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
        'startLine' => 57,
        'endLine' => 60,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\LearningAssignmentTarget',
        'implementingClassName' => 'App\\Models\\LearningAssignmentTarget',
        'currentClassName' => 'App\\Models\\LearningAssignmentTarget',
        'aliasName' => NULL,
      ),
      'assignment' => 
      array (
        'name' => 'assignment',
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
 * @return BelongsTo<LearningAssignment, $this>
 */',
        'startLine' => 65,
        'endLine' => 68,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\LearningAssignmentTarget',
        'implementingClassName' => 'App\\Models\\LearningAssignmentTarget',
        'currentClassName' => 'App\\Models\\LearningAssignmentTarget',
        'aliasName' => NULL,
      ),
      'item' => 
      array (
        'name' => 'item',
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
 * @return BelongsTo<LearningItem, $this>
 */',
        'startLine' => 73,
        'endLine' => 76,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\LearningAssignmentTarget',
        'implementingClassName' => 'App\\Models\\LearningAssignmentTarget',
        'currentClassName' => 'App\\Models\\LearningAssignmentTarget',
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
        'startLine' => 81,
        'endLine' => 84,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\LearningAssignmentTarget',
        'implementingClassName' => 'App\\Models\\LearningAssignmentTarget',
        'currentClassName' => 'App\\Models\\LearningAssignmentTarget',
        'aliasName' => NULL,
      ),
      'assignedBy' => 
      array (
        'name' => 'assignedBy',
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
        'startLine' => 89,
        'endLine' => 92,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\LearningAssignmentTarget',
        'implementingClassName' => 'App\\Models\\LearningAssignmentTarget',
        'currentClassName' => 'App\\Models\\LearningAssignmentTarget',
        'aliasName' => NULL,
      ),
      'completedBy' => 
      array (
        'name' => 'completedBy',
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
        'startLine' => 97,
        'endLine' => 100,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\LearningAssignmentTarget',
        'implementingClassName' => 'App\\Models\\LearningAssignmentTarget',
        'currentClassName' => 'App\\Models\\LearningAssignmentTarget',
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
        'startLine' => 102,
        'endLine' => 112,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\LearningAssignmentTarget',
        'implementingClassName' => 'App\\Models\\LearningAssignmentTarget',
        'currentClassName' => 'App\\Models\\LearningAssignmentTarget',
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