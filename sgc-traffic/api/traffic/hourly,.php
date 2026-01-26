<?php
// File: api/traffic/hourly.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../models/Traffic.php';

$database = new Database();
$db = $database->getConnection();
$traffic = new Traffic($db);

$date = isset($_GET['date']) ? $_GET['date'] : null;
$stmt = $traffic->getHourlyDistribution($date);
$num = $stmt->rowCount();

if($num > 0) {
    $hourly_arr = array();
    $hourly_arr["data"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        array_push($hourly_arr["data"], $row);
    }

    http_response_code(200);
    echo json_encode($hourly_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No hourly data available."));
}
?>
