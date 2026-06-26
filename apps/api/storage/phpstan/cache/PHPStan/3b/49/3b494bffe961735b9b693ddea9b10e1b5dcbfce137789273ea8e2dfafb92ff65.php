<?php declare(strict_types = 1);

// ftm-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/DocumentManagement/Services/DocumentRepositoryService.php
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v5-2.3.2',
   'data' => 
  array (
    0 => 
    array (
      '9000b73f041132617f0ebc8816c1cf02' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\DocumentManagement\\Services',
         'uses' => 
        array (
          'document' => 'App\\Models\\Document',
          'documentcategory' => 'App\\Models\\DocumentCategory',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbonimmutable' => 'Carbon\\CarbonImmutable',
          'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
          'modelnotfoundexception' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
          'uploadedfile' => 'Illuminate\\Http\\UploadedFile',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'storage' => 'Illuminate\\Support\\Facades\\Storage',
          'str' => 'Illuminate\\Support\\Str',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
          'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
        ),
         'className' => 'App\\Modules\\DocumentManagement\\Services\\DocumentRepositoryService',
         'functionName' => NULL,
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
          'DocumentRepositoryFilters' => true,
          'DocumentRepositoryPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '6c4c901b01c8595f2b9395ffdffdcc4a' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\DocumentManagement\\Services',
         'uses' => 
        array (
          'document' => 'App\\Models\\Document',
          'documentcategory' => 'App\\Models\\DocumentCategory',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbonimmutable' => 'Carbon\\CarbonImmutable',
          'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
          'modelnotfoundexception' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
          'uploadedfile' => 'Illuminate\\Http\\UploadedFile',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'storage' => 'Illuminate\\Support\\Facades\\Storage',
          'str' => 'Illuminate\\Support\\Str',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
          'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
        ),
         'className' => 'App\\Modules\\DocumentManagement\\Services\\DocumentRepositoryService',
         'functionName' => '__construct',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\DocumentManagement\\Services',
           'uses' => 
          array (
            'document' => 'App\\Models\\Document',
            'documentcategory' => 'App\\Models\\DocumentCategory',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbonimmutable' => 'Carbon\\CarbonImmutable',
            'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
            'modelnotfoundexception' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
            'uploadedfile' => 'Illuminate\\Http\\UploadedFile',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'storage' => 'Illuminate\\Support\\Facades\\Storage',
            'str' => 'Illuminate\\Support\\Str',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
            'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
          ),
           'className' => 'App\\Modules\\DocumentManagement\\Services\\DocumentRepositoryService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'DocumentRepositoryFilters' => true,
            'DocumentRepositoryPayload' => true,
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
          'DocumentRepositoryFilters' => true,
          'DocumentRepositoryPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '84716e362de35a3f598ada846f60518a' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\DocumentManagement\\Services',
         'uses' => 
        array (
          'document' => 'App\\Models\\Document',
          'documentcategory' => 'App\\Models\\DocumentCategory',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbonimmutable' => 'Carbon\\CarbonImmutable',
          'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
          'modelnotfoundexception' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
          'uploadedfile' => 'Illuminate\\Http\\UploadedFile',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'storage' => 'Illuminate\\Support\\Facades\\Storage',
          'str' => 'Illuminate\\Support\\Str',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
          'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
        ),
         'className' => 'App\\Modules\\DocumentManagement\\Services\\DocumentRepositoryService',
         'functionName' => 'listDocuments',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\DocumentManagement\\Services',
           'uses' => 
          array (
            'document' => 'App\\Models\\Document',
            'documentcategory' => 'App\\Models\\DocumentCategory',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbonimmutable' => 'Carbon\\CarbonImmutable',
            'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
            'modelnotfoundexception' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
            'uploadedfile' => 'Illuminate\\Http\\UploadedFile',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'storage' => 'Illuminate\\Support\\Facades\\Storage',
            'str' => 'Illuminate\\Support\\Str',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
            'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
          ),
           'className' => 'App\\Modules\\DocumentManagement\\Services\\DocumentRepositoryService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'DocumentRepositoryFilters' => true,
            'DocumentRepositoryPayload' => true,
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
          'DocumentRepositoryFilters' => true,
          'DocumentRepositoryPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '5be9434890384e9c976e489ad013d578' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\DocumentManagement\\Services',
         'uses' => 
        array (
          'document' => 'App\\Models\\Document',
          'documentcategory' => 'App\\Models\\DocumentCategory',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbonimmutable' => 'Carbon\\CarbonImmutable',
          'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
          'modelnotfoundexception' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
          'uploadedfile' => 'Illuminate\\Http\\UploadedFile',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'storage' => 'Illuminate\\Support\\Facades\\Storage',
          'str' => 'Illuminate\\Support\\Str',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
          'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
        ),
         'className' => 'App\\Modules\\DocumentManagement\\Services\\DocumentRepositoryService',
         'functionName' => 'create',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\DocumentManagement\\Services',
           'uses' => 
          array (
            'document' => 'App\\Models\\Document',
            'documentcategory' => 'App\\Models\\DocumentCategory',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbonimmutable' => 'Carbon\\CarbonImmutable',
            'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
            'modelnotfoundexception' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
            'uploadedfile' => 'Illuminate\\Http\\UploadedFile',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'storage' => 'Illuminate\\Support\\Facades\\Storage',
            'str' => 'Illuminate\\Support\\Str',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
            'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
          ),
           'className' => 'App\\Modules\\DocumentManagement\\Services\\DocumentRepositoryService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'DocumentRepositoryFilters' => true,
            'DocumentRepositoryPayload' => true,
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
          'DocumentRepositoryFilters' => true,
          'DocumentRepositoryPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '5b7dc983433ff31d8b57469c46322cf3' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\DocumentManagement\\Services',
         'uses' => 
        array (
          'document' => 'App\\Models\\Document',
          'documentcategory' => 'App\\Models\\DocumentCategory',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbonimmutable' => 'Carbon\\CarbonImmutable',
          'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
          'modelnotfoundexception' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
          'uploadedfile' => 'Illuminate\\Http\\UploadedFile',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'storage' => 'Illuminate\\Support\\Facades\\Storage',
          'str' => 'Illuminate\\Support\\Str',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
          'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
        ),
         'className' => 'App\\Modules\\DocumentManagement\\Services\\DocumentRepositoryService',
         'functionName' => 'showDocument',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\DocumentManagement\\Services',
           'uses' => 
          array (
            'document' => 'App\\Models\\Document',
            'documentcategory' => 'App\\Models\\DocumentCategory',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbonimmutable' => 'Carbon\\CarbonImmutable',
            'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
            'modelnotfoundexception' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
            'uploadedfile' => 'Illuminate\\Http\\UploadedFile',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'storage' => 'Illuminate\\Support\\Facades\\Storage',
            'str' => 'Illuminate\\Support\\Str',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
            'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
          ),
           'className' => 'App\\Modules\\DocumentManagement\\Services\\DocumentRepositoryService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'DocumentRepositoryFilters' => true,
            'DocumentRepositoryPayload' => true,
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
          'DocumentRepositoryFilters' => true,
          'DocumentRepositoryPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'ab68f0337361a36ceef6299db6114107' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\DocumentManagement\\Services',
         'uses' => 
        array (
          'document' => 'App\\Models\\Document',
          'documentcategory' => 'App\\Models\\DocumentCategory',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbonimmutable' => 'Carbon\\CarbonImmutable',
          'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
          'modelnotfoundexception' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
          'uploadedfile' => 'Illuminate\\Http\\UploadedFile',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'storage' => 'Illuminate\\Support\\Facades\\Storage',
          'str' => 'Illuminate\\Support\\Str',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
          'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
        ),
         'className' => 'App\\Modules\\DocumentManagement\\Services\\DocumentRepositoryService',
         'functionName' => 'download',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\DocumentManagement\\Services',
           'uses' => 
          array (
            'document' => 'App\\Models\\Document',
            'documentcategory' => 'App\\Models\\DocumentCategory',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbonimmutable' => 'Carbon\\CarbonImmutable',
            'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
            'modelnotfoundexception' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
            'uploadedfile' => 'Illuminate\\Http\\UploadedFile',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'storage' => 'Illuminate\\Support\\Facades\\Storage',
            'str' => 'Illuminate\\Support\\Str',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
            'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
          ),
           'className' => 'App\\Modules\\DocumentManagement\\Services\\DocumentRepositoryService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'DocumentRepositoryFilters' => true,
            'DocumentRepositoryPayload' => true,
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
          'DocumentRepositoryFilters' => true,
          'DocumentRepositoryPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'e5815eda1737da224b76564ee94b487b' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\DocumentManagement\\Services',
         'uses' => 
        array (
          'document' => 'App\\Models\\Document',
          'documentcategory' => 'App\\Models\\DocumentCategory',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbonimmutable' => 'Carbon\\CarbonImmutable',
          'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
          'modelnotfoundexception' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
          'uploadedfile' => 'Illuminate\\Http\\UploadedFile',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'storage' => 'Illuminate\\Support\\Facades\\Storage',
          'str' => 'Illuminate\\Support\\Str',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
          'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
        ),
         'className' => 'App\\Modules\\DocumentManagement\\Services\\DocumentRepositoryService',
         'functionName' => 'resolveCategory',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\DocumentManagement\\Services',
           'uses' => 
          array (
            'document' => 'App\\Models\\Document',
            'documentcategory' => 'App\\Models\\DocumentCategory',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbonimmutable' => 'Carbon\\CarbonImmutable',
            'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
            'modelnotfoundexception' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
            'uploadedfile' => 'Illuminate\\Http\\UploadedFile',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'storage' => 'Illuminate\\Support\\Facades\\Storage',
            'str' => 'Illuminate\\Support\\Str',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
            'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
          ),
           'className' => 'App\\Modules\\DocumentManagement\\Services\\DocumentRepositoryService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'DocumentRepositoryFilters' => true,
            'DocumentRepositoryPayload' => true,
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
          'DocumentRepositoryFilters' => true,
          'DocumentRepositoryPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '4ec5c9584483a7ce1c6d0380d207e669' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\DocumentManagement\\Services',
         'uses' => 
        array (
          'document' => 'App\\Models\\Document',
          'documentcategory' => 'App\\Models\\DocumentCategory',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbonimmutable' => 'Carbon\\CarbonImmutable',
          'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
          'modelnotfoundexception' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
          'uploadedfile' => 'Illuminate\\Http\\UploadedFile',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'storage' => 'Illuminate\\Support\\Facades\\Storage',
          'str' => 'Illuminate\\Support\\Str',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
          'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
        ),
         'className' => 'App\\Modules\\DocumentManagement\\Services\\DocumentRepositoryService',
         'functionName' => 'resolveRepositoryScope',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\DocumentManagement\\Services',
           'uses' => 
          array (
            'document' => 'App\\Models\\Document',
            'documentcategory' => 'App\\Models\\DocumentCategory',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbonimmutable' => 'Carbon\\CarbonImmutable',
            'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
            'modelnotfoundexception' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
            'uploadedfile' => 'Illuminate\\Http\\UploadedFile',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'storage' => 'Illuminate\\Support\\Facades\\Storage',
            'str' => 'Illuminate\\Support\\Str',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
            'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
          ),
           'className' => 'App\\Modules\\DocumentManagement\\Services\\DocumentRepositoryService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'DocumentRepositoryFilters' => true,
            'DocumentRepositoryPayload' => true,
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
          'DocumentRepositoryFilters' => true,
          'DocumentRepositoryPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '67bb64f29d0d64f164050f880e7c5b47' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\DocumentManagement\\Services',
         'uses' => 
        array (
          'document' => 'App\\Models\\Document',
          'documentcategory' => 'App\\Models\\DocumentCategory',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbonimmutable' => 'Carbon\\CarbonImmutable',
          'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
          'modelnotfoundexception' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
          'uploadedfile' => 'Illuminate\\Http\\UploadedFile',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'storage' => 'Illuminate\\Support\\Facades\\Storage',
          'str' => 'Illuminate\\Support\\Str',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
          'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
        ),
         'className' => 'App\\Modules\\DocumentManagement\\Services\\DocumentRepositoryService',
         'functionName' => 'resolveVisibilityScope',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\DocumentManagement\\Services',
           'uses' => 
          array (
            'document' => 'App\\Models\\Document',
            'documentcategory' => 'App\\Models\\DocumentCategory',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbonimmutable' => 'Carbon\\CarbonImmutable',
            'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
            'modelnotfoundexception' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
            'uploadedfile' => 'Illuminate\\Http\\UploadedFile',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'storage' => 'Illuminate\\Support\\Facades\\Storage',
            'str' => 'Illuminate\\Support\\Str',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
            'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
          ),
           'className' => 'App\\Modules\\DocumentManagement\\Services\\DocumentRepositoryService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'DocumentRepositoryFilters' => true,
            'DocumentRepositoryPayload' => true,
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
          'DocumentRepositoryFilters' => true,
          'DocumentRepositoryPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '05fa2f500d537bb7f854bf6d7be92da5' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\DocumentManagement\\Services',
         'uses' => 
        array (
          'document' => 'App\\Models\\Document',
          'documentcategory' => 'App\\Models\\DocumentCategory',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbonimmutable' => 'Carbon\\CarbonImmutable',
          'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
          'modelnotfoundexception' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
          'uploadedfile' => 'Illuminate\\Http\\UploadedFile',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'storage' => 'Illuminate\\Support\\Facades\\Storage',
          'str' => 'Illuminate\\Support\\Str',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
          'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
        ),
         'className' => 'App\\Modules\\DocumentManagement\\Services\\DocumentRepositoryService',
         'functionName' => 'resolveRetentionUntil',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\DocumentManagement\\Services',
           'uses' => 
          array (
            'document' => 'App\\Models\\Document',
            'documentcategory' => 'App\\Models\\DocumentCategory',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbonimmutable' => 'Carbon\\CarbonImmutable',
            'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
            'modelnotfoundexception' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
            'uploadedfile' => 'Illuminate\\Http\\UploadedFile',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'storage' => 'Illuminate\\Support\\Facades\\Storage',
            'str' => 'Illuminate\\Support\\Str',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
            'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
          ),
           'className' => 'App\\Modules\\DocumentManagement\\Services\\DocumentRepositoryService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'DocumentRepositoryFilters' => true,
            'DocumentRepositoryPayload' => true,
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
          'DocumentRepositoryFilters' => true,
          'DocumentRepositoryPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '79fbae00999dc1e4bb5a9d4c3d8bad94' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\DocumentManagement\\Services',
         'uses' => 
        array (
          'document' => 'App\\Models\\Document',
          'documentcategory' => 'App\\Models\\DocumentCategory',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbonimmutable' => 'Carbon\\CarbonImmutable',
          'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
          'modelnotfoundexception' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
          'uploadedfile' => 'Illuminate\\Http\\UploadedFile',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'storage' => 'Illuminate\\Support\\Facades\\Storage',
          'str' => 'Illuminate\\Support\\Str',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
          'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
        ),
         'className' => 'App\\Modules\\DocumentManagement\\Services\\DocumentRepositoryService',
         'functionName' => 'assertDocumentAccessible',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\DocumentManagement\\Services',
           'uses' => 
          array (
            'document' => 'App\\Models\\Document',
            'documentcategory' => 'App\\Models\\DocumentCategory',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbonimmutable' => 'Carbon\\CarbonImmutable',
            'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
            'modelnotfoundexception' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
            'uploadedfile' => 'Illuminate\\Http\\UploadedFile',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'storage' => 'Illuminate\\Support\\Facades\\Storage',
            'str' => 'Illuminate\\Support\\Str',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
            'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
          ),
           'className' => 'App\\Modules\\DocumentManagement\\Services\\DocumentRepositoryService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'DocumentRepositoryFilters' => true,
            'DocumentRepositoryPayload' => true,
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
          'DocumentRepositoryFilters' => true,
          'DocumentRepositoryPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '320b8a7463370798a588810ee306a4c2' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\DocumentManagement\\Services',
         'uses' => 
        array (
          'document' => 'App\\Models\\Document',
          'documentcategory' => 'App\\Models\\DocumentCategory',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbonimmutable' => 'Carbon\\CarbonImmutable',
          'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
          'modelnotfoundexception' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
          'uploadedfile' => 'Illuminate\\Http\\UploadedFile',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'storage' => 'Illuminate\\Support\\Facades\\Storage',
          'str' => 'Illuminate\\Support\\Str',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
          'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
        ),
         'className' => 'App\\Modules\\DocumentManagement\\Services\\DocumentRepositoryService',
         'functionName' => 'canAccessDocument',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\DocumentManagement\\Services',
           'uses' => 
          array (
            'document' => 'App\\Models\\Document',
            'documentcategory' => 'App\\Models\\DocumentCategory',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbonimmutable' => 'Carbon\\CarbonImmutable',
            'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
            'modelnotfoundexception' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
            'uploadedfile' => 'Illuminate\\Http\\UploadedFile',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'storage' => 'Illuminate\\Support\\Facades\\Storage',
            'str' => 'Illuminate\\Support\\Str',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
            'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
          ),
           'className' => 'App\\Modules\\DocumentManagement\\Services\\DocumentRepositoryService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'DocumentRepositoryFilters' => true,
            'DocumentRepositoryPayload' => true,
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
          'DocumentRepositoryFilters' => true,
          'DocumentRepositoryPayload' => true,
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '601c2e784b5ba73c441491ee5b9a0ef9' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'App\\Modules\\DocumentManagement\\Services',
         'uses' => 
        array (
          'document' => 'App\\Models\\Document',
          'documentcategory' => 'App\\Models\\DocumentCategory',
          'user' => 'App\\Models\\User',
          'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
          'carbonimmutable' => 'Carbon\\CarbonImmutable',
          'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
          'modelnotfoundexception' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
          'uploadedfile' => 'Illuminate\\Http\\UploadedFile',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'storage' => 'Illuminate\\Support\\Facades\\Storage',
          'str' => 'Illuminate\\Support\\Str',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
          'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
        ),
         'className' => 'App\\Modules\\DocumentManagement\\Services\\DocumentRepositoryService',
         'functionName' => 'makeDirectory',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'App\\Modules\\DocumentManagement\\Services',
           'uses' => 
          array (
            'document' => 'App\\Models\\Document',
            'documentcategory' => 'App\\Models\\DocumentCategory',
            'user' => 'App\\Models\\User',
            'auditlogger' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'carbonimmutable' => 'Carbon\\CarbonImmutable',
            'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
            'modelnotfoundexception' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
            'uploadedfile' => 'Illuminate\\Http\\UploadedFile',
            'db' => 'Illuminate\\Support\\Facades\\DB',
            'storage' => 'Illuminate\\Support\\Facades\\Storage',
            'str' => 'Illuminate\\Support\\Str',
            'validationexception' => 'Illuminate\\Validation\\ValidationException',
            'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
          ),
           'className' => 'App\\Modules\\DocumentManagement\\Services\\DocumentRepositoryService',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
            'DocumentRepositoryFilters' => true,
            'DocumentRepositoryPayload' => true,
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
          'DocumentRepositoryFilters' => true,
          'DocumentRepositoryPayload' => true,
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
      '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/DocumentManagement/Services/DocumentRepositoryService.php' => '39b14913bc66d5f09f3b52bfd15119f20b2bf933bad4a3c28187feb0d7cad331',
    ),
  ),
));