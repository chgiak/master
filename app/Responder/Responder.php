<?php

declare(strict_types=1);

namespace App\Responder;

use JsonException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
//use Slim\Interfaces\RouteParserInterface;

/**
 * A generic responder.
 */
final class Responder
{

//    private RouteParserInterface $routeParser;
//
//    private ResponseFactoryInterface $responseFactory;

    /**
     * The constructor.
     *
     * @param Twig $twig The twig engine
     * @param RouteParserInterface $routeParser The route parser
     * @param ResponseFactoryInterface $responseFactory The response factory
     */
//    public function __construct(RouteParserInterface $routeParser, ResponseFactoryInterface $responseFactory) {
//        $this->responseFactory = $responseFactory;
//        $this->routeParser = $routeParser;
//    }

    /**
     * Create a new response.
     *
     * @return ResponseInterface The response
     */
//    public function createResponse(): ResponseInterface
//    {
//        return $this->responseFactory->createResponse()->withHeader('Content-Type', 'application/json; charset=utf-8');
//    }




    /**
     * Write JSON to the response body.
     *
     * This method prepares the response object to return an HTTP JSON
     * response to the client.
     *
     * @param ResponseInterface $response The response
     * @param mixed $data The data
     * @param int $options Json encoding options
     *
     * @throws JsonException
     *
     * @return ResponseInterface The response
     */
    public function toJSON(ResponseInterface $response, mixed $data = null, int $options = 0): ResponseInterface {
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write((string) json_encode($data, JSON_THROW_ON_ERROR | $options));

        return $response;
    }


    public function toXML(ResponseInterface $response, mixed $data = null): ResponseInterface
    {
//        var_dump ($data[0]);
        $xmlDoc = new \DOMDocument();
        $root = $xmlDoc->appendChild($xmlDoc->createElement("vessels"));

        foreach ($data as $record) {
            $vessel = $root->appendChild($xmlDoc->createElement('vessel'));

            $vessel->appendChild($xmlDoc->createElement("mmsi", (string) $record['mmsi']));
            $vessel->appendChild($xmlDoc->createElement("status", (string) $record['status']));
            $vessel->appendChild($xmlDoc->createElement("station", (string) $record['station']));
            $vessel->appendChild($xmlDoc->createElement("speed", (string) $record['speed']));

            $position = $vessel->appendChild($xmlDoc->createElement('position'));
            $position->appendChild($xmlDoc->createElement("lon", (string) $record['position']['lon']));
            $position->appendChild($xmlDoc->createElement("lat", (string) $record['position']['lat']));

            $vessel->appendChild($xmlDoc->createElement("course", (string) $record['course']));
            $vessel->appendChild($xmlDoc->createElement("heading", (string) $record['heading']));
            $vessel->appendChild($xmlDoc->createElement("rot", (string) $record['rot']));
            $vessel->appendChild($xmlDoc->createElement("timestamp", (string) $record['timestamp']));

        }
        $xmlDoc->formatOutput = true;
        $string_xml= $xmlDoc->saveXML();

        $response = $response->withHeader('Content-Type', 'application/xml');
        $response->getBody()->write((string) $string_xml);

        return $response;
    }


    public function toCSV(ResponseInterface $response, mixed $data = null): ResponseInterface
    {
        $string_csv = "mmsi,status,station,speed,lon,lat,course,heading,rot,timestamp";
        foreach ($data as $record) {
            $string_csv .= sprintf("\r\n%d,%d,%d,%d,%f,%f,%d,%d,%s,%s",$record['mmsi'],
                                    $record['status'],$record['station'], $record['speed'],
                                    $record['position']['lon'], $record['position']['lat'],
                                    $record['course'], $record['heading'], $record['rot'], $record['timestamp']);
        }

        $response = $response->withHeader('Content-Type', 'text/csv');
        $response->getBody()->write((string) $string_csv);
        return $response;
    }
}
