<?php declare(strict_types = 1);

// ftm-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/AttendanceManagement/Services/AttendanceCorrectionService.php
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v5-2.3.2',
   'data' => 
  array (
    0 => 
    array (
      '386beb59404238e955cc114f3976d4f2' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'user' => 'App\\Models\\User',
          'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCorrectionService',
         'functionName' => NULL,
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
          'AttendanceCorrectionFilters' => true,
          'AttendanceCorrectionPayload' => true,
          'AttendanceCorrectionDecisionPayload' => true,
          'CorrectedAttendanceValues' => true,
          'AttendanceRecordSnapshot' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '145299ac64ad803abced6e1658395c29' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'user' => 'App\\Models\\User',
          'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCorrectionService',
         'functionName' => '__construct',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'company' => 'App\\Models\\Company',
            'user' => 'App\\Models\\User',
            'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
            'workflowtask' => 'App\\Models\\WorkflowTask',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCorrectionService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'AttendanceCorrectionFilters' => true,
            'AttendanceCorrectionPayload' => true,
            'AttendanceCorrectionDecisionPayload' => true,
            'CorrectedAttendanceValues' => true,
            'AttendanceRecordSnapshot' => true,
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
          'AttendanceCorrectionFilters' => true,
          'AttendanceCorrectionPayload' => true,
          'AttendanceCorrectionDecisionPayload' => true,
          'CorrectedAttendanceValues' => true,
          'AttendanceRecordSnapshot' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '88a14492767b06eb57dfdeb5c5013f77' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'user' => 'App\\Models\\User',
          'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCorrectionService',
         'functionName' => 'search',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'company' => 'App\\Models\\Company',
            'user' => 'App\\Models\\User',
            'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
            'workflowtask' => 'App\\Models\\WorkflowTask',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCorrectionService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'AttendanceCorrectionFilters' => true,
            'AttendanceCorrectionPayload' => true,
            'AttendanceCorrectionDecisionPayload' => true,
            'CorrectedAttendanceValues' => true,
            'AttendanceRecordSnapshot' => true,
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
          'AttendanceCorrectionFilters' => true,
          'AttendanceCorrectionPayload' => true,
          'AttendanceCorrectionDecisionPayload' => true,
          'CorrectedAttendanceValues' => true,
          'AttendanceRecordSnapshot' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '6985de098b436184ed03a0bc8abd1deb' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'user' => 'App\\Models\\User',
          'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCorrectionService',
         'functionName' => 'create',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'company' => 'App\\Models\\Company',
            'user' => 'App\\Models\\User',
            'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
            'workflowtask' => 'App\\Models\\WorkflowTask',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCorrectionService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'AttendanceCorrectionFilters' => true,
            'AttendanceCorrectionPayload' => true,
            'AttendanceCorrectionDecisionPayload' => true,
            'CorrectedAttendanceValues' => true,
            'AttendanceRecordSnapshot' => true,
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
          'AttendanceCorrectionFilters' => true,
          'AttendanceCorrectionPayload' => true,
          'AttendanceCorrectionDecisionPayload' => true,
          'CorrectedAttendanceValues' => true,
          'AttendanceRecordSnapshot' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '924ef61bfbac026b9cbac68ce31354e6' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'user' => 'App\\Models\\User',
          'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCorrectionService',
         'functionName' => 'decide',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'company' => 'App\\Models\\Company',
            'user' => 'App\\Models\\User',
            'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
            'workflowtask' => 'App\\Models\\WorkflowTask',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCorrectionService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'AttendanceCorrectionFilters' => true,
            'AttendanceCorrectionPayload' => true,
            'AttendanceCorrectionDecisionPayload' => true,
            'CorrectedAttendanceValues' => true,
            'AttendanceRecordSnapshot' => true,
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
          'AttendanceCorrectionFilters' => true,
          'AttendanceCorrectionPayload' => true,
          'AttendanceCorrectionDecisionPayload' => true,
          'CorrectedAttendanceValues' => true,
          'AttendanceRecordSnapshot' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '254307be8aab6c8c98031a1d88f9bfa8' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'user' => 'App\\Models\\User',
          'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCorrectionService',
         'functionName' => 'resolveRecordForCorrection',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'company' => 'App\\Models\\Company',
            'user' => 'App\\Models\\User',
            'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
            'workflowtask' => 'App\\Models\\WorkflowTask',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCorrectionService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'AttendanceCorrectionFilters' => true,
            'AttendanceCorrectionPayload' => true,
            'AttendanceCorrectionDecisionPayload' => true,
            'CorrectedAttendanceValues' => true,
            'AttendanceRecordSnapshot' => true,
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
          'AttendanceCorrectionFilters' => true,
          'AttendanceCorrectionPayload' => true,
          'AttendanceCorrectionDecisionPayload' => true,
          'CorrectedAttendanceValues' => true,
          'AttendanceRecordSnapshot' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'e53b8480a5074118426c8002045dc876' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'user' => 'App\\Models\\User',
          'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCorrectionService',
         'functionName' => 'normalizeCorrectedValues',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'company' => 'App\\Models\\Company',
            'user' => 'App\\Models\\User',
            'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
            'workflowtask' => 'App\\Models\\WorkflowTask',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCorrectionService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'AttendanceCorrectionFilters' => true,
            'AttendanceCorrectionPayload' => true,
            'AttendanceCorrectionDecisionPayload' => true,
            'CorrectedAttendanceValues' => true,
            'AttendanceRecordSnapshot' => true,
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
          'AttendanceCorrectionFilters' => true,
          'AttendanceCorrectionPayload' => true,
          'AttendanceCorrectionDecisionPayload' => true,
          'CorrectedAttendanceValues' => true,
          'AttendanceRecordSnapshot' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'c2347fbd2690875568f232d3b9f2fdd6' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'user' => 'App\\Models\\User',
          'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCorrectionService',
         'functionName' => 'ensureWorkflowDefinition',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'company' => 'App\\Models\\Company',
            'user' => 'App\\Models\\User',
            'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
            'workflowtask' => 'App\\Models\\WorkflowTask',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCorrectionService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'AttendanceCorrectionFilters' => true,
            'AttendanceCorrectionPayload' => true,
            'AttendanceCorrectionDecisionPayload' => true,
            'CorrectedAttendanceValues' => true,
            'AttendanceRecordSnapshot' => true,
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
          'AttendanceCorrectionFilters' => true,
          'AttendanceCorrectionPayload' => true,
          'AttendanceCorrectionDecisionPayload' => true,
          'CorrectedAttendanceValues' => true,
          'AttendanceRecordSnapshot' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '109d60af8ce74449b27d5724a8bcd3ff' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'user' => 'App\\Models\\User',
          'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCorrectionService',
         'functionName' => 'parseTimestamp',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'company' => 'App\\Models\\Company',
            'user' => 'App\\Models\\User',
            'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
            'workflowtask' => 'App\\Models\\WorkflowTask',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCorrectionService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'AttendanceCorrectionFilters' => true,
            'AttendanceCorrectionPayload' => true,
            'AttendanceCorrectionDecisionPayload' => true,
            'CorrectedAttendanceValues' => true,
            'AttendanceRecordSnapshot' => true,
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
          'AttendanceCorrectionFilters' => true,
          'AttendanceCorrectionPayload' => true,
          'AttendanceCorrectionDecisionPayload' => true,
          'CorrectedAttendanceValues' => true,
          'AttendanceRecordSnapshot' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '44acccee70975a3b1650d24a04751a5e' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'user' => 'App\\Models\\User',
          'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCorrectionService',
         'functionName' => 'buildRecordSnapshot',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'company' => 'App\\Models\\Company',
            'user' => 'App\\Models\\User',
            'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
            'workflowtask' => 'App\\Models\\WorkflowTask',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCorrectionService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'AttendanceCorrectionFilters' => true,
            'AttendanceCorrectionPayload' => true,
            'AttendanceCorrectionDecisionPayload' => true,
            'CorrectedAttendanceValues' => true,
            'AttendanceRecordSnapshot' => true,
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
          'AttendanceCorrectionFilters' => true,
          'AttendanceCorrectionPayload' => true,
          'AttendanceCorrectionDecisionPayload' => true,
          'CorrectedAttendanceValues' => true,
          'AttendanceRecordSnapshot' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'f0e71b759369136c700db9a5a7cda532' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'user' => 'App\\Models\\User',
          'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCorrectionService',
         'functionName' => 'loadCorrection',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'company' => 'App\\Models\\Company',
            'user' => 'App\\Models\\User',
            'workflowdefinition' => 'App\\Models\\WorkflowDefinition',
            'workflowtask' => 'App\\Models\\WorkflowTask',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCorrectionService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'AttendanceCorrectionFilters' => true,
            'AttendanceCorrectionPayload' => true,
            'AttendanceCorrectionDecisionPayload' => true,
            'CorrectedAttendanceValues' => true,
            'AttendanceRecordSnapshot' => true,
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
          'AttendanceCorrectionFilters' => true,
          'AttendanceCorrectionPayload' => true,
          'AttendanceCorrectionDecisionPayload' => true,
          'CorrectedAttendanceValues' => true,
          'AttendanceRecordSnapshot' => true,
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
      '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/AttendanceManagement/Services/AttendanceCorrectionService.php' => '37bd7bd632a6f2633433aee6ca4f204b0c7ba788b2d16f710329d726a3ea145e',
    ),
  ),
));