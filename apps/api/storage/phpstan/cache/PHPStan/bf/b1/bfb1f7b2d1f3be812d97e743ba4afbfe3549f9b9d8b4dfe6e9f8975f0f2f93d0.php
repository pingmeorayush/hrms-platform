<?php declare(strict_types = 1);

// ftm-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/PayrollManagement/Services/EmployeeCompensationService.php
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v5-2.3.2',
   'data' => 
  array (
    0 => 
    array (
      'f4bc1e01afc74ac8f8ac223143b2cbbe' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'employeecompensation' => 'App\\Models\\EmployeeCompensation',
          'salarystructure' => 'App\\Models\\SalaryStructure',
          'salarystructurecomponent' => 'App\\Models\\SalaryStructureComponent',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Illuminate\\Support\\Carbon',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
         'functionName' => NULL,
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
          'EmployeeCompensationFilters' => true,
          'EmployeeCompensationSummaryEmployee' => true,
          'PayrollResolvedFormulaInputs' => true,
          'PayrollComponentSnapshotLine' => true,
          'EmployeeCompensationAssignmentPayload' => true,
          'EmployeeCompensationSummary' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'bb75b5ccd02536b7aae5971de9bedd9d' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'employeecompensation' => 'App\\Models\\EmployeeCompensation',
          'salarystructure' => 'App\\Models\\SalaryStructure',
          'salarystructurecomponent' => 'App\\Models\\SalaryStructureComponent',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Illuminate\\Support\\Carbon',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
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
            'salarystructure' => 'App\\Models\\SalaryStructure',
            'salarystructurecomponent' => 'App\\Models\\SalaryStructureComponent',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Illuminate\\Support\\Carbon',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'EmployeeCompensationFilters' => true,
            'EmployeeCompensationSummaryEmployee' => true,
            'PayrollResolvedFormulaInputs' => true,
            'PayrollComponentSnapshotLine' => true,
            'EmployeeCompensationAssignmentPayload' => true,
            'EmployeeCompensationSummary' => true,
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
          'EmployeeCompensationFilters' => true,
          'EmployeeCompensationSummaryEmployee' => true,
          'PayrollResolvedFormulaInputs' => true,
          'PayrollComponentSnapshotLine' => true,
          'EmployeeCompensationAssignmentPayload' => true,
          'EmployeeCompensationSummary' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '612dc22b149cdd165a510a1fd2e01697' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'employeecompensation' => 'App\\Models\\EmployeeCompensation',
          'salarystructure' => 'App\\Models\\SalaryStructure',
          'salarystructurecomponent' => 'App\\Models\\SalaryStructureComponent',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Illuminate\\Support\\Carbon',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
         'functionName' => 'listCompensations',
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
            'salarystructure' => 'App\\Models\\SalaryStructure',
            'salarystructurecomponent' => 'App\\Models\\SalaryStructureComponent',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Illuminate\\Support\\Carbon',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'EmployeeCompensationFilters' => true,
            'EmployeeCompensationSummaryEmployee' => true,
            'PayrollResolvedFormulaInputs' => true,
            'PayrollComponentSnapshotLine' => true,
            'EmployeeCompensationAssignmentPayload' => true,
            'EmployeeCompensationSummary' => true,
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
          'EmployeeCompensationFilters' => true,
          'EmployeeCompensationSummaryEmployee' => true,
          'PayrollResolvedFormulaInputs' => true,
          'PayrollComponentSnapshotLine' => true,
          'EmployeeCompensationAssignmentPayload' => true,
          'EmployeeCompensationSummary' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'cbf6d92a8d5624f8ed155100b9f2e44f' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'employeecompensation' => 'App\\Models\\EmployeeCompensation',
          'salarystructure' => 'App\\Models\\SalaryStructure',
          'salarystructurecomponent' => 'App\\Models\\SalaryStructureComponent',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Illuminate\\Support\\Carbon',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
         'functionName' => 'showEmployeeCompensations',
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
            'salarystructure' => 'App\\Models\\SalaryStructure',
            'salarystructurecomponent' => 'App\\Models\\SalaryStructureComponent',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Illuminate\\Support\\Carbon',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'EmployeeCompensationFilters' => true,
            'EmployeeCompensationSummaryEmployee' => true,
            'PayrollResolvedFormulaInputs' => true,
            'PayrollComponentSnapshotLine' => true,
            'EmployeeCompensationAssignmentPayload' => true,
            'EmployeeCompensationSummary' => true,
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
          'EmployeeCompensationFilters' => true,
          'EmployeeCompensationSummaryEmployee' => true,
          'PayrollResolvedFormulaInputs' => true,
          'PayrollComponentSnapshotLine' => true,
          'EmployeeCompensationAssignmentPayload' => true,
          'EmployeeCompensationSummary' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'd61e990475b595eb0ea832a7d67bbcf0' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'employeecompensation' => 'App\\Models\\EmployeeCompensation',
          'salarystructure' => 'App\\Models\\SalaryStructure',
          'salarystructurecomponent' => 'App\\Models\\SalaryStructureComponent',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Illuminate\\Support\\Carbon',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
         'functionName' => 'assignCompensation',
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
            'salarystructure' => 'App\\Models\\SalaryStructure',
            'salarystructurecomponent' => 'App\\Models\\SalaryStructureComponent',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Illuminate\\Support\\Carbon',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'EmployeeCompensationFilters' => true,
            'EmployeeCompensationSummaryEmployee' => true,
            'PayrollResolvedFormulaInputs' => true,
            'PayrollComponentSnapshotLine' => true,
            'EmployeeCompensationAssignmentPayload' => true,
            'EmployeeCompensationSummary' => true,
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
          'EmployeeCompensationFilters' => true,
          'EmployeeCompensationSummaryEmployee' => true,
          'PayrollResolvedFormulaInputs' => true,
          'PayrollComponentSnapshotLine' => true,
          'EmployeeCompensationAssignmentPayload' => true,
          'EmployeeCompensationSummary' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'eda7974e28a67f9f674ed0ba48d09d56' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'employeecompensation' => 'App\\Models\\EmployeeCompensation',
          'salarystructure' => 'App\\Models\\SalaryStructure',
          'salarystructurecomponent' => 'App\\Models\\SalaryStructureComponent',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Illuminate\\Support\\Carbon',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
         'functionName' => 'ensureAssignmentAllowed',
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
            'salarystructure' => 'App\\Models\\SalaryStructure',
            'salarystructurecomponent' => 'App\\Models\\SalaryStructureComponent',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Illuminate\\Support\\Carbon',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'EmployeeCompensationFilters' => true,
            'EmployeeCompensationSummaryEmployee' => true,
            'PayrollResolvedFormulaInputs' => true,
            'PayrollComponentSnapshotLine' => true,
            'EmployeeCompensationAssignmentPayload' => true,
            'EmployeeCompensationSummary' => true,
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
          'EmployeeCompensationFilters' => true,
          'EmployeeCompensationSummaryEmployee' => true,
          'PayrollResolvedFormulaInputs' => true,
          'PayrollComponentSnapshotLine' => true,
          'EmployeeCompensationAssignmentPayload' => true,
          'EmployeeCompensationSummary' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'b520e7a4a575c3474460e7b5c7298716' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'employeecompensation' => 'App\\Models\\EmployeeCompensation',
          'salarystructure' => 'App\\Models\\SalaryStructure',
          'salarystructurecomponent' => 'App\\Models\\SalaryStructureComponent',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Illuminate\\Support\\Carbon',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
         'functionName' => 'buildComponentSnapshot',
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
            'salarystructure' => 'App\\Models\\SalaryStructure',
            'salarystructurecomponent' => 'App\\Models\\SalaryStructureComponent',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Illuminate\\Support\\Carbon',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\EmployeeCompensationService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'EmployeeCompensationFilters' => true,
            'EmployeeCompensationSummaryEmployee' => true,
            'PayrollResolvedFormulaInputs' => true,
            'PayrollComponentSnapshotLine' => true,
            'EmployeeCompensationAssignmentPayload' => true,
            'EmployeeCompensationSummary' => true,
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
          'EmployeeCompensationFilters' => true,
          'EmployeeCompensationSummaryEmployee' => true,
          'PayrollResolvedFormulaInputs' => true,
          'PayrollComponentSnapshotLine' => true,
          'EmployeeCompensationAssignmentPayload' => true,
          'EmployeeCompensationSummary' => true,
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
      '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/PayrollManagement/Services/EmployeeCompensationService.php' => '431f8c9c49271780433d921809bfbc1e88b14eefcbfe0b2fe040554653448db9',
    ),
  ),
));