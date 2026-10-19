<?php

return [
    'connection' => env('AION_REGISTRATION_CONNECTION', env('AION_DB_CONNECTION', 'aion_game')),
    'otp_flag' => (int) env('AION_REGISTRATION_OTP_FLAG', 3),
    'password_flag' => (int) env('AION_REGISTRATION_PASSWORD_FLAG', 0),
];
