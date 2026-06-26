<?php declare(strict_types = 1);

// osfsl-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/vendor/composer/../laravel/framework/src/Illuminate/Console/Scheduling/Event.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Illuminate\Console\Scheduling\Event
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-53253ca6cdd3e695dc7fd886fffa79e6903e129d4136c15f8148dfd91f93d2cc-8.4.4-6.70.0.1',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Illuminate\\Console\\Scheduling\\Event',
        'filename' => '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/vendor/composer/../laravel/framework/src/Illuminate/Console/Scheduling/Event.php',
      ),
    ),
    'namespace' => 'Illuminate\\Console\\Scheduling',
    'name' => 'Illuminate\\Console\\Scheduling\\Event',
    'shortName' => 'Event',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 25,
    'endLine' => 936,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => NULL,
    'implementsClassNames' => 
    array (
    ),
    'traitClassNames' => 
    array (
      0 => 'Illuminate\\Support\\Traits\\Macroable',
      1 => 'Illuminate\\Console\\Scheduling\\ManagesAttributes',
      2 => 'Illuminate\\Console\\Scheduling\\ManagesFrequencies',
      3 => 'Illuminate\\Support\\Traits\\ReflectsClosures',
      4 => 'Illuminate\\Support\\Traits\\Tappable',
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
      'command' => 
      array (
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'name' => 'command',
        'modifiers' => 1,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The command string.
 *
 * @var string|null
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 34,
        'endLine' => 34,
        'startColumn' => 5,
        'endColumn' => 20,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'output' => 
      array (
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'name' => 'output',
        'modifiers' => 1,
        'type' => NULL,
        'default' => 
        array (
          'code' => '\'/dev/null\'',
          'attributes' => 
          array (
            'startLine' => 41,
            'endLine' => 41,
            'startTokenPos' => 148,
            'startFilePos' => 1072,
            'endTokenPos' => 148,
            'endFilePos' => 1082,
          ),
        ),
        'docComment' => '/**
 * The location that output should be sent to.
 *
 * @var string
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 41,
        'endLine' => 41,
        'startColumn' => 5,
        'endColumn' => 33,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'shouldAppendOutput' => 
      array (
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'name' => 'shouldAppendOutput',
        'modifiers' => 1,
        'type' => NULL,
        'default' => 
        array (
          'code' => 'false',
          'attributes' => 
          array (
            'startLine' => 48,
            'endLine' => 48,
            'startTokenPos' => 159,
            'startFilePos' => 1211,
            'endTokenPos' => 159,
            'endFilePos' => 1215,
          ),
        ),
        'docComment' => '/**
 * Indicates whether output should be appended.
 *
 * @var bool
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 48,
        'endLine' => 48,
        'startColumn' => 5,
        'endColumn' => 39,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'beforeCallbacks' => 
      array (
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'name' => 'beforeCallbacks',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 55,
            'endLine' => 55,
            'startTokenPos' => 170,
            'startFilePos' => 1362,
            'endTokenPos' => 171,
            'endFilePos' => 1363,
          ),
        ),
        'docComment' => '/**
 * The array of callbacks to be run before the event is started.
 *
 * @var array
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 55,
        'endLine' => 55,
        'startColumn' => 5,
        'endColumn' => 36,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'afterCallbacks' => 
      array (
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'name' => 'afterCallbacks',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 62,
            'endLine' => 62,
            'startTokenPos' => 182,
            'startFilePos' => 1509,
            'endTokenPos' => 183,
            'endFilePos' => 1510,
          ),
        ),
        'docComment' => '/**
 * The array of callbacks to be run after the event is finished.
 *
 * @var array
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 62,
        'endLine' => 62,
        'startColumn' => 5,
        'endColumn' => 35,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'mutex' => 
      array (
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'name' => 'mutex',
        'modifiers' => 1,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The event mutex implementation.
 *
 * @var \\Illuminate\\Console\\Scheduling\\EventMutex
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 69,
        'endLine' => 69,
        'startColumn' => 5,
        'endColumn' => 18,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'mutexNameResolver' => 
      array (
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'name' => 'mutexNameResolver',
        'modifiers' => 1,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The mutex name resolver callback.
 *
 * @var \\Closure|null
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 76,
        'endLine' => 76,
        'startColumn' => 5,
        'endColumn' => 30,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'lastChecked' => 
      array (
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'name' => 'lastChecked',
        'modifiers' => 2,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The last time the event was checked for eligibility to run.
 *
 * Utilized by sub-minute repeated events.
 *
 * @var \\Illuminate\\Support\\Carbon|null
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 85,
        'endLine' => 85,
        'startColumn' => 5,
        'endColumn' => 27,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'exitCode' => 
      array (
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'name' => 'exitCode',
        'modifiers' => 1,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The exit status code of the command.
 *
 * @var int|null
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 92,
        'endLine' => 92,
        'startColumn' => 5,
        'endColumn' => 21,
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
      '__construct' => 
      array (
        'name' => '__construct',
        'parameters' => 
        array (
          'mutex' => 
          array (
            'name' => 'mutex',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Console\\Scheduling\\EventMutex',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 101,
            'endLine' => 101,
            'startColumn' => 33,
            'endColumn' => 49,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'command' => 
          array (
            'name' => 'command',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 101,
            'endLine' => 101,
            'startColumn' => 52,
            'endColumn' => 59,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'timezone' => 
          array (
            'name' => 'timezone',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 101,
                'endLine' => 101,
                'startTokenPos' => 234,
                'startFilePos' => 2381,
                'endTokenPos' => 234,
                'endFilePos' => 2384,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 101,
            'endLine' => 101,
            'startColumn' => 62,
            'endColumn' => 77,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Create a new event instance.
 *
 * @param  \\Illuminate\\Console\\Scheduling\\EventMutex  $mutex
 * @param  string  $command
 * @param  \\DateTimeZone|string|null  $timezone
 */',
        'startLine' => 101,
        'endLine' => 108,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'getDefaultOutput' => 
      array (
        'name' => 'getDefaultOutput',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the default output depending on the OS.
 *
 * @return string
 */',
        'startLine' => 115,
        'endLine' => 118,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'run' => 
      array (
        'name' => 'run',
        'parameters' => 
        array (
          'container' => 
          array (
            'name' => 'container',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Contracts\\Container\\Container',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 128,
            'endLine' => 128,
            'startColumn' => 25,
            'endColumn' => 44,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Run the given event.
 *
 * @param  \\Illuminate\\Contracts\\Container\\Container  $container
 * @return void
 *
 * @throws \\Throwable
 */',
        'startLine' => 128,
        'endLine' => 141,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'shouldSkipDueToOverlapping' => 
      array (
        'name' => 'shouldSkipDueToOverlapping',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if the event should skip because another process is overlapping.
 *
 * @return bool
 */',
        'startLine' => 148,
        'endLine' => 151,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'isRepeatable' => 
      array (
        'name' => 'isRepeatable',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if the event has been configured to repeat multiple times per minute.
 *
 * @return bool
 */',
        'startLine' => 158,
        'endLine' => 161,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'shouldRepeatNow' => 
      array (
        'name' => 'shouldRepeatNow',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if the event is ready to repeat.
 *
 * @return bool
 */',
        'startLine' => 168,
        'endLine' => 172,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'start' => 
      array (
        'name' => 'start',
        'parameters' => 
        array (
          'container' => 
          array (
            'name' => 'container',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 182,
            'endLine' => 182,
            'startColumn' => 30,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Run the command process.
 *
 * @param  \\Illuminate\\Contracts\\Container\\Container  $container
 * @return int
 *
 * @throws \\Throwable
 */',
        'startLine' => 182,
        'endLine' => 193,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'execute' => 
      array (
        'name' => 'execute',
        'parameters' => 
        array (
          'container' => 
          array (
            'name' => 'container',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 201,
            'endLine' => 201,
            'startColumn' => 32,
            'endColumn' => 41,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Run the command process.
 *
 * @param  \\Illuminate\\Contracts\\Container\\Container  $container
 * @return int
 */',
        'startLine' => 201,
        'endLine' => 212,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'finish' => 
      array (
        'name' => 'finish',
        'parameters' => 
        array (
          'container' => 
          array (
            'name' => 'container',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Contracts\\Container\\Container',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 221,
            'endLine' => 221,
            'startColumn' => 28,
            'endColumn' => 47,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'exitCode' => 
          array (
            'name' => 'exitCode',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 221,
            'endLine' => 221,
            'startColumn' => 50,
            'endColumn' => 58,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Mark the command process as finished and run callbacks/cleanup.
 *
 * @param  \\Illuminate\\Contracts\\Container\\Container  $container
 * @param  int  $exitCode
 * @return void
 */',
        'startLine' => 221,
        'endLine' => 230,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'callBeforeCallbacks' => 
      array (
        'name' => 'callBeforeCallbacks',
        'parameters' => 
        array (
          'container' => 
          array (
            'name' => 'container',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Contracts\\Container\\Container',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 238,
            'endLine' => 238,
            'startColumn' => 41,
            'endColumn' => 60,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Call all of the "before" callbacks for the event.
 *
 * @param  \\Illuminate\\Contracts\\Container\\Container  $container
 * @return void
 */',
        'startLine' => 238,
        'endLine' => 243,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'callAfterCallbacks' => 
      array (
        'name' => 'callAfterCallbacks',
        'parameters' => 
        array (
          'container' => 
          array (
            'name' => 'container',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Contracts\\Container\\Container',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 251,
            'endLine' => 251,
            'startColumn' => 40,
            'endColumn' => 59,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Call all of the "after" callbacks for the event.
 *
 * @param  \\Illuminate\\Contracts\\Container\\Container  $container
 * @return void
 */',
        'startLine' => 251,
        'endLine' => 256,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'buildCommand' => 
      array (
        'name' => 'buildCommand',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Build the command string.
 *
 * @return string
 */',
        'startLine' => 263,
        'endLine' => 266,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'isDue' => 
      array (
        'name' => 'isDue',
        'parameters' => 
        array (
          'app' => 
          array (
            'name' => 'app',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 274,
            'endLine' => 274,
            'startColumn' => 27,
            'endColumn' => 30,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if the given event should run based on the Cron expression.
 *
 * @param  \\Illuminate\\Contracts\\Foundation\\Application  $app
 * @return bool
 */',
        'startLine' => 274,
        'endLine' => 282,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'runsInMaintenanceMode' => 
      array (
        'name' => 'runsInMaintenanceMode',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if the event runs in maintenance mode.
 *
 * @return bool
 */',
        'startLine' => 289,
        'endLine' => 292,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'runsWhenPaused' => 
      array (
        'name' => 'runsWhenPaused',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if the event runs when the scheduler is paused.
 *
 * @return bool
 */',
        'startLine' => 299,
        'endLine' => 302,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'expressionPasses' => 
      array (
        'name' => 'expressionPasses',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if the Cron expression passes.
 *
 * @return bool
 */',
        'startLine' => 309,
        'endLine' => 318,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'runsInEnvironment' => 
      array (
        'name' => 'runsInEnvironment',
        'parameters' => 
        array (
          'environment' => 
          array (
            'name' => 'environment',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 326,
            'endLine' => 326,
            'startColumn' => 39,
            'endColumn' => 50,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if the event runs in the given environment.
 *
 * @param  string  $environment
 * @return bool
 */',
        'startLine' => 326,
        'endLine' => 329,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'filtersPass' => 
      array (
        'name' => 'filtersPass',
        'parameters' => 
        array (
          'app' => 
          array (
            'name' => 'app',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 337,
            'endLine' => 337,
            'startColumn' => 33,
            'endColumn' => 36,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if the filters pass for the event.
 *
 * @param  \\Illuminate\\Contracts\\Foundation\\Application  $app
 * @return bool
 */',
        'startLine' => 337,
        'endLine' => 354,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'storeOutput' => 
      array (
        'name' => 'storeOutput',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Ensure that the output is stored on disk in a log file.
 *
 * @return $this
 */',
        'startLine' => 361,
        'endLine' => 366,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'sendOutputTo' => 
      array (
        'name' => 'sendOutputTo',
        'parameters' => 
        array (
          'location' => 
          array (
            'name' => 'location',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 375,
            'endLine' => 375,
            'startColumn' => 34,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'append' => 
          array (
            'name' => 'append',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 375,
                'endLine' => 375,
                'startTokenPos' => 1224,
                'startFilePos' => 9020,
                'endTokenPos' => 1224,
                'endFilePos' => 9024,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 375,
            'endLine' => 375,
            'startColumn' => 45,
            'endColumn' => 59,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Send the output of the command to a given location.
 *
 * @param  string  $location
 * @param  bool  $append
 * @return $this
 */',
        'startLine' => 375,
        'endLine' => 382,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'appendOutputTo' => 
      array (
        'name' => 'appendOutputTo',
        'parameters' => 
        array (
          'location' => 
          array (
            'name' => 'location',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 390,
            'endLine' => 390,
            'startColumn' => 36,
            'endColumn' => 44,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Append the output of the command to a given location.
 *
 * @param  string  $location
 * @return $this
 */',
        'startLine' => 390,
        'endLine' => 393,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'emailOutputTo' => 
      array (
        'name' => 'emailOutputTo',
        'parameters' => 
        array (
          'addresses' => 
          array (
            'name' => 'addresses',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 404,
            'endLine' => 404,
            'startColumn' => 35,
            'endColumn' => 44,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'onlyIfOutputExists' => 
          array (
            'name' => 'onlyIfOutputExists',
            'default' => 
            array (
              'code' => 'true',
              'attributes' => 
              array (
                'startLine' => 404,
                'endLine' => 404,
                'startTokenPos' => 1297,
                'startFilePos' => 9672,
                'endTokenPos' => 1297,
                'endFilePos' => 9675,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 404,
            'endLine' => 404,
            'startColumn' => 47,
            'endColumn' => 72,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * E-mail the results of the scheduled operation.
 *
 * @param  mixed  $addresses
 * @param  bool  $onlyIfOutputExists
 * @return $this
 *
 * @throws \\LogicException
 */',
        'startLine' => 404,
        'endLine' => 413,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'emailWrittenOutputTo' => 
      array (
        'name' => 'emailWrittenOutputTo',
        'parameters' => 
        array (
          'addresses' => 
          array (
            'name' => 'addresses',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 423,
            'endLine' => 423,
            'startColumn' => 42,
            'endColumn' => 51,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * E-mail the results of the scheduled operation if it produces output.
 *
 * @param  mixed  $addresses
 * @return $this
 *
 * @throws \\LogicException
 */',
        'startLine' => 423,
        'endLine' => 426,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'emailOutputOnFailure' => 
      array (
        'name' => 'emailOutputOnFailure',
        'parameters' => 
        array (
          'addresses' => 
          array (
            'name' => 'addresses',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 434,
            'endLine' => 434,
            'startColumn' => 42,
            'endColumn' => 51,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * E-mail the results of the scheduled operation if it fails.
 *
 * @param  mixed  $addresses
 * @return $this
 */',
        'startLine' => 434,
        'endLine' => 443,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'ensureOutputIsBeingCaptured' => 
      array (
        'name' => 'ensureOutputIsBeingCaptured',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Ensure that the command output is being captured.
 *
 * @return void
 */',
        'startLine' => 450,
        'endLine' => 455,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'emailOutput' => 
      array (
        'name' => 'emailOutput',
        'parameters' => 
        array (
          'mailer' => 
          array (
            'name' => 'mailer',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Contracts\\Mail\\Mailer',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 465,
            'endLine' => 465,
            'startColumn' => 36,
            'endColumn' => 49,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'addresses' => 
          array (
            'name' => 'addresses',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 465,
            'endLine' => 465,
            'startColumn' => 52,
            'endColumn' => 61,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'onlyIfOutputExists' => 
          array (
            'name' => 'onlyIfOutputExists',
            'default' => 
            array (
              'code' => 'true',
              'attributes' => 
              array (
                'startLine' => 465,
                'endLine' => 465,
                'startTokenPos' => 1553,
                'startFilePos' => 11398,
                'endTokenPos' => 1553,
                'endFilePos' => 11401,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 465,
            'endLine' => 465,
            'startColumn' => 64,
            'endColumn' => 89,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * E-mail the output of the event to the recipients.
 *
 * @param  \\Illuminate\\Contracts\\Mail\\Mailer  $mailer
 * @param  array  $addresses
 * @param  bool  $onlyIfOutputExists
 * @return void
 */',
        'startLine' => 465,
        'endLine' => 476,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'getEmailSubject' => 
      array (
        'name' => 'getEmailSubject',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the e-mail subject line for output results.
 *
 * @return string
 */',
        'startLine' => 483,
        'endLine' => 490,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'pingBefore' => 
      array (
        'name' => 'pingBefore',
        'parameters' => 
        array (
          'url' => 
          array (
            'name' => 'url',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 498,
            'endLine' => 498,
            'startColumn' => 32,
            'endColumn' => 35,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Register a callback to ping a given URL before the job runs.
 *
 * @param  string  $url
 * @return $this
 */',
        'startLine' => 498,
        'endLine' => 501,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'pingBeforeIf' => 
      array (
        'name' => 'pingBeforeIf',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 510,
            'endLine' => 510,
            'startColumn' => 34,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'url' => 
          array (
            'name' => 'url',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 510,
            'endLine' => 510,
            'startColumn' => 42,
            'endColumn' => 45,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Register a callback to ping a given URL before the job runs if the given condition is true.
 *
 * @param  bool  $value
 * @param  string  $url
 * @return $this
 */',
        'startLine' => 510,
        'endLine' => 513,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'thenPing' => 
      array (
        'name' => 'thenPing',
        'parameters' => 
        array (
          'url' => 
          array (
            'name' => 'url',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 521,
            'endLine' => 521,
            'startColumn' => 30,
            'endColumn' => 33,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Register a callback to ping a given URL after the job runs.
 *
 * @param  string  $url
 * @return $this
 */',
        'startLine' => 521,
        'endLine' => 524,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'thenPingIf' => 
      array (
        'name' => 'thenPingIf',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 533,
            'endLine' => 533,
            'startColumn' => 32,
            'endColumn' => 37,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'url' => 
          array (
            'name' => 'url',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 533,
            'endLine' => 533,
            'startColumn' => 40,
            'endColumn' => 43,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Register a callback to ping a given URL after the job runs if the given condition is true.
 *
 * @param  bool  $value
 * @param  string  $url
 * @return $this
 */',
        'startLine' => 533,
        'endLine' => 536,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'pingOnSuccess' => 
      array (
        'name' => 'pingOnSuccess',
        'parameters' => 
        array (
          'url' => 
          array (
            'name' => 'url',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 544,
            'endLine' => 544,
            'startColumn' => 35,
            'endColumn' => 38,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Register a callback to ping a given URL if the operation succeeds.
 *
 * @param  string  $url
 * @return $this
 */',
        'startLine' => 544,
        'endLine' => 547,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'pingOnSuccessIf' => 
      array (
        'name' => 'pingOnSuccessIf',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 556,
            'endLine' => 556,
            'startColumn' => 37,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'url' => 
          array (
            'name' => 'url',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 556,
            'endLine' => 556,
            'startColumn' => 45,
            'endColumn' => 48,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Register a callback to ping a given URL if the operation succeeds and if the given condition is true.
 *
 * @param  bool  $value
 * @param  string  $url
 * @return $this
 */',
        'startLine' => 556,
        'endLine' => 559,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'pingOnFailure' => 
      array (
        'name' => 'pingOnFailure',
        'parameters' => 
        array (
          'url' => 
          array (
            'name' => 'url',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 567,
            'endLine' => 567,
            'startColumn' => 35,
            'endColumn' => 38,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Register a callback to ping a given URL if the operation fails.
 *
 * @param  string  $url
 * @return $this
 */',
        'startLine' => 567,
        'endLine' => 570,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'pingOnFailureIf' => 
      array (
        'name' => 'pingOnFailureIf',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 579,
            'endLine' => 579,
            'startColumn' => 37,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'url' => 
          array (
            'name' => 'url',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 579,
            'endLine' => 579,
            'startColumn' => 45,
            'endColumn' => 48,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Register a callback to ping a given URL if the operation fails and if the given condition is true.
 *
 * @param  bool  $value
 * @param  string  $url
 * @return $this
 */',
        'startLine' => 579,
        'endLine' => 582,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'pingCallback' => 
      array (
        'name' => 'pingCallback',
        'parameters' => 
        array (
          'url' => 
          array (
            'name' => 'url',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 590,
            'endLine' => 590,
            'startColumn' => 37,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the callback that pings the given URL.
 *
 * @param  string  $url
 * @return \\Closure
 */',
        'startLine' => 590,
        'endLine' => 599,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'getHttpClient' => 
      array (
        'name' => 'getHttpClient',
        'parameters' => 
        array (
          'container' => 
          array (
            'name' => 'container',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Contracts\\Container\\Container',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 607,
            'endLine' => 607,
            'startColumn' => 38,
            'endColumn' => 57,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the Guzzle HTTP client to use to send pings.
 *
 * @param  \\Illuminate\\Contracts\\Container\\Container  $container
 * @return \\GuzzleHttp\\ClientInterface
 */',
        'startLine' => 607,
        'endLine' => 618,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'before' => 
      array (
        'name' => 'before',
        'parameters' => 
        array (
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Closure',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 626,
            'endLine' => 626,
            'startColumn' => 28,
            'endColumn' => 44,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Register a callback to be called before the operation.
 *
 * @param  \\Closure  $callback
 * @return $this
 */',
        'startLine' => 626,
        'endLine' => 631,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'after' => 
      array (
        'name' => 'after',
        'parameters' => 
        array (
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Closure',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 639,
            'endLine' => 639,
            'startColumn' => 27,
            'endColumn' => 43,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Register a callback to be called after the operation.
 *
 * @param  \\Closure  $callback
 * @return $this
 */',
        'startLine' => 639,
        'endLine' => 642,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'then' => 
      array (
        'name' => 'then',
        'parameters' => 
        array (
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Closure',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 650,
            'endLine' => 650,
            'startColumn' => 26,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Register a callback to be called after the operation.
 *
 * @param  \\Closure  $callback
 * @return $this
 */',
        'startLine' => 650,
        'endLine' => 661,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'thenWithOutput' => 
      array (
        'name' => 'thenWithOutput',
        'parameters' => 
        array (
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Closure',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 670,
            'endLine' => 670,
            'startColumn' => 36,
            'endColumn' => 52,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'onlyIfOutputExists' => 
          array (
            'name' => 'onlyIfOutputExists',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 670,
                'endLine' => 670,
                'startTokenPos' => 2317,
                'startFilePos' => 16776,
                'endTokenPos' => 2317,
                'endFilePos' => 16780,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 670,
            'endLine' => 670,
            'startColumn' => 55,
            'endColumn' => 81,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Register a callback that uses the output after the job runs.
 *
 * @param  \\Closure  $callback
 * @param  bool  $onlyIfOutputExists
 * @return $this
 */',
        'startLine' => 670,
        'endLine' => 675,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'onSuccess' => 
      array (
        'name' => 'onSuccess',
        'parameters' => 
        array (
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Closure',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 683,
            'endLine' => 683,
            'startColumn' => 31,
            'endColumn' => 47,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Register a callback to be called if the operation succeeds.
 *
 * @param  \\Closure  $callback
 * @return $this
 */',
        'startLine' => 683,
        'endLine' => 696,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'onSuccessWithOutput' => 
      array (
        'name' => 'onSuccessWithOutput',
        'parameters' => 
        array (
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Closure',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 705,
            'endLine' => 705,
            'startColumn' => 41,
            'endColumn' => 57,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'onlyIfOutputExists' => 
          array (
            'name' => 'onlyIfOutputExists',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 705,
                'endLine' => 705,
                'startTokenPos' => 2482,
                'startFilePos' => 17825,
                'endTokenPos' => 2482,
                'endFilePos' => 17829,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 705,
            'endLine' => 705,
            'startColumn' => 60,
            'endColumn' => 86,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Register a callback that uses the output if the operation succeeds.
 *
 * @param  \\Closure  $callback
 * @param  bool  $onlyIfOutputExists
 * @return $this
 */',
        'startLine' => 705,
        'endLine' => 710,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'onFailure' => 
      array (
        'name' => 'onFailure',
        'parameters' => 
        array (
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Closure',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 718,
            'endLine' => 718,
            'startColumn' => 31,
            'endColumn' => 47,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Register a callback to be called if the operation fails.
 *
 * @param  \\Closure  $callback
 * @return $this
 */',
        'startLine' => 718,
        'endLine' => 731,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'onFailureWithOutput' => 
      array (
        'name' => 'onFailureWithOutput',
        'parameters' => 
        array (
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Closure',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 740,
            'endLine' => 740,
            'startColumn' => 41,
            'endColumn' => 57,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'onlyIfOutputExists' => 
          array (
            'name' => 'onlyIfOutputExists',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 740,
                'endLine' => 740,
                'startTokenPos' => 2647,
                'startFilePos' => 18873,
                'endTokenPos' => 2647,
                'endFilePos' => 18877,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 740,
            'endLine' => 740,
            'startColumn' => 60,
            'endColumn' => 86,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Register a callback that uses the output if the operation fails.
 *
 * @param  \\Closure  $callback
 * @param  bool  $onlyIfOutputExists
 * @return $this
 */',
        'startLine' => 740,
        'endLine' => 745,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'withOutputCallback' => 
      array (
        'name' => 'withOutputCallback',
        'parameters' => 
        array (
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Closure',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 754,
            'endLine' => 754,
            'startColumn' => 43,
            'endColumn' => 59,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'onlyIfOutputExists' => 
          array (
            'name' => 'onlyIfOutputExists',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 754,
                'endLine' => 754,
                'startTokenPos' => 2696,
                'startFilePos' => 19282,
                'endTokenPos' => 2696,
                'endFilePos' => 19286,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 754,
            'endLine' => 754,
            'startColumn' => 62,
            'endColumn' => 88,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get a callback that provides output.
 *
 * @param  \\Closure  $callback
 * @param  bool  $onlyIfOutputExists
 * @return \\Closure
 */',
        'startLine' => 754,
        'endLine' => 763,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'callEventCallback' => 
      array (
        'name' => 'callEventCallback',
        'parameters' => 
        array (
          'container' => 
          array (
            'name' => 'container',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Contracts\\Container\\Container',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 773,
            'endLine' => 773,
            'startColumn' => 42,
            'endColumn' => 61,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'callable',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 773,
            'endLine' => 773,
            'startColumn' => 64,
            'endColumn' => 81,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'parameters' => 
          array (
            'name' => 'parameters',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 773,
                'endLine' => 773,
                'startTokenPos' => 2824,
                'startFilePos' => 20027,
                'endTokenPos' => 2825,
                'endFilePos' => 20028,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 773,
            'endLine' => 773,
            'startColumn' => 84,
            'endColumn' => 105,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Call the given event callback.
 *
 * @param  \\Illuminate\\Contracts\\Container\\Container  $container
 * @param  callable  $callback
 * @param  array<string, mixed>  $parameters
 * @return mixed
 */',
        'startLine' => 773,
        'endLine' => 782,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'eventParametersForCallback' => 
      array (
        'name' => 'eventParametersForCallback',
        'parameters' => 
        array (
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Closure',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 790,
            'endLine' => 790,
            'startColumn' => 51,
            'endColumn' => 67,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the event parameters for the given callback.
 *
 * @param  \\Closure  $callback
 * @return array
 */',
        'startLine' => 790,
        'endLine' => 801,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'getSummaryForDisplay' => 
      array (
        'name' => 'getSummaryForDisplay',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the summary of the event for display.
 *
 * @return string
 */',
        'startLine' => 808,
        'endLine' => 815,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'nextRunDate' => 
      array (
        'name' => 'nextRunDate',
        'parameters' => 
        array (
          'currentTime' => 
          array (
            'name' => 'currentTime',
            'default' => 
            array (
              'code' => '\'now\'',
              'attributes' => 
              array (
                'startLine' => 825,
                'endLine' => 825,
                'startTokenPos' => 3023,
                'startFilePos' => 21323,
                'endTokenPos' => 3023,
                'endFilePos' => 21327,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 825,
            'endLine' => 825,
            'startColumn' => 33,
            'endColumn' => 52,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'nth' => 
          array (
            'name' => 'nth',
            'default' => 
            array (
              'code' => '0',
              'attributes' => 
              array (
                'startLine' => 825,
                'endLine' => 825,
                'startTokenPos' => 3030,
                'startFilePos' => 21337,
                'endTokenPos' => 3030,
                'endFilePos' => 21337,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 825,
            'endLine' => 825,
            'startColumn' => 55,
            'endColumn' => 62,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'allowCurrentDate' => 
          array (
            'name' => 'allowCurrentDate',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 825,
                'endLine' => 825,
                'startTokenPos' => 3037,
                'startFilePos' => 21360,
                'endTokenPos' => 3037,
                'endFilePos' => 21364,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 825,
            'endLine' => 825,
            'startColumn' => 65,
            'endColumn' => 89,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine the next due date for an event.
 *
 * @param  \\DateTimeInterface|string  $currentTime
 * @param  int  $nth
 * @param  bool  $allowCurrentDate
 * @return \\Illuminate\\Support\\Carbon
 */',
        'startLine' => 825,
        'endLine' => 829,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'getExpression' => 
      array (
        'name' => 'getExpression',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the Cron expression for the event.
 *
 * @return string
 */',
        'startLine' => 836,
        'endLine' => 839,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'preventOverlapsUsing' => 
      array (
        'name' => 'preventOverlapsUsing',
        'parameters' => 
        array (
          'mutex' => 
          array (
            'name' => 'mutex',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Console\\Scheduling\\EventMutex',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 847,
            'endLine' => 847,
            'startColumn' => 42,
            'endColumn' => 58,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set the event mutex implementation to be used.
 *
 * @param  \\Illuminate\\Console\\Scheduling\\EventMutex  $mutex
 * @return $this
 */',
        'startLine' => 847,
        'endLine' => 852,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'mutexName' => 
      array (
        'name' => 'mutexName',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the mutex name for the scheduled command.
 *
 * @return string
 */',
        'startLine' => 859,
        'endLine' => 869,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'createMutexNameUsing' => 
      array (
        'name' => 'createMutexNameUsing',
        'parameters' => 
        array (
          'mutexName' => 
          array (
            'name' => 'mutexName',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'Closure',
                      'isIdentifier' => false,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'string',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 877,
            'endLine' => 877,
            'startColumn' => 42,
            'endColumn' => 66,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set the mutex name or name resolver callback.
 *
 * @param  \\Closure|string  $mutexName
 * @return $this
 */',
        'startLine' => 877,
        'endLine' => 882,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'ensureMutexIsReleasedOnSignal' => 
      array (
        'name' => 'ensureMutexIsReleasedOnSignal',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Ensure the mutex is released if the process receives a termination signal.
 *
 * @return void
 */',
        'startLine' => 889,
        'endLine' => 906,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'removeMutex' => 
      array (
        'name' => 'removeMutex',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Delete the mutex for the event.
 *
 * @return void
 */',
        'startLine' => 913,
        'endLine' => 918,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'aliasName' => NULL,
      ),
      'normalizeCommand' => 
      array (
        'name' => 'normalizeCommand',
        'parameters' => 
        array (
          'command' => 
          array (
            'name' => 'command',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 926,
            'endLine' => 926,
            'startColumn' => 45,
            'endColumn' => 52,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Format the given command string with a normalized PHP binary path.
 *
 * @param  string  $command
 * @return string
 */',
        'startLine' => 926,
        'endLine' => 935,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Console\\Scheduling',
        'declaringClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'implementingClassName' => 'Illuminate\\Console\\Scheduling\\Event',
        'currentClassName' => 'Illuminate\\Console\\Scheduling\\Event',
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