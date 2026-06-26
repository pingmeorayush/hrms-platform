<?php declare(strict_types = 1);

// odsl-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Models/PolicyAcknowledgement.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\PolicyAcknowledgement
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.1-8.4.4-051476b23335a3952a7605ec443d27f7ba49f1f44efb45a64cdc18700a6fb426',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\PolicyAcknowledgement',
        'filename' => '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Models/PolicyAcknowledgement.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\PolicyAcknowledgement',
    'shortName' => 'PolicyAcknowledgement',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property int $id
 * @property int $company_id
 * @property int $document_id
 * @property int $employee_id
 * @property string $policy_title
 * @property string $policy_version
 * @property string $status
 * @property int|null $assigned_by_user_id
 * @property Carbon|null $due_date
 * @property string|null $assignment_notes
 * @property Carbon|null $acknowledged_at
 * @property int|null $acknowledged_by_user_id
 * @property string|null $acknowledgement_notes
 * @property-read Document|null $document
 * @property-read Employee|null $employee
 * @property-read User|null $assignedBy
 * @property-read User|null $acknowledgedBy
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
            'code' => '[\'company_id\', \'document_id\', \'employee_id\', \'policy_title\', \'policy_version\', \'status\', \'assigned_by_user_id\', \'due_date\', \'assignment_notes\', \'acknowledged_at\', \'acknowledged_by_user_id\', \'acknowledgement_notes\']',
            'attributes' => 
            array (
              'startLine' => 30,
              'endLine' => 43,
              'startTokenPos' => 37,
              'startFilePos' => 923,
              'endTokenPos' => 75,
              'endFilePos' => 1187,
            ),
          ),
        ),
      ),
    ),
    'startLine' => 30,
    'endLine' => 87,
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
      'document' => 
      array (
        'name' => 'document',
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
 * @return BelongsTo<Document, $this>
 */',
        'startLine' => 51,
        'endLine' => 54,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PolicyAcknowledgement',
        'implementingClassName' => 'App\\Models\\PolicyAcknowledgement',
        'currentClassName' => 'App\\Models\\PolicyAcknowledgement',
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
        'declaringClassName' => 'App\\Models\\PolicyAcknowledgement',
        'implementingClassName' => 'App\\Models\\PolicyAcknowledgement',
        'currentClassName' => 'App\\Models\\PolicyAcknowledgement',
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
        'declaringClassName' => 'App\\Models\\PolicyAcknowledgement',
        'implementingClassName' => 'App\\Models\\PolicyAcknowledgement',
        'currentClassName' => 'App\\Models\\PolicyAcknowledgement',
        'aliasName' => NULL,
      ),
      'acknowledgedBy' => 
      array (
        'name' => 'acknowledgedBy',
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
        'declaringClassName' => 'App\\Models\\PolicyAcknowledgement',
        'implementingClassName' => 'App\\Models\\PolicyAcknowledgement',
        'currentClassName' => 'App\\Models\\PolicyAcknowledgement',
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
        'startLine' => 80,
        'endLine' => 86,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PolicyAcknowledgement',
        'implementingClassName' => 'App\\Models\\PolicyAcknowledgement',
        'currentClassName' => 'App\\Models\\PolicyAcknowledgement',
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