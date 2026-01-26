<?php
// File: api/traffic/create.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/database.php';
include_once '../../models/Traffic.php';

$database = new Database();
$db = $database->getConnection();
$traffic = new Traffic($db);

$data = json_decode(file_get_contents("php://input"));

if(
    !empty($data->intersection) &&
    !empty($data->motor) &&
    !empty($data->mobil) &&
    !empty($data->truk)
) {
    $traffic->timestamp = date('Y-m-d H:i:s');
    $traffic->intersection = $data->intersection;
    $traffic->motor = $data->motor;
    $traffic->mobil = $data->mobil;
    $traffic->truk = $data->truk;
    $traffic->total = $data->motor + $data->mobil + $data->truk;
    $traffic->delay = $data->delay ?? 0;
    
    // Determine status
    if ($traffic->delay > 90) {
        $traffic->status = 'Macet';
    } elseif ($traffic->delay > 50) {
        $traffic->status = 'Padat';
    } else {
        $traffic->status = 'Lancar';
    }

    if($traffic->create()) {
        http_response_code(201);
        echo json_encode(array("message" => "Traffic log created successfully."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to create traffic log."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data."));
}
?>