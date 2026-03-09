-- Nestle NDRC Supply Chain Last Mile Visibility System
-- Initial Database Schema

CREATE DATABASE IF NOT EXISTS nestle_ndrc;
USE nestle_ndrc;

-- Warehouses/Distribution Centers
CREATE TABLE IF NOT EXISTS warehouses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255),
    capacity INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Delivery Personnel
CREATE TABLE IF NOT EXISTS drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    vehicle_type ENUM('bike', 'van', 'truck'),
    status ENUM('idle', 'active', 'offline') DEFAULT 'offline',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders/Deliveries
CREATE TABLE IF NOT EXISTS deliveries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(50) UNIQUE NOT NULL,
    customer_name VARCHAR(255),
    delivery_address TEXT,
    warehouse_id INT,
    driver_id INT,
    status ENUM('pending', 'picked_up', 'in_transit', 'delivered', 'failed') DEFAULT 'pending',
    estimated_delivery DATETIME,
    actual_delivery DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id),
    FOREIGN KEY (driver_id) REFERENCES drivers(id)
);

-- API Access Logs (Future use)
CREATE TABLE IF NOT EXISTS api_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    endpoint VARCHAR(255),
    request_data TEXT,
    response_code INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
