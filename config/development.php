<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Error handler
$settings['error'] = [
    // Should be set to false in production
    'display_error_details' => true,
    // Should be set to false for unit tests
    'log_errors' => true,
    // Display error details in error log
    'log_error_details' => true,
];

$settings['redis_conf'] = [
    'dsn' => 'tcp://redis:6380',
];

