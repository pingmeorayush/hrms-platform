<?php declare(strict_types = 1);

// odsl-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/RecruitmentManagement/Services/OfferAccessScopeService.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Modules\RecruitmentManagement\Services\OfferAccessScopeService
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.1-8.4.4-93e8a63f6e42fef5098500136aefbbbed8cda93ff328eee3fc8b8144a8a99a87',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Modules\\RecruitmentManagement\\Services\\OfferAccessScopeService',
        'filename' => '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/RecruitmentManagement/Services/OfferAccessScopeService.php',
      ),
    ),
    'namespace' => 'App\\Modules\\RecruitmentManagement\\Services',
    'name' => 'App\\Modules\\RecruitmentManagement\\Services\\OfferAccessScopeService',
    'shortName' => 'OfferAccessScopeService',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 10,
    'endLine' => 65,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => NULL,
    'implementsClassNames' => 
    array (
    ),
    'traitClassNames' => 
    array (
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
    ),
    'immediateMethods' => 
    array (
      'offersQuery' => 
      array (
        'name' => 'offersQuery',
        'parameters' => 
        array (
          'actor' => 
          array (
            'name' => 'actor',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\User',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 17,
            'endLine' => 17,
            'startColumn' => 9,
            'endColumn' => 19,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'with' => 
          array (
            'name' => 'with',
            'default' => 
            array (
              'code' => '[\'candidate.recruiter\', \'requisition\', \'recruiter\', \'requestedBy\', \'handoff.employee\', \'workflowInstance.tasks.assignee\', \'workflowInstance.tasks.actor\', \'decisions.actor\']',
              'attributes' => 
              array (
                'startLine' => 18,
                'endLine' => 27,
                'startTokenPos' => 53,
                'startFilePos' => 368,
                'endTokenPos' => 79,
                'endFilePos' => 646,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 18,
            'endLine' => 27,
            'startColumn' => 9,
            'endColumn' => 9,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Builder',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  array<int, string>  $with
 * @return Builder<Offer>
 */',
        'startLine' => 16,
        'endLine' => 52,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\RecruitmentManagement\\Services',
        'declaringClassName' => 'App\\Modules\\RecruitmentManagement\\Services\\OfferAccessScopeService',
        'implementingClassName' => 'App\\Modules\\RecruitmentManagement\\Services\\OfferAccessScopeService',
        'currentClassName' => 'App\\Modules\\RecruitmentManagement\\Services\\OfferAccessScopeService',
        'aliasName' => NULL,
      ),
      'resolveAccessibleOffer' => 
      array (
        'name' => 'resolveAccessibleOffer',
        'parameters' => 
        array (
          'actor' => 
          array (
            'name' => 'actor',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\User',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 54,
            'endLine' => 54,
            'startColumn' => 44,
            'endColumn' => 54,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'offerId' => 
          array (
            'name' => 'offerId',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 54,
            'endLine' => 54,
            'startColumn' => 57,
            'endColumn' => 68,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'App\\Models\\Offer',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 54,
        'endLine' => 57,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\RecruitmentManagement\\Services',
        'declaringClassName' => 'App\\Modules\\RecruitmentManagement\\Services\\OfferAccessScopeService',
        'implementingClassName' => 'App\\Modules\\RecruitmentManagement\\Services\\OfferAccessScopeService',
        'currentClassName' => 'App\\Modules\\RecruitmentManagement\\Services\\OfferAccessScopeService',
        'aliasName' => NULL,
      ),
      'findLinkedEmployee' => 
      array (
        'name' => 'findLinkedEmployee',
        'parameters' => 
        array (
          'actor' => 
          array (
            'name' => 'actor',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\User',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 59,
            'endLine' => 59,
            'startColumn' => 41,
            'endColumn' => 51,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
          'data' => 
          array (
            'types' => 
            array (
              0 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'App\\Models\\Employee',
                  'isIdentifier' => false,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 59,
        'endLine' => 64,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\RecruitmentManagement\\Services',
        'declaringClassName' => 'App\\Modules\\RecruitmentManagement\\Services\\OfferAccessScopeService',
        'implementingClassName' => 'App\\Modules\\RecruitmentManagement\\Services\\OfferAccessScopeService',
        'currentClassName' => 'App\\Modules\\RecruitmentManagement\\Services\\OfferAccessScopeService',
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