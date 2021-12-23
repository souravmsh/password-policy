<?php

return [
    'enable'         => env('PASSWORD_POLICY_EANBLE', true),
    'cache_minutes'  => env('PASSWORD_POLICY_CACHE_TIME', 720),
    'check_old_password'  => env('PASSWORD_POLICY_CHECK_OLD_PASSWORD', true),
];