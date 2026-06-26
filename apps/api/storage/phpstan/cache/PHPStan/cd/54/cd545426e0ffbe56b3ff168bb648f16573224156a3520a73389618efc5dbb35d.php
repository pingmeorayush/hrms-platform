<?php declare(strict_types = 1);

// odsl-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/PayrollManagement/Services/EmployeeCompensationService.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Modules\PayrollManagement\Services\EmployeeCompensationService
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.1-8.4.4-431f8c9c49271780433d921809bfbc1e88b14eefcbfe0b2fe040554653448db9',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
        'filename' => '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/PayrollManagement/Services/EmployeeCompensationService.php',
      ),
    ),
    'namespace' => 'App\\Modules\\PayrollManagement\\Services',
    'name' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
    'shortName' => 'EmployeeCompensationService',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @phpstan-type EmployeeCompensationFilters array{
 *   employee_id?: int|string|null,
 *   current_only?: bool
 * }
 * @phpstan-type EmployeeCompensationSummaryEmployee array{
 *   id: int,
 *   employee_code: string,
 *   full_name: string,
 *   email: string|null,
 *   employment_status: string|null
 * }
 * @phpstan-type PayrollResolvedFormulaInputs array{
 *   calculation_type: string|null,
 *   flat_amount: float|int|string|null,
 *   percentage_value: float|int|string|null,
 *   percentage_basis_component_codes: list<string>,
 *   expression_formula: string|null
 * }
 * @phpstan-type PayrollComponentSnapshotLine array{
 *   salary_component_id: int,
 *   code: string|null,
 *   name: string|null,
 *   category: string|null,
 *   display_order: int,
 *   is_proratable?: bool,
 *   resolved_formula_inputs: PayrollResolvedFormulaInputs
 * }
 * @phpstan-type EmployeeCompensationAssignmentPayload array{
 *   employee_id: int|string,
 *   salary_structure_id: int|string,
 *   revision_reason: string,
 *   effective_from: string,
 *   revision_date: string,
 *   notes?: string|null
 * }
 * @phpstan-type EmployeeCompensationSummary array{
 *   employee: EmployeeCompensationSummaryEmployee,
 *   current_assignment: EmployeeCompensation|null,
 *   history: Collection<int, EmployeeCompensation>
 * }
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 58,
    'endLine' => 256,
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
        'declaringClassName' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
        'implementingClassName' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
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
        'startLine' => 60,
        'endLine' => 60,
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
            'startLine' => 60,
            'endLine' => 60,
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
        'startLine' => 60,
        'endLine' => 60,
        'startColumn' => 5,
        'endColumn' => 77,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\PayrollManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
        'implementingClassName' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
        'currentClassName' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
        'aliasName' => NULL,
      ),
      'listCompensations' => 
      array (
        'name' => 'listCompensations',
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
            'startLine' => 66,
            'endLine' => 66,
            'startColumn' => 39,
            'endColumn' => 49,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'filters' => 
          array (
            'name' => 'filters',
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
            'startLine' => 66,
            'endLine' => 66,
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
            'name' => 'Illuminate\\Support\\Collection',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  EmployeeCompensationFilters  $filters
 * @return Collection<int, EmployeeCompensation>
 */',
        'startLine' => 66,
        'endLine' => 86,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\PayrollManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
        'implementingClassName' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
        'currentClassName' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
        'aliasName' => NULL,
      ),
      'showEmployeeCompensations' => 
      array (
        'name' => 'showEmployeeCompensations',
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
            'startLine' => 91,
            'endLine' => 91,
            'startColumn' => 47,
            'endColumn' => 57,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'employeeId' => 
          array (
            'name' => 'employeeId',
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
            'startLine' => 91,
            'endLine' => 91,
            'startColumn' => 60,
            'endColumn' => 74,
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
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @return EmployeeCompensationSummary
 */',
        'startLine' => 91,
        'endLine' => 114,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\PayrollManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
        'implementingClassName' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
        'currentClassName' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
        'aliasName' => NULL,
      ),
      'assignCompensation' => 
      array (
        'name' => 'assignCompensation',
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
            'startColumn' => 40,
            'endColumn' => 50,
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
            'startLine' => 119,
            'endLine' => 119,
            'startColumn' => 53,
            'endColumn' => 66,
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
            'name' => 'App\\Models\\EmployeeCompensation',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  EmployeeCompensationAssignmentPayload  $payload
 */',
        'startLine' => 119,
        'endLine' => 190,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\PayrollManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
        'implementingClassName' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
        'currentClassName' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
        'aliasName' => NULL,
      ),
      'ensureAssignmentAllowed' => 
      array (
        'name' => 'ensureAssignmentAllowed',
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
            'startLine' => 192,
            'endLine' => 192,
            'startColumn' => 46,
            'endColumn' => 63,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'salaryStructure' => 
          array (
            'name' => 'salaryStructure',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\SalaryStructure',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 192,
            'endLine' => 192,
            'startColumn' => 66,
            'endColumn' => 97,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'effectiveFrom' => 
          array (
            'name' => 'effectiveFrom',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Support\\Carbon',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 192,
            'endLine' => 192,
            'startColumn' => 100,
            'endColumn' => 120,
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
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 192,
        'endLine' => 220,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\PayrollManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
        'implementingClassName' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
        'currentClassName' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
        'aliasName' => NULL,
      ),
      'buildComponentSnapshot' => 
      array (
        'name' => 'buildComponentSnapshot',
        'parameters' => 
        array (
          'salaryStructure' => 
          array (
            'name' => 'salaryStructure',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\SalaryStructure',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 225,
            'endLine' => 225,
            'startColumn' => 45,
            'endColumn' => 76,
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
 * @return list<PayrollComponentSnapshotLine>
 */',
        'startLine' => 225,
        'endLine' => 255,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\PayrollManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
        'implementingClassName' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
        'currentClassName' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
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