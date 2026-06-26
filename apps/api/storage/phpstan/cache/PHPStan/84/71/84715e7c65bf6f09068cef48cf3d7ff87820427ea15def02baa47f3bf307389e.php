<?php declare(strict_types = 1);

// osfsl-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/PerformanceManagement/Services/PerformanceConfigurationService.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Modules\PerformanceManagement\Services\PerformanceConfigurationService
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-2ad2c19a9e3e5f957e05a3119df686f16c147aa7699ef3a691e4e14ac855ce45-8.4.4-6.70.0.1',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'filename' => '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/PerformanceManagement/Services/PerformanceConfigurationService.php',
      ),
    ),
    'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
    'name' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
    'shortName' => 'PerformanceConfigurationService',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @phpstan-type PerformanceGoalFilters array{
 *   status?: string,
 *   owner_employee_id?: int|string,
 *   review_cycle_id?: int|string,
 *   department_id?: int|string,
 *   q?: string,
 *   per_page?: int|string
 * }
 * @phpstan-type PerformanceCompetencyFilters array{
 *   category?: string,
 *   status?: string,
 *   q?: string,
 *   per_page?: int|string
 * }
 * @phpstan-type PerformanceReviewCycleFilters array{
 *   cycle_type?: string,
 *   status?: string,
 *   q?: string,
 *   per_page?: int|string
 * }
 * @phpstan-type SuccessMetricPayload array{
 *   measure_type?: mixed,
 *   target_value?: mixed,
 *   unit?: mixed,
 *   notes?: mixed
 * }
 * @phpstan-type SuccessMetricData array{
 *   measure_type: string|null,
 *   target_value: mixed,
 *   unit: string|null,
 *   notes: string|null
 * }
 * @phpstan-type GoalPayloadInput array{
 *   goal_code: string,
 *   goal_type: string,
 *   title: string,
 *   description?: string|null,
 *   performance_review_cycle_id?: int|string|null,
 *   owner_employee_id: int|string,
 *   department_id?: int|string|null,
 *   due_on: string,
 *   weight_percent: int|float|string,
 *   success_metric?: SuccessMetricPayload|null,
 *   status: string
 * }
 * @phpstan-type GoalPayloadUpdate array{
 *   goal_code?: string,
 *   goal_type?: string,
 *   title?: string,
 *   description?: string|null,
 *   performance_review_cycle_id?: int|string|null,
 *   owner_employee_id?: int|string,
 *   department_id?: int|string|null,
 *   due_on?: string,
 *   weight_percent?: int|float|string,
 *   success_metric?: SuccessMetricPayload|null,
 *   status?: string
 * }
 * @phpstan-type GoalPayloadData array{
 *   performance_review_cycle_id: int|null,
 *   owner_employee_id: int,
 *   department_id: int|null,
 *   goal_code: string,
 *   goal_type: string,
 *   title: string,
 *   description: string|null,
 *   due_on: string,
 *   weight_percent: float,
 *   success_metric: SuccessMetricData|null,
 *   status: string
 * }
 * @phpstan-type ScaleLabelPayload array{
 *   value: int|string,
 *   label: string
 * }
 * @phpstan-type ScaleLabel array{
 *   value: int,
 *   label: string
 * }
 * @phpstan-type ScaleDefinitionPayload array{
 *   min_rating: int|string,
 *   max_rating: int|string,
 *   labels: list<ScaleLabelPayload>
 * }
 * @phpstan-type ScaleDefinition array{
 *   min_rating: int,
 *   max_rating: int,
 *   labels: list<ScaleLabel>
 * }
 * @phpstan-type CompetencyPayloadInput array{
 *   code: string,
 *   name: string,
 *   category: string,
 *   description?: string|null,
 *   scale_definition: ScaleDefinitionPayload,
 *   status: string
 * }
 * @phpstan-type CompetencyPayloadUpdate array{
 *   code?: string,
 *   name?: string,
 *   category?: string,
 *   description?: string|null,
 *   scale_definition?: ScaleDefinitionPayload,
 *   status?: string
 * }
 * @phpstan-type CompetencyPayloadData array{
 *   code: string,
 *   name: string,
 *   category: string,
 *   description: string|null,
 *   scale_definition: ScaleDefinition,
 *   status: string
 * }
 * @phpstan-type ParticipantPopulationPayload array{
 *   employment_statuses?: list<mixed>,
 *   employment_types?: list<mixed>,
 *   department_ids?: list<mixed>,
 *   designation_ids?: list<mixed>
 * }
 * @phpstan-type ParticipantPopulation array{
 *   employment_statuses: list<string>,
 *   employment_types: list<string>,
 *   department_ids: list<int>,
 *   designation_ids: list<int>
 * }
 * @phpstan-type ReviewerRulesPayload array{
 *   self_review_required?: bool|int|string,
 *   manager_review_required?: bool|int|string,
 *   peer_reviewer_slots?: int|string|null,
 *   allow_hr_reviewer?: bool|int|string
 * }
 * @phpstan-type ReviewerRules array{
 *   self_review_required: bool,
 *   manager_review_required: bool,
 *   peer_reviewer_slots: int,
 *   allow_hr_reviewer: bool
 * }
 * @phpstan-type ParticipantRulesPayload array{
 *   population: ParticipantPopulationPayload,
 *   reviewers: ReviewerRulesPayload
 * }
 * @phpstan-type ParticipantRules array{
 *   population: ParticipantPopulation,
 *   reviewers: ReviewerRules
 * }
 * @phpstan-type ReviewTemplateSectionPayload array{
 *   key: string,
 *   label: string,
 *   weight_percent: int|float|string,
 *   required: bool|int|string
 * }
 * @phpstan-type ReviewTemplateSection array{
 *   key: string,
 *   label: string,
 *   weight_percent: float,
 *   required: bool
 * }
 * @phpstan-type RatingScalePayload array{
 *   min: int|string,
 *   max: int|string
 * }
 * @phpstan-type RatingScale array{
 *   min: int,
 *   max: int
 * }
 * @phpstan-type ReviewTemplatePayload array{
 *   sections: list<ReviewTemplateSectionPayload>,
 *   rating_scale: RatingScalePayload
 * }
 * @phpstan-type ReviewTemplate array{
 *   sections: list<ReviewTemplateSection>,
 *   rating_scale: RatingScale
 * }
 * @phpstan-type CompetencyVisibilityPayload array{
 *   enabled?: bool|int|string,
 *   visible_to_employee?: bool|int|string,
 *   visible_to_manager?: bool|int|string,
 *   visible_to_hr?: bool|int|string,
 *   required_competency_ids?: list<mixed>
 * }
 * @phpstan-type CompetencyVisibility array{
 *   enabled: bool,
 *   visible_to_employee: bool,
 *   visible_to_manager: bool,
 *   visible_to_hr: bool,
 *   required_competency_ids: list<int>
 * }
 * @phpstan-type ReviewCyclePayloadInput array{
 *   code: string,
 *   name: string,
 *   cycle_type: string,
 *   starts_on: string,
 *   ends_on: string,
 *   self_review_due_on?: string|null,
 *   manager_review_due_on?: string|null,
 *   calibration_starts_on?: string|null,
 *   calibration_ends_on?: string|null,
 *   publish_on?: string|null,
 *   participant_rules: ParticipantRulesPayload,
 *   review_template: ReviewTemplatePayload,
 *   competency_visibility: CompetencyVisibilityPayload,
 *   status: string
 * }
 * @phpstan-type ReviewCyclePayloadUpdate array{
 *   code?: string,
 *   name?: string,
 *   cycle_type?: string,
 *   starts_on?: string,
 *   ends_on?: string,
 *   self_review_due_on?: string|null,
 *   manager_review_due_on?: string|null,
 *   calibration_starts_on?: string|null,
 *   calibration_ends_on?: string|null,
 *   publish_on?: string|null,
 *   participant_rules?: ParticipantRulesPayload,
 *   review_template?: ReviewTemplatePayload,
 *   competency_visibility?: CompetencyVisibilityPayload,
 *   status?: string
 * }
 * @phpstan-type ReviewCyclePayloadData array{
 *   code: string,
 *   name: string,
 *   cycle_type: string,
 *   starts_on: string,
 *   ends_on: string,
 *   self_review_due_on: string|null,
 *   manager_review_due_on: string|null,
 *   calibration_starts_on: string|null,
 *   calibration_ends_on: string|null,
 *   publish_on: string|null,
 *   participant_rules: ParticipantRules,
 *   review_template: ReviewTemplate,
 *   competency_visibility: CompetencyVisibility,
 *   status: string
 * }
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 255,
    'endLine' => 1055,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => NULL,
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
      'accessScopeService' => 
      array (
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'name' => 'accessScopeService',
        'modifiers' => 132,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceAccessScopeService',
            'isIdentifier' => false,
          ),
        ),
        'default' => NULL,
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 258,
        'endLine' => 258,
        'startColumn' => 9,
        'endColumn' => 74,
        'isPromoted' => true,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'auditLogger' => 
      array (
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'name' => 'auditLogger',
        'modifiers' => 132,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
            'isIdentifier' => false,
          ),
        ),
        'default' => NULL,
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 259,
        'endLine' => 259,
        'startColumn' => 9,
        'endColumn' => 49,
        'isPromoted' => true,
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
          'accessScopeService' => 
          array (
            'name' => 'accessScopeService',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceAccessScopeService',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => true,
            'attributes' => 
            array (
            ),
            'startLine' => 258,
            'endLine' => 258,
            'startColumn' => 9,
            'endColumn' => 74,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'auditLogger' => 
          array (
            'name' => 'auditLogger',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Modules\\Platform\\Audit\\Services\\AuditLogger',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => true,
            'attributes' => 
            array (
            ),
            'startLine' => 259,
            'endLine' => 259,
            'startColumn' => 9,
            'endColumn' => 49,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 257,
        'endLine' => 260,
        'startColumn' => 5,
        'endColumn' => 8,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'searchGoals' => 
      array (
        'name' => 'searchGoals',
        'parameters' => 
        array (
          'actor' => 
          array (
            'name' => 'actor',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\User',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 266,
            'endLine' => 266,
            'startColumn' => 33,
            'endColumn' => 43,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'filters' => 
          array (
            'name' => 'filters',
            'default' => NULL,
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
            'startLine' => 266,
            'endLine' => 266,
            'startColumn' => 46,
            'endColumn' => 59,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  PerformanceGoalFilters  $filters
 * @return LengthAwarePaginator<int, PerformanceGoal>
 */',
        'startLine' => 266,
        'endLine' => 289,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'searchCompetencies' => 
      array (
        'name' => 'searchCompetencies',
        'parameters' => 
        array (
          'actor' => 
          array (
            'name' => 'actor',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\User',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 295,
            'endLine' => 295,
            'startColumn' => 40,
            'endColumn' => 50,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'filters' => 
          array (
            'name' => 'filters',
            'default' => NULL,
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
            'startLine' => 295,
            'endLine' => 295,
            'startColumn' => 53,
            'endColumn' => 66,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  PerformanceCompetencyFilters  $filters
 * @return LengthAwarePaginator<int, PerformanceCompetency>
 */',
        'startLine' => 295,
        'endLine' => 316,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'searchReviewCycles' => 
      array (
        'name' => 'searchReviewCycles',
        'parameters' => 
        array (
          'actor' => 
          array (
            'name' => 'actor',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\User',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 322,
            'endLine' => 322,
            'startColumn' => 40,
            'endColumn' => 50,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'filters' => 
          array (
            'name' => 'filters',
            'default' => NULL,
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
            'startLine' => 322,
            'endLine' => 322,
            'startColumn' => 53,
            'endColumn' => 66,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Contracts\\Pagination\\LengthAwarePaginator',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  PerformanceReviewCycleFilters  $filters
 * @return LengthAwarePaginator<int, PerformanceReviewCycle>
 */',
        'startLine' => 322,
        'endLine' => 342,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'findGoalForView' => 
      array (
        'name' => 'findGoalForView',
        'parameters' => 
        array (
          'actor' => 
          array (
            'name' => 'actor',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\User',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 344,
            'endLine' => 344,
            'startColumn' => 37,
            'endColumn' => 47,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'goalId' => 
          array (
            'name' => 'goalId',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 344,
            'endLine' => 344,
            'startColumn' => 50,
            'endColumn' => 60,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'App\\Models\\PerformanceGoal',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 344,
        'endLine' => 347,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'findCompetencyForView' => 
      array (
        'name' => 'findCompetencyForView',
        'parameters' => 
        array (
          'actor' => 
          array (
            'name' => 'actor',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\User',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 349,
            'endLine' => 349,
            'startColumn' => 43,
            'endColumn' => 53,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'competencyId' => 
          array (
            'name' => 'competencyId',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 349,
            'endLine' => 349,
            'startColumn' => 56,
            'endColumn' => 72,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'App\\Models\\PerformanceCompetency',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 349,
        'endLine' => 352,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'findReviewCycleForView' => 
      array (
        'name' => 'findReviewCycleForView',
        'parameters' => 
        array (
          'actor' => 
          array (
            'name' => 'actor',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\User',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 354,
            'endLine' => 354,
            'startColumn' => 44,
            'endColumn' => 54,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'reviewCycleId' => 
          array (
            'name' => 'reviewCycleId',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 354,
            'endLine' => 354,
            'startColumn' => 57,
            'endColumn' => 74,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'App\\Models\\PerformanceReviewCycle',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 354,
        'endLine' => 357,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'createGoal' => 
      array (
        'name' => 'createGoal',
        'parameters' => 
        array (
          'actor' => 
          array (
            'name' => 'actor',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\User',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 362,
            'endLine' => 362,
            'startColumn' => 32,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'payload' => 
          array (
            'name' => 'payload',
            'default' => NULL,
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
            'startLine' => 362,
            'endLine' => 362,
            'startColumn' => 45,
            'endColumn' => 58,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'App\\Models\\PerformanceGoal',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  GoalPayloadInput  $payload
 */',
        'startLine' => 362,
        'endLine' => 401,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'updateGoal' => 
      array (
        'name' => 'updateGoal',
        'parameters' => 
        array (
          'actor' => 
          array (
            'name' => 'actor',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\User',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 406,
            'endLine' => 406,
            'startColumn' => 32,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'goal' => 
          array (
            'name' => 'goal',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\PerformanceGoal',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 406,
            'endLine' => 406,
            'startColumn' => 45,
            'endColumn' => 65,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'payload' => 
          array (
            'name' => 'payload',
            'default' => NULL,
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
            'startLine' => 406,
            'endLine' => 406,
            'startColumn' => 68,
            'endColumn' => 81,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'App\\Models\\PerformanceGoal',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  GoalPayloadUpdate  $payload
 */',
        'startLine' => 406,
        'endLine' => 462,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'createCompetency' => 
      array (
        'name' => 'createCompetency',
        'parameters' => 
        array (
          'actor' => 
          array (
            'name' => 'actor',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\User',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 467,
            'endLine' => 467,
            'startColumn' => 38,
            'endColumn' => 48,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'payload' => 
          array (
            'name' => 'payload',
            'default' => NULL,
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
            'startLine' => 467,
            'endLine' => 467,
            'startColumn' => 51,
            'endColumn' => 64,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'App\\Models\\PerformanceCompetency',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  CompetencyPayloadInput  $payload
 */',
        'startLine' => 467,
        'endLine' => 495,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'updateCompetency' => 
      array (
        'name' => 'updateCompetency',
        'parameters' => 
        array (
          'actor' => 
          array (
            'name' => 'actor',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\User',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 500,
            'endLine' => 500,
            'startColumn' => 38,
            'endColumn' => 48,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'competency' => 
          array (
            'name' => 'competency',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\PerformanceCompetency',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 500,
            'endLine' => 500,
            'startColumn' => 51,
            'endColumn' => 83,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'payload' => 
          array (
            'name' => 'payload',
            'default' => NULL,
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
            'startLine' => 500,
            'endLine' => 500,
            'startColumn' => 86,
            'endColumn' => 99,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'App\\Models\\PerformanceCompetency',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  CompetencyPayloadUpdate  $payload
 */',
        'startLine' => 500,
        'endLine' => 541,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'createReviewCycle' => 
      array (
        'name' => 'createReviewCycle',
        'parameters' => 
        array (
          'actor' => 
          array (
            'name' => 'actor',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\User',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 546,
            'endLine' => 546,
            'startColumn' => 39,
            'endColumn' => 49,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'payload' => 
          array (
            'name' => 'payload',
            'default' => NULL,
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
            'startLine' => 546,
            'endLine' => 546,
            'startColumn' => 52,
            'endColumn' => 65,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'App\\Models\\PerformanceReviewCycle',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  ReviewCyclePayloadInput  $payload
 */',
        'startLine' => 546,
        'endLine' => 576,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'updateReviewCycle' => 
      array (
        'name' => 'updateReviewCycle',
        'parameters' => 
        array (
          'actor' => 
          array (
            'name' => 'actor',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\User',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 581,
            'endLine' => 581,
            'startColumn' => 39,
            'endColumn' => 49,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'cycle' => 
          array (
            'name' => 'cycle',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\PerformanceReviewCycle',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 581,
            'endLine' => 581,
            'startColumn' => 52,
            'endColumn' => 80,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'payload' => 
          array (
            'name' => 'payload',
            'default' => NULL,
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
            'startLine' => 581,
            'endLine' => 581,
            'startColumn' => 83,
            'endColumn' => 96,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'App\\Models\\PerformanceReviewCycle',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  ReviewCyclePayloadUpdate  $payload
 */',
        'startLine' => 581,
        'endLine' => 644,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'normalizeGoalPayload' => 
      array (
        'name' => 'normalizeGoalPayload',
        'parameters' => 
        array (
          'actor' => 
          array (
            'name' => 'actor',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\User',
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
            'startColumn' => 43,
            'endColumn' => 53,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'payload' => 
          array (
            'name' => 'payload',
            'default' => NULL,
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
            'startLine' => 650,
            'endLine' => 650,
            'startColumn' => 56,
            'endColumn' => 69,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
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
        'docComment' => '/**
 * @param  array<string, mixed>  $payload
 * @return GoalPayloadData
 */',
        'startLine' => 650,
        'endLine' => 683,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'normalizeCompetencyPayload' => 
      array (
        'name' => 'normalizeCompetencyPayload',
        'parameters' => 
        array (
          'payload' => 
          array (
            'name' => 'payload',
            'default' => NULL,
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
            'startLine' => 689,
            'endLine' => 689,
            'startColumn' => 49,
            'endColumn' => 62,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
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
        'docComment' => '/**
 * @param  array<string, mixed>  $payload
 * @return CompetencyPayloadData
 */',
        'startLine' => 689,
        'endLine' => 699,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'normalizeReviewCyclePayload' => 
      array (
        'name' => 'normalizeReviewCyclePayload',
        'parameters' => 
        array (
          'actor' => 
          array (
            'name' => 'actor',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\User',
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
            'startColumn' => 50,
            'endColumn' => 60,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'payload' => 
          array (
            'name' => 'payload',
            'default' => NULL,
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
            'startLine' => 705,
            'endLine' => 705,
            'startColumn' => 63,
            'endColumn' => 76,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
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
        'docComment' => '/**
 * @param  array<string, mixed>  $payload
 * @return ReviewCyclePayloadData
 */',
        'startLine' => 705,
        'endLine' => 727,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'normalizeSuccessMetric' => 
      array (
        'name' => 'normalizeSuccessMetric',
        'parameters' => 
        array (
          'payload' => 
          array (
            'name' => 'payload',
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
                      'name' => 'array',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
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
            'startLine' => 733,
            'endLine' => 733,
            'startColumn' => 45,
            'endColumn' => 59,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
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
                  'name' => 'array',
                  'isIdentifier' => true,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  SuccessMetricPayload|null  $payload
 * @return SuccessMetricData|null
 */',
        'startLine' => 733,
        'endLine' => 745,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'normalizeScaleDefinition' => 
      array (
        'name' => 'normalizeScaleDefinition',
        'parameters' => 
        array (
          'payload' => 
          array (
            'name' => 'payload',
            'default' => NULL,
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
            'startLine' => 751,
            'endLine' => 751,
            'startColumn' => 47,
            'endColumn' => 60,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
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
        'docComment' => '/**
 * @param  ScaleDefinitionPayload  $payload
 * @return ScaleDefinition
 */',
        'startLine' => 751,
        'endLine' => 768,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'normalizeParticipantRules' => 
      array (
        'name' => 'normalizeParticipantRules',
        'parameters' => 
        array (
          'actor' => 
          array (
            'name' => 'actor',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\User',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 774,
            'endLine' => 774,
            'startColumn' => 48,
            'endColumn' => 58,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'payload' => 
          array (
            'name' => 'payload',
            'default' => NULL,
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
            'startLine' => 774,
            'endLine' => 774,
            'startColumn' => 61,
            'endColumn' => 74,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
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
        'docComment' => '/**
 * @param  ParticipantRulesPayload|array<string, mixed>  $payload
 * @return ParticipantRules
 */',
        'startLine' => 774,
        'endLine' => 804,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'normalizeReviewTemplate' => 
      array (
        'name' => 'normalizeReviewTemplate',
        'parameters' => 
        array (
          'payload' => 
          array (
            'name' => 'payload',
            'default' => NULL,
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
            'startLine' => 810,
            'endLine' => 810,
            'startColumn' => 46,
            'endColumn' => 59,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
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
        'docComment' => '/**
 * @param  ReviewTemplatePayload|array<string, mixed>  $payload
 * @return ReviewTemplate
 */',
        'startLine' => 810,
        'endLine' => 848,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'normalizeCompetencyVisibility' => 
      array (
        'name' => 'normalizeCompetencyVisibility',
        'parameters' => 
        array (
          'actor' => 
          array (
            'name' => 'actor',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\User',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 854,
            'endLine' => 854,
            'startColumn' => 52,
            'endColumn' => 62,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'payload' => 
          array (
            'name' => 'payload',
            'default' => NULL,
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
            'startLine' => 854,
            'endLine' => 854,
            'startColumn' => 65,
            'endColumn' => 78,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
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
        'docComment' => '/**
 * @param  CompetencyVisibilityPayload|array<string, mixed>  $payload
 * @return CompetencyVisibility
 */',
        'startLine' => 854,
        'endLine' => 866,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'ensureGoalCodeUnique' => 
      array (
        'name' => 'ensureGoalCodeUnique',
        'parameters' => 
        array (
          'companyId' => 
          array (
            'name' => 'companyId',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 868,
            'endLine' => 868,
            'startColumn' => 43,
            'endColumn' => 56,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'goalCode' => 
          array (
            'name' => 'goalCode',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 868,
            'endLine' => 868,
            'startColumn' => 59,
            'endColumn' => 74,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'ignoreGoalId' => 
          array (
            'name' => 'ignoreGoalId',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 868,
                'endLine' => 868,
                'startTokenPos' => 4626,
                'startFilePos' => 32380,
                'endTokenPos' => 4626,
                'endFilePos' => 32383,
              ),
            ),
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
                      'name' => 'int',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
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
            'startLine' => 868,
            'endLine' => 868,
            'startColumn' => 77,
            'endColumn' => 101,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 868,
        'endLine' => 881,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'ensureCompetencyCodeUnique' => 
      array (
        'name' => 'ensureCompetencyCodeUnique',
        'parameters' => 
        array (
          'companyId' => 
          array (
            'name' => 'companyId',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 883,
            'endLine' => 883,
            'startColumn' => 49,
            'endColumn' => 62,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'code' => 
          array (
            'name' => 'code',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 883,
            'endLine' => 883,
            'startColumn' => 65,
            'endColumn' => 76,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'ignoreCompetencyId' => 
          array (
            'name' => 'ignoreCompetencyId',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 883,
                'endLine' => 883,
                'startTokenPos' => 4752,
                'startFilePos' => 32978,
                'endTokenPos' => 4752,
                'endFilePos' => 32981,
              ),
            ),
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
                      'name' => 'int',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
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
            'startLine' => 883,
            'endLine' => 883,
            'startColumn' => 79,
            'endColumn' => 109,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 883,
        'endLine' => 896,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'ensureReviewCycleCodeUnique' => 
      array (
        'name' => 'ensureReviewCycleCodeUnique',
        'parameters' => 
        array (
          'companyId' => 
          array (
            'name' => 'companyId',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 898,
            'endLine' => 898,
            'startColumn' => 50,
            'endColumn' => 63,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'code' => 
          array (
            'name' => 'code',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 898,
            'endLine' => 898,
            'startColumn' => 66,
            'endColumn' => 77,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'ignoreReviewCycleId' => 
          array (
            'name' => 'ignoreReviewCycleId',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 898,
                'endLine' => 898,
                'startTokenPos' => 4878,
                'startFilePos' => 33576,
                'endTokenPos' => 4878,
                'endFilePos' => 33579,
              ),
            ),
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
                      'name' => 'int',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
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
            'startLine' => 898,
            'endLine' => 898,
            'startColumn' => 80,
            'endColumn' => 111,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 898,
        'endLine' => 911,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'ensureGoalWeightBudget' => 
      array (
        'name' => 'ensureGoalWeightBudget',
        'parameters' => 
        array (
          'actor' => 
          array (
            'name' => 'actor',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\User',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 914,
            'endLine' => 914,
            'startColumn' => 9,
            'endColumn' => 19,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'ownerEmployeeId' => 
          array (
            'name' => 'ownerEmployeeId',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 915,
            'endLine' => 915,
            'startColumn' => 9,
            'endColumn' => 28,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'reviewCycleId' => 
          array (
            'name' => 'reviewCycleId',
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
                      'name' => 'int',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
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
            'startLine' => 916,
            'endLine' => 916,
            'startColumn' => 9,
            'endColumn' => 27,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
          'weightPercent' => 
          array (
            'name' => 'weightPercent',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'float',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 917,
            'endLine' => 917,
            'startColumn' => 9,
            'endColumn' => 28,
            'parameterIndex' => 3,
            'isOptional' => false,
          ),
          'ignoreGoalId' => 
          array (
            'name' => 'ignoreGoalId',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 918,
                'endLine' => 918,
                'startTokenPos' => 5016,
                'startFilePos' => 34256,
                'endTokenPos' => 5016,
                'endFilePos' => 34259,
              ),
            ),
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
                      'name' => 'int',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
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
            'startLine' => 918,
            'endLine' => 918,
            'startColumn' => 9,
            'endColumn' => 33,
            'parameterIndex' => 4,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 913,
        'endLine' => 937,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'ensureEmployeeBelongsToCompany' => 
      array (
        'name' => 'ensureEmployeeBelongsToCompany',
        'parameters' => 
        array (
          'actor' => 
          array (
            'name' => 'actor',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\User',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 939,
            'endLine' => 939,
            'startColumn' => 53,
            'endColumn' => 63,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'employeeId' => 
          array (
            'name' => 'employeeId',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 939,
            'endLine' => 939,
            'startColumn' => 66,
            'endColumn' => 80,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 939,
        'endLine' => 947,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'ensureDepartmentBelongsToCompany' => 
      array (
        'name' => 'ensureDepartmentBelongsToCompany',
        'parameters' => 
        array (
          'actor' => 
          array (
            'name' => 'actor',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\User',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 949,
            'endLine' => 949,
            'startColumn' => 55,
            'endColumn' => 65,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'departmentId' => 
          array (
            'name' => 'departmentId',
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
                      'name' => 'int',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
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
            'startLine' => 949,
            'endLine' => 949,
            'startColumn' => 68,
            'endColumn' => 85,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 949,
        'endLine' => 961,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'ensureDepartmentIdsBelongToCompany' => 
      array (
        'name' => 'ensureDepartmentIdsBelongToCompany',
        'parameters' => 
        array (
          'actor' => 
          array (
            'name' => 'actor',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\User',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 966,
            'endLine' => 966,
            'startColumn' => 57,
            'endColumn' => 67,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'departmentIds' => 
          array (
            'name' => 'departmentIds',
            'default' => NULL,
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
            'startLine' => 966,
            'endLine' => 966,
            'startColumn' => 70,
            'endColumn' => 89,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  list<int>  $departmentIds
 */',
        'startLine' => 966,
        'endLine' => 982,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'ensureDesignationIdsBelongToCompany' => 
      array (
        'name' => 'ensureDesignationIdsBelongToCompany',
        'parameters' => 
        array (
          'actor' => 
          array (
            'name' => 'actor',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\User',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 987,
            'endLine' => 987,
            'startColumn' => 58,
            'endColumn' => 68,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'designationIds' => 
          array (
            'name' => 'designationIds',
            'default' => NULL,
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
            'startLine' => 987,
            'endLine' => 987,
            'startColumn' => 71,
            'endColumn' => 91,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  list<int>  $designationIds
 */',
        'startLine' => 987,
        'endLine' => 1003,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'ensureCompetencyIdsBelongToCompany' => 
      array (
        'name' => 'ensureCompetencyIdsBelongToCompany',
        'parameters' => 
        array (
          'actor' => 
          array (
            'name' => 'actor',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'App\\Models\\User',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1008,
            'endLine' => 1008,
            'startColumn' => 57,
            'endColumn' => 67,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'competencyIds' => 
          array (
            'name' => 'competencyIds',
            'default' => NULL,
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
            'startLine' => 1008,
            'endLine' => 1008,
            'startColumn' => 70,
            'endColumn' => 89,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  list<int>  $competencyIds
 */',
        'startLine' => 1008,
        'endLine' => 1024,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'normalizeIntegerArray' => 
      array (
        'name' => 'normalizeIntegerArray',
        'parameters' => 
        array (
          'values' => 
          array (
            'name' => 'values',
            'default' => NULL,
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
            'startLine' => 1030,
            'endLine' => 1030,
            'startColumn' => 44,
            'endColumn' => 56,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
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
        'docComment' => '/**
 * @param  array<int, mixed>  $values
 * @return array<int, int>
 */',
        'startLine' => 1030,
        'endLine' => 1039,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'aliasName' => NULL,
      ),
      'normalizeStringArray' => 
      array (
        'name' => 'normalizeStringArray',
        'parameters' => 
        array (
          'values' => 
          array (
            'name' => 'values',
            'default' => NULL,
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
            'startLine' => 1045,
            'endLine' => 1045,
            'startColumn' => 43,
            'endColumn' => 55,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
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
        'docComment' => '/**
 * @param  array<int, mixed>  $values
 * @return array<int, string>
 */',
        'startLine' => 1045,
        'endLine' => 1054,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Modules\\PerformanceManagement\\Services',
        'declaringClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'implementingClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
        'currentClassName' => 'App\\Modules\\PerformanceManagement\\Services\\PerformanceConfigurationService',
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