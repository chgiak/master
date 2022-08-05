<?php
    use Slim\App;
    use Slim\Routing\RouteCollectorProxy;
    use App\Action\Vessel\VesselAction;
    use App\Middleware\LogMiddleware;
    use App\Middleware\ContentTypeMiddleware;
    use Monolog\Logger;

    return function (App $app) {

        $app->group('/v1', function (RouteCollectorProxy $group) {
            $group->get('/vessels', VesselAction::class);
        });
    };

