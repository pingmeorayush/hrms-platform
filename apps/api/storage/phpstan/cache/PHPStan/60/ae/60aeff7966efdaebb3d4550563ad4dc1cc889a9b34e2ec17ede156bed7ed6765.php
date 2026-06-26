<?php declare(strict_types = 1);

// osfsl-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/Platform/Audit/Services/AuditHistoryService.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Modules\Platform\Audit\Services\AuditHistoryService
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-f917562e8048ac334977e6f09092dfb1ad1da7f4d7d2fbf9035ffe5928838531-8.4.4-6.70.0.1',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Modules\\Platform\\Audit\\Services\\AuditHistoryService',
        'filename' => '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/Platform/Audit/Services/AuditHistoryService.php',
      ),
    ),
    'namespace' => 'App\\Modules\\Platform\\Audit\\Services',
    'name' => 'App\\Modules\\Platform\\Audit\\Services\\AuditHistoryService',
    'shortName' => 'AuditHistoryService',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @phpstan-type OrganizationHistoryFilters array{
 *   entity_type?: string,
 *   entity_id?: int|string
 * }
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 15,
    'endLine' => 70,
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
      'EMPLOYEE_EVENT_TYPES' => 
      array (
        'declaringClassName' => 'App\\Modules\\Platform\\Audit\\Services\\AuditHistoryService',
        'implementingClassName' => 'App\\Modules\\Platform\\Audit\\Services\\AuditHistoryService',
        'name' => 'EMPLOYEE_EVENT_TYPES',
        'modifiers' => 4,
        'type' => NULL,
        'value' => 
        array (
          'code' => '[\'employee.record.created\', \'employee.record.updated\', \'employee.record.transferred\', \'employee.record.promoted\', \'employee.record.terminated\']',
          'attributes' => 
          array (
            'startLine' => 17,
            'endLine' => 23,
            'startTokenPos' => 38,
            'startFilePos' => 352,
            'endTokenPos' => 55,
            'endFilePos' => 541,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 17,
        'endLine' => 23,
        'startColumn' => 5,
        'endColumn' => 6,
      ),
      'ORGANIZATION_ENTITY_TYPES' => 
      array (
        'declaringClassName' => 'App\\Modules\\Platform\\Audit\\Services\\AuditHistoryService',
        'implementingClassName' => 'App\\Modules\\Platform\\Audit\\Services\\AuditHistoryService',
        'name' => 'ORGANIZATION_ENTITY_TYPES',
        'modifiers' => 4,
        'type' => NULL,
        'value' => 
        array (
          'code' => '[\'company\', \'department\', \'designation\', \'location\', \'cost_center\']',
          'attributes' => 
          array (
            'startLine' => 25,
            'endLine' => 31,
            'startTokenPos' => 66,
            'startFilePos' => 591,
            'endTokenPos' => 83,
            'endFilePos' => 704,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 25,
        'endLine' => 31,
        'startColumn' => 5,
        'endColumn' => 6,
      ),
    ),
    'immediateProperties' => 
    array (
    ),
    'immediateMethods' => 
    array (
      'paginateEmployeeHistory' => 
      array (
        'name' => 'paginateEmployeeHistory',
        'parameters' => 
        array (
          'employee' => 
          array (
            'name' => 'employee',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\Employee',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 36,
            'endLine' => 36,
            'startColumn' => 45,
            'endColumn' => 62,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'perPage' => 
          array (
            'name' => 'perPage',
            'default' => 
            array (
              'code' => '25',
              'attributes' => 
              array (
                'startLine' => 36,
                'endLine' => 36,
                'startTokenPos' => 105,
                'startFilePos' => 854,
                'endTokenPos' => 105,
                'endFilePos' => 855,
              ),
            ),
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
            'startLine' => 36,
            'endLine' => 36,
            'startColumn' => 65,
            'endColumn' => 81,
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
            'name' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @return LengthAwarePaginator<int, AuditLog>
 */',
        'startLine' => 36,
        'endLine' => 46,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\Platform\\Audit\\Services',
        'declaringClassName' => 'App\\Modules\\Platform\\Audit\\Services\\AuditHistoryService',
        'implementingClassName' => 'App\\Modules\\Platform\\Audit\\Services\\AuditHistoryService',
        'currentClassName' => 'App\\Modules\\Platform\\Audit\\Services\\AuditHistoryService',
        'aliasName' => NULL,
      ),
      'paginateOrganizationHistory' => 
      array (
        'name' => 'paginateOrganizationHistory',
        'parameters' => 
        array (
          'filters' => 
          array (
            'name' => 'filters',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 52,
                'endLine' => 52,
                'startTokenPos' => 195,
                'startFilePos' => 1411,
                'endTokenPos' => 196,
                'endFilePos' => 1412,
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
            'startLine' => 52,
            'endLine' => 52,
            'startColumn' => 49,
            'endColumn' => 67,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'perPage' => 
          array (
            'name' => 'perPage',
            'default' => 
            array (
              'code' => '25',
              'attributes' => 
              array (
                'startLine' => 52,
                'endLine' => 52,
                'startTokenPos' => 205,
                'startFilePos' => 1430,
                'endTokenPos' => 205,
                'endFilePos' => 1431,
              ),
            ),
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
            'startColumn' => 70,
            'endColumn' => 86,
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
            'name' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  OrganizationHistoryFilters  $filters
 * @return LengthAwarePaginator<int, AuditLog>
 */',
        'startLine' => 52,
        'endLine' => 69,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\Platform\\Audit\\Services',
        'declaringClassName' => 'App\\Modules\\Platform\\Audit\\Services\\AuditHistoryService',
        'implementingClassName' => 'App\\Modules\\Platform\\Audit\\Services\\AuditHistoryService',
        'currentClassName' => 'App\\Modules\\Platform\\Audit\\Services\\AuditHistoryService',
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