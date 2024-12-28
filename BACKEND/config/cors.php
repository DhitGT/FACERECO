<?php
return [

    /*
    |----------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |----------------------------------------------------------------------
    |
    | This configuration determines the cross-origin operations that can be
    | executed in web browsers. It has been set to allow all origins,
    | methods, and headers for simplicity.
    |
    */

    'paths' => ['*'], // Ensure this includes all necessary paths

    'allowed_methods' => ['*'], // Allow all HTTP methods (GET, POST, PUT, DELETE, etc.)

    'allowed_origins' => ['*'], // Allow requests from all origins

    'allowed_origins_patterns' => [], // You can use regex here if needed

    'allowed_headers' => ['*'], // Allow all headers (Authorization, Content-Type, etc.)

    'exposed_headers' => [], // No exposed headers, can be customized if needed

    'max_age' => 0, // No caching of the CORS response

    'supports_credentials' => false, // Don't allow credentials (cookies, HTTP authentication)

];
