<?php
    error_reporting(E_ERROR | E_PARSE);
    sleep(1);
    require_once(__DIR__ . '/../../vendor/autoload.php');
    /****************************************
     *                                      *
     *          Initialize Application      *
     *          Variables & Middleware      *
     *                                      *
     ****************************************/
    if (file_exists(__DIR__ . '/../../.env')) {
        $dotenv = Dotenv\Dotenv::createMutable(__DIR__ . '/../../');
        $dotenv->load();
    }

    $conf = require_once(__DIR__ . '/../../.env.php');
    //var_dump ($conf);

    // Redis
    $env = $conf['redis_conf'];
    $redis = new Predis\Client("{$env['scheme']}://{$env['host']}:{$env['port']}?auth={$env['password']}");


    //PostgreSQL connection
    $db_conf = $conf['postgresql'];
    $port = $db_conf['db_port'] ?? '5432';
    $pdo = new PDO("{$db_conf['db_driver']}:host={$db_conf['db_host']};dbname={$db_conf['db_database']};port={$port}", $db_conf['db_username'], htmlspecialchars($db_conf['db_password']));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Read the JSON file
    $json_data = file_get_contents(__DIR__ . '/../../' . $conf['import']['import_data_file']);

    // Decode the JSON file
    $json_data = json_decode($json_data,true);

    //build import query
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("TRUNCATE TABLE ship_positions");
        $stmt->execute();

        //    $import_qry = "INSERT INTO ship_positions (mmsi, status, stationId, speed,  position, course, heading, rotation, timestamp) VALUES";
        foreach ($json_data as $data) {
            //$import_qry .= sprintf("(%d, %d, %d, %d, '(%d, %d)', %d, %d, '%s', to_timestamp(%d)),", $data['mmsi'], $data['status'], $data['stationid'], $data['speed'], $data['lon'], $data['lat'], $data['course'], $data['heading'], $data['rot'], $data['timestamp']);

            $import_qry = "INSERT INTO ship_positions (mmsi, status, stationid, speed,  position, course, heading, rotation, timestamp) VALUES 
                                (:mmsi, :status, :stationid, :speed, :position, :course, :heading, :rotation, to_timestamp(:timestamp));";
            $stm = $pdo->prepare($import_qry);
            $params = ['mmsi' => $data['mmsi'], 'status' => $data['status'], 'stationid'=>$data['stationId'], 'speed' => $data['speed'], 'position'=>"(" . $data['lon']. ", " . $data['lat'] . ")" , 'course'=>$data['course'], 'heading'=>$data['heading'], 'rotation'=>$data['rot'],  'timestamp'=>$data['timestamp']];
            $stm->execute($params);
        }
        $pdo->commit();
    } catch(PDOException $e) {
        $pdo->rollBack();
    }
?>