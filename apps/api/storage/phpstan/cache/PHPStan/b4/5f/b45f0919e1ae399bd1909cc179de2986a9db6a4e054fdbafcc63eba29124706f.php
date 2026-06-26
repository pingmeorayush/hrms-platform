<?php declare(strict_types = 1);

// ftm-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/PayrollManagement/Services/PayrollControlService.php
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v5-2.3.2',
   'data' => 
  array (
    0 => 
    array (
      'e931281c5289d592aaac1b32e5312db3' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'payrollcalendar' => 'App\\Models\\PayrollCalendar',
          'payrollperiod' => 'App\\Models\\PayrollPeriod',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
         'functionName' => NULL,
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
          'PayrollPeriodFilters' => true,
          'PayrollRunFilters' => true,
          'PayrollCalendarPayload' => true,
          'PayrollCalendarNormalizedPayload' => true,
          'PayrollPeriodPayload' => true,
          'PayrollRunActionPayload' => true,
          'PayrollPreparationResult' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '98d1da0d6c0db7559bd69818e80fb202' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'payrollcalendar' => 'App\\Models\\PayrollCalendar',
          'payrollperiod' => 'App\\Models\\PayrollPeriod',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
         'functionName' => '__construct',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'payrollcalendar' => 'App\\Models\\PayrollCalendar',
            'payrollperiod' => 'App\\Models\\PayrollPeriod',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollPeriodFilters' => true,
            'PayrollRunFilters' => true,
            'PayrollCalendarPayload' => true,
            'PayrollCalendarNormalizedPayload' => true,
            'PayrollPeriodPayload' => true,
            'PayrollRunActionPayload' => true,
            'PayrollPreparationResult' => true,
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
          'PayrollPeriodFilters' => true,
          'PayrollRunFilters' => true,
          'PayrollCalendarPayload' => true,
          'PayrollCalendarNormalizedPayload' => true,
          'PayrollPeriodPayload' => true,
          'PayrollRunActionPayload' => true,
          'PayrollPreparationResult' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '213b028675bcd9cc1a1c3ef18935d981' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'payrollcalendar' => 'App\\Models\\PayrollCalendar',
          'payrollperiod' => 'App\\Models\\PayrollPeriod',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
         'functionName' => 'searchPeriods',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'payrollcalendar' => 'App\\Models\\PayrollCalendar',
            'payrollperiod' => 'App\\Models\\PayrollPeriod',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollPeriodFilters' => true,
            'PayrollRunFilters' => true,
            'PayrollCalendarPayload' => true,
            'PayrollCalendarNormalizedPayload' => true,
            'PayrollPeriodPayload' => true,
            'PayrollRunActionPayload' => true,
            'PayrollPreparationResult' => true,
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
          'PayrollPeriodFilters' => true,
          'PayrollRunFilters' => true,
          'PayrollCalendarPayload' => true,
          'PayrollCalendarNormalizedPayload' => true,
          'PayrollPeriodPayload' => true,
          'PayrollRunActionPayload' => true,
          'PayrollPreparationResult' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '926168c114fa09299e9aa41b270ff4cb' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'payrollcalendar' => 'App\\Models\\PayrollCalendar',
          'payrollperiod' => 'App\\Models\\PayrollPeriod',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
         'functionName' => 'searchRuns',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'payrollcalendar' => 'App\\Models\\PayrollCalendar',
            'payrollperiod' => 'App\\Models\\PayrollPeriod',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollPeriodFilters' => true,
            'PayrollRunFilters' => true,
            'PayrollCalendarPayload' => true,
            'PayrollCalendarNormalizedPayload' => true,
            'PayrollPeriodPayload' => true,
            'PayrollRunActionPayload' => true,
            'PayrollPreparationResult' => true,
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
          'PayrollPeriodFilters' => true,
          'PayrollRunFilters' => true,
          'PayrollCalendarPayload' => true,
          'PayrollCalendarNormalizedPayload' => true,
          'PayrollPeriodPayload' => true,
          'PayrollRunActionPayload' => true,
          'PayrollPreparationResult' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'a81220f74ebe892b55cbfacb7225019d' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'payrollcalendar' => 'App\\Models\\PayrollCalendar',
          'payrollperiod' => 'App\\Models\\PayrollPeriod',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
         'functionName' => 'createCalendar',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'payrollcalendar' => 'App\\Models\\PayrollCalendar',
            'payrollperiod' => 'App\\Models\\PayrollPeriod',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollPeriodFilters' => true,
            'PayrollRunFilters' => true,
            'PayrollCalendarPayload' => true,
            'PayrollCalendarNormalizedPayload' => true,
            'PayrollPeriodPayload' => true,
            'PayrollRunActionPayload' => true,
            'PayrollPreparationResult' => true,
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
          'PayrollPeriodFilters' => true,
          'PayrollRunFilters' => true,
          'PayrollCalendarPayload' => true,
          'PayrollCalendarNormalizedPayload' => true,
          'PayrollPeriodPayload' => true,
          'PayrollRunActionPayload' => true,
          'PayrollPreparationResult' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '94e1cc9ec2a83be56d036b2f2af0093f' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'payrollcalendar' => 'App\\Models\\PayrollCalendar',
          'payrollperiod' => 'App\\Models\\PayrollPeriod',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
         'functionName' => 'updateCalendar',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'payrollcalendar' => 'App\\Models\\PayrollCalendar',
            'payrollperiod' => 'App\\Models\\PayrollPeriod',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollPeriodFilters' => true,
            'PayrollRunFilters' => true,
            'PayrollCalendarPayload' => true,
            'PayrollCalendarNormalizedPayload' => true,
            'PayrollPeriodPayload' => true,
            'PayrollRunActionPayload' => true,
            'PayrollPreparationResult' => true,
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
          'PayrollPeriodFilters' => true,
          'PayrollRunFilters' => true,
          'PayrollCalendarPayload' => true,
          'PayrollCalendarNormalizedPayload' => true,
          'PayrollPeriodPayload' => true,
          'PayrollRunActionPayload' => true,
          'PayrollPreparationResult' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '2b8a738572b155c51b83dd3f6dc69e52' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'payrollcalendar' => 'App\\Models\\PayrollCalendar',
          'payrollperiod' => 'App\\Models\\PayrollPeriod',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
         'functionName' => 'createPeriod',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'payrollcalendar' => 'App\\Models\\PayrollCalendar',
            'payrollperiod' => 'App\\Models\\PayrollPeriod',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollPeriodFilters' => true,
            'PayrollRunFilters' => true,
            'PayrollCalendarPayload' => true,
            'PayrollCalendarNormalizedPayload' => true,
            'PayrollPeriodPayload' => true,
            'PayrollRunActionPayload' => true,
            'PayrollPreparationResult' => true,
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
          'PayrollPeriodFilters' => true,
          'PayrollRunFilters' => true,
          'PayrollCalendarPayload' => true,
          'PayrollCalendarNormalizedPayload' => true,
          'PayrollPeriodPayload' => true,
          'PayrollRunActionPayload' => true,
          'PayrollPreparationResult' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '7c1fee0d1b0d28e5a1a7f26583c0b2bb' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'payrollcalendar' => 'App\\Models\\PayrollCalendar',
          'payrollperiod' => 'App\\Models\\PayrollPeriod',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
         'functionName' => 'openPeriod',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'payrollcalendar' => 'App\\Models\\PayrollCalendar',
            'payrollperiod' => 'App\\Models\\PayrollPeriod',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollPeriodFilters' => true,
            'PayrollRunFilters' => true,
            'PayrollCalendarPayload' => true,
            'PayrollCalendarNormalizedPayload' => true,
            'PayrollPeriodPayload' => true,
            'PayrollRunActionPayload' => true,
            'PayrollPreparationResult' => true,
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
          'PayrollPeriodFilters' => true,
          'PayrollRunFilters' => true,
          'PayrollCalendarPayload' => true,
          'PayrollCalendarNormalizedPayload' => true,
          'PayrollPeriodPayload' => true,
          'PayrollRunActionPayload' => true,
          'PayrollPreparationResult' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '50a23cfabb0dd139cd7d88f4160554e7' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'payrollcalendar' => 'App\\Models\\PayrollCalendar',
          'payrollperiod' => 'App\\Models\\PayrollPeriod',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
         'functionName' => 'preparePeriod',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'payrollcalendar' => 'App\\Models\\PayrollCalendar',
            'payrollperiod' => 'App\\Models\\PayrollPeriod',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollPeriodFilters' => true,
            'PayrollRunFilters' => true,
            'PayrollCalendarPayload' => true,
            'PayrollCalendarNormalizedPayload' => true,
            'PayrollPeriodPayload' => true,
            'PayrollRunActionPayload' => true,
            'PayrollPreparationResult' => true,
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
          'PayrollPeriodFilters' => true,
          'PayrollRunFilters' => true,
          'PayrollCalendarPayload' => true,
          'PayrollCalendarNormalizedPayload' => true,
          'PayrollPeriodPayload' => true,
          'PayrollRunActionPayload' => true,
          'PayrollPreparationResult' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'f253aee0533fcb999d6a5cf17153a208' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'payrollcalendar' => 'App\\Models\\PayrollCalendar',
          'payrollperiod' => 'App\\Models\\PayrollPeriod',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
         'functionName' => 'closePeriod',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'payrollcalendar' => 'App\\Models\\PayrollCalendar',
            'payrollperiod' => 'App\\Models\\PayrollPeriod',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollPeriodFilters' => true,
            'PayrollRunFilters' => true,
            'PayrollCalendarPayload' => true,
            'PayrollCalendarNormalizedPayload' => true,
            'PayrollPeriodPayload' => true,
            'PayrollRunActionPayload' => true,
            'PayrollPreparationResult' => true,
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
          'PayrollPeriodFilters' => true,
          'PayrollRunFilters' => true,
          'PayrollCalendarPayload' => true,
          'PayrollCalendarNormalizedPayload' => true,
          'PayrollPeriodPayload' => true,
          'PayrollRunActionPayload' => true,
          'PayrollPreparationResult' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '6a4b2851835d8a2f2786c4eacb91596e' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'payrollcalendar' => 'App\\Models\\PayrollCalendar',
          'payrollperiod' => 'App\\Models\\PayrollPeriod',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
         'functionName' => 'previewPrerequisites',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'payrollcalendar' => 'App\\Models\\PayrollCalendar',
            'payrollperiod' => 'App\\Models\\PayrollPeriod',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollPeriodFilters' => true,
            'PayrollRunFilters' => true,
            'PayrollCalendarPayload' => true,
            'PayrollCalendarNormalizedPayload' => true,
            'PayrollPeriodPayload' => true,
            'PayrollRunActionPayload' => true,
            'PayrollPreparationResult' => true,
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
          'PayrollPeriodFilters' => true,
          'PayrollRunFilters' => true,
          'PayrollCalendarPayload' => true,
          'PayrollCalendarNormalizedPayload' => true,
          'PayrollPeriodPayload' => true,
          'PayrollRunActionPayload' => true,
          'PayrollPreparationResult' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '636ac62dd28d56dd0405090ca9492e31' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'payrollcalendar' => 'App\\Models\\PayrollCalendar',
          'payrollperiod' => 'App\\Models\\PayrollPeriod',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
         'functionName' => 'calculateRun',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'payrollcalendar' => 'App\\Models\\PayrollCalendar',
            'payrollperiod' => 'App\\Models\\PayrollPeriod',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollPeriodFilters' => true,
            'PayrollRunFilters' => true,
            'PayrollCalendarPayload' => true,
            'PayrollCalendarNormalizedPayload' => true,
            'PayrollPeriodPayload' => true,
            'PayrollRunActionPayload' => true,
            'PayrollPreparationResult' => true,
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
          'PayrollPeriodFilters' => true,
          'PayrollRunFilters' => true,
          'PayrollCalendarPayload' => true,
          'PayrollCalendarNormalizedPayload' => true,
          'PayrollPeriodPayload' => true,
          'PayrollRunActionPayload' => true,
          'PayrollPreparationResult' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'e2bf2841152da3d370bc81325d31c4ee' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'payrollcalendar' => 'App\\Models\\PayrollCalendar',
          'payrollperiod' => 'App\\Models\\PayrollPeriod',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
         'functionName' => 'approveRun',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'payrollcalendar' => 'App\\Models\\PayrollCalendar',
            'payrollperiod' => 'App\\Models\\PayrollPeriod',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollPeriodFilters' => true,
            'PayrollRunFilters' => true,
            'PayrollCalendarPayload' => true,
            'PayrollCalendarNormalizedPayload' => true,
            'PayrollPeriodPayload' => true,
            'PayrollRunActionPayload' => true,
            'PayrollPreparationResult' => true,
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
          'PayrollPeriodFilters' => true,
          'PayrollRunFilters' => true,
          'PayrollCalendarPayload' => true,
          'PayrollCalendarNormalizedPayload' => true,
          'PayrollPeriodPayload' => true,
          'PayrollRunActionPayload' => true,
          'PayrollPreparationResult' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '86a09c49459774cfc71c06ad44e176cd' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'payrollcalendar' => 'App\\Models\\PayrollCalendar',
          'payrollperiod' => 'App\\Models\\PayrollPeriod',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
         'functionName' => 'lockRun',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'payrollcalendar' => 'App\\Models\\PayrollCalendar',
            'payrollperiod' => 'App\\Models\\PayrollPeriod',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollPeriodFilters' => true,
            'PayrollRunFilters' => true,
            'PayrollCalendarPayload' => true,
            'PayrollCalendarNormalizedPayload' => true,
            'PayrollPeriodPayload' => true,
            'PayrollRunActionPayload' => true,
            'PayrollPreparationResult' => true,
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
          'PayrollPeriodFilters' => true,
          'PayrollRunFilters' => true,
          'PayrollCalendarPayload' => true,
          'PayrollCalendarNormalizedPayload' => true,
          'PayrollPeriodPayload' => true,
          'PayrollRunActionPayload' => true,
          'PayrollPreparationResult' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '7863a3164acf67fd23706f3b84741b6e' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'payrollcalendar' => 'App\\Models\\PayrollCalendar',
          'payrollperiod' => 'App\\Models\\PayrollPeriod',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
         'functionName' => 'reopenRun',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'payrollcalendar' => 'App\\Models\\PayrollCalendar',
            'payrollperiod' => 'App\\Models\\PayrollPeriod',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollPeriodFilters' => true,
            'PayrollRunFilters' => true,
            'PayrollCalendarPayload' => true,
            'PayrollCalendarNormalizedPayload' => true,
            'PayrollPeriodPayload' => true,
            'PayrollRunActionPayload' => true,
            'PayrollPreparationResult' => true,
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
          'PayrollPeriodFilters' => true,
          'PayrollRunFilters' => true,
          'PayrollCalendarPayload' => true,
          'PayrollCalendarNormalizedPayload' => true,
          'PayrollPeriodPayload' => true,
          'PayrollRunActionPayload' => true,
          'PayrollPreparationResult' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'cd075542b9d91534926f60a041b7f002' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'payrollcalendar' => 'App\\Models\\PayrollCalendar',
          'payrollperiod' => 'App\\Models\\PayrollPeriod',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
         'functionName' => 'normalizeCalendarPayload',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'payrollcalendar' => 'App\\Models\\PayrollCalendar',
            'payrollperiod' => 'App\\Models\\PayrollPeriod',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollPeriodFilters' => true,
            'PayrollRunFilters' => true,
            'PayrollCalendarPayload' => true,
            'PayrollCalendarNormalizedPayload' => true,
            'PayrollPeriodPayload' => true,
            'PayrollRunActionPayload' => true,
            'PayrollPreparationResult' => true,
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
          'PayrollPeriodFilters' => true,
          'PayrollRunFilters' => true,
          'PayrollCalendarPayload' => true,
          'PayrollCalendarNormalizedPayload' => true,
          'PayrollPeriodPayload' => true,
          'PayrollRunActionPayload' => true,
          'PayrollPreparationResult' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'b17059a9ac15e3e706f6fc390126abd6' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'payrollcalendar' => 'App\\Models\\PayrollCalendar',
          'payrollperiod' => 'App\\Models\\PayrollPeriod',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
         'functionName' => 'clearOtherDefaultCalendars',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'payrollcalendar' => 'App\\Models\\PayrollCalendar',
            'payrollperiod' => 'App\\Models\\PayrollPeriod',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollPeriodFilters' => true,
            'PayrollRunFilters' => true,
            'PayrollCalendarPayload' => true,
            'PayrollCalendarNormalizedPayload' => true,
            'PayrollPeriodPayload' => true,
            'PayrollRunActionPayload' => true,
            'PayrollPreparationResult' => true,
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
          'PayrollPeriodFilters' => true,
          'PayrollRunFilters' => true,
          'PayrollCalendarPayload' => true,
          'PayrollCalendarNormalizedPayload' => true,
          'PayrollPeriodPayload' => true,
          'PayrollRunActionPayload' => true,
          'PayrollPreparationResult' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'd53cc625f13a380bf23ff43d6d73380b' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'payrollcalendar' => 'App\\Models\\PayrollCalendar',
          'payrollperiod' => 'App\\Models\\PayrollPeriod',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
         'functionName' => 'ensureNoOverlappingRuns',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'payrollcalendar' => 'App\\Models\\PayrollCalendar',
            'payrollperiod' => 'App\\Models\\PayrollPeriod',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollPeriodFilters' => true,
            'PayrollRunFilters' => true,
            'PayrollCalendarPayload' => true,
            'PayrollCalendarNormalizedPayload' => true,
            'PayrollPeriodPayload' => true,
            'PayrollRunActionPayload' => true,
            'PayrollPreparationResult' => true,
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
          'PayrollPeriodFilters' => true,
          'PayrollRunFilters' => true,
          'PayrollCalendarPayload' => true,
          'PayrollCalendarNormalizedPayload' => true,
          'PayrollPeriodPayload' => true,
          'PayrollRunActionPayload' => true,
          'PayrollPreparationResult' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'c90f48ff3f2f3d585ed5a63ae316a592' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\PayrollManagement\\Services',
         'uses' => 
        array (
          'payrollcalendar' => 'App\\Models\\PayrollCalendar',
          'payrollperiod' => 'App\\Models\\PayrollPeriod',
          'payrollrun' => 'App\\Models\\PayrollRun',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
         'functionName' => 'makeRunName',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\PayrollManagement\\Services',
           'uses' => 
          array (
            'payrollcalendar' => 'App\\Models\\PayrollCalendar',
            'payrollperiod' => 'App\\Models\\PayrollPeriod',
            'payrollrun' => 'App\\Models\\PayrollRun',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbon' => 'Carbon\\Carbon',
            'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
          ),
           'className' => 'App\\Modules\\PayrollManagement\\Services\\PayrollControlService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'PayrollPeriodFilters' => true,
            'PayrollRunFilters' => true,
            'PayrollCalendarPayload' => true,
            'PayrollCalendarNormalizedPayload' => true,
            'PayrollPeriodPayload' => true,
            'PayrollRunActionPayload' => true,
            'PayrollPreparationResult' => true,
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
          'PayrollPeriodFilters' => true,
          'PayrollRunFilters' => true,
          'PayrollCalendarPayload' => true,
          'PayrollCalendarNormalizedPayload' => true,
          'PayrollPeriodPayload' => true,
          'PayrollRunActionPayload' => true,
          'PayrollPreparationResult' => true,
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
      '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/PayrollManagement/Services/PayrollControlService.php' => '9d211b2905236e4bcd30e0773e35569546c0ef33cce51f18c6298d5676be90d0',
    ),
  ),
));