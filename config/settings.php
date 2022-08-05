<?php

// Load default settings
$settings = require __DIR__ . '/defaults.php';



// Overwrite default settings with environment specific local settings
if (file_exists(__DIR__ . '/../.env.php')) {
    $env_settings = require __DIR__ . '/../.env.php';
    $settings = array_merge($settings, $env_settings);
}

// Unit-test and integration environment (Travis CI)
if (defined('APP_ENV')) {
    $env_settings = require __DIR__ . '/' . basename(APP_ENV) . '.php';
    $settings = array_merge($settings, $env_settings);
}

// Unit-test and integration environment (Travis CI)
if (getenv('APP_ENV')) {
    $env_settings = require __DIR__ . '/' . basename(getenv('APP_ENV')) . '.php';
    $settings = array_merge($settings, $env_settings);
}

return $settings;
