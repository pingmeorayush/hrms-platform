<?php declare(strict_types = 1);

// odsl-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/LeaveManagement/Services/LeaveConfigurationService.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Modules\LeaveManagement\Services\LeaveConfigurationService
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.1-8.4.4-b2b1130453d94975bade5c78d7dbbdee0df3d51f0b13a42a589162184e623340',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'filename' => '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/LeaveManagement/Services/LeaveConfigurationService.php',
      ),
    ),
    'namespace' => 'App\\Modules\\LeaveManagement\\Services',
    'name' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
    'shortName' => 'LeaveConfigurationService',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @phpstan-type LeaveEligibilityRuleInput array{
 *   employment_types?: list<string>,
 *   employment_statuses?: list<string>,
 *   genders?: list<string>,
 *   marital_statuses?: list<string>,
 *   minimum_tenure_days?: int|string|null
 * }
 * @phpstan-type LeaveEligibilityRule array{
 *   employment_types: list<string>,
 *   employment_statuses: list<string>,
 *   genders: list<string>,
 *   marital_statuses: list<string>,
 *   minimum_tenure_days: int|null
 * }
 * @phpstan-type LeaveTypePayload array{
 *   code: string,
 *   name: string,
 *   category: string,
 *   description?: string|null,
 *   is_paid: bool|int|string,
 *   requires_approval: bool|int|string,
 *   allows_half_day: bool|int|string,
 *   color_token: string,
 *   status: string
 * }
 * @phpstan-type LeaveTypeNormalizedPayload array{
 *   code: string,
 *   name: string,
 *   category: string,
 *   description: string|null,
 *   is_paid: bool,
 *   requires_approval: bool,
 *   allows_half_day: bool,
 *   color_token: string,
 *   status: string
 * }
 * @phpstan-type LeavePolicyPayload array{
 *   leave_type_id: int|string,
 *   annual_allowance_days: int|float|string,
 *   opening_balance_days: int|float|string,
 *   accrual_frequency: string,
 *   carry_forward_limit_days: int|float|string,
 *   encashment_limit_days: int|float|string,
 *   max_consecutive_days: int|float|string,
 *   min_notice_days: int|string,
 *   requires_documentation_after_days?: int|string|null,
 *   applicable_department_id?: int|string|null,
 *   applicable_location_id?: int|string|null,
 *   eligibility_rule?: LeaveEligibilityRuleInput|null,
 *   status: string,
 *   version?: int|string
 * }
 * @phpstan-type LeavePolicyNormalizedPayload array{
 *   leave_type_id: int,
 *   version: int,
 *   annual_allowance_days: float,
 *   opening_balance_days: float,
 *   accrual_frequency: string,
 *   carry_forward_limit_days: float,
 *   encashment_limit_days: float,
 *   max_consecutive_days: float,
 *   min_notice_days: int,
 *   requires_documentation_after_days: int|null,
 *   applicable_department_id: int|null,
 *   applicable_location_id: int|null,
 *   eligibility_rule: LeaveEligibilityRule,
 *   status: string,
 *   scope_key: string
 * }
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 83,
    'endLine' => 380,
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
      'auditLogger' => 
      array (
        'declaringClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'implementingClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'name' => 'auditLogger',
        'modifiers' => 132,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'isIdentifier' => false,
          ),
        ),
        'default' => NULL,
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 85,
        'endLine' => 85,
        'startColumn' => 33,
        'endColumn' => 73,
        'isPromoted' => true,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
    ),
    'immediateMethods' => 
    array (
      '__construct' => 
      array (
        'name' => '__construct',
        'parameters' => 
        array (
          'auditLogger' => 
          array (
            'name' => 'auditLogger',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => true,
            'attributes' => 
            array (
            ),
            'startLine' => 85,
            'endLine' => 85,
            'startColumn' => 33,
            'endColumn' => 73,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 85,
        'endLine' => 85,
        'startColumn' => 5,
        'endColumn' => 77,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\LeaveManagement\\Services',
        'declaringClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'implementingClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'currentClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'aliasName' => NULL,
      ),
      'createLeaveType' => 
      array (
        'name' => 'createLeaveType',
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
            'startLine' => 90,
            'endLine' => 90,
            'startColumn' => 37,
            'endColumn' => 47,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'payload' => 
          array (
            'name' => 'payload',
            'default' => NULL,
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
            'startLine' => 90,
            'endLine' => 90,
            'startColumn' => 50,
            'endColumn' => 63,
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
            'name' => 'App\\Models\\LeaveType',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  LeaveTypePayload  $payload
 */',
        'startLine' => 90,
        'endLine' => 114,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\LeaveManagement\\Services',
        'declaringClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'implementingClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'currentClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'aliasName' => NULL,
      ),
      'updateLeaveType' => 
      array (
        'name' => 'updateLeaveType',
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
            'startLine' => 119,
            'endLine' => 119,
            'startColumn' => 37,
            'endColumn' => 47,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'leaveType' => 
          array (
            'name' => 'leaveType',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\LeaveType',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 119,
            'endLine' => 119,
            'startColumn' => 50,
            'endColumn' => 69,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'payload' => 
          array (
            'name' => 'payload',
            'default' => NULL,
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
            'startLine' => 119,
            'endLine' => 119,
            'startColumn' => 72,
            'endColumn' => 85,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'App\\Models\\LeaveType',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  LeaveTypePayload  $payload
 */',
        'startLine' => 119,
        'endLine' => 160,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\LeaveManagement\\Services',
        'declaringClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'implementingClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'currentClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'aliasName' => NULL,
      ),
      'createLeavePolicy' => 
      array (
        'name' => 'createLeavePolicy',
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
            'startLine' => 165,
            'endLine' => 165,
            'startColumn' => 39,
            'endColumn' => 49,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'payload' => 
          array (
            'name' => 'payload',
            'default' => NULL,
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
            'startLine' => 165,
            'endLine' => 165,
            'startColumn' => 52,
            'endColumn' => 65,
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
            'name' => 'App\\Models\\LeavePolicy',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  LeavePolicyPayload  $payload
 */',
        'startLine' => 165,
        'endLine' => 199,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\LeaveManagement\\Services',
        'declaringClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'implementingClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'currentClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'aliasName' => NULL,
      ),
      'updateLeavePolicy' => 
      array (
        'name' => 'updateLeavePolicy',
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
            'startLine' => 204,
            'endLine' => 204,
            'startColumn' => 39,
            'endColumn' => 49,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'policy' => 
          array (
            'name' => 'policy',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\LeavePolicy',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 204,
            'endLine' => 204,
            'startColumn' => 52,
            'endColumn' => 70,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'payload' => 
          array (
            'name' => 'payload',
            'default' => NULL,
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
            'startLine' => 204,
            'endLine' => 204,
            'startColumn' => 73,
            'endColumn' => 86,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'App\\Models\\LeavePolicy',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  LeavePolicyPayload  $payload
 */',
        'startLine' => 204,
        'endLine' => 261,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\LeaveManagement\\Services',
        'declaringClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'implementingClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'currentClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'aliasName' => NULL,
      ),
      'normalizeLeaveTypePayload' => 
      array (
        'name' => 'normalizeLeaveTypePayload',
        'parameters' => 
        array (
          'payload' => 
          array (
            'name' => 'payload',
            'default' => NULL,
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
            'startLine' => 267,
            'endLine' => 267,
            'startColumn' => 48,
            'endColumn' => 61,
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
 * @param  LeaveTypePayload  $payload
 * @return LeaveTypeNormalizedPayload
 */',
        'startLine' => 267,
        'endLine' => 282,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\LeaveManagement\\Services',
        'declaringClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'implementingClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'currentClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'aliasName' => NULL,
      ),
      'normalizePolicyPayload' => 
      array (
        'name' => 'normalizePolicyPayload',
        'parameters' => 
        array (
          'payload' => 
          array (
            'name' => 'payload',
            'default' => NULL,
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
            'startLine' => 288,
            'endLine' => 288,
            'startColumn' => 45,
            'endColumn' => 58,
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
 * @param  LeavePolicyPayload  $payload
 * @return LeavePolicyNormalizedPayload
 */',
        'startLine' => 288,
        'endLine' => 316,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\LeaveManagement\\Services',
        'declaringClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'implementingClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'currentClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'aliasName' => NULL,
      ),
      'normalizeEligibilityRule' => 
      array (
        'name' => 'normalizeEligibilityRule',
        'parameters' => 
        array (
          'payload' => 
          array (
            'name' => 'payload',
            'default' => NULL,
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
            'startLine' => 322,
            'endLine' => 322,
            'startColumn' => 47,
            'endColumn' => 60,
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
 * @param  LeaveEligibilityRuleInput  $payload
 * @return LeaveEligibilityRule
 */',
        'startLine' => 322,
        'endLine' => 333,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\LeaveManagement\\Services',
        'declaringClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'implementingClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'currentClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'aliasName' => NULL,
      ),
      'normalizeStringArray' => 
      array (
        'name' => 'normalizeStringArray',
        'parameters' => 
        array (
          'values' => 
          array (
            'name' => 'values',
            'default' => NULL,
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
            'startLine' => 339,
            'endLine' => 339,
            'startColumn' => 43,
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
 * @param  array<int, mixed>  $values
 * @return array<int, string>
 */',
        'startLine' => 339,
        'endLine' => 348,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\LeaveManagement\\Services',
        'declaringClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'implementingClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'currentClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'aliasName' => NULL,
      ),
      'makePolicyScopeKey' => 
      array (
        'name' => 'makePolicyScopeKey',
        'parameters' => 
        array (
          'payload' => 
          array (
            'name' => 'payload',
            'default' => NULL,
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
            'startLine' => 353,
            'endLine' => 353,
            'startColumn' => 41,
            'endColumn' => 54,
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
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  array<string, mixed>  $payload
 */',
        'startLine' => 353,
        'endLine' => 361,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\LeaveManagement\\Services',
        'declaringClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'implementingClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'currentClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'aliasName' => NULL,
      ),
      'ensurePolicyScopeIsUnique' => 
      array (
        'name' => 'ensurePolicyScopeIsUnique',
        'parameters' => 
        array (
          'payload' => 
          array (
            'name' => 'payload',
            'default' => NULL,
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
            'startLine' => 366,
            'endLine' => 366,
            'startColumn' => 48,
            'endColumn' => 61,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'ignoreId' => 
          array (
            'name' => 'ignoreId',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 366,
                'endLine' => 366,
                'startTokenPos' => 1811,
                'startFilePos' => 13505,
                'endTokenPos' => 1811,
                'endFilePos' => 13508,
              ),
            ),
            'type' => 
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
                      'name' => 'int',
                      'isIdentifier' => true,
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
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 366,
            'endLine' => 366,
            'startColumn' => 64,
            'endColumn' => 84,
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
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  array<string, mixed>  $payload
 */',
        'startLine' => 366,
        'endLine' => 379,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\LeaveManagement\\Services',
        'declaringClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'implementingClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
        'currentClassName' => 'App\\Modules\\LeaveManagement\\Services\\LeaveConfigurationService',
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