<?php
    namespace App\Middleware;

    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
    use Slim\Psr7\Response;
    use Slim\App;
    use Monolog\Logger;
    use Predis\Client as Redis;

    class LogMiddleware
    {
        private Logger $papertrail;
        private Redis $redis;
        private $requests_per_minute;

        /**
         * The constructor.
         *
         * @param Responder $responder The responder
         */
        public function __construct(App $app)
        {
            $this->papertrail = $app->getContainer()->get(Logger::class);
            $this->redis = $app->getContainer()->get(Redis::class);
            $this->caching = $app->getContainer()->get('settings');

            if (isset($app->getContainer()->get('settings')['limiter']['requests_per_minute'])) {
                $this->requests_per_minute = $app->getContainer()->get('settings')['limiter']['requests_per_minute'];
            }
            else {
                $this->requests_per_minute = 10;
            }
        }

        /**
         * Example middleware invokable class
         *
         * @param  ServerRequest  $request PSR-7 request
         * @param  RequestHandler $handler PSR-15 request handler
         *
         * @return Response
         */
        public function __invoke(Request $request, RequestHandler $handler): Response
        {
            $response = $handler->handle($request);

            $route = $request->getUri()->getPath();
            $ipAddress = $this->getIPAddress();
            $data = file_get_contents('php://input');

            // Use the new logger
            //log before apply request limiter - log all requsts (even those who exceed request limiter)
            //$this->papertrail->info("[$ipAddress] - $route - ($data)");

            //log request client ip and apply request limiter
            $key = "access_api:ip_{$ipAddress}";

            $this->redis->lpush($key, time());

            if ($this->redis->llen($key) > $this->requests_per_minute) {

                $last_timestamp = intval($this->redis->rpop($key));

                if (strtotime("-1 minute") < $last_timestamp) {
                    //echo "block - ".  strtotime("-1 minute"); exit;
                    $newResponse = new Response();
                    $newResponse->getBody()->write('Too Many Requests!');
                    return $newResponse->withStatus(429); //Too Many Requests (RFC 6585)
                }
            }
//            else {
//                $this->redis->rpop($key);
//            }

            //log after apply request limiter - only allowed requests are been logged
            $this->papertrail->info("[$ipAddress] - $route - ($data)");

            return $response;
        }


        private function getIPAddress() {
            //whether ip is from the share internet
            if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            }
            //whether ip is from the proxy
            elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            //whether ip is from the remote address
            else{
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            return $ip;
        }
    }