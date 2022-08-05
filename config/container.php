<?php

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
//use Psr\Http\Message\ResponseFactoryInterface;
//use Slim\Middleware\ErrorMiddleware;
use Slim\App;
use Slim\Factory\AppFactory;
use Predis\Client as Redis;

return [
    // Application settings
    'settings' => function () {
        if (file_exists(__DIR__ . '/../.env')) {
            $dotenv = Dotenv\Dotenv::createMutable(__DIR__ . '/../');
            $dotenv->load();
        }

        $var =  require __DIR__ . '/settings.php';
        return $var;
    },

    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);
        return AppFactory::create();
    },

    // For the responder
//    ResponseFactoryInterface::class => function (ContainerInterface $container) {
//        return $container->get(App::class)->getResponseFactory();
//    },

    // The logger factory
//    LoggerFactory::class => function (ContainerInterface $container) {
//        return new LoggerFactory($container->get('settings')['logger']);
//    },

//    ErrorMiddleware::class => function (ContainerInterface $container) {
//        $settings = $container->get('settings')['error'];
//        $app = $container->get(App::class);
//
//        $logger = $container->get(LoggerFactory::class)
//            ->addFileHandler('error.log')
//            ->createInstance('default_error_handler');
//
//        $errorMiddleware = new ErrorMiddleware(
//            $app->getCallableResolver(),
//            $app->getResponseFactory(),
//            (bool)$settings['display_error_details'],
//            (bool)$settings['log_errors'],
//            (bool)$settings['log_error_details'],
//            $logger
//        );
//
//        $errorMiddleware->setDefaultErrorHandler($container->get(DefaultErrorHandler::class));
//
//        return $errorMiddleware;
//    },

    Redis::class => function (ContainerInterface $container) {
        $conf = (array)$container->get('settings')['redis_conf'];
        if (!class_exists('Redis')) {
            return new class {
                public function get($key = false): bool
                {
                    return $key;
                }
            };
        }
        return new Redis($conf['dsn']);
    },

    Logger::class => function(ContainerInterface $container) {
        $conf = $container->get('settings')['papertrail_conf'];
        //$conf = $env['papertrail_conf'];
        $PAPERTRAIL_HOST = $conf['host'];
        $PAPERTRAIL_PORT = $conf['port'];

        // Set the format
        $output = "%channel%.%level_name%: %message%";
        $formatter = new LineFormatter($output);

        $log = new Logger("[" . $conf['prefix'] ."]");
        $log_handler = new SyslogUdpHandler($PAPERTRAIL_HOST, $PAPERTRAIL_PORT);
        $log_handler->setFormatter($formatter);
        $log->pushHandler($log_handler);
        return $log;
    },



    'pdo' => function (ContainerInterface $container) {
        $db_conf = $container->get('settings')['postgresql'];
        $port = $db_conf['db_port'] ?? '5432';
        $pdo = new PDO("{$db_conf['db_driver']}:host={$db_conf['db_host']};dbname={$db_conf['db_database']};port={$port}", $db_conf['db_username'], htmlspecialchars($db_conf['db_password']));
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    },


];