<?php declare(strict_types = 1);

// ftm-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/RecruitmentManagement/Services/RecruitmentHireHandoffService.php
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v5-2.3.2',
   'data' => 
  array (
    0 => 
    array (
      'a820f3b7a54e14673c1c3ee8f1b5ddd0' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\RecruitmentManagement\\Services',
         'uses' => 
        array (
          'candidate' => 'App\\Models\\Candidate',
          'employee' => 'App\\Models\\Employee',
          'employeelifecycletasktemplate' => 'App\\Models\\EmployeeLifecycleTaskTemplate',
          'jobrequisition' => 'App\\Models\\JobRequisition',
          'offer' => 'App\\Models\\Offer',
          'recruitmenthirehandoff' => 'App\\Models\\RecruitmentHireHandoff',
          'user' => 'App\\Models\\User',
          'employeecreationrules' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeCreationRules',
          'employeelifecycletasktemplateservice' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeLifecycleTaskTemplateService',
          'employeeservice' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'notificationservice' => 'App\\Modules\\Platform\\Notifications\\Services\\NotificationService',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validator' => 'Illuminate\\Support\\Facades\\Validator',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\RecruitmentManagement\\Services\\RecruitmentHireHandoffService',
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
      '36862929f5d71faef3f750fe9c5a9011' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\RecruitmentManagement\\Services',
         'uses' => 
        array (
          'candidate' => 'App\\Models\\Candidate',
          'employee' => 'App\\Models\\Employee',
          'employeelifecycletasktemplate' => 'App\\Models\\EmployeeLifecycleTaskTemplate',
          'jobrequisition' => 'App\\Models\\JobRequisition',
          'offer' => 'App\\Models\\Offer',
          'recruitmenthirehandoff' => 'App\\Models\\RecruitmentHireHandoff',
          'user' => 'App\\Models\\User',
          'employeecreationrules' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeCreationRules',
          'employeelifecycletasktemplateservice' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeLifecycleTaskTemplateService',
          'employeeservice' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'notificationservice' => 'App\\Modules\\Platform\\Notifications\\Services\\NotificationService',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validator' => 'Illuminate\\Support\\Facades\\Validator',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\RecruitmentManagement\\Services\\RecruitmentHireHandoffService',
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
      'e347d3bf9ebe9f49085427b0d51bd13e' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\RecruitmentManagement\\Services',
         'uses' => 
        array (
          'candidate' => 'App\\Models\\Candidate',
          'employee' => 'App\\Models\\Employee',
          'employeelifecycletasktemplate' => 'App\\Models\\EmployeeLifecycleTaskTemplate',
          'jobrequisition' => 'App\\Models\\JobRequisition',
          'offer' => 'App\\Models\\Offer',
          'recruitmenthirehandoff' => 'App\\Models\\RecruitmentHireHandoff',
          'user' => 'App\\Models\\User',
          'employeecreationrules' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeCreationRules',
          'employeelifecycletasktemplateservice' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeLifecycleTaskTemplateService',
          'employeeservice' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'notificationservice' => 'App\\Modules\\Platform\\Notifications\\Services\\NotificationService',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validator' => 'Illuminate\\Support\\Facades\\Validator',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\RecruitmentManagement\\Services\\RecruitmentHireHandoffService',
         'functionName' => 'search',
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
      '6fb6e8782b358376b554cbc7e7275e4a' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\RecruitmentManagement\\Services',
         'uses' => 
        array (
          'candidate' => 'App\\Models\\Candidate',
          'employee' => 'App\\Models\\Employee',
          'employeelifecycletasktemplate' => 'App\\Models\\EmployeeLifecycleTaskTemplate',
          'jobrequisition' => 'App\\Models\\JobRequisition',
          'offer' => 'App\\Models\\Offer',
          'recruitmenthirehandoff' => 'App\\Models\\RecruitmentHireHandoff',
          'user' => 'App\\Models\\User',
          'employeecreationrules' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeCreationRules',
          'employeelifecycletasktemplateservice' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeLifecycleTaskTemplateService',
          'employeeservice' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'notificationservice' => 'App\\Modules\\Platform\\Notifications\\Services\\NotificationService',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validator' => 'Illuminate\\Support\\Facades\\Validator',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\RecruitmentManagement\\Services\\RecruitmentHireHandoffService',
         'functionName' => 'findForView',
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
      'd9c2721aefeb0296e46e33771b64bde9' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\RecruitmentManagement\\Services',
         'uses' => 
        array (
          'candidate' => 'App\\Models\\Candidate',
          'employee' => 'App\\Models\\Employee',
          'employeelifecycletasktemplate' => 'App\\Models\\EmployeeLifecycleTaskTemplate',
          'jobrequisition' => 'App\\Models\\JobRequisition',
          'offer' => 'App\\Models\\Offer',
          'recruitmenthirehandoff' => 'App\\Models\\RecruitmentHireHandoff',
          'user' => 'App\\Models\\User',
          'employeecreationrules' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeCreationRules',
          'employeelifecycletasktemplateservice' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeLifecycleTaskTemplateService',
          'employeeservice' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'notificationservice' => 'App\\Modules\\Platform\\Notifications\\Services\\NotificationService',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validator' => 'Illuminate\\Support\\Facades\\Validator',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\RecruitmentManagement\\Services\\RecruitmentHireHandoffService',
         'functionName' => 'createFromAcceptedOffer',
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
      'd591ed0a68fcdcd713ffce4e3ef24b42' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\RecruitmentManagement\\Services',
         'uses' => 
        array (
          'candidate' => 'App\\Models\\Candidate',
          'employee' => 'App\\Models\\Employee',
          'employeelifecycletasktemplate' => 'App\\Models\\EmployeeLifecycleTaskTemplate',
          'jobrequisition' => 'App\\Models\\JobRequisition',
          'offer' => 'App\\Models\\Offer',
          'recruitmenthirehandoff' => 'App\\Models\\RecruitmentHireHandoff',
          'user' => 'App\\Models\\User',
          'employeecreationrules' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeCreationRules',
          'employeelifecycletasktemplateservice' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeLifecycleTaskTemplateService',
          'employeeservice' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'notificationservice' => 'App\\Modules\\Platform\\Notifications\\Services\\NotificationService',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validator' => 'Illuminate\\Support\\Facades\\Validator',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\RecruitmentManagement\\Services\\RecruitmentHireHandoffService',
         'functionName' => 'buildEmployeePayload',
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
      'ac3f51eb9ffbef74912408f252373671' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\RecruitmentManagement\\Services',
         'uses' => 
        array (
          'candidate' => 'App\\Models\\Candidate',
          'employee' => 'App\\Models\\Employee',
          'employeelifecycletasktemplate' => 'App\\Models\\EmployeeLifecycleTaskTemplate',
          'jobrequisition' => 'App\\Models\\JobRequisition',
          'offer' => 'App\\Models\\Offer',
          'recruitmenthirehandoff' => 'App\\Models\\RecruitmentHireHandoff',
          'user' => 'App\\Models\\User',
          'employeecreationrules' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeCreationRules',
          'employeelifecycletasktemplateservice' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeLifecycleTaskTemplateService',
          'employeeservice' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'notificationservice' => 'App\\Modules\\Platform\\Notifications\\Services\\NotificationService',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validator' => 'Illuminate\\Support\\Facades\\Validator',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\RecruitmentManagement\\Services\\RecruitmentHireHandoffService',
         'functionName' => 'validateEmployeePayload',
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
      '1a7737f51f9a70365b93b4bc4813881d' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\RecruitmentManagement\\Services',
         'uses' => 
        array (
          'candidate' => 'App\\Models\\Candidate',
          'employee' => 'App\\Models\\Employee',
          'employeelifecycletasktemplate' => 'App\\Models\\EmployeeLifecycleTaskTemplate',
          'jobrequisition' => 'App\\Models\\JobRequisition',
          'offer' => 'App\\Models\\Offer',
          'recruitmenthirehandoff' => 'App\\Models\\RecruitmentHireHandoff',
          'user' => 'App\\Models\\User',
          'employeecreationrules' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeCreationRules',
          'employeelifecycletasktemplateservice' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeLifecycleTaskTemplateService',
          'employeeservice' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'notificationservice' => 'App\\Modules\\Platform\\Notifications\\Services\\NotificationService',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validator' => 'Illuminate\\Support\\Facades\\Validator',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\RecruitmentManagement\\Services\\RecruitmentHireHandoffService',
         'functionName' => 'defaultEmploymentStatus',
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
      '33a1487681c685f57329cade8b455b16' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\RecruitmentManagement\\Services',
         'uses' => 
        array (
          'candidate' => 'App\\Models\\Candidate',
          'employee' => 'App\\Models\\Employee',
          'employeelifecycletasktemplate' => 'App\\Models\\EmployeeLifecycleTaskTemplate',
          'jobrequisition' => 'App\\Models\\JobRequisition',
          'offer' => 'App\\Models\\Offer',
          'recruitmenthirehandoff' => 'App\\Models\\RecruitmentHireHandoff',
          'user' => 'App\\Models\\User',
          'employeecreationrules' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeCreationRules',
          'employeelifecycletasktemplateservice' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeLifecycleTaskTemplateService',
          'employeeservice' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'notificationservice' => 'App\\Modules\\Platform\\Notifications\\Services\\NotificationService',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validator' => 'Illuminate\\Support\\Facades\\Validator',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\RecruitmentManagement\\Services\\RecruitmentHireHandoffService',
         'functionName' => 'triggerOnboardingTasks',
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
      '03c0b6bbe850d1fc117aa89837e96144' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\RecruitmentManagement\\Services',
         'uses' => 
        array (
          'candidate' => 'App\\Models\\Candidate',
          'employee' => 'App\\Models\\Employee',
          'employeelifecycletasktemplate' => 'App\\Models\\EmployeeLifecycleTaskTemplate',
          'jobrequisition' => 'App\\Models\\JobRequisition',
          'offer' => 'App\\Models\\Offer',
          'recruitmenthirehandoff' => 'App\\Models\\RecruitmentHireHandoff',
          'user' => 'App\\Models\\User',
          'employeecreationrules' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeCreationRules',
          'employeelifecycletasktemplateservice' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeLifecycleTaskTemplateService',
          'employeeservice' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'notificationservice' => 'App\\Modules\\Platform\\Notifications\\Services\\NotificationService',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validator' => 'Illuminate\\Support\\Facades\\Validator',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\RecruitmentManagement\\Services\\RecruitmentHireHandoffService',
         'functionName' => 'markCandidateAsHired',
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
      '485cf3735860132d56ba829b6b617e8c' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\RecruitmentManagement\\Services',
         'uses' => 
        array (
          'candidate' => 'App\\Models\\Candidate',
          'employee' => 'App\\Models\\Employee',
          'employeelifecycletasktemplate' => 'App\\Models\\EmployeeLifecycleTaskTemplate',
          'jobrequisition' => 'App\\Models\\JobRequisition',
          'offer' => 'App\\Models\\Offer',
          'recruitmenthirehandoff' => 'App\\Models\\RecruitmentHireHandoff',
          'user' => 'App\\Models\\User',
          'employeecreationrules' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeCreationRules',
          'employeelifecycletasktemplateservice' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeLifecycleTaskTemplateService',
          'employeeservice' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'notificationservice' => 'App\\Modules\\Platform\\Notifications\\Services\\NotificationService',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validator' => 'Illuminate\\Support\\Facades\\Validator',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\RecruitmentManagement\\Services\\RecruitmentHireHandoffService',
         'functionName' => 'notifyStakeholders',
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
      'ae4693c8f2641333ac51050f28e3bcb4' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\RecruitmentManagement\\Services',
         'uses' => 
        array (
          'candidate' => 'App\\Models\\Candidate',
          'employee' => 'App\\Models\\Employee',
          'employeelifecycletasktemplate' => 'App\\Models\\EmployeeLifecycleTaskTemplate',
          'jobrequisition' => 'App\\Models\\JobRequisition',
          'offer' => 'App\\Models\\Offer',
          'recruitmenthirehandoff' => 'App\\Models\\RecruitmentHireHandoff',
          'user' => 'App\\Models\\User',
          'employeecreationrules' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeCreationRules',
          'employeelifecycletasktemplateservice' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeLifecycleTaskTemplateService',
          'employeeservice' => 'App\\Modules\\EmployeeManagement\\Services\\EmployeeService',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'notificationservice' => 'App\\Modules\\Platform\\Notifications\\Services\\NotificationService',
          'carbon' => 'Carbon\\Carbon',
          'lengthawarepaginator' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'validator' => 'Illuminate\\Support\\Facades\\Validator',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'className' => 'App\\Modules\\RecruitmentManagement\\Services\\RecruitmentHireHandoffService',
         'functionName' => 'loadHandoff',
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
      '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/RecruitmentManagement/Services/RecruitmentHireHandoffService.php' => '848e7bde8745ba7bc49ae64bab6fe0d319c6446bd11632dc658cbcce729a974e',
    ),
  ),
));