<?php
// File: api/traffic/stats.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../models/Traffic.php';

$database = new Database();
$db = $database->getConnection();
$traffic = new Traffic($db);

$timeRange = isset($_GET['range']) ? $_GET['range'] : 'today';
$stats = $traffic->getStats($timeRange);

if($stats) {
    http_response_code(200);
    echo json_encode($stats);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No statistics available."));
}
?>