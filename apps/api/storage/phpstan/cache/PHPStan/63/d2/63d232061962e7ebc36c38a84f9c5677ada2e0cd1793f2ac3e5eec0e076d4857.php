<?php declare(strict_types = 1);

// ftm-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/AttendanceManagement/Services/AttendanceRecordService.php
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v5-2.3.2',
   'data' => 
  array (
    0 => 
    array (
      '86033620df2a1ed2e3041f1fb9cf7820' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceRecordService',
         'functionName' => NULL,
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
          'AttendanceRecordFilters' => true,
          'AttendanceCapturePayload' => true,
          'AttendanceCaptureContext' => true,
          'AttendanceCaptureMetadata' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '5ed11460ca218eb978fffb648842a38f' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceRecordService',
         'functionName' => '__construct',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'company' => 'App\\Models\\Company',
            'employee' => 'App\\Models\\Employee',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceRecordService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'AttendanceRecordFilters' => true,
            'AttendanceCapturePayload' => true,
            'AttendanceCaptureContext' => true,
            'AttendanceCaptureMetadata' => true,
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
          'AttendanceRecordFilters' => true,
          'AttendanceCapturePayload' => true,
          'AttendanceCaptureContext' => true,
          'AttendanceCaptureMetadata' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '34b12f17a8852d18f372f98689baddbc' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceRecordService',
         'functionName' => 'search',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'company' => 'App\\Models\\Company',
            'employee' => 'App\\Models\\Employee',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceRecordService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'AttendanceRecordFilters' => true,
            'AttendanceCapturePayload' => true,
            'AttendanceCaptureContext' => true,
            'AttendanceCaptureMetadata' => true,
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
          'AttendanceRecordFilters' => true,
          'AttendanceCapturePayload' => true,
          'AttendanceCaptureContext' => true,
          'AttendanceCaptureMetadata' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '4dcf0ce5da06bcb25ad21ba816e6f9c8' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceRecordService',
         'functionName' => 'findForView',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'company' => 'App\\Models\\Company',
            'employee' => 'App\\Models\\Employee',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceRecordService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'AttendanceRecordFilters' => true,
            'AttendanceCapturePayload' => true,
            'AttendanceCaptureContext' => true,
            'AttendanceCaptureMetadata' => true,
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
          'AttendanceRecordFilters' => true,
          'AttendanceCapturePayload' => true,
          'AttendanceCaptureContext' => true,
          'AttendanceCaptureMetadata' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'e629efba767f95e6f0ccdd57d9fd5d10' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceRecordService',
         'functionName' => 'checkIn',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'company' => 'App\\Models\\Company',
            'employee' => 'App\\Models\\Employee',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceRecordService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'AttendanceRecordFilters' => true,
            'AttendanceCapturePayload' => true,
            'AttendanceCaptureContext' => true,
            'AttendanceCaptureMetadata' => true,
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
          'AttendanceRecordFilters' => true,
          'AttendanceCapturePayload' => true,
          'AttendanceCaptureContext' => true,
          'AttendanceCaptureMetadata' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '6f4dff71790ce4da086afc8d7ed9bc1b' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceRecordService',
         'functionName' => 'checkOut',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'company' => 'App\\Models\\Company',
            'employee' => 'App\\Models\\Employee',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceRecordService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'AttendanceRecordFilters' => true,
            'AttendanceCapturePayload' => true,
            'AttendanceCaptureContext' => true,
            'AttendanceCaptureMetadata' => true,
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
          'AttendanceRecordFilters' => true,
          'AttendanceCapturePayload' => true,
          'AttendanceCaptureContext' => true,
          'AttendanceCaptureMetadata' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'de7daed196076a29351cee2ad1c81c41' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceRecordService',
         'functionName' => 'resolveLinkedEmployee',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'company' => 'App\\Models\\Company',
            'employee' => 'App\\Models\\Employee',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceRecordService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'AttendanceRecordFilters' => true,
            'AttendanceCapturePayload' => true,
            'AttendanceCaptureContext' => true,
            'AttendanceCaptureMetadata' => true,
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
          'AttendanceRecordFilters' => true,
          'AttendanceCapturePayload' => true,
          'AttendanceCaptureContext' => true,
          'AttendanceCaptureMetadata' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'a91f2ce19b3d0ace1482250e4dd3c622' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceRecordService',
         'functionName' => 'ensureEmployeeCanCaptureAttendance',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'company' => 'App\\Models\\Company',
            'employee' => 'App\\Models\\Employee',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceRecordService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'AttendanceRecordFilters' => true,
            'AttendanceCapturePayload' => true,
            'AttendanceCaptureContext' => true,
            'AttendanceCaptureMetadata' => true,
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
          'AttendanceRecordFilters' => true,
          'AttendanceCapturePayload' => true,
          'AttendanceCaptureContext' => true,
          'AttendanceCaptureMetadata' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '0ab61f3487a46d3be140b669c3dd90cc' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceRecordService',
         'functionName' => 'ensureCheckInIsAllowed',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'company' => 'App\\Models\\Company',
            'employee' => 'App\\Models\\Employee',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceRecordService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'AttendanceRecordFilters' => true,
            'AttendanceCapturePayload' => true,
            'AttendanceCaptureContext' => true,
            'AttendanceCaptureMetadata' => true,
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
          'AttendanceRecordFilters' => true,
          'AttendanceCapturePayload' => true,
          'AttendanceCaptureContext' => true,
          'AttendanceCaptureMetadata' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '02ad68e9527a4082289605ebc74a6825' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceRecordService',
         'functionName' => 'resolveCapturedAt',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'company' => 'App\\Models\\Company',
            'employee' => 'App\\Models\\Employee',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceRecordService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'AttendanceRecordFilters' => true,
            'AttendanceCapturePayload' => true,
            'AttendanceCaptureContext' => true,
            'AttendanceCaptureMetadata' => true,
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
          'AttendanceRecordFilters' => true,
          'AttendanceCapturePayload' => true,
          'AttendanceCaptureContext' => true,
          'AttendanceCaptureMetadata' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '41731ecf598eefc9f010be81e3af3894' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceRecordService',
         'functionName' => 'parseCapturedAt',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'company' => 'App\\Models\\Company',
            'employee' => 'App\\Models\\Employee',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceRecordService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'AttendanceRecordFilters' => true,
            'AttendanceCapturePayload' => true,
            'AttendanceCaptureContext' => true,
            'AttendanceCaptureMetadata' => true,
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
          'AttendanceRecordFilters' => true,
          'AttendanceCapturePayload' => true,
          'AttendanceCaptureContext' => true,
          'AttendanceCaptureMetadata' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'cb7356a187cf881389105c25da5ce96c' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceRecordService',
         'functionName' => 'extractCaptureMetadata',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'company' => 'App\\Models\\Company',
            'employee' => 'App\\Models\\Employee',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceRecordService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'AttendanceRecordFilters' => true,
            'AttendanceCapturePayload' => true,
            'AttendanceCaptureContext' => true,
            'AttendanceCaptureMetadata' => true,
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
          'AttendanceRecordFilters' => true,
          'AttendanceCapturePayload' => true,
          'AttendanceCaptureContext' => true,
          'AttendanceCaptureMetadata' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '3bb2458697b015ad1a3c55069c0e70be' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'attendancerecord' => 'App\\Models\\AttendanceRecord',
          'company' => 'App\\Models\\Company',
          'employee' => 'App\\Models\\Employee',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceRecordService',
         'functionName' => 'nextDate',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'attendancerecord' => 'App\\Models\\AttendanceRecord',
            'company' => 'App\\Models\\Company',
            'employee' => 'App\\Models\\Employee',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceRecordService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'AttendanceRecordFilters' => true,
            'AttendanceCapturePayload' => true,
            'AttendanceCaptureContext' => true,
            'AttendanceCaptureMetadata' => true,
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
          'AttendanceRecordFilters' => true,
          'AttendanceCapturePayload' => true,
          'AttendanceCaptureContext' => true,
          'AttendanceCaptureMetadata' => true,
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
      '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/AttendanceManagement/Services/AttendanceRecordService.php' => 'fd5cff8421e9f1edfa49cfcb006a1ef9db230c8c6324f8967be2be0892cb71a7',
    ),
  ),
));