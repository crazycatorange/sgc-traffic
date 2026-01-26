<?php
// File: api/traffic/realtime.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../models/Traffic.php';

$database = new Database();
$db = $database->getConnection();
$traffic = new Traffic($db);

$stmt = $traffic->getRealtime();
$num = $stmt->rowCount();

if($num > 0) {
    $traffic_arr = array();
    $traffic_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        
        $traffic_item = array(
            "timestamp" => $timestamp,
            "intersection" => $intersection,
            "motor" => $motor,
            "mobil" => $mobil,
            "truk" => $truk,
            "total" => $total,
            "delay" => $delay,
            "status" => $status
        );

        array_push($traffic_arr["records"], $traffic_item);
    }

    http_response_code(200);
    echo json_encode($traffic_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No real-time data available."));
}
?>