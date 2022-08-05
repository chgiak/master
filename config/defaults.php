<?php

// Configure defaults for the whole application.

// Error reporting
ini_set('display_errors', '1');
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Timezone
date_default_timezone_set('Europe/Athens');

// Settings
$settings = [];

// Path settings
$settings['root'] = dirname(__DIR__);
$settings['temp'] = $settings['root'] . '/tmp';
$settings['public'] = $settings['root'] . '/public';

// Error handler
$settings['error'] = [
    // Should be set to false in production
    'display_error_details' => true,
    // Should be set to false for unit tests
    'log_errors' => true,
    // Display error details in error log
    'log_error_details' => true,
];

// Logger settings
//$settings['logger'] = [
//    'name' => 'app',
//    'path' => $settings['root'] . '/logs',
//    'filename' => 'app.log',
//    'level' => \Monolog\Logger::DEBUG,
//    'file_permission' => 0775,
//];


$settings['redis_conf'] = [
    'dsn' => 'redis://redis:6379',
];

$settings['limiter'] = [
    'requests_per_minute' => 10,
];

return $settings;
