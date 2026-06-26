<?php declare(strict_types = 1);

// odsl-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/PerformanceManagement/Services
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v1-enums',
   'data' => 
  array (
    '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/PerformanceManagement/Services/PerformanceAccessScopeService.php' => 
    array (
      0 => '8678965fc256046a5b7de386a65d4b123584b032ae4cd6197b88d15b22621146',
      1 => 
      array (
        0 => 'app\\modules\\performancemanagement\\services\\performanceaccessscopeservice',
      ),
      2 => 
      array (
        0 => 'app\\modules\\performancemanagement\\services\\goalsquery',
        1 => 'app\\modules\\performancemanagement\\services\\competenciesquery',
        2 => 'app\\modules\\performancemanagement\\services\\reviewcyclesquery',
        3 => 'app\\modules\\performancemanagement\\services\\reviewsquery',
        4 => 'app\\modules\\performancemanagement\\services\\resolveaccessiblegoal',
        5 => 'app\\modules\\performancemanagement\\services\\resolveaccessiblecompetency',
        6 => 'app\\modules\\performancemanagement\\services\\resolveaccessiblereviewcycle',
        7 => 'app\\modules\\performancemanagement\\services\\resolveaccessiblereview',
        8 => 'app\\modules\\performancemanagement\\services\\determineactorrole',
        9 => 'app\\modules\\performancemanagement\\services\\canviewsubmission',
        10 => 'app\\modules\\performancemanagement\\services\\shouldanonymizesubmissionforactor',
        11 => 'app\\modules\\performancemanagement\\services\\ensurecompanymatch',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/PerformanceManagement/Services/PerformanceConfigurationService.php' => 
    array (
      0 => 'c276075e101fc01c5f902e178035fc16f68e0542f31e5fe85a15c24c9245edc0',
      1 => 
      array (
        0 => 'app\\modules\\performancemanagement\\services\\performanceconfigurationservice',
      ),
      2 => 
      array (
        0 => 'app\\modules\\performancemanagement\\services\\__construct',
        1 => 'app\\modules\\performancemanagement\\services\\searchgoals',
        2 => 'app\\modules\\performancemanagement\\services\\searchcompetencies',
        3 => 'app\\modules\\performancemanagement\\services\\searchreviewcycles',
        4 => 'app\\modules\\performancemanagement\\services\\findgoalforview',
        5 => 'app\\modules\\performancemanagement\\services\\findcompetencyforview',
        6 => 'app\\modules\\performancemanagement\\services\\findreviewcycleforview',
        7 => 'app\\modules\\performancemanagement\\services\\creategoal',
        8 => 'app\\modules\\performancemanagement\\services\\updategoal',
        9 => 'app\\modules\\performancemanagement\\services\\createcompetency',
        10 => 'app\\modules\\performancemanagement\\services\\updatecompetency',
        11 => 'app\\modules\\performancemanagement\\services\\createreviewcycle',
        12 => 'app\\modules\\performancemanagement\\services\\updatereviewcycle',
        13 => 'app\\modules\\performancemanagement\\services\\normalizegoalpayload',
        14 => 'app\\modules\\performancemanagement\\services\\normalizecompetencypayload',
        15 => 'app\\modules\\performancemanagement\\services\\normalizereviewcyclepayload',
        16 => 'app\\modules\\performancemanagement\\services\\normalizesuccessmetric',
        17 => 'app\\modules\\performancemanagement\\services\\normalizescaledefinition',
        18 => 'app\\modules\\performancemanagement\\services\\normalizeparticipantrules',
        19 => 'app\\modules\\performancemanagement\\services\\normalizereviewtemplate',
        20 => 'app\\modules\\performancemanagement\\services\\normalizecompetencyvisibility',
        21 => 'app\\modules\\performancemanagement\\services\\ensuregoalcodeunique',
        22 => 'app\\modules\\performancemanagement\\services\\ensurecompetencycodeunique',
        23 => 'app\\modules\\performancemanagement\\services\\ensurereviewcyclecodeunique',
        24 => 'app\\modules\\performancemanagement\\services\\ensuregoalweightbudget',
        25 => 'app\\modules\\performancemanagement\\services\\ensureemployeebelongstocompany',
        26 => 'app\\modules\\performancemanagement\\services\\ensuredepartmentbelongstocompany',
        27 => 'app\\modules\\performancemanagement\\services\\ensuredepartmentidsbelongtocompany',
        28 => 'app\\modules\\performancemanagement\\services\\ensuredesignationidsbelongtocompany',
        29 => 'app\\modules\\performancemanagement\\services\\ensurecompetencyidsbelongtocompany',
        30 => 'app\\modules\\performancemanagement\\services\\normalizeintegerarray',
        31 => 'app\\modules\\performancemanagement\\services\\normalizestringarray',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/PerformanceManagement/Services/PerformanceReviewExecutionService.php' => 
    array (
      0 => '542d820b63872803ecf2434182aa20060a66eaeffa54e3366432fb2713e87a60',
      1 => 
      array (
        0 => 'app\\modules\\performancemanagement\\services\\performancereviewexecutionservice',
      ),
      2 => 
      array (
        0 => 'app\\modules\\performancemanagement\\services\\__construct',
        1 => 'app\\modules\\performancemanagement\\services\\searchreviews',
        2 => 'app\\modules\\performancemanagement\\services\\findforview',
        3 => 'app\\modules\\performancemanagement\\services\\createreview',
        4 => 'app\\modules\\performancemanagement\\services\\updatereview',
        5 => 'app\\modules\\performancemanagement\\services\\submitreview',
        6 => 'app\\modules\\performancemanagement\\services\\calibratereview',
        7 => 'app\\modules\\performancemanagement\\services\\finalizereview',
        8 => 'app\\modules\\performancemanagement\\services\\publishreview',
        9 => 'app\\modules\\performancemanagement\\services\\reopenreview',
        10 => 'app\\modules\\performancemanagement\\services\\normalizerevieweruserids',
        11 => 'app\\modules\\performancemanagement\\services\\mergevisibilityrules',
        12 => 'app\\modules\\performancemanagement\\services\\buildgoalsnapshot',
        13 => 'app\\modules\\performancemanagement\\services\\buildcompetencysnapshot',
        14 => 'app\\modules\\performancemanagement\\services\\initialstatusforreview',
        15 => 'app\\modules\\performancemanagement\\services\\ensureemployeematchescyclepopulation',
        16 => 'app\\modules\\performancemanagement\\services\\resolveemployeeforcompany',
        17 => 'app\\modules\\performancemanagement\\services\\normalizesubmissionpayload',
        18 => 'app\\modules\\performancemanagement\\services\\normalizecalibrationpayload',
        19 => 'app\\modules\\performancemanagement\\services\\normalizefinalpayload',
        20 => 'app\\modules\\performancemanagement\\services\\ensuresubmissionwindowisopen',
        21 => 'app\\modules\\performancemanagement\\services\\visibilityscopeforrole',
        22 => 'app\\modules\\performancemanagement\\services\\marksubmissiontimestamp',
        23 => 'app\\modules\\performancemanagement\\services\\recomputereviewstatus',
        24 => 'app\\modules\\performancemanagement\\services\\ensurereviewinputscomplete',
        25 => 'app\\modules\\performancemanagement\\services\\ensurecanadministerreview',
        26 => 'app\\modules\\performancemanagement\\services\\ensurecanmanagereview',
        27 => 'app\\modules\\performancemanagement\\services\\ensureratingwithinscale',
      ),
      3 => 
      array (
      ),
    ),
  ),
));