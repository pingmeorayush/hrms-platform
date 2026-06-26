<?php declare(strict_types = 1);

// ftm-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/AttendanceManagement/Listeners/SyncAttendanceCorrectionWorkflowState.php
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v5-2.3.2',
   'data' => 
  array (
    0 => 
    array (
      '26528924798990fcb1717216525f4b9b' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Listeners',
         'uses' => 
        array (
          'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'user' => 'App\\Models\\User',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'notificationservice' => 'App\\Modules\\Platform\\Notifications\\Services\\NotificationService',
          'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
          'carbon' => 'Carbon\\Carbon',
          'db' => 'Illuminate\\Support\\Facades\\DB',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Listeners\\SyncAttendanceCorrectionWorkflowState',
         'functionName' => NULL,
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'b053758e1ec6bfd9ee6bef72ec54b4a8' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Listeners',
         'uses' => 
        array (
          'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'user' => 'App\\Models\\User',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'notificationservice' => 'App\\Modules\\Platform\\Notifications\\Services\\NotificationService',
          'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
          'carbon' => 'Carbon\\Carbon',
          'db' => 'Illuminate\\Support\\Facades\\DB',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Listeners\\SyncAttendanceCorrectionWorkflowState',
         'functionName' => '__construct',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '031f8db7a949b4ddf353f7bb6fd719f9' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Listeners',
         'uses' => 
        array (
          'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'user' => 'App\\Models\\User',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'notificationservice' => 'App\\Modules\\Platform\\Notifications\\Services\\NotificationService',
          'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
          'carbon' => 'Carbon\\Carbon',
          'db' => 'Illuminate\\Support\\Facades\\DB',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Listeners\\SyncAttendanceCorrectionWorkflowState',
         'functionName' => 'handle',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '8a92de5373c51389cdbe400893d45805' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Listeners',
         'uses' => 
        array (
          'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'user' => 'App\\Models\\User',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'notificationservice' => 'App\\Modules\\Platform\\Notifications\\Services\\NotificationService',
          'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
          'carbon' => 'Carbon\\Carbon',
          'db' => 'Illuminate\\Support\\Facades\\DB',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Listeners\\SyncAttendanceCorrectionWorkflowState',
         'functionName' => 'markApproved',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '3f4d4e2e044d55984aa81f9c08690e63' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Listeners',
         'uses' => 
        array (
          'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'user' => 'App\\Models\\User',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'notificationservice' => 'App\\Modules\\Platform\\Notifications\\Services\\NotificationService',
          'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
          'carbon' => 'Carbon\\Carbon',
          'db' => 'Illuminate\\Support\\Facades\\DB',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Listeners\\SyncAttendanceCorrectionWorkflowState',
         'functionName' => 'markRejected',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '6b416e2491c6a5135fbe1b08b202debb' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Listeners',
         'uses' => 
        array (
          'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'user' => 'App\\Models\\User',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'notificationservice' => 'App\\Modules\\Platform\\Notifications\\Services\\NotificationService',
          'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
          'carbon' => 'Carbon\\Carbon',
          'db' => 'Illuminate\\Support\\Facades\\DB',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Listeners\\SyncAttendanceCorrectionWorkflowState',
         'functionName' => 'markChangesRequested',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'e3420d00aa11046b4db639ba280ed186' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Listeners',
         'uses' => 
        array (
          'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'user' => 'App\\Models\\User',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'notificationservice' => 'App\\Modules\\Platform\\Notifications\\Services\\NotificationService',
          'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
          'carbon' => 'Carbon\\Carbon',
          'db' => 'Illuminate\\Support\\Facades\\DB',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Listeners\\SyncAttendanceCorrectionWorkflowState',
         'functionName' => 'parseTimestamp',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '5f530359feb40d5d0a75641fc5f35f16' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Listeners',
         'uses' => 
        array (
          'attendancecorrection' => 'App\\Models\\AttendanceCorrection',
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'user' => 'App\\Models\\User',
          'workflowtask' => 'App\\Models\\WorkflowTask',
          'attendancecalculationservice' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceCalculationService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'notificationservice' => 'App\\Modules\\Platform\\Notifications\\Services\\NotificationService',
          'workflowinstancetransitioned' => 'App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned',
          'carbon' => 'Carbon\\Carbon',
          'db' => 'Illuminate\\Support\\Facades\\DB',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Listeners\\SyncAttendanceCorrectionWorkflowState',
         'functionName' => 'buildRecordSnapshot',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
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
      '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/AttendanceManagement/Listeners/SyncAttendanceCorrectionWorkflowState.php' => '5f3b236b0419065acd2b539b492b7b542a0816f40bf3ad00506da05907be29fd',
    ),
  ),
));