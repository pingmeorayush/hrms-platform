<?php declare(strict_types = 1);

// odsl-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/EmployeeManagement/Services/EmployeeSelfServiceWorkspaceService.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Modules\EmployeeManagement\Services\EmployeeSelfServiceWorkspaceService
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.1-8.4.4-4e78a0a7d8279a833acb4a5a114bd19664ae22e5e8bac72f5810b7020e40fb9d',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'filename' => '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/EmployeeManagement/Services/EmployeeSelfServiceWorkspaceService.php',
      ),
    ),
    'namespace' => 'App\\Modules\\EmployeeManagement\\Services',
    'name' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
    'shortName' => 'EmployeeSelfServiceWorkspaceService',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @phpstan-type EmployeeSelfServiceDirectoryNode array{
 *   id: int,
 *   code: string,
 *   name: string,
 *   status: string
 * }
 * @phpstan-type EmployeeSelfServiceManagerNode array{
 *   id: int,
 *   employee_code: string,
 *   full_name: string,
 *   email: string|null
 * }
 * @phpstan-type EmployeeSelfServiceLocationNode array{
 *   id: int,
 *   code: string,
 *   name: string,
 *   timezone: string|null,
 *   currency: string|null,
 *   city: string|null,
 *   country: string|null,
 *   status: string
 * }
 * @phpstan-type EmployeeSelfServiceEmployee array{
 *   id: int,
 *   employee_code: string,
 *   first_name: string,
 *   middle_name: string|null,
 *   last_name: string|null,
 *   full_name: string,
 *   email: string|null,
 *   phone: string|null,
 *   date_of_birth: string|null,
 *   gender: string|null,
 *   marital_status: string|null,
 *   date_of_joining: string|null,
 *   employment_type: string|null,
 *   employment_status: string|null,
 *   termination_reason: string|null,
 *   terminated_at: string|null,
 *   department: EmployeeSelfServiceDirectoryNode|null,
 *   designation: EmployeeSelfServiceDirectoryNode|null,
 *   manager: EmployeeSelfServiceManagerNode|null,
 *   location: EmployeeSelfServiceLocationNode|null,
 *   cost_center: EmployeeSelfServiceDirectoryNode|null,
 *   user_id: int|null,
 *   created_at: string|null,
 *   updated_at: string|null
 * }
 * @phpstan-type EmployeeSelfServiceContact array{
 *   id: int,
 *   employee_id: int,
 *   type: string,
 *   label: string|null,
 *   value: string,
 *   is_primary: bool,
 *   status: string,
 *   notes: string|null,
 *   created_at: string|null,
 *   updated_at: string|null
 * }
 * @phpstan-type EmployeeSelfServiceAddress array{
 *   id: int,
 *   employee_id: int,
 *   type: string,
 *   address_line_1: string,
 *   address_line_2: string|null,
 *   city: string|null,
 *   state: string|null,
 *   country: string|null,
 *   postal_code: string|null,
 *   notes: string|null,
 *   created_at: string|null,
 *   updated_at: string|null
 * }
 * @phpstan-type EmployeeSelfServiceEmergencyContact array{
 *   id: int,
 *   employee_id: int,
 *   name: string,
 *   relationship: string|null,
 *   phone_number: string|null,
 *   email: string|null,
 *   address: string|null,
 *   priority: int,
 *   notes: string|null,
 *   created_at: string|null,
 *   updated_at: string|null
 * }
 * @phpstan-type EmployeeSelfServiceDocumentCategory array{
 *   id: int,
 *   code: string,
 *   name: string
 * }
 * @phpstan-type EmployeeSelfServiceDocumentItem array{
 *   id: string,
 *   source_type: \'policy_acknowledgement\'|\'employee_document\'|\'repository_document\',
 *   source_id: int,
 *   title: string,
 *   subtitle: string,
 *   status: string,
 *   document_type: string,
 *   file_name: string|null,
 *   mime_type: string|null,
 *   file_size_bytes: int|null,
 *   due_date: string|null,
 *   expiry_date: string|null,
 *   visibility_scope: string|null,
 *   download_url: string|null,
 *   acknowledge_url: string|null,
 *   action_required: bool,
 *   notes: string|null,
 *   category: EmployeeSelfServiceDocumentCategory|null,
 *   repository_scope: string|null,
 *   created_at: string|null,
 *   updated_at: string|null
 * }
 * @phpstan-type EmployeeSelfServiceAssetCategory array{
 *   id: int,
 *   code: string,
 *   name: string,
 *   status: string
 * }
 * @phpstan-type EmployeeSelfServiceAssetAssignment array{
 *   id: int,
 *   status: string,
 *   assigned_at: string|null,
 *   issued_at: string|null,
 *   expected_return_date: string|null,
 *   returned_at: string|null,
 *   handover_condition: string|null,
 *   return_condition: string|null,
 *   assignment_notes: string|null,
 *   issue_notes: string|null,
 *   return_notes: string|null,
 *   due_state: string
 * }
 * @phpstan-type EmployeeSelfServiceAssetItem array{
 *   id: int,
 *   asset_tag: string,
 *   name: string,
 *   asset_type: string|null,
 *   status: string,
 *   serial_number: string|null,
 *   manufacturer: string|null,
 *   model_name: string|null,
 *   purchase_date: string|null,
 *   notes: string|null,
 *   category: EmployeeSelfServiceAssetCategory|null,
 *   assignment: EmployeeSelfServiceAssetAssignment|null,
 *   created_at: string|null,
 *   updated_at: string|null
 * }
 * @phpstan-type EmployeeSelfServiceWorkspace array{
 *   employee: EmployeeSelfServiceEmployee,
 *   profile: array{
 *     contacts: list<EmployeeSelfServiceContact>,
 *     addresses: list<EmployeeSelfServiceAddress>,
 *     emergency_contacts: list<EmployeeSelfServiceEmergencyContact>,
 *     sensitive_panels: array{
 *       bank_accounts: array{
 *         visible: bool,
 *         message: string|null
 *       }
 *     }
 *   },
 *   documents: array{
 *     summary: array{
 *       total_count: int,
 *       pending_acknowledgement_count: int,
 *       acknowledged_count: int,
 *       downloadable_count: int,
 *       hidden_sensitive_count: int
 *     },
 *     items: list<EmployeeSelfServiceDocumentItem>
 *   },
 *   assets: array{
 *     summary: array{
 *       active_count: int,
 *       assigned_count: int,
 *       issued_count: int,
 *       overdue_count: int
 *     },
 *     items: list<EmployeeSelfServiceAssetItem>
 *   }
 * }
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 208,
    'endLine' => 738,
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
        'declaringClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'implementingClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
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
        'startLine' => 211,
        'endLine' => 211,
        'startColumn' => 9,
        'endColumn' => 49,
        'isPromoted' => true,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'employeeDocumentService' => 
      array (
        'declaringClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'implementingClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'name' => 'employeeDocumentService',
        'modifiers' => 132,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeDocumentService',
            'isIdentifier' => false,
          ),
        ),
        'default' => NULL,
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 212,
        'endLine' => 212,
        'startColumn' => 9,
        'endColumn' => 73,
        'isPromoted' => true,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'selfServiceAccessScopeService' => 
      array (
        'declaringClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'implementingClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'name' => 'selfServiceAccessScopeService',
        'modifiers' => 132,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceAccessScopeService',
            'isIdentifier' => false,
          ),
        ),
        'default' => NULL,
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 213,
        'endLine' => 213,
        'startColumn' => 9,
        'endColumn' => 93,
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
            'startLine' => 211,
            'endLine' => 211,
            'startColumn' => 9,
            'endColumn' => 49,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'employeeDocumentService' => 
          array (
            'name' => 'employeeDocumentService',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeDocumentService',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => true,
            'attributes' => 
            array (
            ),
            'startLine' => 212,
            'endLine' => 212,
            'startColumn' => 9,
            'endColumn' => 73,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'selfServiceAccessScopeService' => 
          array (
            'name' => 'selfServiceAccessScopeService',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceAccessScopeService',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => true,
            'attributes' => 
            array (
            ),
            'startLine' => 213,
            'endLine' => 213,
            'startColumn' => 9,
            'endColumn' => 93,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 210,
        'endLine' => 214,
        'startColumn' => 5,
        'endColumn' => 8,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\EmployeeManagement\\Services',
        'declaringClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'implementingClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'currentClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'aliasName' => NULL,
      ),
      'workspace' => 
      array (
        'name' => 'workspace',
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
            'startLine' => 219,
            'endLine' => 219,
            'startColumn' => 31,
            'endColumn' => 41,
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
 * @return EmployeeSelfServiceWorkspace
 */',
        'startLine' => 219,
        'endLine' => 307,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\EmployeeManagement\\Services',
        'declaringClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'implementingClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'currentClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'aliasName' => NULL,
      ),
      'downloadEmployeeDocument' => 
      array (
        'name' => 'downloadEmployeeDocument',
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
            'startLine' => 309,
            'endLine' => 309,
            'startColumn' => 46,
            'endColumn' => 56,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'employeeDocumentId' => 
          array (
            'name' => 'employeeDocumentId',
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
            'startLine' => 309,
            'endLine' => 309,
            'startColumn' => 59,
            'endColumn' => 81,
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
            'name' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 309,
        'endLine' => 322,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\EmployeeManagement\\Services',
        'declaringClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'implementingClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'currentClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'aliasName' => NULL,
      ),
      'downloadRepositoryDocument' => 
      array (
        'name' => 'downloadRepositoryDocument',
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
            'startLine' => 324,
            'endLine' => 324,
            'startColumn' => 48,
            'endColumn' => 58,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'documentId' => 
          array (
            'name' => 'documentId',
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
            'startLine' => 324,
            'endLine' => 324,
            'startColumn' => 61,
            'endColumn' => 75,
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
            'name' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 324,
        'endLine' => 350,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\EmployeeManagement\\Services',
        'declaringClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'implementingClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'currentClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'aliasName' => NULL,
      ),
      'loadPolicyAcknowledgements' => 
      array (
        'name' => 'loadPolicyAcknowledgements',
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
            'startLine' => 355,
            'endLine' => 355,
            'startColumn' => 49,
            'endColumn' => 66,
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
            'name' => 'Illuminate\\Database\\Eloquent\\Collection',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @return EloquentCollection<int, PolicyAcknowledgement>
 */',
        'startLine' => 355,
        'endLine' => 364,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\EmployeeManagement\\Services',
        'declaringClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'implementingClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'currentClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'aliasName' => NULL,
      ),
      'loadEmployeeDocuments' => 
      array (
        'name' => 'loadEmployeeDocuments',
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
            'startLine' => 369,
            'endLine' => 369,
            'startColumn' => 44,
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
            'name' => 'Illuminate\\Database\\Eloquent\\Collection',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @return EloquentCollection<int, EmployeeDocument>
 */',
        'startLine' => 369,
        'endLine' => 376,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\EmployeeManagement\\Services',
        'declaringClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'implementingClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'currentClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'aliasName' => NULL,
      ),
      'loadAccessibleRepositoryDocuments' => 
      array (
        'name' => 'loadAccessibleRepositoryDocuments',
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
            'startLine' => 381,
            'endLine' => 381,
            'startColumn' => 56,
            'endColumn' => 73,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
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
            'startLine' => 381,
            'endLine' => 381,
            'startColumn' => 76,
            'endColumn' => 86,
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
 * @return SupportCollection<int, Document>
 */',
        'startLine' => 381,
        'endLine' => 398,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\EmployeeManagement\\Services',
        'declaringClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'implementingClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'currentClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'aliasName' => NULL,
      ),
      'loadRepositoryDocumentCount' => 
      array (
        'name' => 'loadRepositoryDocumentCount',
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
            'startLine' => 400,
            'endLine' => 400,
            'startColumn' => 50,
            'endColumn' => 67,
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
            'name' => 'int',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 400,
        'endLine' => 407,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\EmployeeManagement\\Services',
        'declaringClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'implementingClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'currentClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'aliasName' => NULL,
      ),
      'loadAssignedAssets' => 
      array (
        'name' => 'loadAssignedAssets',
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
            'startLine' => 412,
            'endLine' => 412,
            'startColumn' => 41,
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
            'name' => 'Illuminate\\Database\\Eloquent\\Collection',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @return EloquentCollection<int, Asset>
 */',
        'startLine' => 412,
        'endLine' => 423,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\EmployeeManagement\\Services',
        'declaringClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'implementingClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'currentClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'aliasName' => NULL,
      ),
      'canViewRepositoryDocument' => 
      array (
        'name' => 'canViewRepositoryDocument',
        'parameters' => 
        array (
          'document' => 
          array (
            'name' => 'document',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\Document',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 428,
            'endLine' => 428,
            'startColumn' => 48,
            'endColumn' => 65,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
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
            'startLine' => 428,
            'endLine' => 428,
            'startColumn' => 68,
            'endColumn' => 78,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'roleNames' => 
          array (
            'name' => 'roleNames',
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
            'startLine' => 428,
            'endLine' => 428,
            'startColumn' => 81,
            'endColumn' => 96,
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
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  list<string>  $roleNames
 */',
        'startLine' => 428,
        'endLine' => 448,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\EmployeeManagement\\Services',
        'declaringClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'implementingClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'currentClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'aliasName' => NULL,
      ),
      'mapEmployee' => 
      array (
        'name' => 'mapEmployee',
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
            'startLine' => 453,
            'endLine' => 453,
            'startColumn' => 34,
            'endColumn' => 51,
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
 * @return EmployeeSelfServiceEmployee
 */',
        'startLine' => 453,
        'endLine' => 510,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\EmployeeManagement\\Services',
        'declaringClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'implementingClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'currentClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'aliasName' => NULL,
      ),
      'mapContact' => 
      array (
        'name' => 'mapContact',
        'parameters' => 
        array (
          'contact' => 
          array (
            'name' => 'contact',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\EmployeeContact',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 515,
            'endLine' => 515,
            'startColumn' => 33,
            'endColumn' => 56,
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
 * @return EmployeeSelfServiceContact
 */',
        'startLine' => 515,
        'endLine' => 529,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\EmployeeManagement\\Services',
        'declaringClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'implementingClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'currentClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'aliasName' => NULL,
      ),
      'mapAddress' => 
      array (
        'name' => 'mapAddress',
        'parameters' => 
        array (
          'address' => 
          array (
            'name' => 'address',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\EmployeeAddress',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 534,
            'endLine' => 534,
            'startColumn' => 33,
            'endColumn' => 56,
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
 * @return EmployeeSelfServiceAddress
 */',
        'startLine' => 534,
        'endLine' => 550,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\EmployeeManagement\\Services',
        'declaringClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'implementingClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'currentClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'aliasName' => NULL,
      ),
      'mapEmergencyContact' => 
      array (
        'name' => 'mapEmergencyContact',
        'parameters' => 
        array (
          'contact' => 
          array (
            'name' => 'contact',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\EmployeeEmergencyContact',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 555,
            'endLine' => 555,
            'startColumn' => 42,
            'endColumn' => 74,
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
 * @return EmployeeSelfServiceEmergencyContact
 */',
        'startLine' => 555,
        'endLine' => 570,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\EmployeeManagement\\Services',
        'declaringClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'implementingClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'currentClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'aliasName' => NULL,
      ),
      'mapPolicyAcknowledgement' => 
      array (
        'name' => 'mapPolicyAcknowledgement',
        'parameters' => 
        array (
          'acknowledgement' => 
          array (
            'name' => 'acknowledgement',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\PolicyAcknowledgement',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 575,
            'endLine' => 575,
            'startColumn' => 47,
            'endColumn' => 84,
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
 * @return EmployeeSelfServiceDocumentItem
 */',
        'startLine' => 575,
        'endLine' => 604,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\EmployeeManagement\\Services',
        'declaringClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'implementingClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'currentClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'aliasName' => NULL,
      ),
      'mapEmployeeDocument' => 
      array (
        'name' => 'mapEmployeeDocument',
        'parameters' => 
        array (
          'document' => 
          array (
            'name' => 'document',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\EmployeeDocument',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 609,
            'endLine' => 609,
            'startColumn' => 42,
            'endColumn' => 67,
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
 * @return EmployeeSelfServiceDocumentItem
 */',
        'startLine' => 609,
        'endLine' => 636,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\EmployeeManagement\\Services',
        'declaringClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'implementingClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'currentClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'aliasName' => NULL,
      ),
      'mapRepositoryDocument' => 
      array (
        'name' => 'mapRepositoryDocument',
        'parameters' => 
        array (
          'document' => 
          array (
            'name' => 'document',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\Document',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 641,
            'endLine' => 641,
            'startColumn' => 44,
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
 * @return EmployeeSelfServiceDocumentItem
 */',
        'startLine' => 641,
        'endLine' => 674,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\EmployeeManagement\\Services',
        'declaringClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'implementingClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'currentClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'aliasName' => NULL,
      ),
      'mapAssignedAsset' => 
      array (
        'name' => 'mapAssignedAsset',
        'parameters' => 
        array (
          'asset' => 
          array (
            'name' => 'asset',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\Asset',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 679,
            'endLine' => 679,
            'startColumn' => 39,
            'endColumn' => 50,
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
 * @return EmployeeSelfServiceAssetItem
 */',
        'startLine' => 679,
        'endLine' => 717,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\EmployeeManagement\\Services',
        'declaringClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'implementingClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'currentClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'aliasName' => NULL,
      ),
      'resolveDueState' => 
      array (
        'name' => 'resolveDueState',
        'parameters' => 
        array (
          'expectedReturnDate' => 
          array (
            'name' => 'expectedReturnDate',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'mixed',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 719,
            'endLine' => 719,
            'startColumn' => 38,
            'endColumn' => 62,
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
        'docComment' => NULL,
        'startLine' => 719,
        'endLine' => 737,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\EmployeeManagement\\Services',
        'declaringClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'implementingClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
        'currentClassName' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeSelfServiceWorkspaceService',
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