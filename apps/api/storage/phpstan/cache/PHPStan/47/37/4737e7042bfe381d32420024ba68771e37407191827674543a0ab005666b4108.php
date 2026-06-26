<?php declare(strict_types = 1);

// osfsl-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/PayrollManagement/Services/PayrollPrerequisiteService.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Modules\PayrollManagement\Services\PayrollPrerequisiteService
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-dd22ada0f76044d10b579ba9f10753201a387e23473d0727a5dd90158f906d9a-8.4.4-6.70.0.1',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Modules\\PayrollManagement\\Services\\PayrollPrerequisiteService',
        'filename' => '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/PayrollManagement/Services/PayrollPrerequisiteService.php',
      ),
    ),
    'namespace' => 'App\\Modules\\PayrollManagement\\Services',
    'name' => 'App\\Modules\\PayrollManagement\\Services\\PayrollPrerequisiteService',
    'shortName' => 'PayrollPrerequisiteService',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @phpstan-type PayrollPrerequisiteMetrics array<string, bool|int>
 * @phpstan-type PayrollPrerequisiteCheck array{
 *   code: string,
 *   label: string,
 *   status: \'passed\'|\'warning\'|\'failed\',
 *   blocking: bool,
 *   message: string,
 *   metrics: PayrollPrerequisiteMetrics
 * }
 * @phpstan-type PayrollPrerequisiteSummary array{
 *   ready_for_calculation: bool,
 *   blocking_count: int,
 *   warning_count: int,
 *   passed_count: int
 * }
 * @phpstan-type PayrollPrerequisiteSnapshot array{
 *   checks: list<PayrollPrerequisiteCheck>,
 *   summary: PayrollPrerequisiteSummary
 * }
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 34,
    'endLine' => 342,
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
      'buildSnapshot' => 
      array (
        'name' => 'buildSnapshot',
        'parameters' => 
        array (
          'period' => 
          array (
            'name' => 'period',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\PayrollPeriod',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 39,
            'endLine' => 39,
            'startColumn' => 35,
            'endColumn' => 55,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
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
        'docComment' => '/**
 * @return PayrollPrerequisiteSnapshot
 */',
        'startLine' => 39,
        'endLine' => 137,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\PayrollManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PayrollManagement\\Services\\PayrollPrerequisiteService',
        'implementingClassName' => 'App\\Modules\\PayrollManagement\\Services\\PayrollPrerequisiteService',
        'currentClassName' => 'App\\Modules\\PayrollManagement\\Services\\PayrollPrerequisiteService',
        'aliasName' => NULL,
      ),
      'activeEmployeeRosterCheck' => 
      array (
        'name' => 'activeEmployeeRosterCheck',
        'parameters' => 
        array (
          'activeEmployeeCount' => 
          array (
            'name' => 'activeEmployeeCount',
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
            'startLine' => 142,
            'endLine' => 142,
            'startColumn' => 48,
            'endColumn' => 71,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
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
        'docComment' => '/**
 * @return PayrollPrerequisiteCheck
 */',
        'startLine' => 142,
        'endLine' => 167,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\PayrollManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PayrollManagement\\Services\\PayrollPrerequisiteService',
        'implementingClassName' => 'App\\Modules\\PayrollManagement\\Services\\PayrollPrerequisiteService',
        'currentClassName' => 'App\\Modules\\PayrollManagement\\Services\\PayrollPrerequisiteService',
        'aliasName' => NULL,
      ),
      'attendanceCompletionCheck' => 
      array (
        'name' => 'attendanceCompletionCheck',
        'parameters' => 
        array (
          'activeEmployeeCount' => 
          array (
            'name' => 'activeEmployeeCount',
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
            'startLine' => 173,
            'endLine' => 173,
            'startColumn' => 9,
            'endColumn' => 32,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'attendanceRecordsCount' => 
          array (
            'name' => 'attendanceRecordsCount',
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
            'startLine' => 174,
            'endLine' => 174,
            'startColumn' => 9,
            'endColumn' => 35,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'incompleteAttendanceCount' => 
          array (
            'name' => 'incompleteAttendanceCount',
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
            'startLine' => 175,
            'endLine' => 175,
            'startColumn' => 9,
            'endColumn' => 38,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
          'pendingAttendanceCorrectionsCount' => 
          array (
            'name' => 'pendingAttendanceCorrectionsCount',
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
            'startLine' => 176,
            'endLine' => 176,
            'startColumn' => 9,
            'endColumn' => 46,
            'parameterIndex' => 3,
            'isOptional' => false,
          ),
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
        'docComment' => '/**
 * @return PayrollPrerequisiteCheck
 */',
        'startLine' => 172,
        'endLine' => 235,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\PayrollManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PayrollManagement\\Services\\PayrollPrerequisiteService',
        'implementingClassName' => 'App\\Modules\\PayrollManagement\\Services\\PayrollPrerequisiteService',
        'currentClassName' => 'App\\Modules\\PayrollManagement\\Services\\PayrollPrerequisiteService',
        'aliasName' => NULL,
      ),
      'leaveApprovalCheck' => 
      array (
        'name' => 'leaveApprovalCheck',
        'parameters' => 
        array (
          'pendingLeaveRequestsCount' => 
          array (
            'name' => 'pendingLeaveRequestsCount',
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
            'startLine' => 240,
            'endLine' => 240,
            'startColumn' => 41,
            'endColumn' => 70,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
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
        'docComment' => '/**
 * @return PayrollPrerequisiteCheck
 */',
        'startLine' => 240,
        'endLine' => 265,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\PayrollManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PayrollManagement\\Services\\PayrollPrerequisiteService',
        'implementingClassName' => 'App\\Modules\\PayrollManagement\\Services\\PayrollPrerequisiteService',
        'currentClassName' => 'App\\Modules\\PayrollManagement\\Services\\PayrollPrerequisiteService',
        'aliasName' => NULL,
      ),
      'compensationReadinessCheck' => 
      array (
        'name' => 'compensationReadinessCheck',
        'parameters' => 
        array (
          'activeEmployeeCount' => 
          array (
            'name' => 'activeEmployeeCount',
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
            'startLine' => 271,
            'endLine' => 271,
            'startColumn' => 9,
            'endColumn' => 32,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'salaryStructuresReady' => 
          array (
            'name' => 'salaryStructuresReady',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'bool',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 272,
            'endLine' => 272,
            'startColumn' => 9,
            'endColumn' => 35,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'employeeCompensationsReady' => 
          array (
            'name' => 'employeeCompensationsReady',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'bool',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 273,
            'endLine' => 273,
            'startColumn' => 9,
            'endColumn' => 40,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
          'assignedEmployeeCount' => 
          array (
            'name' => 'assignedEmployeeCount',
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
            'startLine' => 274,
            'endLine' => 274,
            'startColumn' => 9,
            'endColumn' => 34,
            'parameterIndex' => 3,
            'isOptional' => false,
          ),
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
        'docComment' => '/**
 * @return PayrollPrerequisiteCheck
 */',
        'startLine' => 270,
        'endLine' => 341,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\PayrollManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PayrollManagement\\Services\\PayrollPrerequisiteService',
        'implementingClassName' => 'App\\Modules\\PayrollManagement\\Services\\PayrollPrerequisiteService',
        'currentClassName' => 'App\\Modules\\PayrollManagement\\Services\\PayrollPrerequisiteService',
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