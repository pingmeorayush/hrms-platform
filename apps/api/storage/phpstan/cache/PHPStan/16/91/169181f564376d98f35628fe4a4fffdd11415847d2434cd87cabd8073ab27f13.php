<?php declare(strict_types = 1);

// odsl-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/LeaveManagement/Services
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v1-enums',
   'data' => 
  array (
    '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/LeaveManagement/Services/LeaveAccrualService.php' => 
    array (
      0 => 'd95462dee3f5211fcb681e3affccd7af252d8f166e67f07bf4292dde428b011c',
      1 => 
      array (
        0 => 'app\\modules\\leavemanagement\\services\\leaveaccrualservice',
      ),
      2 => 
      array (
        0 => 'app\\modules\\leavemanagement\\services\\__construct',
        1 => 'app\\modules\\leavemanagement\\services\\previewaccrual',
        2 => 'app\\modules\\leavemanagement\\services\\resolvecycle',
        3 => 'app\\modules\\leavemanagement\\services\\resolveeligibility',
        4 => 'app\\modules\\leavemanagement\\services\\resolveaccrueddays',
        5 => 'app\\modules\\leavemanagement\\services\\syncprojectedencashment',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/LeaveManagement/Services/LeaveBalanceAccessScopeService.php' => 
    array (
      0 => 'd521ad5790b118485197ffe12db9df8acf7823fe6011720c5f62910075c0230b',
      1 => 
      array (
        0 => 'app\\modules\\leavemanagement\\services\\leavebalanceaccessscopeservice',
      ),
      2 => 
      array (
        0 => 'app\\modules\\leavemanagement\\services\\balancesquery',
        1 => 'app\\modules\\leavemanagement\\services\\resolveaccessibleemployee',
        2 => 'app\\modules\\leavemanagement\\services\\canviewalltenantbalances',
        3 => 'app\\modules\\leavemanagement\\services\\findlinkedemployee',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/LeaveManagement/Services/LeaveBalanceService.php' => 
    array (
      0 => '1e10beb0b9275aeb78eff268d380ce93fdd6619f60432fc35f95ea5c2659defb',
      1 => 
      array (
        0 => 'app\\modules\\leavemanagement\\services\\leavebalanceservice',
      ),
      2 => 
      array (
        0 => 'app\\modules\\leavemanagement\\services\\__construct',
        1 => 'app\\modules\\leavemanagement\\services\\syncprojectedbalance',
        2 => 'app\\modules\\leavemanagement\\services\\reserveforleaverequest',
        3 => 'app\\modules\\leavemanagement\\services\\releaseleaverequestreservation',
        4 => 'app\\modules\\leavemanagement\\services\\listbalances',
        5 => 'app\\modules\\leavemanagement\\services\\showemployeebalances',
        6 => 'app\\modules\\leavemanagement\\services\\removesupersededentries',
        7 => 'app\\modules\\leavemanagement\\services\\syncaccrualentries',
        8 => 'app\\modules\\leavemanagement\\services\\recalculatebalancesnapshot',
        9 => 'app\\modules\\leavemanagement\\services\\entrypriority',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/LeaveManagement/Services/LeaveConfigurationService.php' => 
    array (
      0 => 'e7074d45e61beebf1f7ff95ff4ff491f0f576e21304bb21959222fe97246b62e',
      1 => 
      array (
        0 => 'app\\modules\\leavemanagement\\services\\leaveconfigurationservice',
      ),
      2 => 
      array (
        0 => 'app\\modules\\leavemanagement\\services\\__construct',
        1 => 'app\\modules\\leavemanagement\\services\\createleavetype',
        2 => 'app\\modules\\leavemanagement\\services\\updateleavetype',
        3 => 'app\\modules\\leavemanagement\\services\\createleavepolicy',
        4 => 'app\\modules\\leavemanagement\\services\\updateleavepolicy',
        5 => 'app\\modules\\leavemanagement\\services\\normalizeleavetypepayload',
        6 => 'app\\modules\\leavemanagement\\services\\normalizepolicypayload',
        7 => 'app\\modules\\leavemanagement\\services\\normalizeeligibilityrule',
        8 => 'app\\modules\\leavemanagement\\services\\normalizestringarray',
        9 => 'app\\modules\\leavemanagement\\services\\makepolicyscopekey',
        10 => 'app\\modules\\leavemanagement\\services\\ensurepolicyscopeisunique',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/LeaveManagement/Services/LeaveRequestAccessScopeService.php' => 
    array (
      0 => 'b0cd3bc20f254fd6e603fe58eacef60be3ffcff01b6dbcafc6c44a0b79b9e6bb',
      1 => 
      array (
        0 => 'app\\modules\\leavemanagement\\services\\leaverequestaccessscopeservice',
      ),
      2 => 
      array (
        0 => 'app\\modules\\leavemanagement\\services\\requestsquery',
        1 => 'app\\modules\\leavemanagement\\services\\resolveaccessiblerequest',
        2 => 'app\\modules\\leavemanagement\\services\\resolvelinkedemployee',
        3 => 'app\\modules\\leavemanagement\\services\\canviewalltenantrequests',
        4 => 'app\\modules\\leavemanagement\\services\\findlinkedemployee',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/LeaveManagement/Services/LeaveRequestService.php' => 
    array (
      0 => 'c080a1d413c2ea099591222353ddd5d54b351cdad71528dc6066383e153b5ec7',
      1 => 
      array (
        0 => 'app\\modules\\leavemanagement\\services\\leaverequestservice',
      ),
      2 => 
      array (
        0 => 'app\\modules\\leavemanagement\\services\\__construct',
        1 => 'app\\modules\\leavemanagement\\services\\search',
        2 => 'app\\modules\\leavemanagement\\services\\findforview',
        3 => 'app\\modules\\leavemanagement\\services\\submit',
        4 => 'app\\modules\\leavemanagement\\services\\update',
        5 => 'app\\modules\\leavemanagement\\services\\cancel',
        6 => 'app\\modules\\leavemanagement\\services\\ensureemployeecanrequestleave',
        7 => 'app\\modules\\leavemanagement\\services\\ensuredatesarepolicycompliant',
        8 => 'app\\modules\\leavemanagement\\services\\ensurenooverlap',
        9 => 'app\\modules\\leavemanagement\\services\\resolvepolicy',
        10 => 'app\\modules\\leavemanagement\\services\\syncattendanceforapprovedrequest',
        11 => 'app\\modules\\leavemanagement\\services\\syncattendanceforcancelledapprovedrequest',
        12 => 'app\\modules\\leavemanagement\\services\\nextdate',
      ),
      3 => 
      array (
      ),
    ),
  ),
));