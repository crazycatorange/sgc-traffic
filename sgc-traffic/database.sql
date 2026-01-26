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