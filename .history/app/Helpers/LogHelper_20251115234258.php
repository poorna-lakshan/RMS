<?php

namespace App\Helpers;

use App\Models\UserLog;
use Browser;

class LogHelper
{
    public static function log($user, $action, $module, $details = null)
    {
        UserLog::create([
            'user'       => $user,
            'action'     => $action,
            'module'     => $module,
            'details'    => $details,

            // Client Environment
            'ip'         => request()->ip(),
            'device'     => Browser::deviceType(),       // mobile / desktop / tablet
            'browser'    => Browser::browserName(),      // Chrome, Firefox
            'os'         => Browser::platformName(),     // Windows, Android, iOS
            'user_agent' => request()->header('User-Agent'),
        ]);
    }
}
