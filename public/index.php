<?php


// Error reporting
ini_set('display_errors', '1');
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//require __DIR__ . '/../config/bootstrap.php';
//
//$app->run();


(require __DIR__ . '/../config/bootstrap.php')->run();