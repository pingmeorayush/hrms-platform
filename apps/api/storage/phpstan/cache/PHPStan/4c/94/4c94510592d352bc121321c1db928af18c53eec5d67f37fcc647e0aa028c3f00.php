<?php declare(strict_types = 1);

// odsl-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Models/RecruitmentHireHandoff.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\RecruitmentHireHandoff
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.1-8.4.4-3e82b57c8345790ed3402294bfcec951d909cd6102935359e3ec05563feec105',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\RecruitmentHireHandoff',
        'filename' => '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Models/RecruitmentHireHandoff.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\RecruitmentHireHandoff',
    'shortName' => 'RecruitmentHireHandoff',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property int $id
 * @property int $company_id
 * @property int $job_requisition_id
 * @property int $candidate_id
 * @property int $offer_id
 * @property int $employee_id
 * @property int|null $recruiter_user_id
 * @property int|null $converted_by_user_id
 * @property int|null $source_resume_id
 * @property string $status
 * @property array<string, mixed>|null $offer_snapshot
 * @property array<string, mixed>|null $candidate_snapshot
 * @property array<string, mixed>|null $requisition_snapshot
 * @property array<int, mixed>|null $document_references
 * @property array<int, int>|null $onboarding_template_ids
 * @property array<int, int>|null $onboarding_task_ids
 * @property string|null $notes
 * @property Carbon|null $converted_at
 * @property Carbon|null $onboarding_triggered_at
 * @property-read JobRequisition|null $requisition
 * @property-read Candidate|null $candidate
 * @property-read Offer|null $offer
 * @property-read Employee|null $employee
 * @property-read User|null $recruiter
 * @property-read User|null $convertedBy
 * @property-read CandidateResume|null $sourceResume
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
            'code' => '[\'company_id\', \'job_requisition_id\', \'candidate_id\', \'offer_id\', \'employee_id\', \'recruiter_user_id\', \'converted_by_user_id\', \'source_resume_id\', \'status\', \'offer_snapshot\', \'candidate_snapshot\', \'requisition_snapshot\', \'document_references\', \'onboarding_template_ids\', \'onboarding_task_ids\', \'notes\', \'converted_at\', \'onboarding_triggered_at\']',
            'attributes' => 
            array (
              'startLine' => 39,
              'endLine' => 58,
              'startTokenPos' => 37,
              'startFilePos' => 1390,
              'endTokenPos' => 93,
              'endFilePos' => 1807,
            ),
          ),
        ),
      ),
    ),
    'startLine' => 39,
    'endLine' => 132,
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
      'requisition' => 
      array (
        'name' => 'requisition',
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
 * @return BelongsTo<JobRequisition, $this>
 */',
        'startLine' => 66,
        'endLine' => 69,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\RecruitmentHireHandoff',
        'implementingClassName' => 'App\\Models\\RecruitmentHireHandoff',
        'currentClassName' => 'App\\Models\\RecruitmentHireHandoff',
        'aliasName' => NULL,
      ),
      'candidate' => 
      array (
        'name' => 'candidate',
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
 * @return BelongsTo<Candidate, $this>
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
        'declaringClassName' => 'App\\Models\\RecruitmentHireHandoff',
        'implementingClassName' => 'App\\Models\\RecruitmentHireHandoff',
        'currentClassName' => 'App\\Models\\RecruitmentHireHandoff',
        'aliasName' => NULL,
      ),
      'offer' => 
      array (
        'name' => 'offer',
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
 * @return BelongsTo<Offer, $this>
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
        'declaringClassName' => 'App\\Models\\RecruitmentHireHandoff',
        'implementingClassName' => 'App\\Models\\RecruitmentHireHandoff',
        'currentClassName' => 'App\\Models\\RecruitmentHireHandoff',
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
        'declaringClassName' => 'App\\Models\\RecruitmentHireHandoff',
        'implementingClassName' => 'App\\Models\\RecruitmentHireHandoff',
        'currentClassName' => 'App\\Models\\RecruitmentHireHandoff',
        'aliasName' => NULL,
      ),
      'recruiter' => 
      array (
        'name' => 'recruiter',
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
        'declaringClassName' => 'App\\Models\\RecruitmentHireHandoff',
        'implementingClassName' => 'App\\Models\\RecruitmentHireHandoff',
        'currentClassName' => 'App\\Models\\RecruitmentHireHandoff',
        'aliasName' => NULL,
      ),
      'convertedBy' => 
      array (
        'name' => 'convertedBy',
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
        'declaringClassName' => 'App\\Models\\RecruitmentHireHandoff',
        'implementingClassName' => 'App\\Models\\RecruitmentHireHandoff',
        'currentClassName' => 'App\\Models\\RecruitmentHireHandoff',
        'aliasName' => NULL,
      ),
      'sourceResume' => 
      array (
        'name' => 'sourceResume',
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
 * @return BelongsTo<CandidateResume, $this>
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
        'declaringClassName' => 'App\\Models\\RecruitmentHireHandoff',
        'implementingClassName' => 'App\\Models\\RecruitmentHireHandoff',
        'currentClassName' => 'App\\Models\\RecruitmentHireHandoff',
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
        'startLine' => 119,
        'endLine' => 131,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\RecruitmentHireHandoff',
        'implementingClassName' => 'App\\Models\\RecruitmentHireHandoff',
        'currentClassName' => 'App\\Models\\RecruitmentHireHandoff',
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