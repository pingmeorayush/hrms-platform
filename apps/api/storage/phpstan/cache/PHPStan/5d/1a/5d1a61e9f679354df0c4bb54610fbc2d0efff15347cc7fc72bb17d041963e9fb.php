<?php declare(strict_types = 1);

// odsl-/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/Platform/Auth
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v1-enums',
   'data' => 
  array (
    '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/Platform/Auth/Controllers/AuthController.php' => 
    array (
      0 => '630e2734f812a1506063f3cf9289b18ad88c1b0c581107520c0ae3208969b4cb',
      1 => 
      array (
        0 => 'app\\modules\\platform\\auth\\controllers\\authcontroller',
      ),
      2 => 
      array (
        0 => 'app\\modules\\platform\\auth\\controllers\\__construct',
        1 => 'app\\modules\\platform\\auth\\controllers\\login',
        2 => 'app\\modules\\platform\\auth\\controllers\\verifymfa',
        3 => 'app\\modules\\platform\\auth\\controllers\\forgotpassword',
        4 => 'app\\modules\\platform\\auth\\controllers\\resetpassword',
        5 => 'app\\modules\\platform\\auth\\controllers\\logout',
        6 => 'app\\modules\\platform\\auth\\controllers\\me',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/Platform/Auth/Requests/ForgotPasswordRequest.php' => 
    array (
      0 => '2f8f3eb735027817151aa082c06663fb4567ebeae61e7e97c6d60c6f4792b973',
      1 => 
      array (
        0 => 'app\\modules\\platform\\auth\\requests\\forgotpasswordrequest',
      ),
      2 => 
      array (
        0 => 'app\\modules\\platform\\auth\\requests\\authorize',
        1 => 'app\\modules\\platform\\auth\\requests\\rules',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/Platform/Auth/Requests/LoginRequest.php' => 
    array (
      0 => '3cf68a580705ce2021de7201670562b203799afece3cff126b6cc3d6abbe2500',
      1 => 
      array (
        0 => 'app\\modules\\platform\\auth\\requests\\loginrequest',
      ),
      2 => 
      array (
        0 => 'app\\modules\\platform\\auth\\requests\\authorize',
        1 => 'app\\modules\\platform\\auth\\requests\\rules',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/Platform/Auth/Requests/ResetPasswordRequest.php' => 
    array (
      0 => '9707d877a21d018adfe3b135fe872beba02615eaa6b9ee0afa42bf4787572236',
      1 => 
      array (
        0 => 'app\\modules\\platform\\auth\\requests\\resetpasswordrequest',
      ),
      2 => 
      array (
        0 => 'app\\modules\\platform\\auth\\requests\\authorize',
        1 => 'app\\modules\\platform\\auth\\requests\\rules',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/Platform/Auth/Requests/VerifyMfaRequest.php' => 
    array (
      0 => '1c7ea1e12060a5243010a46f72270c275ae654f05d700de311e4bb734ab02623',
      1 => 
      array (
        0 => 'app\\modules\\platform\\auth\\requests\\verifymfarequest',
      ),
      2 => 
      array (
        0 => 'app\\modules\\platform\\auth\\requests\\authorize',
        1 => 'app\\modules\\platform\\auth\\requests\\rules',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/Platform/Auth/Services/AuthService.php' => 
    array (
      0 => 'b95b42da299a0950a27bab149d9b959d92a128794e310615014da8e09f3e62d7',
      1 => 
      array (
        0 => 'app\\modules\\platform\\auth\\services\\authservice',
      ),
      2 => 
      array (
        0 => 'app\\modules\\platform\\auth\\services\\__construct',
        1 => 'app\\modules\\platform\\auth\\services\\login',
        2 => 'app\\modules\\platform\\auth\\services\\verifymfa',
        3 => 'app\\modules\\platform\\auth\\services\\logout',
        4 => 'app\\modules\\platform\\auth\\services\\sendpasswordresetlink',
        5 => 'app\\modules\\platform\\auth\\services\\resetpassword',
        6 => 'app\\modules\\platform\\auth\\services\\completeauthentication',
        7 => 'app\\modules\\platform\\auth\\services\\guardagainstlockedaccount',
        8 => 'app\\modules\\platform\\auth\\services\\guardagainstinactivetenant',
        9 => 'app\\modules\\platform\\auth\\services\\recordfailedattempt',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/api/app/Modules/Platform/Auth/Services/MfaService.php' => 
    array (
      0 => '2e290e4fd13883054ce21264820d41c13ef36d43e295c468542fa57f66a4bce0',
      1 => 
      array (
        0 => 'app\\modules\\platform\\auth\\services\\mfaservice',
      ),
      2 => 
      array (
        0 => 'app\\modules\\platform\\auth\\services\\issueemailchallenge',
        1 => 'app\\modules\\platform\\auth\\services\\verify',
        2 => 'app\\modules\\platform\\auth\\services\\methodfor',
        3 => 'app\\modules\\platform\\auth\\services\\verifytotp',
        4 => 'app\\modules\\platform\\auth\\services\\generatetotp',
        5 => 'app\\modules\\platform\\auth\\services\\decodebase32',
      ),
      3 => 
      array (
      ),
    ),
  ),
));