<?php declare(strict_types = 1);

// ftm-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/Platform/Workflow/Services/WorkflowService.php
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v5-2.3.2',
   'data' => 
  array (
    0 => 
    array (
      '1386fa826a4ebe1f46d11286240fa409' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
          'workflowinstance' => 'App\\Models\\WorkflowInstance',
          'workflowstage' => 'App\\Models\\WorkflowStage',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'workflowversion' => 'App\\Models\\WorkflowVersion',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
          'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
          'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
         'functionName' => NULL,
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
          'WorkflowStagePayload' => true,
          'WorkflowStageData' => true,
          'WorkflowDefinitionCreatePayload' => true,
          'WorkflowDefinitionUpdatePayload' => true,
          'WorkflowInstanceStartPayload' => true,
          'WorkflowTaskDecisionPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'c99071296b3e54348e9b91c8f46fc78a' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
          'workflowinstance' => 'App\\Models\\WorkflowInstance',
          'workflowstage' => 'App\\Models\\WorkflowStage',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'workflowversion' => 'App\\Models\\WorkflowVersion',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
          'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
          'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
         'functionName' => '__construct',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'user' => 'App\\Models\\User',
            'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
            'workflowinstance' => 'App\\Models\\WorkflowInstance',
            'workflowstage' => 'App\\Models\\WorkflowStage',
            'workflowtask' => 'App\\Models\\WorkflowTask',
            'workflowversion' => 'App\\Models\\WorkflowVersion',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
            'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
            'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'WorkflowStagePayload' => true,
            'WorkflowStageData' => true,
            'WorkflowDefinitionCreatePayload' => true,
            'WorkflowDefinitionUpdatePayload' => true,
            'WorkflowInstanceStartPayload' => true,
            'WorkflowTaskDecisionPayload' => true,
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
          'WorkflowStagePayload' => true,
          'WorkflowStageData' => true,
          'WorkflowDefinitionCreatePayload' => true,
          'WorkflowDefinitionUpdatePayload' => true,
          'WorkflowInstanceStartPayload' => true,
          'WorkflowTaskDecisionPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '086ddf3f56efdf6f6ff5e9282595996a' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
          'workflowinstance' => 'App\\Models\\WorkflowInstance',
          'workflowstage' => 'App\\Models\\WorkflowStage',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'workflowversion' => 'App\\Models\\WorkflowVersion',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
          'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
          'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
         'functionName' => 'createDefinition',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'user' => 'App\\Models\\User',
            'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
            'workflowinstance' => 'App\\Models\\WorkflowInstance',
            'workflowstage' => 'App\\Models\\WorkflowStage',
            'workflowtask' => 'App\\Models\\WorkflowTask',
            'workflowversion' => 'App\\Models\\WorkflowVersion',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
            'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
            'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'WorkflowStagePayload' => true,
            'WorkflowStageData' => true,
            'WorkflowDefinitionCreatePayload' => true,
            'WorkflowDefinitionUpdatePayload' => true,
            'WorkflowInstanceStartPayload' => true,
            'WorkflowTaskDecisionPayload' => true,
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
          'WorkflowStagePayload' => true,
          'WorkflowStageData' => true,
          'WorkflowDefinitionCreatePayload' => true,
          'WorkflowDefinitionUpdatePayload' => true,
          'WorkflowInstanceStartPayload' => true,
          'WorkflowTaskDecisionPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'c9533fa1214511e83035e511e7a048be' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
          'workflowinstance' => 'App\\Models\\WorkflowInstance',
          'workflowstage' => 'App\\Models\\WorkflowStage',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'workflowversion' => 'App\\Models\\WorkflowVersion',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
          'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
          'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
         'functionName' => 'updateDefinition',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'user' => 'App\\Models\\User',
            'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
            'workflowinstance' => 'App\\Models\\WorkflowInstance',
            'workflowstage' => 'App\\Models\\WorkflowStage',
            'workflowtask' => 'App\\Models\\WorkflowTask',
            'workflowversion' => 'App\\Models\\WorkflowVersion',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
            'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
            'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'WorkflowStagePayload' => true,
            'WorkflowStageData' => true,
            'WorkflowDefinitionCreatePayload' => true,
            'WorkflowDefinitionUpdatePayload' => true,
            'WorkflowInstanceStartPayload' => true,
            'WorkflowTaskDecisionPayload' => true,
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
          'WorkflowStagePayload' => true,
          'WorkflowStageData' => true,
          'WorkflowDefinitionCreatePayload' => true,
          'WorkflowDefinitionUpdatePayload' => true,
          'WorkflowInstanceStartPayload' => true,
          'WorkflowTaskDecisionPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'a54574840e050a8d8e7272b4052aa22b' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
          'workflowinstance' => 'App\\Models\\WorkflowInstance',
          'workflowstage' => 'App\\Models\\WorkflowStage',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'workflowversion' => 'App\\Models\\WorkflowVersion',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
          'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
          'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
         'functionName' => 'startInstance',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'user' => 'App\\Models\\User',
            'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
            'workflowinstance' => 'App\\Models\\WorkflowInstance',
            'workflowstage' => 'App\\Models\\WorkflowStage',
            'workflowtask' => 'App\\Models\\WorkflowTask',
            'workflowversion' => 'App\\Models\\WorkflowVersion',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
            'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
            'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'WorkflowStagePayload' => true,
            'WorkflowStageData' => true,
            'WorkflowDefinitionCreatePayload' => true,
            'WorkflowDefinitionUpdatePayload' => true,
            'WorkflowInstanceStartPayload' => true,
            'WorkflowTaskDecisionPayload' => true,
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
          'WorkflowStagePayload' => true,
          'WorkflowStageData' => true,
          'WorkflowDefinitionCreatePayload' => true,
          'WorkflowDefinitionUpdatePayload' => true,
          'WorkflowInstanceStartPayload' => true,
          'WorkflowTaskDecisionPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '013c9ec40eb1cc6f86baf43efb2882bd' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
          'workflowinstance' => 'App\\Models\\WorkflowInstance',
          'workflowstage' => 'App\\Models\\WorkflowStage',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'workflowversion' => 'App\\Models\\WorkflowVersion',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
          'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
          'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
         'functionName' => 'decideTask',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'user' => 'App\\Models\\User',
            'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
            'workflowinstance' => 'App\\Models\\WorkflowInstance',
            'workflowstage' => 'App\\Models\\WorkflowStage',
            'workflowtask' => 'App\\Models\\WorkflowTask',
            'workflowversion' => 'App\\Models\\WorkflowVersion',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
            'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
            'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'WorkflowStagePayload' => true,
            'WorkflowStageData' => true,
            'WorkflowDefinitionCreatePayload' => true,
            'WorkflowDefinitionUpdatePayload' => true,
            'WorkflowInstanceStartPayload' => true,
            'WorkflowTaskDecisionPayload' => true,
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
          'WorkflowStagePayload' => true,
          'WorkflowStageData' => true,
          'WorkflowDefinitionCreatePayload' => true,
          'WorkflowDefinitionUpdatePayload' => true,
          'WorkflowInstanceStartPayload' => true,
          'WorkflowTaskDecisionPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '3f081a939f998b7eb5aa6db633e2b425' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
          'workflowinstance' => 'App\\Models\\WorkflowInstance',
          'workflowstage' => 'App\\Models\\WorkflowStage',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'workflowversion' => 'App\\Models\\WorkflowVersion',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
          'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
          'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
         'functionName' => 'cancelInstance',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'user' => 'App\\Models\\User',
            'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
            'workflowinstance' => 'App\\Models\\WorkflowInstance',
            'workflowstage' => 'App\\Models\\WorkflowStage',
            'workflowtask' => 'App\\Models\\WorkflowTask',
            'workflowversion' => 'App\\Models\\WorkflowVersion',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
            'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
            'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'WorkflowStagePayload' => true,
            'WorkflowStageData' => true,
            'WorkflowDefinitionCreatePayload' => true,
            'WorkflowDefinitionUpdatePayload' => true,
            'WorkflowInstanceStartPayload' => true,
            'WorkflowTaskDecisionPayload' => true,
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
          'WorkflowStagePayload' => true,
          'WorkflowStageData' => true,
          'WorkflowDefinitionCreatePayload' => true,
          'WorkflowDefinitionUpdatePayload' => true,
          'WorkflowInstanceStartPayload' => true,
          'WorkflowTaskDecisionPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '4d2c8c8a2dfa1ac4688b5bb7e29db6dd' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
          'workflowinstance' => 'App\\Models\\WorkflowInstance',
          'workflowstage' => 'App\\Models\\WorkflowStage',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'workflowversion' => 'App\\Models\\WorkflowVersion',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
          'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
          'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
         'functionName' => 'createVersion',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'user' => 'App\\Models\\User',
            'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
            'workflowinstance' => 'App\\Models\\WorkflowInstance',
            'workflowstage' => 'App\\Models\\WorkflowStage',
            'workflowtask' => 'App\\Models\\WorkflowTask',
            'workflowversion' => 'App\\Models\\WorkflowVersion',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
            'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
            'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'WorkflowStagePayload' => true,
            'WorkflowStageData' => true,
            'WorkflowDefinitionCreatePayload' => true,
            'WorkflowDefinitionUpdatePayload' => true,
            'WorkflowInstanceStartPayload' => true,
            'WorkflowTaskDecisionPayload' => true,
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
          'WorkflowStagePayload' => true,
          'WorkflowStageData' => true,
          'WorkflowDefinitionCreatePayload' => true,
          'WorkflowDefinitionUpdatePayload' => true,
          'WorkflowInstanceStartPayload' => true,
          'WorkflowTaskDecisionPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'e436fe3d643337903e07c0123d853fea' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
          'workflowinstance' => 'App\\Models\\WorkflowInstance',
          'workflowstage' => 'App\\Models\\WorkflowStage',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'workflowversion' => 'App\\Models\\WorkflowVersion',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
          'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
          'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
         'functionName' => 'normalizeStagePayloads',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'user' => 'App\\Models\\User',
            'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
            'workflowinstance' => 'App\\Models\\WorkflowInstance',
            'workflowstage' => 'App\\Models\\WorkflowStage',
            'workflowtask' => 'App\\Models\\WorkflowTask',
            'workflowversion' => 'App\\Models\\WorkflowVersion',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
            'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
            'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'WorkflowStagePayload' => true,
            'WorkflowStageData' => true,
            'WorkflowDefinitionCreatePayload' => true,
            'WorkflowDefinitionUpdatePayload' => true,
            'WorkflowInstanceStartPayload' => true,
            'WorkflowTaskDecisionPayload' => true,
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
          'WorkflowStagePayload' => true,
          'WorkflowStageData' => true,
          'WorkflowDefinitionCreatePayload' => true,
          'WorkflowDefinitionUpdatePayload' => true,
          'WorkflowInstanceStartPayload' => true,
          'WorkflowTaskDecisionPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'ab0e1b89fe053e4527b1df0661cb45bc' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
          'workflowinstance' => 'App\\Models\\WorkflowInstance',
          'workflowstage' => 'App\\Models\\WorkflowStage',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'workflowversion' => 'App\\Models\\WorkflowVersion',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
          'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
          'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
         'functionName' => 'publishDefinition',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'user' => 'App\\Models\\User',
            'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
            'workflowinstance' => 'App\\Models\\WorkflowInstance',
            'workflowstage' => 'App\\Models\\WorkflowStage',
            'workflowtask' => 'App\\Models\\WorkflowTask',
            'workflowversion' => 'App\\Models\\WorkflowVersion',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
            'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
            'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'WorkflowStagePayload' => true,
            'WorkflowStageData' => true,
            'WorkflowDefinitionCreatePayload' => true,
            'WorkflowDefinitionUpdatePayload' => true,
            'WorkflowInstanceStartPayload' => true,
            'WorkflowTaskDecisionPayload' => true,
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
          'WorkflowStagePayload' => true,
          'WorkflowStageData' => true,
          'WorkflowDefinitionCreatePayload' => true,
          'WorkflowDefinitionUpdatePayload' => true,
          'WorkflowInstanceStartPayload' => true,
          'WorkflowTaskDecisionPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'bc5dde01d9bce55043961804addc6636' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
          'workflowinstance' => 'App\\Models\\WorkflowInstance',
          'workflowstage' => 'App\\Models\\WorkflowStage',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'workflowversion' => 'App\\Models\\WorkflowVersion',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
          'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
          'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
         'functionName' => 'createTaskForStage',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'user' => 'App\\Models\\User',
            'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
            'workflowinstance' => 'App\\Models\\WorkflowInstance',
            'workflowstage' => 'App\\Models\\WorkflowStage',
            'workflowtask' => 'App\\Models\\WorkflowTask',
            'workflowversion' => 'App\\Models\\WorkflowVersion',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
            'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
            'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'WorkflowStagePayload' => true,
            'WorkflowStageData' => true,
            'WorkflowDefinitionCreatePayload' => true,
            'WorkflowDefinitionUpdatePayload' => true,
            'WorkflowInstanceStartPayload' => true,
            'WorkflowTaskDecisionPayload' => true,
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
          'WorkflowStagePayload' => true,
          'WorkflowStageData' => true,
          'WorkflowDefinitionCreatePayload' => true,
          'WorkflowDefinitionUpdatePayload' => true,
          'WorkflowInstanceStartPayload' => true,
          'WorkflowTaskDecisionPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '8ec5be6907d91abc21d9e1800e3c7b54' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
          'workflowinstance' => 'App\\Models\\WorkflowInstance',
          'workflowstage' => 'App\\Models\\WorkflowStage',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'workflowversion' => 'App\\Models\\WorkflowVersion',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
          'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
          'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
         'functionName' => 'resolveApprover',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'user' => 'App\\Models\\User',
            'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
            'workflowinstance' => 'App\\Models\\WorkflowInstance',
            'workflowstage' => 'App\\Models\\WorkflowStage',
            'workflowtask' => 'App\\Models\\WorkflowTask',
            'workflowversion' => 'App\\Models\\WorkflowVersion',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
            'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
            'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'WorkflowStagePayload' => true,
            'WorkflowStageData' => true,
            'WorkflowDefinitionCreatePayload' => true,
            'WorkflowDefinitionUpdatePayload' => true,
            'WorkflowInstanceStartPayload' => true,
            'WorkflowTaskDecisionPayload' => true,
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
          'WorkflowStagePayload' => true,
          'WorkflowStageData' => true,
          'WorkflowDefinitionCreatePayload' => true,
          'WorkflowDefinitionUpdatePayload' => true,
          'WorkflowInstanceStartPayload' => true,
          'WorkflowTaskDecisionPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '9ae8b71df61a9510b23f1090f1d1e6de' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
          'workflowinstance' => 'App\\Models\\WorkflowInstance',
          'workflowstage' => 'App\\Models\\WorkflowStage',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'workflowversion' => 'App\\Models\\WorkflowVersion',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
          'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
          'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
         'functionName' => 'resolveEmployeeManagerApprover',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'user' => 'App\\Models\\User',
            'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
            'workflowinstance' => 'App\\Models\\WorkflowInstance',
            'workflowstage' => 'App\\Models\\WorkflowStage',
            'workflowtask' => 'App\\Models\\WorkflowTask',
            'workflowversion' => 'App\\Models\\WorkflowVersion',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
            'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
            'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'WorkflowStagePayload' => true,
            'WorkflowStageData' => true,
            'WorkflowDefinitionCreatePayload' => true,
            'WorkflowDefinitionUpdatePayload' => true,
            'WorkflowInstanceStartPayload' => true,
            'WorkflowTaskDecisionPayload' => true,
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
          'WorkflowStagePayload' => true,
          'WorkflowStageData' => true,
          'WorkflowDefinitionCreatePayload' => true,
          'WorkflowDefinitionUpdatePayload' => true,
          'WorkflowInstanceStartPayload' => true,
          'WorkflowTaskDecisionPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '641427dcdb1540729f7606e91e9d9a60' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
         'uses' => 
        array (
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
          'workflowinstance' => 'App\\Models\\WorkflowInstance',
          'workflowstage' => 'App\\Models\\WorkflowStage',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'workflowversion' => 'App\\Models\\WorkflowVersion',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
          'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
          'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
         'functionName' => 'resolvePayloadUserApprover',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\Platform\\Workflow\\Services',
           'uses' => 
          array (
            'employee' => 'App\\Models\\Employee',
            'user' => 'App\\Models\\User',
            'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
            'workflowinstance' => 'App\\Models\\WorkflowInstance',
            'workflowstage' => 'App\\Models\\WorkflowStage',
            'workflowtask' => 'App\\Models\\WorkflowTask',
            'workflowversion' => 'App\\Models\\WorkflowVersion',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
            'workflowtaskassigned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned',
            'dispatcher' => 'Illuminate\\Contracts\\Events\\Dispatcher',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'WorkflowStagePayload' => true,
            'WorkflowStageData' => true,
            'WorkflowDefinitionCreatePayload' => true,
            'WorkflowDefinitionUpdatePayload' => true,
            'WorkflowInstanceStartPayload' => true,
            'WorkflowTaskDecisionPayload' => true,
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
          'WorkflowStagePayload' => true,
          'WorkflowStageData' => true,
          'WorkflowDefinitionCreatePayload' => true,
          'WorkflowDefinitionUpdatePayload' => true,
          'WorkflowInstanceStartPayload' => true,
          'WorkflowTaskDecisionPayload' => true,
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
      '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/Platform/Workflow/Services/WorkflowService.php' => 'f4a879faeb0be6480324c19ce3954af057c53fc4f3e1b2a777d9e82c72a67759',
    ),
  ),
));