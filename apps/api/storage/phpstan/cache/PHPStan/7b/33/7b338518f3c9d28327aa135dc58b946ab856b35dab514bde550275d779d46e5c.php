<?php declare(strict_types = 1);

// ftm-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/LeaveManagement/Services/LeaveRequestService.php
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v5-2.3.2',
   'data' => 
  array (
    0 => 
    array (
      '423b26c93806ebf09df4237c69c2b5d8' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\LeaveManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'employee' => 'App\\Models\\Employee',
          'leavebalance' => 'App\\Models\\LeaveBalance',
          'leavepolicy' => 'App\\Models\\LeavePolicy',
          'leaverequest' => 'App\\Models\\LeaveRequest',
          'leavetype' => 'App\\Models\\LeaveType',
          'user' => 'App\\Models\\User',
          'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
          'carbon' => 'Carbon\\Carbon',
          'carbonperiod' => 'Carbon\\CarbonPeriod',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
         'functionName' => NULL,
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
          'LeaveRequestFilters' => true,
          'LeaveRequestSubmitPayload' => true,
          'LeaveRequestActionPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '20cc61602a15dca12238033ec9f70242' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\LeaveManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'employee' => 'App\\Models\\Employee',
          'leavebalance' => 'App\\Models\\LeaveBalance',
          'leavepolicy' => 'App\\Models\\LeavePolicy',
          'leaverequest' => 'App\\Models\\LeaveRequest',
          'leavetype' => 'App\\Models\\LeaveType',
          'user' => 'App\\Models\\User',
          'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
          'carbon' => 'Carbon\\Carbon',
          'carbonperiod' => 'Carbon\\CarbonPeriod',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
         'functionName' => '__construct',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\LeaveManagement\\Services',
           'uses' => 
          array (
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'employee' => 'App\\Models\\Employee',
            'leavebalance' => 'App\\Models\\LeaveBalance',
            'leavepolicy' => 'App\\Models\\LeavePolicy',
            'leaverequest' => 'App\\Models\\LeaveRequest',
            'leavetype' => 'App\\Models\\LeaveType',
            'user' => 'App\\Models\\User',
            'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
            'carbon' => 'Carbon\\Carbon',
            'carbonperiod' => 'Carbon\\CarbonPeriod',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'LeaveRequestFilters' => true,
            'LeaveRequestSubmitPayload' => true,
            'LeaveRequestActionPayload' => true,
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
          'LeaveRequestFilters' => true,
          'LeaveRequestSubmitPayload' => true,
          'LeaveRequestActionPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '1a1364cb220883357d2b4fbd4d7b3f93' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\LeaveManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'employee' => 'App\\Models\\Employee',
          'leavebalance' => 'App\\Models\\LeaveBalance',
          'leavepolicy' => 'App\\Models\\LeavePolicy',
          'leaverequest' => 'App\\Models\\LeaveRequest',
          'leavetype' => 'App\\Models\\LeaveType',
          'user' => 'App\\Models\\User',
          'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
          'carbon' => 'Carbon\\Carbon',
          'carbonperiod' => 'Carbon\\CarbonPeriod',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
         'functionName' => 'search',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\LeaveManagement\\Services',
           'uses' => 
          array (
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'employee' => 'App\\Models\\Employee',
            'leavebalance' => 'App\\Models\\LeaveBalance',
            'leavepolicy' => 'App\\Models\\LeavePolicy',
            'leaverequest' => 'App\\Models\\LeaveRequest',
            'leavetype' => 'App\\Models\\LeaveType',
            'user' => 'App\\Models\\User',
            'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
            'carbon' => 'Carbon\\Carbon',
            'carbonperiod' => 'Carbon\\CarbonPeriod',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'LeaveRequestFilters' => true,
            'LeaveRequestSubmitPayload' => true,
            'LeaveRequestActionPayload' => true,
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
          'LeaveRequestFilters' => true,
          'LeaveRequestSubmitPayload' => true,
          'LeaveRequestActionPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'f4a15b81bb5647880eb68e8c57415669' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\LeaveManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'employee' => 'App\\Models\\Employee',
          'leavebalance' => 'App\\Models\\LeaveBalance',
          'leavepolicy' => 'App\\Models\\LeavePolicy',
          'leaverequest' => 'App\\Models\\LeaveRequest',
          'leavetype' => 'App\\Models\\LeaveType',
          'user' => 'App\\Models\\User',
          'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
          'carbon' => 'Carbon\\Carbon',
          'carbonperiod' => 'Carbon\\CarbonPeriod',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
         'functionName' => 'findForView',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\LeaveManagement\\Services',
           'uses' => 
          array (
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'employee' => 'App\\Models\\Employee',
            'leavebalance' => 'App\\Models\\LeaveBalance',
            'leavepolicy' => 'App\\Models\\LeavePolicy',
            'leaverequest' => 'App\\Models\\LeaveRequest',
            'leavetype' => 'App\\Models\\LeaveType',
            'user' => 'App\\Models\\User',
            'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
            'carbon' => 'Carbon\\Carbon',
            'carbonperiod' => 'Carbon\\CarbonPeriod',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'LeaveRequestFilters' => true,
            'LeaveRequestSubmitPayload' => true,
            'LeaveRequestActionPayload' => true,
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
          'LeaveRequestFilters' => true,
          'LeaveRequestSubmitPayload' => true,
          'LeaveRequestActionPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'c9b5b00d9731d3f1ae7dd1cac0a31cc5' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\LeaveManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'employee' => 'App\\Models\\Employee',
          'leavebalance' => 'App\\Models\\LeaveBalance',
          'leavepolicy' => 'App\\Models\\LeavePolicy',
          'leaverequest' => 'App\\Models\\LeaveRequest',
          'leavetype' => 'App\\Models\\LeaveType',
          'user' => 'App\\Models\\User',
          'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
          'carbon' => 'Carbon\\Carbon',
          'carbonperiod' => 'Carbon\\CarbonPeriod',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
         'functionName' => 'submit',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\LeaveManagement\\Services',
           'uses' => 
          array (
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'employee' => 'App\\Models\\Employee',
            'leavebalance' => 'App\\Models\\LeaveBalance',
            'leavepolicy' => 'App\\Models\\LeavePolicy',
            'leaverequest' => 'App\\Models\\LeaveRequest',
            'leavetype' => 'App\\Models\\LeaveType',
            'user' => 'App\\Models\\User',
            'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
            'carbon' => 'Carbon\\Carbon',
            'carbonperiod' => 'Carbon\\CarbonPeriod',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'LeaveRequestFilters' => true,
            'LeaveRequestSubmitPayload' => true,
            'LeaveRequestActionPayload' => true,
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
          'LeaveRequestFilters' => true,
          'LeaveRequestSubmitPayload' => true,
          'LeaveRequestActionPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '3cb37dde8fcad63aa175c0d999f1adea' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\LeaveManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'employee' => 'App\\Models\\Employee',
          'leavebalance' => 'App\\Models\\LeaveBalance',
          'leavepolicy' => 'App\\Models\\LeavePolicy',
          'leaverequest' => 'App\\Models\\LeaveRequest',
          'leavetype' => 'App\\Models\\LeaveType',
          'user' => 'App\\Models\\User',
          'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
          'carbon' => 'Carbon\\Carbon',
          'carbonperiod' => 'Carbon\\CarbonPeriod',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
         'functionName' => 'update',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\LeaveManagement\\Services',
           'uses' => 
          array (
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'employee' => 'App\\Models\\Employee',
            'leavebalance' => 'App\\Models\\LeaveBalance',
            'leavepolicy' => 'App\\Models\\LeavePolicy',
            'leaverequest' => 'App\\Models\\LeaveRequest',
            'leavetype' => 'App\\Models\\LeaveType',
            'user' => 'App\\Models\\User',
            'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
            'carbon' => 'Carbon\\Carbon',
            'carbonperiod' => 'Carbon\\CarbonPeriod',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'LeaveRequestFilters' => true,
            'LeaveRequestSubmitPayload' => true,
            'LeaveRequestActionPayload' => true,
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
          'LeaveRequestFilters' => true,
          'LeaveRequestSubmitPayload' => true,
          'LeaveRequestActionPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '4fef0bfa34c312fe19146604015b705f' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\LeaveManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'employee' => 'App\\Models\\Employee',
          'leavebalance' => 'App\\Models\\LeaveBalance',
          'leavepolicy' => 'App\\Models\\LeavePolicy',
          'leaverequest' => 'App\\Models\\LeaveRequest',
          'leavetype' => 'App\\Models\\LeaveType',
          'user' => 'App\\Models\\User',
          'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
          'carbon' => 'Carbon\\Carbon',
          'carbonperiod' => 'Carbon\\CarbonPeriod',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
         'functionName' => 'cancel',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\LeaveManagement\\Services',
           'uses' => 
          array (
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'employee' => 'App\\Models\\Employee',
            'leavebalance' => 'App\\Models\\LeaveBalance',
            'leavepolicy' => 'App\\Models\\LeavePolicy',
            'leaverequest' => 'App\\Models\\LeaveRequest',
            'leavetype' => 'App\\Models\\LeaveType',
            'user' => 'App\\Models\\User',
            'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
            'carbon' => 'Carbon\\Carbon',
            'carbonperiod' => 'Carbon\\CarbonPeriod',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'LeaveRequestFilters' => true,
            'LeaveRequestSubmitPayload' => true,
            'LeaveRequestActionPayload' => true,
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
          'LeaveRequestFilters' => true,
          'LeaveRequestSubmitPayload' => true,
          'LeaveRequestActionPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '1275afe3b8f8d3b7ca98f9f50a6fbd84' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\LeaveManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'employee' => 'App\\Models\\Employee',
          'leavebalance' => 'App\\Models\\LeaveBalance',
          'leavepolicy' => 'App\\Models\\LeavePolicy',
          'leaverequest' => 'App\\Models\\LeaveRequest',
          'leavetype' => 'App\\Models\\LeaveType',
          'user' => 'App\\Models\\User',
          'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
          'carbon' => 'Carbon\\Carbon',
          'carbonperiod' => 'Carbon\\CarbonPeriod',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
         'functionName' => 'ensureEmployeeCanRequestLeave',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\LeaveManagement\\Services',
           'uses' => 
          array (
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'employee' => 'App\\Models\\Employee',
            'leavebalance' => 'App\\Models\\LeaveBalance',
            'leavepolicy' => 'App\\Models\\LeavePolicy',
            'leaverequest' => 'App\\Models\\LeaveRequest',
            'leavetype' => 'App\\Models\\LeaveType',
            'user' => 'App\\Models\\User',
            'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
            'carbon' => 'Carbon\\Carbon',
            'carbonperiod' => 'Carbon\\CarbonPeriod',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'LeaveRequestFilters' => true,
            'LeaveRequestSubmitPayload' => true,
            'LeaveRequestActionPayload' => true,
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
          'LeaveRequestFilters' => true,
          'LeaveRequestSubmitPayload' => true,
          'LeaveRequestActionPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '5cb5ed5109bdd73490237a2ac044c2cc' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\LeaveManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'employee' => 'App\\Models\\Employee',
          'leavebalance' => 'App\\Models\\LeaveBalance',
          'leavepolicy' => 'App\\Models\\LeavePolicy',
          'leaverequest' => 'App\\Models\\LeaveRequest',
          'leavetype' => 'App\\Models\\LeaveType',
          'user' => 'App\\Models\\User',
          'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
          'carbon' => 'Carbon\\Carbon',
          'carbonperiod' => 'Carbon\\CarbonPeriod',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
         'functionName' => 'ensureDatesArePolicyCompliant',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\LeaveManagement\\Services',
           'uses' => 
          array (
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'employee' => 'App\\Models\\Employee',
            'leavebalance' => 'App\\Models\\LeaveBalance',
            'leavepolicy' => 'App\\Models\\LeavePolicy',
            'leaverequest' => 'App\\Models\\LeaveRequest',
            'leavetype' => 'App\\Models\\LeaveType',
            'user' => 'App\\Models\\User',
            'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
            'carbon' => 'Carbon\\Carbon',
            'carbonperiod' => 'Carbon\\CarbonPeriod',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'LeaveRequestFilters' => true,
            'LeaveRequestSubmitPayload' => true,
            'LeaveRequestActionPayload' => true,
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
          'LeaveRequestFilters' => true,
          'LeaveRequestSubmitPayload' => true,
          'LeaveRequestActionPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'c101a397cfedc5d528a3cde7b9a1abc1' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\LeaveManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'employee' => 'App\\Models\\Employee',
          'leavebalance' => 'App\\Models\\LeaveBalance',
          'leavepolicy' => 'App\\Models\\LeavePolicy',
          'leaverequest' => 'App\\Models\\LeaveRequest',
          'leavetype' => 'App\\Models\\LeaveType',
          'user' => 'App\\Models\\User',
          'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
          'carbon' => 'Carbon\\Carbon',
          'carbonperiod' => 'Carbon\\CarbonPeriod',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
         'functionName' => 'ensureNoOverlap',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\LeaveManagement\\Services',
           'uses' => 
          array (
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'employee' => 'App\\Models\\Employee',
            'leavebalance' => 'App\\Models\\LeaveBalance',
            'leavepolicy' => 'App\\Models\\LeavePolicy',
            'leaverequest' => 'App\\Models\\LeaveRequest',
            'leavetype' => 'App\\Models\\LeaveType',
            'user' => 'App\\Models\\User',
            'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
            'carbon' => 'Carbon\\Carbon',
            'carbonperiod' => 'Carbon\\CarbonPeriod',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'LeaveRequestFilters' => true,
            'LeaveRequestSubmitPayload' => true,
            'LeaveRequestActionPayload' => true,
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
          'LeaveRequestFilters' => true,
          'LeaveRequestSubmitPayload' => true,
          'LeaveRequestActionPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'b8e73e269699a8612f9d6f6e714d2f78' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\LeaveManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'employee' => 'App\\Models\\Employee',
          'leavebalance' => 'App\\Models\\LeaveBalance',
          'leavepolicy' => 'App\\Models\\LeavePolicy',
          'leaverequest' => 'App\\Models\\LeaveRequest',
          'leavetype' => 'App\\Models\\LeaveType',
          'user' => 'App\\Models\\User',
          'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
          'carbon' => 'Carbon\\Carbon',
          'carbonperiod' => 'Carbon\\CarbonPeriod',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
         'functionName' => 'resolvePolicy',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\LeaveManagement\\Services',
           'uses' => 
          array (
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'employee' => 'App\\Models\\Employee',
            'leavebalance' => 'App\\Models\\LeaveBalance',
            'leavepolicy' => 'App\\Models\\LeavePolicy',
            'leaverequest' => 'App\\Models\\LeaveRequest',
            'leavetype' => 'App\\Models\\LeaveType',
            'user' => 'App\\Models\\User',
            'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
            'carbon' => 'Carbon\\Carbon',
            'carbonperiod' => 'Carbon\\CarbonPeriod',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'LeaveRequestFilters' => true,
            'LeaveRequestSubmitPayload' => true,
            'LeaveRequestActionPayload' => true,
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
          'LeaveRequestFilters' => true,
          'LeaveRequestSubmitPayload' => true,
          'LeaveRequestActionPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '28c85ff63bff29b6781960695ef7a069' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\LeaveManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'employee' => 'App\\Models\\Employee',
          'leavebalance' => 'App\\Models\\LeaveBalance',
          'leavepolicy' => 'App\\Models\\LeavePolicy',
          'leaverequest' => 'App\\Models\\LeaveRequest',
          'leavetype' => 'App\\Models\\LeaveType',
          'user' => 'App\\Models\\User',
          'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
          'carbon' => 'Carbon\\Carbon',
          'carbonperiod' => 'Carbon\\CarbonPeriod',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
         'functionName' => 'syncAttendanceForApprovedRequest',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\LeaveManagement\\Services',
           'uses' => 
          array (
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'employee' => 'App\\Models\\Employee',
            'leavebalance' => 'App\\Models\\LeaveBalance',
            'leavepolicy' => 'App\\Models\\LeavePolicy',
            'leaverequest' => 'App\\Models\\LeaveRequest',
            'leavetype' => 'App\\Models\\LeaveType',
            'user' => 'App\\Models\\User',
            'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
            'carbon' => 'Carbon\\Carbon',
            'carbonperiod' => 'Carbon\\CarbonPeriod',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'LeaveRequestFilters' => true,
            'LeaveRequestSubmitPayload' => true,
            'LeaveRequestActionPayload' => true,
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
          'LeaveRequestFilters' => true,
          'LeaveRequestSubmitPayload' => true,
          'LeaveRequestActionPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '15293faf33c8d0f9a8a575743febeef9' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\LeaveManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'employee' => 'App\\Models\\Employee',
          'leavebalance' => 'App\\Models\\LeaveBalance',
          'leavepolicy' => 'App\\Models\\LeavePolicy',
          'leaverequest' => 'App\\Models\\LeaveRequest',
          'leavetype' => 'App\\Models\\LeaveType',
          'user' => 'App\\Models\\User',
          'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
          'carbon' => 'Carbon\\Carbon',
          'carbonperiod' => 'Carbon\\CarbonPeriod',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
         'functionName' => 'syncAttendanceForCancelledApprovedRequest',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\LeaveManagement\\Services',
           'uses' => 
          array (
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'employee' => 'App\\Models\\Employee',
            'leavebalance' => 'App\\Models\\LeaveBalance',
            'leavepolicy' => 'App\\Models\\LeavePolicy',
            'leaverequest' => 'App\\Models\\LeaveRequest',
            'leavetype' => 'App\\Models\\LeaveType',
            'user' => 'App\\Models\\User',
            'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
            'carbon' => 'Carbon\\Carbon',
            'carbonperiod' => 'Carbon\\CarbonPeriod',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'LeaveRequestFilters' => true,
            'LeaveRequestSubmitPayload' => true,
            'LeaveRequestActionPayload' => true,
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
          'LeaveRequestFilters' => true,
          'LeaveRequestSubmitPayload' => true,
          'LeaveRequestActionPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '3d1690574c4129feed69416da296ec5f' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\LeaveManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'employee' => 'App\\Models\\Employee',
          'leavebalance' => 'App\\Models\\LeaveBalance',
          'leavepolicy' => 'App\\Models\\LeavePolicy',
          'leaverequest' => 'App\\Models\\LeaveRequest',
          'leavetype' => 'App\\Models\\LeaveType',
          'user' => 'App\\Models\\User',
          'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
          'carbon' => 'Carbon\\Carbon',
          'carbonperiod' => 'Carbon\\CarbonPeriod',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
         'functionName' => 'nextDate',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\LeaveManagement\\Services',
           'uses' => 
          array (
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'employee' => 'App\\Models\\Employee',
            'leavebalance' => 'App\\Models\\LeaveBalance',
            'leavepolicy' => 'App\\Models\\LeavePolicy',
            'leaverequest' => 'App\\Models\\LeaveRequest',
            'leavetype' => 'App\\Models\\LeaveType',
            'user' => 'App\\Models\\User',
            'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'workflowservice' => 'App\\Modules\\Platform\\Workflow\\Services\\WorkflowService',
            'carbon' => 'Carbon\\Carbon',
            'carbonperiod' => 'Carbon\\CarbonPeriod',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\LeaveManagement\\Services\\LeaveRequestService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'LeaveRequestFilters' => true,
            'LeaveRequestSubmitPayload' => true,
            'LeaveRequestActionPayload' => true,
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
          'LeaveRequestFilters' => true,
          'LeaveRequestSubmitPayload' => true,
          'LeaveRequestActionPayload' => true,
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
      '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/LeaveManagement/Services/LeaveRequestService.php' => '23f4391b66e3bfa20b0752ba3ffcb4abb39eb3edbc5c933858a8e5094716d478',
    ),
  ),
));