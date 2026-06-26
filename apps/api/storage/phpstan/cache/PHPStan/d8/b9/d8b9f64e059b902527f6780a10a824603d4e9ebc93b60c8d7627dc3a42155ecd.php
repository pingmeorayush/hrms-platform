<?php declare(strict_types = 1);

// ftm-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/AttendanceManagement/Services/AttendanceSchedulingService.php
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v5-2.3.2',
   'data' => 
  array (
    0 => 
    array (
      '80e484f6bbb89b73a0447f8c1ce4c4e3' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'shift' => 'App\\Models\\Shift',
          'shiftassignment' => 'App\\Models\\ShiftAssignment',
          'shiftroster' => 'App\\Models\\ShiftRoster',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
         'functionName' => NULL,
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
          'ShiftPayload' => true,
          'ShiftAssignmentPayload' => true,
          'ShiftRosterEntry' => true,
          'ShiftRosterBatchPayload' => true,
          'ShiftRosterPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '0eac444adfea2aad51e66033a6106f07' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'shift' => 'App\\Models\\Shift',
          'shiftassignment' => 'App\\Models\\ShiftAssignment',
          'shiftroster' => 'App\\Models\\ShiftRoster',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
         'functionName' => '__construct',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'shift' => 'App\\Models\\Shift',
            'shiftassignment' => 'App\\Models\\ShiftAssignment',
            'shiftroster' => 'App\\Models\\ShiftRoster',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'ShiftPayload' => true,
            'ShiftAssignmentPayload' => true,
            'ShiftRosterEntry' => true,
            'ShiftRosterBatchPayload' => true,
            'ShiftRosterPayload' => true,
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
          'ShiftPayload' => true,
          'ShiftAssignmentPayload' => true,
          'ShiftRosterEntry' => true,
          'ShiftRosterBatchPayload' => true,
          'ShiftRosterPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '757f35059418aaa820d455b6fadad402' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'shift' => 'App\\Models\\Shift',
          'shiftassignment' => 'App\\Models\\ShiftAssignment',
          'shiftroster' => 'App\\Models\\ShiftRoster',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
         'functionName' => 'createShift',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'shift' => 'App\\Models\\Shift',
            'shiftassignment' => 'App\\Models\\ShiftAssignment',
            'shiftroster' => 'App\\Models\\ShiftRoster',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'ShiftPayload' => true,
            'ShiftAssignmentPayload' => true,
            'ShiftRosterEntry' => true,
            'ShiftRosterBatchPayload' => true,
            'ShiftRosterPayload' => true,
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
          'ShiftPayload' => true,
          'ShiftAssignmentPayload' => true,
          'ShiftRosterEntry' => true,
          'ShiftRosterBatchPayload' => true,
          'ShiftRosterPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '9c87dc349a532332be3d4e2477075d43' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'shift' => 'App\\Models\\Shift',
          'shiftassignment' => 'App\\Models\\ShiftAssignment',
          'shiftroster' => 'App\\Models\\ShiftRoster',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
         'functionName' => 'updateShift',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'shift' => 'App\\Models\\Shift',
            'shiftassignment' => 'App\\Models\\ShiftAssignment',
            'shiftroster' => 'App\\Models\\ShiftRoster',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'ShiftPayload' => true,
            'ShiftAssignmentPayload' => true,
            'ShiftRosterEntry' => true,
            'ShiftRosterBatchPayload' => true,
            'ShiftRosterPayload' => true,
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
          'ShiftPayload' => true,
          'ShiftAssignmentPayload' => true,
          'ShiftRosterEntry' => true,
          'ShiftRosterBatchPayload' => true,
          'ShiftRosterPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '9f5ff64d65a3f0ac7e634b312aebfcdb' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'shift' => 'App\\Models\\Shift',
          'shiftassignment' => 'App\\Models\\ShiftAssignment',
          'shiftroster' => 'App\\Models\\ShiftRoster',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
         'functionName' => 'createShiftAssignment',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'shift' => 'App\\Models\\Shift',
            'shiftassignment' => 'App\\Models\\ShiftAssignment',
            'shiftroster' => 'App\\Models\\ShiftRoster',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'ShiftPayload' => true,
            'ShiftAssignmentPayload' => true,
            'ShiftRosterEntry' => true,
            'ShiftRosterBatchPayload' => true,
            'ShiftRosterPayload' => true,
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
          'ShiftPayload' => true,
          'ShiftAssignmentPayload' => true,
          'ShiftRosterEntry' => true,
          'ShiftRosterBatchPayload' => true,
          'ShiftRosterPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '1d01da08a70d34ecaf5cc1910cda190c' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'shift' => 'App\\Models\\Shift',
          'shiftassignment' => 'App\\Models\\ShiftAssignment',
          'shiftroster' => 'App\\Models\\ShiftRoster',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
         'functionName' => 'updateShiftAssignment',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'shift' => 'App\\Models\\Shift',
            'shiftassignment' => 'App\\Models\\ShiftAssignment',
            'shiftroster' => 'App\\Models\\ShiftRoster',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'ShiftPayload' => true,
            'ShiftAssignmentPayload' => true,
            'ShiftRosterEntry' => true,
            'ShiftRosterBatchPayload' => true,
            'ShiftRosterPayload' => true,
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
          'ShiftPayload' => true,
          'ShiftAssignmentPayload' => true,
          'ShiftRosterEntry' => true,
          'ShiftRosterBatchPayload' => true,
          'ShiftRosterPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '8807ef3b148d75859eff24e84eae51b7' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'shift' => 'App\\Models\\Shift',
          'shiftassignment' => 'App\\Models\\ShiftAssignment',
          'shiftroster' => 'App\\Models\\ShiftRoster',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
         'functionName' => 'createRosters',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'shift' => 'App\\Models\\Shift',
            'shiftassignment' => 'App\\Models\\ShiftAssignment',
            'shiftroster' => 'App\\Models\\ShiftRoster',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'ShiftPayload' => true,
            'ShiftAssignmentPayload' => true,
            'ShiftRosterEntry' => true,
            'ShiftRosterBatchPayload' => true,
            'ShiftRosterPayload' => true,
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
          'ShiftPayload' => true,
          'ShiftAssignmentPayload' => true,
          'ShiftRosterEntry' => true,
          'ShiftRosterBatchPayload' => true,
          'ShiftRosterPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '0d16a58369144be8f921260f5a413166' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'shift' => 'App\\Models\\Shift',
          'shiftassignment' => 'App\\Models\\ShiftAssignment',
          'shiftroster' => 'App\\Models\\ShiftRoster',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
         'functionName' => 'updateRoster',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'shift' => 'App\\Models\\Shift',
            'shiftassignment' => 'App\\Models\\ShiftAssignment',
            'shiftroster' => 'App\\Models\\ShiftRoster',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'ShiftPayload' => true,
            'ShiftAssignmentPayload' => true,
            'ShiftRosterEntry' => true,
            'ShiftRosterBatchPayload' => true,
            'ShiftRosterPayload' => true,
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
          'ShiftPayload' => true,
          'ShiftAssignmentPayload' => true,
          'ShiftRosterEntry' => true,
          'ShiftRosterBatchPayload' => true,
          'ShiftRosterPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'd982720a6bdd96ed18e09a8f1c10cd10' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'shift' => 'App\\Models\\Shift',
          'shiftassignment' => 'App\\Models\\ShiftAssignment',
          'shiftroster' => 'App\\Models\\ShiftRoster',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
         'functionName' => 'normalizeShiftPayload',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'shift' => 'App\\Models\\Shift',
            'shiftassignment' => 'App\\Models\\ShiftAssignment',
            'shiftroster' => 'App\\Models\\ShiftRoster',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'ShiftPayload' => true,
            'ShiftAssignmentPayload' => true,
            'ShiftRosterEntry' => true,
            'ShiftRosterBatchPayload' => true,
            'ShiftRosterPayload' => true,
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
          'ShiftPayload' => true,
          'ShiftAssignmentPayload' => true,
          'ShiftRosterEntry' => true,
          'ShiftRosterBatchPayload' => true,
          'ShiftRosterPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '187dddac512ce4afce9c72125b527baa' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'shift' => 'App\\Models\\Shift',
          'shiftassignment' => 'App\\Models\\ShiftAssignment',
          'shiftroster' => 'App\\Models\\ShiftRoster',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
         'functionName' => 'normalizeRosterEntries',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'shift' => 'App\\Models\\Shift',
            'shiftassignment' => 'App\\Models\\ShiftAssignment',
            'shiftroster' => 'App\\Models\\ShiftRoster',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'ShiftPayload' => true,
            'ShiftAssignmentPayload' => true,
            'ShiftRosterEntry' => true,
            'ShiftRosterBatchPayload' => true,
            'ShiftRosterPayload' => true,
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
          'ShiftPayload' => true,
          'ShiftAssignmentPayload' => true,
          'ShiftRosterEntry' => true,
          'ShiftRosterBatchPayload' => true,
          'ShiftRosterPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'b98b19b0d410132e55e1845509205e59' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'shift' => 'App\\Models\\Shift',
          'shiftassignment' => 'App\\Models\\ShiftAssignment',
          'shiftroster' => 'App\\Models\\ShiftRoster',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
         'functionName' => 'ensureAssignmentDoesNotOverlap',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'shift' => 'App\\Models\\Shift',
            'shiftassignment' => 'App\\Models\\ShiftAssignment',
            'shiftroster' => 'App\\Models\\ShiftRoster',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'ShiftPayload' => true,
            'ShiftAssignmentPayload' => true,
            'ShiftRosterEntry' => true,
            'ShiftRosterBatchPayload' => true,
            'ShiftRosterPayload' => true,
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
          'ShiftPayload' => true,
          'ShiftAssignmentPayload' => true,
          'ShiftRosterEntry' => true,
          'ShiftRosterBatchPayload' => true,
          'ShiftRosterPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '55a5578749c4bc968d33db5a630f79fd' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'shift' => 'App\\Models\\Shift',
          'shiftassignment' => 'App\\Models\\ShiftAssignment',
          'shiftroster' => 'App\\Models\\ShiftRoster',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
         'functionName' => 'ensureRosterEntriesDoNotConflict',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'shift' => 'App\\Models\\Shift',
            'shiftassignment' => 'App\\Models\\ShiftAssignment',
            'shiftroster' => 'App\\Models\\ShiftRoster',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'ShiftPayload' => true,
            'ShiftAssignmentPayload' => true,
            'ShiftRosterEntry' => true,
            'ShiftRosterBatchPayload' => true,
            'ShiftRosterPayload' => true,
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
          'ShiftPayload' => true,
          'ShiftAssignmentPayload' => true,
          'ShiftRosterEntry' => true,
          'ShiftRosterBatchPayload' => true,
          'ShiftRosterPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'a8422812eb2dce591fd6411dcc78b636' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'shift' => 'App\\Models\\Shift',
          'shiftassignment' => 'App\\Models\\ShiftAssignment',
          'shiftroster' => 'App\\Models\\ShiftRoster',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
         'functionName' => 'ensureSingleRosterDoesNotConflict',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'shift' => 'App\\Models\\Shift',
            'shiftassignment' => 'App\\Models\\ShiftAssignment',
            'shiftroster' => 'App\\Models\\ShiftRoster',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'ShiftPayload' => true,
            'ShiftAssignmentPayload' => true,
            'ShiftRosterEntry' => true,
            'ShiftRosterBatchPayload' => true,
            'ShiftRosterPayload' => true,
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
          'ShiftPayload' => true,
          'ShiftAssignmentPayload' => true,
          'ShiftRosterEntry' => true,
          'ShiftRosterBatchPayload' => true,
          'ShiftRosterPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '14a4100e9d50911467f6a4dd752a544a' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'shift' => 'App\\Models\\Shift',
          'shiftassignment' => 'App\\Models\\ShiftAssignment',
          'shiftroster' => 'App\\Models\\ShiftRoster',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
         'functionName' => 'scopeColumn',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'shift' => 'App\\Models\\Shift',
            'shiftassignment' => 'App\\Models\\ShiftAssignment',
            'shiftroster' => 'App\\Models\\ShiftRoster',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'ShiftPayload' => true,
            'ShiftAssignmentPayload' => true,
            'ShiftRosterEntry' => true,
            'ShiftRosterBatchPayload' => true,
            'ShiftRosterPayload' => true,
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
          'ShiftPayload' => true,
          'ShiftAssignmentPayload' => true,
          'ShiftRosterEntry' => true,
          'ShiftRosterBatchPayload' => true,
          'ShiftRosterPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'd90c66d8d21ed57492337c999256882c' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
         'uses' => 
        array (
          'shift' => 'App\\Models\\Shift',
          'shiftassignment' => 'App\\Models\\ShiftAssignment',
          'shiftroster' => 'App\\Models\\ShiftRoster',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'collection' => 'Illuminate\\Support\\Collection',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
         'functionName' => 'nextDate',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\AttendanceManagement\\Services',
           'uses' => 
          array (
            'shift' => 'App\\Models\\Shift',
            'shiftassignment' => 'App\\Models\\ShiftAssignment',
            'shiftroster' => 'App\\Models\\ShiftRoster',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'collection' => 'Illuminate\\Support\\Collection',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\AttendanceManagement\\Services\\AttendanceSchedulingService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'ShiftPayload' => true,
            'ShiftAssignmentPayload' => true,
            'ShiftRosterEntry' => true,
            'ShiftRosterBatchPayload' => true,
            'ShiftRosterPayload' => true,
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
          'ShiftPayload' => true,
          'ShiftAssignmentPayload' => true,
          'ShiftRosterEntry' => true,
          'ShiftRosterBatchPayload' => true,
          'ShiftRosterPayload' => true,
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
      '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/AttendanceManagement/Services/AttendanceSchedulingService.php' => '6d7f86c555198c73f35e553a90bbbdf0055afc7773ecbaa3e5e155ab14f8763b',
    ),
  ),
));