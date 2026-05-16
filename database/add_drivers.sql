-- ============================================================
-- add_drivers.sql
-- Run this in phpMyAdmin on the ambulance_locator database
-- to add driver login support.
-- ============================================================

USE ambulance_locator;

-- ============================================================
-- Table: drivers
-- Each driver account is linked to one ambulance row.
-- ============================================================
CREATE TABLE IF NOT EXISTS drivers (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    ambulance_id INT          NOT NULL UNIQUE,   -- one driver per ambulance
    name         VARCHAR(100) NOT NULL,
    phone        VARCHAR(20)  NOT NULL,
    email        VARCHAR(150) NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,
    license_no   VARCHAR(50)  NOT NULL DEFAULT '',
    experience   TINYINT      NOT NULL DEFAULT 0, -- years
    is_active    TINYINT(1)   NOT NULL DEFAULT 1,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ambulance_id) REFERENCES ambulances(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Seed: drivers  (plain password: driver123)
-- Hash = password_hash('driver123', PASSWORD_BCRYPT)
-- Run setup_driver.php to auto-generate correct hashes.
-- ============================================================
INSERT IGNORE INTO drivers
    (ambulance_id, name, phone, email, password, license_no, experience)
VALUES
(1,  'Rajan Mehta',   '9001122334', 'rajan@driver.com',   '$2y$10$PLACEHOLDER', 'DL-MH-2019-001', 5),
(2,  'Suresh Yadav',  '9002233445', 'suresh@driver.com',  '$2y$10$PLACEHOLDER', 'DL-MH-2020-002', 4),
(3,  'Deepak Nair',   '9003344556', 'deepak@driver.com',  '$2y$10$PLACEHOLDER', 'DL-MH-2018-003', 6),
(4,  'Manoj Tiwari',  '9004455667', 'manoj@driver.com',   '$2y$10$PLACEHOLDER', 'DL-DL-2021-004', 3),
(5,  'Arun Verma',    '9005566778', 'arun@driver.com',    '$2y$10$PLACEHOLDER', 'DL-DL-2017-005', 7),
(6,  'Kiran Reddy',   '9006677889', 'kiran@driver.com',   '$2y$10$PLACEHOLDER', 'DL-KA-2022-006', 2),
(7,  'Prasad Iyer',   '9007788990', 'prasad@driver.com',  '$2y$10$PLACEHOLDER', 'DL-KA-2016-007', 8),
(8,  'Sanjay Gupta',  '9008899001', 'sanjay@driver.com',  '$2y$10$PLACEHOLDER', 'DL-TN-2019-008', 5),
(9,  'Ramesh Pillai', '9009900112', 'ramesh@driver.com',  '$2y$10$PLACEHOLDER', 'DL-TN-2020-009', 4),
(10, 'Naresh Joshi',  '9010011223', 'naresh@driver.com',  '$2y$10$PLACEHOLDER', 'DL-GJ-2018-010', 6);

-- NOTE: Run http://localhost/ambulance_locator/setup_driver.php
-- to replace PLACEHOLDER hashes with real bcrypt hashes.
