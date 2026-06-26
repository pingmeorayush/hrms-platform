<?php declare(strict_types = 1);

// odsl-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/AttendanceManagement/Services
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v1-enums',
   'data' => 
  array (
    '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/AttendanceManagement/Services/AttendanceAccessScopeService.php' => 
    array (
      0 => '4d40768fbc1f0c1dd4ad04b6bae27e159c330ef22602d2339926bfc72fbba5a6',
      1 => 
      array (
        0 => 'app\\modules\\attendancemanagement\\services\\attendanceaccessscopeservice',
      ),
      2 => 
      array (
        0 => 'app\\modules\\attendancemanagement\\services\\attendancerecordsquery',
        1 => 'app\\modules\\attendancemanagement\\services\\attendancecorrectionsquery',
        2 => 'app\\modules\\attendancemanagement\\services\\canviewalltenantattendance',
        3 => 'app\\modules\\attendancemanagement\\services\\findlinkedemployee',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/AttendanceManagement/Services/AttendanceCalculationService.php' => 
    array (
      0 => '00f7a0528b575104ed95bd769be30e9e47c795ef90c4dc3c424df941374007bd',
      1 => 
      array (
        0 => 'app\\modules\\attendancemanagement\\services\\attendancecalculationservice',
      ),
      2 => 
      array (
        0 => 'app\\modules\\attendancemanagement\\services\\__construct',
        1 => 'app\\modules\\attendancemanagement\\services\\calculaterecord',
        2 => 'app\\modules\\attendancemanagement\\services\\resolveapprovedleavefordate',
        3 => 'app\\modules\\attendancemanagement\\services\\recalculate',
        4 => 'app\\modules\\attendancemanagement\\services\\employeeexistsondate',
        5 => 'app\\modules\\attendancemanagement\\services\\nextdate',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/AttendanceManagement/Services/AttendanceConfigurationService.php' => 
    array (
      0 => '14fad27785b67a1301efd46e4c6b9f0f763285172a7a5ec1023fe4fe5f8a7a31',
      1 => 
      array (
        0 => 'app\\modules\\attendancemanagement\\services\\attendanceconfigurationservice',
      ),
      2 => 
      array (
        0 => 'app\\modules\\attendancemanagement\\services\\__construct',
        1 => 'app\\modules\\attendancemanagement\\services\\getorcreatepolicy',
        2 => 'app\\modules\\attendancemanagement\\services\\updatepolicy',
        3 => 'app\\modules\\attendancemanagement\\services\\createholidaycalendar',
        4 => 'app\\modules\\attendancemanagement\\services\\updateholidaycalendar',
        5 => 'app\\modules\\attendancemanagement\\services\\createholiday',
        6 => 'app\\modules\\attendancemanagement\\services\\updateholiday',
        7 => 'app\\modules\\attendancemanagement\\services\\normalizepolicypayload',
        8 => 'app\\modules\\attendancemanagement\\services\\normalizeholidaycalendarpayload',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/AttendanceManagement/Services/AttendanceContextResolver.php' => 
    array (
      0 => 'b518f1b9851af2477de15790dbe746891b39d583294854c89d08aec1f11fce17',
      1 => 
      array (
        0 => 'app\\modules\\attendancemanagement\\services\\attendancecontextresolver',
      ),
      2 => 
      array (
        0 => 'app\\modules\\attendancemanagement\\services\\resolveschedulefordate',
        1 => 'app\\modules\\attendancemanagement\\services\\resolveholidayfordate',
        2 => 'app\\modules\\attendancemanagement\\services\\holidaycalendarspecificity',
        3 => 'app\\modules\\attendancemanagement\\services\\buildschedulepayload',
        4 => 'app\\modules\\attendancemanagement\\services\\nextdate',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/AttendanceManagement/Services/AttendanceCorrectionService.php' => 
    array (
      0 => 'e080a4e43298165534c643f0f820972a1117e12fc9ca19241a59e6fdaa92d435',
      1 => 
      array (
        0 => 'app\\modules\\attendancemanagement\\services\\attendancecorrectionservice',
      ),
      2 => 
      array (
        0 => 'app\\modules\\attendancemanagement\\services\\__construct',
        1 => 'app\\modules\\attendancemanagement\\services\\search',
        2 => 'app\\modules\\attendancemanagement\\services\\create',
        3 => 'app\\modules\\attendancemanagement\\services\\decide',
        4 => 'app\\modules\\attendancemanagement\\services\\resolverecordforcorrection',
        5 => 'app\\modules\\attendancemanagement\\services\\normalizecorrectedvalues',
        6 => 'app\\modules\\attendancemanagement\\services\\ensureworkflowdefinition',
        7 => 'app\\modules\\attendancemanagement\\services\\parsetimestamp',
        8 => 'app\\modules\\attendancemanagement\\services\\buildrecordsnapshot',
        9 => 'app\\modules\\attendancemanagement\\services\\loadcorrection',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/AttendanceManagement/Services/AttendanceOperationalReviewService.php' => 
    array (
      0 => 'd3731fb5d204571e9f712becdd4dd584878e957e85fc3777812b84876e03c3ed',
      1 => 
      array (
        0 => 'app\\modules\\attendancemanagement\\services\\attendanceoperationalreviewservice',
      ),
      2 => 
      array (
        0 => 'app\\modules\\attendancemanagement\\services\\__construct',
        1 => 'app\\modules\\attendancemanagement\\services\\overview',
        2 => 'app\\modules\\attendancemanagement\\services\\pendingexceptions',
        3 => 'app\\modules\\attendancemanagement\\services\\resolvewindowdate',
        4 => 'app\\modules\\attendancemanagement\\services\\recordsforwindow',
        5 => 'app\\modules\\attendancemanagement\\services\\isexceptionrecord',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/AttendanceManagement/Services/AttendanceRecordService.php' => 
    array (
      0 => '58af0c1957bc01aa65c6d8998c01afded48e0b19e05cef7141604d86c926fb87',
      1 => 
      array (
        0 => 'app\\modules\\attendancemanagement\\services\\attendancerecordservice',
      ),
      2 => 
      array (
        0 => 'app\\modules\\attendancemanagement\\services\\__construct',
        1 => 'app\\modules\\attendancemanagement\\services\\search',
        2 => 'app\\modules\\attendancemanagement\\services\\findforview',
        3 => 'app\\modules\\attendancemanagement\\services\\checkin',
        4 => 'app\\modules\\attendancemanagement\\services\\checkout',
        5 => 'app\\modules\\attendancemanagement\\services\\resolvelinkedemployee',
        6 => 'app\\modules\\attendancemanagement\\services\\ensureemployeecancaptureattendance',
        7 => 'app\\modules\\attendancemanagement\\services\\ensurecheckinisallowed',
        8 => 'app\\modules\\attendancemanagement\\services\\resolvecapturedat',
        9 => 'app\\modules\\attendancemanagement\\services\\parsecapturedat',
        10 => 'app\\modules\\attendancemanagement\\services\\extractcapturemetadata',
        11 => 'app\\modules\\attendancemanagement\\services\\nextdate',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/AttendanceManagement/Services/AttendanceSchedulingService.php' => 
    array (
      0 => '551536d7feebc78ac34e72a225c264a6634e543ff292ddf11bede336d17fef25',
      1 => 
      array (
        0 => 'app\\modules\\attendancemanagement\\services\\attendanceschedulingservice',
      ),
      2 => 
      array (
        0 => 'app\\modules\\attendancemanagement\\services\\__construct',
        1 => 'app\\modules\\attendancemanagement\\services\\createshift',
        2 => 'app\\modules\\attendancemanagement\\services\\updateshift',
        3 => 'app\\modules\\attendancemanagement\\services\\createshiftassignment',
        4 => 'app\\modules\\attendancemanagement\\services\\updateshiftassignment',
        5 => 'app\\modules\\attendancemanagement\\services\\createrosters',
        6 => 'app\\modules\\attendancemanagement\\services\\updateroster',
        7 => 'app\\modules\\attendancemanagement\\services\\normalizeshiftpayload',
        8 => 'app\\modules\\attendancemanagement\\services\\ensureassignmentdoesnotoverlap',
        9 => 'app\\modules\\attendancemanagement\\services\\ensurerosterentriesdonotconflict',
        10 => 'app\\modules\\attendancemanagement\\services\\ensuresinglerosterdoesnotconflict',
        11 => 'app\\modules\\attendancemanagement\\services\\scopecolumn',
        12 => 'app\\modules\\attendancemanagement\\services\\nextdate',
      ),
      3 => 
      array (
      ),
    ),
  ),
));