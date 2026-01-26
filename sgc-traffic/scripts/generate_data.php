<?php
// File: scripts/generate_data.php
// Script untuk generate dummy data secara berkala

include_once '../config/database.php';
include_once '../models/Traffic.php';

$database = new Database();
$db = $database->getConnection();
$traffic = new Traffic($db);

$intersections = ['SGC Utara', 'SGC Selatan', 'SGC Timur', 'SGC Barat'];

function generateTrafficData() {
    global $intersections;
    
    $data = array(
        'intersection' => $intersections[array_rand($intersections)],
        'motor' => rand(50, 200),
        'mobil' => rand(30, 130),
        'truk' => rand(5, 35),
        'delay' => rand(20, 140)
    );
    
    return $data;
}

// Generate 100 data points
for ($i = 0; $i < 100; $i++) {
    $data = generateTrafficData();
    
    $traffic->timestamp = date('Y-m-d H:i:s', strtotime("-{$i} minutes"));
    $traffic->intersection = $data['intersection'];
    $traffic->motor = $data['motor'];
    $traffic->mobil = $data['mobil'];
    $traffic->truk = $data['truk'];
    $traffic->total = $data['motor'] + $data['mobil'] + $data['truk'];
    $traffic->delay = $data['delay'];
    
    if ($traffic->delay > 90) {
        $traffic->status = 'Macet';
    } elseif ($traffic->delay > 50) {
        $traffic->status = 'Padat';
    } else {
        $traffic->status = 'Lancar';
    }
    
    if ($traffic->create()) {
        echo "Data {$i} inserted successfully\n";
    } else {
        echo "Failed to insert data {$i}\n";
    }
}

echo "\nData generation completed!\n";
?>
