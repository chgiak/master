<?php
declare(strict_types=1);

namespace App\Action\Vessel;

use App\Responder\Responder;
use Predis\Client as Redis;
use Psr\Http\Message\ResponseInterface;
use Psr\http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Psr7\Response;


final class VesselAction
{

    private $pdo;
    private $responder;
    private $redis;

    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     */
    public function __construct(App $app, Responder $responder) //Responder $responder)
    {
        //$this->responder = $responder;
        $this->pdo = $app->getContainer()->get('pdo');
        $this->responder = $responder;
        $this->redis = $app->getContainer()->get(Redis::class);
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     *
     * @return ResponseInterface The response
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['filters'])) {
            $filters = $data['filters'];
        }

        if (isset($data['ContentType'])) {
            $reuested_contentType = $data['ContentType'];
        }
        else {
            $reuested_contentType = 'application/json';
        }

        $filter_found = false;

        $qry = "SELECT sp.mmsi, sp.status, sp.stationid as station, sp.speed, 
                json_build_object('lon', sp.position[0], 'lat', sp.position[1]) as position,
                sp.course, sp.heading, sp.rotation as rot, extract(epoch from sp.timestamp) as timestamp 
                FROM ship_positions sp
                WHERE 1=1 ";

        $qry_params = [];
        if (isset($filters['mmsi']) && is_array($filters['mmsi']) && count($filters['mmsi'])>=1) {
            //consider mmsi
            $mmsis = implode(', ', $filters['mmsi']);
            $qry .= "AND sp.mmsi in (" . $mmsis . ")";
            $filter_found = true;
        }

        if (isset($filters['points']) && is_array($filters['points']) && count($filters['points'])==2) {
            //consider points

            if (isset($filters['points'][0]['lon']) && isset($filters['points'][0]['lat']) && isset($filters['points'][1]['lon']) && isset($filters['points'][1]['lat']) ) {
                if (($filters['points'][1]['lon'] > $filters['points'][0]['lon'] && $filters['points'][1]['lat'] > $filters['points'][0]['lat'])) {
                    //points shaped by two points. A is upper left corner and B is lower right corner
                    $qry .= " AND sp.position[0] >= :lonA AND sp.position[0] <= :lonB AND sp.position[1] >= :latA AND sp.position[1] <= :latB";
                    $qry_params = ['lonA'=> $filters['points'][0]['lon'], 'latA'=> $filters['points'][0]['lat'], 'lonB'=> $filters['points'][1]['lon'], 'latB'=> $filters['points'][1]['lat']];
                } elseif (($filters['points'][1]['lon'] > $filters['points'][0]['lon'] && $filters['points'][1]['lat'] < $filters['points'][0]['lat'])) {
                    //polygon shaped by two points. A is lower left corner and B is upper right corner
                    $qry .= " AND sp.position[0] <= :lonB AND sp.position[0] >= :lonA AND sp.position[1] <= :latA AND sp.position[1] >= :latB";
                    $qry_params = ['lonA'=> $filters['points'][0]['lon'], 'latA'=> $filters['points'][0]['lat'], 'lonB'=> $filters['points'][1]['lon'], 'latB'=> $filters['points'][1]['lat']];
                }

                $filter_found = true;
            }
        }

        if (isset($filters['time']) && is_array($filters['time']) && count($filters['time'])==2) {
            //consider time
            if (isset($filters['time']['ts_since']) && isset($filters['time']['ts_to']) && $filters['time']['ts_to'] > $filters['time']['ts_since']) {
                $qry .= " AND sp.timestamp >= to_timestamp(:ts_since) AND sp.timestamp <= to_timestamp(:ts_to)";
                $qry_params = array_merge($qry_params, ['ts_since'=> $filters['time']['ts_since'], 'ts_to'=> $filters['time']['ts_to']]);
                $filter_found = true;
            }
        }

        if ($filter_found) {
            $cacheKey = "getVessels:" . md5(serialize($qry_params));
            if (!$this->redis->get($cacheKey)) {
                $stm = $this->pdo->prepare($qry);
                $stm->execute($qry_params);

                $results = $stm->fetchAll();

                foreach ($results as &$record) {
                    $record['position'] = json_decode($record['position'], true, JSON_UNESCAPED_UNICODE);
                }

                $this->redis->set($cacheKey, serialize($results));
                $this->redis->expire($cacheKey, 5*60); //cache results for 5 minutes
            }
            else {
                $results = unserialize($this->redis->get($cacheKey));
            }
        }

        if (isset($data['ContentType'])) {
            $reuested_contentType = $data['ContentType'];
        }
        else {
            $reuested_contentType = 'application/json';
        }
        $allowed_content_types = ['application/json', 'application/vnd.api+json', 'application/ld+json'];

        switch (true) {
            case ($filter_found==false) :
                $newResponse = new Response();
                $newResponse->getBody()->write('Invalid Input!');
                return $newResponse->withStatus(400); //Bad Request
                break;
            case (in_array($reuested_contentType, $allowed_content_types)):
                return $this->responder->toJSON($response, $results);
                break;
            case ($reuested_contentType == 'application/xml'):
                return $this->responder->toXML($response, $results);
                break;
            case ($reuested_contentType == 'text/csv'):
                return $this->responder->toCSV($response, $results);
                break;
        }
    }
}
