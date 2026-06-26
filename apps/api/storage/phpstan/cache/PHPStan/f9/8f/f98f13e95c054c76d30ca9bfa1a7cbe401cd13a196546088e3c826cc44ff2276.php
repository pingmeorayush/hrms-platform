<?php declare(strict_types = 1);

// odsl-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Models/PerformanceReviewSubmission.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\PerformanceReviewSubmission
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.1-8.4.4-d761849106adfa8d26b5a931d19e24cb08b9ac638f4ac3fa0dccee1d278ff88e',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\PerformanceReviewSubmission',
        'filename' => '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Models/PerformanceReviewSubmission.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\PerformanceReviewSubmission',
    'shortName' => 'PerformanceReviewSubmission',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property int $id
 * @property int $company_id
 * @property int $performance_review_id
 * @property int $reviewer_user_id
 * @property int|null $reviewer_employee_id
 * @property string $role_type
 * @property array<string, mixed>|null $visibility_scope
 * @property array<string, mixed>|null $section_payload
 * @property array<string, mixed>|null $competency_payload
 * @property float|null $overall_rating
 * @property string|null $summary
 * @property string|null $confidential_notes
 * @property Carbon|null $submitted_at
 * @property-read Company|null $company
 * @property-read PerformanceReview|null $review
 * @property-read User|null $reviewer
 * @property-read Employee|null $reviewerEmployee
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
            'code' => '[\'company_id\', \'performance_review_id\', \'reviewer_user_id\', \'reviewer_employee_id\', \'role_type\', \'visibility_scope\', \'section_payload\', \'competency_payload\', \'overall_rating\', \'summary\', \'confidential_notes\', \'submitted_at\']',
            'attributes' => 
            array (
              'startLine' => 30,
              'endLine' => 43,
              'startTokenPos' => 37,
              'startFilePos' => 996,
              'endTokenPos' => 75,
              'endFilePos' => 1270,
            ),
          ),
        ),
      ),
    ),
    'startLine' => 30,
    'endLine' => 90,
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
        'declaringClassName' => 'App\\Models\\PerformanceReviewSubmission',
        'implementingClassName' => 'App\\Models\\PerformanceReviewSubmission',
        'currentClassName' => 'App\\Models\\PerformanceReviewSubmission',
        'aliasName' => NULL,
      ),
      'review' => 
      array (
        'name' => 'review',
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
 * @return BelongsTo<PerformanceReview, $this>
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
        'declaringClassName' => 'App\\Models\\PerformanceReviewSubmission',
        'implementingClassName' => 'App\\Models\\PerformanceReviewSubmission',
        'currentClassName' => 'App\\Models\\PerformanceReviewSubmission',
        'aliasName' => NULL,
      ),
      'reviewer' => 
      array (
        'name' => 'reviewer',
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
        'declaringClassName' => 'App\\Models\\PerformanceReviewSubmission',
        'implementingClassName' => 'App\\Models\\PerformanceReviewSubmission',
        'currentClassName' => 'App\\Models\\PerformanceReviewSubmission',
        'aliasName' => NULL,
      ),
      'reviewerEmployee' => 
      array (
        'name' => 'reviewerEmployee',
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
        'declaringClassName' => 'App\\Models\\PerformanceReviewSubmission',
        'implementingClassName' => 'App\\Models\\PerformanceReviewSubmission',
        'currentClassName' => 'App\\Models\\PerformanceReviewSubmission',
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
        'endLine' => 89,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PerformanceReviewSubmission',
        'implementingClassName' => 'App\\Models\\PerformanceReviewSubmission',
        'currentClassName' => 'App\\Models\\PerformanceReviewSubmission',
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