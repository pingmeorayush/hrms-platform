<?php declare(strict_types = 1);

// odsl-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/RecruitmentManagement/Services/RecruitmentHireHandoffAccessScopeService.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Modules\RecruitmentManagement\Services\RecruitmentHireHandoffAccessScopeService
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.1-8.4.4-9e2f815c753802e0eb521b733ae794b77a5cec8a355da0769a92a257f5920135',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Modules\\RecruitmentManagement\\Services\\RecruitmentHireHandoffAccessScopeService',
        'filename' => '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/RecruitmentManagement/Services/RecruitmentHireHandoffAccessScopeService.php',
      ),
    ),
    'namespace' => 'App\\Modules\\RecruitmentManagement\\Services',
    'name' => 'App\\Modules\\RecruitmentManagement\\Services\\RecruitmentHireHandoffAccessScopeService',
    'shortName' => 'RecruitmentHireHandoffAccessScopeService',
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
    'endLine' => 63,
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
      'handoffsQuery' => 
      array (
        'name' => 'handoffsQuery',
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
              'code' => '[\'offer\', \'candidate\', \'requisition\', \'employee\', \'recruiter\', \'convertedBy\', \'sourceResume\']',
              'attributes' => 
              array (
                'startLine' => 18,
                'endLine' => 26,
                'startTokenPos' => 53,
                'startFilePos' => 421,
                'endTokenPos' => 76,
                'endFilePos' => 608,
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
            'endLine' => 26,
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
 * @return Builder<RecruitmentHireHandoff>
 */',
        'startLine' => 16,
        'endLine' => 50,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\RecruitmentManagement\\Services',
        'declaringClassName' => 'App\\Modules\\RecruitmentManagement\\Services\\RecruitmentHireHandoffAccessScopeService',
        'implementingClassName' => 'App\\Modules\\RecruitmentManagement\\Services\\RecruitmentHireHandoffAccessScopeService',
        'currentClassName' => 'App\\Modules\\RecruitmentManagement\\Services\\RecruitmentHireHandoffAccessScopeService',
        'aliasName' => NULL,
      ),
      'resolveAccessibleHandoff' => 
      array (
        'name' => 'resolveAccessibleHandoff',
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
            'startLine' => 52,
            'endLine' => 52,
            'startColumn' => 46,
            'endColumn' => 56,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'handoffId' => 
          array (
            'name' => 'handoffId',
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
            'startLine' => 52,
            'endLine' => 52,
            'startColumn' => 59,
            'endColumn' => 72,
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
            'name' => 'App\\Models\\RecruitmentHireHandoff',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 52,
        'endLine' => 55,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\RecruitmentManagement\\Services',
        'declaringClassName' => 'App\\Modules\\RecruitmentManagement\\Services\\RecruitmentHireHandoffAccessScopeService',
        'implementingClassName' => 'App\\Modules\\RecruitmentManagement\\Services\\RecruitmentHireHandoffAccessScopeService',
        'currentClassName' => 'App\\Modules\\RecruitmentManagement\\Services\\RecruitmentHireHandoffAccessScopeService',
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
            'startLine' => 57,
            'endLine' => 57,
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
        'startLine' => 57,
        'endLine' => 62,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\RecruitmentManagement\\Services',
        'declaringClassName' => 'App\\Modules\\RecruitmentManagement\\Services\\RecruitmentHireHandoffAccessScopeService',
        'implementingClassName' => 'App\\Modules\\RecruitmentManagement\\Services\\RecruitmentHireHandoffAccessScopeService',
        'currentClassName' => 'App\\Modules\\RecruitmentManagement\\Services\\RecruitmentHireHandoffAccessScopeService',
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