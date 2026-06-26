<?php declare(strict_types = 1);

// ftm-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/PayrollManagement/Services/PayrollCalculationService.php
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v5-2.3.2',
   'data' => 
  array (
    0 => 
    array (
      '0d958beaa9083ae39872adb8dabbcd62' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'employeecompensation' => 'App\\Models\\EmployeeCompensation',
          'payrollinput' => 'App\\Models\\PayrollInput',
          'payrollitem' => 'App\\Models\\PayrollItem',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
         'functionName' => NULL,
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
          'PayrollComponentSnapshotLine' => true,
          'PayrollResolvedFormulaInputs' => true,
          'PayrollRunActionPayload' => true,
          'PayrollComponentContext' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'ca494247249c0ca01ed0e1184f3faee0' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'employeecompensation' => 'App\\Models\\EmployeeCompensation',
          'payrollinput' => 'App\\Models\\PayrollInput',
          'payrollitem' => 'App\\Models\\PayrollItem',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
         'functionName' => '__construct',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'employeecompensation' => 'App\\Models\\EmployeeCompensation',
            'payrollinput' => 'App\\Models\\PayrollInput',
            'payrollitem' => 'App\\Models\\PayrollItem',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollComponentSnapshotLine' => true,
            'PayrollResolvedFormulaInputs' => true,
            'PayrollRunActionPayload' => true,
            'PayrollComponentContext' => true,
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
          'PayrollComponentSnapshotLine' => true,
          'PayrollResolvedFormulaInputs' => true,
          'PayrollRunActionPayload' => true,
          'PayrollComponentContext' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'e1b7709b3d882a47950e21e4d44808ad' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'employeecompensation' => 'App\\Models\\EmployeeCompensation',
          'payrollinput' => 'App\\Models\\PayrollInput',
          'payrollitem' => 'App\\Models\\PayrollItem',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
         'functionName' => 'calculateRun',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'employeecompensation' => 'App\\Models\\EmployeeCompensation',
            'payrollinput' => 'App\\Models\\PayrollInput',
            'payrollitem' => 'App\\Models\\PayrollItem',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollComponentSnapshotLine' => true,
            'PayrollResolvedFormulaInputs' => true,
            'PayrollRunActionPayload' => true,
            'PayrollComponentContext' => true,
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
          'PayrollComponentSnapshotLine' => true,
          'PayrollResolvedFormulaInputs' => true,
          'PayrollRunActionPayload' => true,
          'PayrollComponentContext' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'e955296b45e9e9852ebe1a491ede9961' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'employeecompensation' => 'App\\Models\\EmployeeCompensation',
          'payrollinput' => 'App\\Models\\PayrollInput',
          'payrollitem' => 'App\\Models\\PayrollItem',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
         'functionName' => 'approveRun',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'employeecompensation' => 'App\\Models\\EmployeeCompensation',
            'payrollinput' => 'App\\Models\\PayrollInput',
            'payrollitem' => 'App\\Models\\PayrollItem',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollComponentSnapshotLine' => true,
            'PayrollResolvedFormulaInputs' => true,
            'PayrollRunActionPayload' => true,
            'PayrollComponentContext' => true,
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
          'PayrollComponentSnapshotLine' => true,
          'PayrollResolvedFormulaInputs' => true,
          'PayrollRunActionPayload' => true,
          'PayrollComponentContext' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '2e8cc2b0789b7be5e3bd70bdfb4a07a9' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'employeecompensation' => 'App\\Models\\EmployeeCompensation',
          'payrollinput' => 'App\\Models\\PayrollInput',
          'payrollitem' => 'App\\Models\\PayrollItem',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
         'functionName' => 'lockRun',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'employeecompensation' => 'App\\Models\\EmployeeCompensation',
            'payrollinput' => 'App\\Models\\PayrollInput',
            'payrollitem' => 'App\\Models\\PayrollItem',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollComponentSnapshotLine' => true,
            'PayrollResolvedFormulaInputs' => true,
            'PayrollRunActionPayload' => true,
            'PayrollComponentContext' => true,
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
          'PayrollComponentSnapshotLine' => true,
          'PayrollResolvedFormulaInputs' => true,
          'PayrollRunActionPayload' => true,
          'PayrollComponentContext' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'f8a8bb99c5935a053fd7a29a0a276646' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'employeecompensation' => 'App\\Models\\EmployeeCompensation',
          'payrollinput' => 'App\\Models\\PayrollInput',
          'payrollitem' => 'App\\Models\\PayrollItem',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
         'functionName' => 'reopenRun',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'employeecompensation' => 'App\\Models\\EmployeeCompensation',
            'payrollinput' => 'App\\Models\\PayrollInput',
            'payrollitem' => 'App\\Models\\PayrollItem',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollComponentSnapshotLine' => true,
            'PayrollResolvedFormulaInputs' => true,
            'PayrollRunActionPayload' => true,
            'PayrollComponentContext' => true,
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
          'PayrollComponentSnapshotLine' => true,
          'PayrollResolvedFormulaInputs' => true,
          'PayrollRunActionPayload' => true,
          'PayrollComponentContext' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'f6946f30821375d6ef93e5f8566cb3dd' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'employeecompensation' => 'App\\Models\\EmployeeCompensation',
          'payrollinput' => 'App\\Models\\PayrollInput',
          'payrollitem' => 'App\\Models\\PayrollItem',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
         'functionName' => 'calculateEmployeeItem',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'employeecompensation' => 'App\\Models\\EmployeeCompensation',
            'payrollinput' => 'App\\Models\\PayrollInput',
            'payrollitem' => 'App\\Models\\PayrollItem',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollComponentSnapshotLine' => true,
            'PayrollResolvedFormulaInputs' => true,
            'PayrollRunActionPayload' => true,
            'PayrollComponentContext' => true,
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
          'PayrollComponentSnapshotLine' => true,
          'PayrollResolvedFormulaInputs' => true,
          'PayrollRunActionPayload' => true,
          'PayrollComponentContext' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '196ce440927112d242f662985ceb273a' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'employeecompensation' => 'App\\Models\\EmployeeCompensation',
          'payrollinput' => 'App\\Models\\PayrollInput',
          'payrollitem' => 'App\\Models\\PayrollItem',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
         'functionName' => 'evaluateResolvedComponent',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'employeecompensation' => 'App\\Models\\EmployeeCompensation',
            'payrollinput' => 'App\\Models\\PayrollInput',
            'payrollitem' => 'App\\Models\\PayrollItem',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollComponentSnapshotLine' => true,
            'PayrollResolvedFormulaInputs' => true,
            'PayrollRunActionPayload' => true,
            'PayrollComponentContext' => true,
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
          'PayrollComponentSnapshotLine' => true,
          'PayrollResolvedFormulaInputs' => true,
          'PayrollRunActionPayload' => true,
          'PayrollComponentContext' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '6df29192b078f551e22eeb970aa2879c' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'employeecompensation' => 'App\\Models\\EmployeeCompensation',
          'payrollinput' => 'App\\Models\\PayrollInput',
          'payrollitem' => 'App\\Models\\PayrollItem',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
         'functionName' => 'evaluatePercentageComponent',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'employeecompensation' => 'App\\Models\\EmployeeCompensation',
            'payrollinput' => 'App\\Models\\PayrollInput',
            'payrollitem' => 'App\\Models\\PayrollItem',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollComponentSnapshotLine' => true,
            'PayrollResolvedFormulaInputs' => true,
            'PayrollRunActionPayload' => true,
            'PayrollComponentContext' => true,
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
          'PayrollComponentSnapshotLine' => true,
          'PayrollResolvedFormulaInputs' => true,
          'PayrollRunActionPayload' => true,
          'PayrollComponentContext' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '6d67ccec263c7092560f367f1085fb0a' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'employeecompensation' => 'App\\Models\\EmployeeCompensation',
          'payrollinput' => 'App\\Models\\PayrollInput',
          'payrollitem' => 'App\\Models\\PayrollItem',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
         'functionName' => 'evaluateExpressionComponent',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'employeecompensation' => 'App\\Models\\EmployeeCompensation',
            'payrollinput' => 'App\\Models\\PayrollInput',
            'payrollitem' => 'App\\Models\\PayrollItem',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollComponentSnapshotLine' => true,
            'PayrollResolvedFormulaInputs' => true,
            'PayrollRunActionPayload' => true,
            'PayrollComponentContext' => true,
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
          'PayrollComponentSnapshotLine' => true,
          'PayrollResolvedFormulaInputs' => true,
          'PayrollRunActionPayload' => true,
          'PayrollComponentContext' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'c4a560c41e8ad199a13c1baf7ba987ee' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'employeecompensation' => 'App\\Models\\EmployeeCompensation',
          'payrollinput' => 'App\\Models\\PayrollInput',
          'payrollitem' => 'App\\Models\\PayrollItem',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
         'functionName' => 'resolveComponentSnapshot',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'employeecompensation' => 'App\\Models\\EmployeeCompensation',
            'payrollinput' => 'App\\Models\\PayrollInput',
            'payrollitem' => 'App\\Models\\PayrollItem',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollComponentSnapshotLine' => true,
            'PayrollResolvedFormulaInputs' => true,
            'PayrollRunActionPayload' => true,
            'PayrollComponentContext' => true,
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
          'PayrollComponentSnapshotLine' => true,
          'PayrollResolvedFormulaInputs' => true,
          'PayrollRunActionPayload' => true,
          'PayrollComponentContext' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '2a2d42b8f6ca3b43c0cac140b40f95b0' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'employeecompensation' => 'App\\Models\\EmployeeCompensation',
          'payrollinput' => 'App\\Models\\PayrollInput',
          'payrollitem' => 'App\\Models\\PayrollItem',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
         'functionName' => 'evaluateNumericExpression',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'employeecompensation' => 'App\\Models\\EmployeeCompensation',
            'payrollinput' => 'App\\Models\\PayrollInput',
            'payrollitem' => 'App\\Models\\PayrollItem',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollComponentSnapshotLine' => true,
            'PayrollResolvedFormulaInputs' => true,
            'PayrollRunActionPayload' => true,
            'PayrollComponentContext' => true,
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
          'PayrollComponentSnapshotLine' => true,
          'PayrollResolvedFormulaInputs' => true,
          'PayrollRunActionPayload' => true,
          'PayrollComponentContext' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '7bb7dcb7c399ab84da6847fee069e4d6' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'employeecompensation' => 'App\\Models\\EmployeeCompensation',
          'payrollinput' => 'App\\Models\\PayrollInput',
          'payrollitem' => 'App\\Models\\PayrollItem',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
         'functionName' => 'parseExpression',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'employeecompensation' => 'App\\Models\\EmployeeCompensation',
            'payrollinput' => 'App\\Models\\PayrollInput',
            'payrollitem' => 'App\\Models\\PayrollItem',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollComponentSnapshotLine' => true,
            'PayrollResolvedFormulaInputs' => true,
            'PayrollRunActionPayload' => true,
            'PayrollComponentContext' => true,
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
          'PayrollComponentSnapshotLine' => true,
          'PayrollResolvedFormulaInputs' => true,
          'PayrollRunActionPayload' => true,
          'PayrollComponentContext' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '72ee373576648febf4ca542fae060f7a' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'employeecompensation' => 'App\\Models\\EmployeeCompensation',
          'payrollinput' => 'App\\Models\\PayrollInput',
          'payrollitem' => 'App\\Models\\PayrollItem',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
         'functionName' => 'parseTerm',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'employeecompensation' => 'App\\Models\\EmployeeCompensation',
            'payrollinput' => 'App\\Models\\PayrollInput',
            'payrollitem' => 'App\\Models\\PayrollItem',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollComponentSnapshotLine' => true,
            'PayrollResolvedFormulaInputs' => true,
            'PayrollRunActionPayload' => true,
            'PayrollComponentContext' => true,
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
          'PayrollComponentSnapshotLine' => true,
          'PayrollResolvedFormulaInputs' => true,
          'PayrollRunActionPayload' => true,
          'PayrollComponentContext' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '23a59ec858675c6b8930de32fed19ecb' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'employeecompensation' => 'App\\Models\\EmployeeCompensation',
          'payrollinput' => 'App\\Models\\PayrollInput',
          'payrollitem' => 'App\\Models\\PayrollItem',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
         'functionName' => 'parseFactor',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'employeecompensation' => 'App\\Models\\EmployeeCompensation',
            'payrollinput' => 'App\\Models\\PayrollInput',
            'payrollitem' => 'App\\Models\\PayrollItem',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollComponentSnapshotLine' => true,
            'PayrollResolvedFormulaInputs' => true,
            'PayrollRunActionPayload' => true,
            'PayrollComponentContext' => true,
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
          'PayrollComponentSnapshotLine' => true,
          'PayrollResolvedFormulaInputs' => true,
          'PayrollRunActionPayload' => true,
          'PayrollComponentContext' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'b84a18d5675bb0be8fe0adebf12dd383' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'employeecompensation' => 'App\\Models\\EmployeeCompensation',
          'payrollinput' => 'App\\Models\\PayrollInput',
          'payrollitem' => 'App\\Models\\PayrollItem',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
         'functionName' => 'employmentDaysInPeriod',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'employeecompensation' => 'App\\Models\\EmployeeCompensation',
            'payrollinput' => 'App\\Models\\PayrollInput',
            'payrollitem' => 'App\\Models\\PayrollItem',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollCalculationService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollComponentSnapshotLine' => true,
            'PayrollResolvedFormulaInputs' => true,
            'PayrollRunActionPayload' => true,
            'PayrollComponentContext' => true,
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
          'PayrollComponentSnapshotLine' => true,
          'PayrollResolvedFormulaInputs' => true,
          'PayrollRunActionPayload' => true,
          'PayrollComponentContext' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
    ),
    1 => 
    array (
      '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/PayrollManagement/Services/PayrollCalculationService.php' => '3a45115002d3bd785793dfdd3f3271ae418952febfb2b64b3346a078aa50b699',
    ),
  ),
));