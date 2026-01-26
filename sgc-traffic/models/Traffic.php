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