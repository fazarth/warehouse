<?php

return [
    // License type from .env
    'type' => env('LICENSE_TYPE', 'basic'),
    
    // Optional license key for validation
    'key' => env('LICENSE_KEY'),
    
    // Optional expiry date
    'expires' => env('LICENSE_EXPIRES'),
];
