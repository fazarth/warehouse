<?php

return [
    // License type from .env
    'type' => env('LICENSE_TYPE', 'full'),
    
    // Optional license key for validation
    'key' => env('LICENSE_KEY'),
    
    // Optional expiry date
    'expires' => env('LICENSE_EXPIRES'),
];
