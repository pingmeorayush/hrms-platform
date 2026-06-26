<?php declare(strict_types = 1);

// odsl-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Models/CandidateResume.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\CandidateResume
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.1-8.4.4-c2e1dd261605f5aafb7fd50807b4b9525ff7ff8eb55cda965e6b7e4955e1af7c',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\CandidateResume',
        'filename' => '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Models/CandidateResume.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\CandidateResume',
    'shortName' => 'CandidateResume',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property int $id
 * @property int $company_id
 * @property int $candidate_id
 * @property int $version_number
 * @property bool $is_current
 * @property string $original_file_name
 * @property string $disk
 * @property string $file_path
 * @property string $mime_type
 * @property int $file_size_bytes
 * @property string $checksum_sha256
 * @property string|null $notes
 * @property-read Candidate|null $candidate
 * @property-read User|null $uploader
 */',
    'attributes' => 
    array (
      0 => 
      array (
        'name' => 'Illuminate\\Database\\Eloquent\\Attributes\\Fillable',
        'isRepeated' => false,
        'arguments' => 
        array (
          0 => 
          array (
            'code' => '[\'company_id\', \'candidate_id\', \'version_number\', \'is_current\', \'original_file_name\', \'disk\', \'file_path\', \'mime_type\', \'file_size_bytes\', \'checksum_sha256\', \'notes\', \'uploaded_by_user_id\']',
            'attributes' => 
            array (
              'startLine' => 26,
              'endLine' => 39,
              'startTokenPos' => 32,
              'startFilePos' => 715,
              'endTokenPos' => 70,
              'endFilePos' => 953,
            ),
          ),
        ),
      ),
    ),
    'startLine' => 26,
    'endLine' => 68,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Illuminate\\Database\\Eloquent\\Model',
    'implementsClassNames' => 
    array (
    ),
    'traitClassNames' => 
    array (
      0 => 'App\\Modules\\Platform\\Tenancy\\Concerns\\BelongsToCompany',
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
    ),
    'immediateMethods' => 
    array (
      'candidate' => 
      array (
        'name' => 'candidate',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @return BelongsTo<Candidate, $this>
 */',
        'startLine' => 47,
        'endLine' => 50,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\CandidateResume',
        'implementingClassName' => 'App\\Models\\CandidateResume',
        'currentClassName' => 'App\\Models\\CandidateResume',
        'aliasName' => NULL,
      ),
      'uploader' => 
      array (
        'name' => 'uploader',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @return BelongsTo<User, $this>
 */',
        'startLine' => 55,
        'endLine' => 58,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\CandidateResume',
        'implementingClassName' => 'App\\Models\\CandidateResume',
        'currentClassName' => 'App\\Models\\CandidateResume',
        'aliasName' => NULL,
      ),
      'casts' => 
      array (
        'name' => 'casts',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 60,
        'endLine' => 67,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\CandidateResume',
        'implementingClassName' => 'App\\Models\\CandidateResume',
        'currentClassName' => 'App\\Models\\CandidateResume',
        'aliasName' => NULL,
      ),
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