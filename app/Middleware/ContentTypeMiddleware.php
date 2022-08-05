<?php
    namespace App\Middleware;

    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Http\Server\MiddlewareInterface;
    use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Slim\Psr7\Response;
    use App\Slim;

    class ContentTypeMiddleware // implements MiddlewareInterface
    {
        /**
         * Example middleware invokable class
         *
         * @param  ServerRequest  $request PSR-7 request
         * @param  RequestHandler $handler PSR-15 request handler
         *
         * @return Response
         */

//        public function process(ServerRequestInterface $request, RequestHandler $handler) : ResponseInterface
//        {
//            $contentType = $request->getHeaderLine('Content-Type');
//            $allowed_content_types = ['application/json', 'application/vnd.api+json', 'application/ld+json', 'application/xml', 'text/csv'];
//            if (!in_array($contentType, $allowed_content_types)) {
//                $response = $handler->handle($request);
//                $response = $response->withHeader($this->options['headers']['remaining'], (string) $remaining);
//            }
//        }
        public function __invoke(Request $request, RequestHandler $handler): Response
        {
            $response = $handler->handle($request);

            $ContentType = $request->getHeaderLine('Content-Type');
            //var_dump($contentType);
            $allowed_content_types = ['application/json', 'application/vnd.api+json', 'application/ld+json'];
            $data = json_decode(file_get_contents('php://input'), true);

            if (!in_array($ContentType, $allowed_content_types)) {
                $newResponse = new Response();
                $newResponse->getBody()->write('ContentType NOT Allowed!');
                return $newResponse->withStatus(415); //Unsupported Media Type (RFC 7231)
            }
            elseif (is_null($data)) {
                $newResponse = new Response();
                $newResponse->getBody()->write('Invalid Input!');
                return $newResponse->withStatus(400); //Bad Request
            }
            else {
                return $response;
            }
        }
    }