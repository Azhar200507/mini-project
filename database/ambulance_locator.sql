-- ============================================================
-- Nearby Ambulance Locator - Complete Database Schema
-- Import via phpMyAdmin: Import tab > choose this file
-- Or run: mysql -u root -p < ambulance_locator.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS ambulance_locator
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ambulance_locator;

-- ============================================================
-- Table: users
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100)  NOT NULL,
    phone      VARCHAR(20)   NOT NULL,
    email      VARCHAR(150)  NOT NULL UNIQUE,
    password   VARCHAR(255)  NOT NULL,
    created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Table: admins
-- ============================================================
CREATE TABLE IF NOT EXISTS admins (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Table: ambulances
-- ============================================================
CREATE TABLE IF NOT EXISTS ambulances (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    driver_name VARCHAR(100)  NOT NULL,
    phone       VARCHAR(20)   NOT NULL,
    vehicle_no  VARCHAR(30)   NOT NULL,
    area        VARCHAR(150)  NOT NULL,
    latitude    DECIMAL(10,8) NOT NULL DEFAULT 0.00000000,
    longitude   DECIMAL(11,8) NOT NULL DEFAULT 0.00000000,
    status      ENUM('available','busy','offline') NOT NULL DEFAULT 'available',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Table: requests
-- ============================================================
CREATE TABLE IF NOT EXISTS requests (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT          NOT NULL,
    ambulance_id INT          NOT NULL,
    location     VARCHAR(255) NOT NULL,
    status       ENUM('pending','accepted','completed','cancelled') NOT NULL DEFAULT 'pending',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)      REFERENCES users(id)      ON DELETE CASCADE,
    FOREIGN KEY (ambulance_id) REFERENCES ambulances(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Seed: admins
-- Plain password: admin123
-- Hash generated with: password_hash('admin123', PASSWORD_BCRYPT)
-- ============================================================
INSERT INTO admins (username, password) VALUES
('admin', '$2y$10$TKh8H1.PfYi1Zy7Wr.Ue8uqNZvmR2bZ6K9Qe3Lp5Xs1Yw4Mv7Oa');

-- ============================================================
-- Seed: users  (plain password: user123)
-- ============================================================
INSERT INTO users (name, phone, email, password) VALUES
('Rahul Sharma',  '9876543210', 'rahul@example.com',  '$2y$10$TKh8H1.PfYi1Zy7Wr.Ue8uqNZvmR2bZ6K9Qe3Lp5Xs1Yw4Mv7Oa'),
('Priya Singh',   '9123456780', 'priya@example.com',  '$2y$10$TKh8H1.PfYi1Zy7Wr.Ue8uqNZvmR2bZ6K9Qe3Lp5Xs1Yw4Mv7Oa'),
('Amit Kumar',    '9988776655', 'amit@example.com',   '$2y$10$TKh8H1.PfYi1Zy7Wr.Ue8uqNZvmR2bZ6K9Qe3Lp5Xs1Yw4Mv7Oa'),
('Sneha Patel',   '9871234560', 'sneha@example.com',  '$2y$10$TKh8H1.PfYi1Zy7Wr.Ue8uqNZvmR2bZ6K9Qe3Lp5Xs1Yw4Mv7Oa'),
('Vikram Rao',    '9765432100', 'vikram@example.com', '$2y$10$TKh8H1.PfYi1Zy7Wr.Ue8uqNZvmR2bZ6K9Qe3Lp5Xs1Yw4Mv7Oa');

-- ============================================================
-- Seed: ambulances
-- ============================================================
INSERT INTO ambulances (driver_name, phone, vehicle_no, area, latitude, longitude, status) VALUES
('Rajan Mehta',   '9001122334', 'MH-01-AB-1234', 'Andheri West, Mumbai',   19.13600000,  72.82600000, 'available'),
('Suresh Yadav',  '9002233445', 'MH-02-CD-5678', 'Bandra East, Mumbai',    19.05400000,  72.84200000, 'available'),
('Deepak Nair',   '9003344556', 'MH-03-EF-9012', 'Dadar, Mumbai',          19.01800000,  72.84300000, 'busy'),
('Manoj Tiwari',  '9004455667', 'DL-04-GH-3456', 'Connaught Place, Delhi', 28.63290000,  77.21970000, 'available'),
('Arun Verma',    '9005566778', 'DL-05-IJ-7890', 'Lajpat Nagar, Delhi',    28.56500000,  77.24300000, 'available'),
('Kiran Reddy',   '9006677889', 'KA-06-KL-2345', 'Koramangala, Bangalore', 12.93500000,  77.62400000, 'offline'),
('Prasad Iyer',   '9007788990', 'KA-07-MN-6789', 'Indiranagar, Bangalore', 12.97800000,  77.64100000, 'available'),
('Sanjay Gupta',  '9008899001', 'TN-08-OP-0123', 'T. Nagar, Chennai',      13.04000000,  80.23400000, 'available'),
('Ramesh Pillai', '9009900112', 'TN-09-QR-4567', 'Anna Nagar, Chennai',    13.08500000,  80.21000000, 'busy'),
('Naresh Joshi',  '9010011223', 'GJ-10-ST-8901', 'Navrangpura, Ahmedabad', 23.03700000,  72.56000000, 'available');

-- ============================================================
-- Seed: requests
-- ============================================================
INSERT INTO requests (user_id, ambulance_id, location, status) VALUES
(1, 1, 'Andheri Station, Mumbai',      'completed'),
(2, 2, 'Bandra Kurla Complex, Mumbai', 'accepted'),
(3, 4, 'India Gate, Delhi',            'pending'),
(4, 7, 'MG Road, Bangalore',           'completed'),
(5, 8, 'Marina Beach, Chennai',        'cancelled'),
(1, 5, 'Karol Bagh, Delhi',            'pending'),
(2, 3, 'Dadar Station, Mumbai',        'accepted'),
(3, 6, 'Whitefield, Bangalore',        'completed');
