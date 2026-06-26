<?php declare(strict_types = 1);

// odsl-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Providers/EventServiceProvider.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Providers\EventServiceProvider
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.1-8.4.4-b90383c6178add5f09f044155d19fadbbd0ba3dfa17a340e742a90f02cfe5b5f',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Providers\\EventServiceProvider',
        'filename' => '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Providers/EventServiceProvider.php',
      ),
    ),
    'namespace' => 'App\\Providers',
    'name' => 'App\\Providers\\EventServiceProvider',
    'shortName' => 'EventServiceProvider',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 16,
    'endLine' => 31,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
    'implementsClassNames' => 
    array (
    ),
    'traitClassNames' => 
    array (
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
      'listen' => 
      array (
        'declaringClassName' => 'App\\Providers\\EventServiceProvider',
        'implementingClassName' => 'App\\Providers\\EventServiceProvider',
        'name' => 'listen',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\\App\\Modules\\Platform\\Workflow\\Events\\WorkflowTaskAssigned::class => [\\App\\Modules\\Platform\\Workflow\\Listeners\\SendWorkflowTaskAssignedNotification::class], \\App\\Modules\\Platform\\Workflow\\Events\\WorkflowInstanceTransitioned::class => [\\App\\Modules\\Platform\\Workflow\\Listeners\\SendWorkflowInstanceTransitionNotification::class, \\App\\Modules\\AttendanceManagement\\Listeners\\SyncAttendanceCorrectionWorkflowState::class, \\App\\Modules\\EmployeeManagement\\Listeners\\SyncEmployeeLifecycleTaskWorkflowState::class, \\App\\Modules\\LeaveManagement\\Listeners\\SyncLeaveRequestWorkflowState::class, \\App\\Modules\\RecruitmentManagement\\Listeners\\SyncJobRequisitionWorkflowState::class, \\App\\Modules\\RecruitmentManagement\\Listeners\\SyncOfferWorkflowState::class]]',
          'attributes' => 
          array (
            'startLine' => 18,
            'endLine' => 30,
            'startTokenPos' => 77,
            'startFilePos' => 897,
            'endTokenPos' => 136,
            'endFilePos' => 1397,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 18,
        'endLine' => 30,
        'startColumn' => 5,
        'endColumn' => 6,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
    ),
    'immediateMethods' => 
    array (
    ),
    'traitsData' => 
    array (
      'aliases' => 
      array (
      ),
      'modifiers' => 
      array (
      ),
      'precedences' => 
      array (
      ),
      'hashes' => 
      array (
      ),
    ),
  ),
));