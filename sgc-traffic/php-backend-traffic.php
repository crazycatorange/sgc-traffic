-- ============================================
-- 1. DATABASE SCHEMA (MySQL)
-- ============================================

-- File: database.sql
-- Jalankan di phpMyAdmin atau MySQL CLI

CREATE DATABASE IF NOT EXISTS sgc_traffic_db;
USE sgc_traffic_db;

-- Tabel traffic logs (Data utama)
CREATE TABLE traffic_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    timestamp DATETIME NOT NULL,
    intersection VARCHAR(50) NOT NULL,
    motor INT NOT NULL DEFAULT 0,
    mobil INT NOT NULL DEFAULT 0,
    truk INT NOT NULL DEFAULT 0,
    total INT NOT NULL DEFAULT 0,
    delay INT NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL,
    weather VARCHAR(50) DEFAULT 'Cerah',
    temperature DECIMAL(5,2) DEFAULT 30.0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_timestamp (timestamp),
    INDEX idx_intersection (intersection),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel intersections (Master data persimpangan)
CREATE TABLE intersections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    capacity INT DEFAULT 1000,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel predictions (Hasil prediksi ML)
CREATE TABLE predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    intersection VARCHAR(50) NOT NULL,
    prediction_time DATETIME NOT NULL,
    predicted_volume INT NOT NULL,
    predicted_delay INT NOT NULL,
    predicted_status VARCHAR(20),
    confidence_score DECIMAL(5,2),
    algorithm VARCHAR(50) DEFAULT 'LSTM',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel analytics_summary (Aggregated data untuk performa)
CREATE TABLE analytics_summary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    hour INT NOT NULL,
    intersection VARCHAR(50) NOT NULL,
    avg_motor DECIMAL(10,2),
    avg_mobil DECIMAL(10,2),
    avg_truk DECIMAL(10,2),
    avg_delay DECIMAL(10,2),
    peak_volume INT,
    status_distribution JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_summary (date, hour, intersection)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel users (Untuk authentication)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'operator', 'viewer') DEFAULT 'viewer',
    is_active BOOLEAN DEFAULT TRUE,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample intersections
INSERT INTO intersections (code, name, latitude, longitude, capacity) VALUES
('SGC-UTARA', 'SGC Utara', -6.2935, 107.1573, 1200),
('SGC-SELATAN', 'SGC Selatan', -6.2945, 107.1573, 1000),
('SGC-TIMUR', 'SGC Timur', -6.2940, 107.1583, 1100),
('SGC-BARAT', 'SGC Barat', -6.2940, 107.1563, 900);

-- Insert sample admin user (password: admin123)
INSERT INTO users (username, password, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@sgc.com', 'admin');


-- ============================================
-- 2. DATABASE CONFIG
-- ============================================

<?php
// File: config/database.php

class Database {
    private $host = "localhost";
    private $db_name = "sgc_traffic_db";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}
?>


-- ============================================
-- 3. TRAFFIC MODEL
-- ============================================

<?php
// File: models/Traffic.php

class Traffic {
    private $conn;
    private $table = "traffic_logs";

    public $id;
    public $timestamp;
    public $intersection;
    public $motor;
    public $mobil;
    public $truk;
    public $total;
    public $delay;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new traffic log
    public function create() {
        $query = "INSERT INTO " . $this->table . "
                SET timestamp=:timestamp,
                    intersection=:intersection,
                    motor=:motor,
                    mobil=:mobil,
                    truk=:truk,
                    total=:total,
                    delay=:delay,
                    status=:status";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":timestamp", $this->timestamp);
        $stmt->bindParam(":intersection", $this->intersection);
        $stmt->bindParam(":motor", $this->motor);
        $stmt->bindParam(":mobil", $this->mobil);
        $stmt->bindParam(":truk", $this->truk);
        $stmt->bindParam(":total", $this->total);
        $stmt->bindParam(":delay", $this->delay);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get all traffic logs with pagination
    public function read($page = 1, $limit = 50) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT * FROM " . $this->table . "
                ORDER BY timestamp DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    // Get real-time data (last 15 minutes)
    public function getRealtime() {
        $query = "SELECT * FROM " . $this->table . "
                WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
                ORDER BY timestamp DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Get data by intersection
    public function getByIntersection($intersection, $date = null) {
        $query = "SELECT * FROM " . $this->table . "
                WHERE intersection = :intersection";
        
        if ($date) {
            $query .= " AND DATE(timestamp) = :date";
        }
        
        $query .= " ORDER BY timestamp DESC LIMIT 100";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":intersection", $intersection);
        
        if ($date) {
            $stmt->bindParam(":date", $date);
        }
        
        $stmt->execute();

        return $stmt;
    }

    // Get statistics
    public function getStats($timeRange = 'today') {
        $whereClause = $this->getTimeRangeClause($timeRange);
        
        $query = "SELECT 
                    COUNT(*) as total_records,
                    AVG(total) as avg_vehicles,
                    AVG(delay) as avg_delay,
                    MAX(total) as peak_volume,
                    SUM(CASE WHEN status = 'Macet' THEN 1 ELSE 0 END) as macet_count,
                    SUM(CASE WHEN status = 'Padat' THEN 1 ELSE 0 END) as padat_count,
                    SUM(CASE WHEN status = 'Lancar' THEN 1 ELSE 0 END) as lancar_count
                FROM " . $this->table . "
                WHERE " . $whereClause;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get hourly distribution
    public function getHourlyDistribution($date = null) {
        $dateClause = $date ? "DATE(timestamp) = :date" : "DATE(timestamp) = CURDATE()";
        
        $query = "SELECT 
                    HOUR(timestamp) as hour,
                    AVG(motor) as avg_motor,
                    AVG(mobil) as avg_mobil,
                    AVG(truk) as avg_truk,
                    AVG(total) as avg_total,
                    AVG(delay) as avg_delay
                FROM " . $this->table . "
                WHERE " . $dateClause . "
                GROUP BY HOUR(timestamp)
                ORDER BY hour";

        $stmt = $this->conn->prepare($query);
        
        if ($date) {
            $stmt->bindParam(":date", $date);
        }
        
        $stmt->execute();

        return $stmt;
    }

    private function getTimeRangeClause($timeRange) {
        switch($timeRange) {
            case '1hour':
                return "timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
            case 'today':
                return "DATE(timestamp) = CURDATE()";
            case 'week':
                return "timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case 'month':
                return "timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            default:
                return "1=1";
        }
    }
}
?>


-- ============================================
-- 4. API ENDPOINTS
-- ============================================

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

<?php
// File: api/traffic/read.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../models/Traffic.php';

$database = new Database();
$db = $database->getConnection();
$traffic = new Traffic($db);

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$limit = isset($_GET['limit']) ? $_GET['limit'] : 50;

$stmt = $traffic->read($page, $limit);
$num = $stmt->rowCount();

if($num > 0) {
    $traffic_arr = array();
    $traffic_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        
        $traffic_item = array(
            "id" => $id,
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
    echo json_encode(array("message" => "No traffic logs found."));
}
?>

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


-- ============================================
-- 5. DATA GENERATOR SCRIPT
-- ============================================

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


-- ============================================
-- 6. .htaccess untuk REST API
-- ============================================

# File: .htaccess

RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]

# Enable CORS
Header set Access-Control-Allow-Origin "*"
Header set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
Header set Access-Control-Allow-Headers "Content-Type, Authorization"