<?php
    use Slim\App;
    use App\Middleware\ContentTypeMiddleware;
    use App\Middleware\LogMiddleware;

    return function (App $app) {
        $app->add(new ContentTypeMiddleware());
        $app->add(new LogMiddleware($app));
    };