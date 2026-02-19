<?php
/**
 * Admin Console Database Configuration - EXAMPLE
 * 
 * Copy this file to config.php and update with your actual credentials
 */

return [
    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'synalyzer_console',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],

    // IMPORTANT: Generate a strong secret key and keep it synchronized
    // Use: openssl rand -base64 32
    'secret_key' => 'CHANGE_THIS_TO_A_STRONG_SECRET_KEY_32_CHARS_MIN',

    // API settings
    'api' => [
        'timezone' => 'Asia/Kolkata', // or 'Asia/Singapore'
        'max_request_age' => 300, // 5 minutes
        'enable_logging' => true,
        'log_file' => __DIR__ . '/../logs/api.log',
    ],

    // Security settings
    'security' => [
        // Whitelist specific IPs (empty array = allow all)
        'allowed_ips' => [
            // '192.168.1.100',
            // '192.168.1.101',
        ],

        'rate_limit' => [
            'enabled' => true,
            'max_requests' => 100,
            'time_window' => 3600, // 1 hour
        ],
    ],
];
