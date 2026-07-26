-- =========================================================
-- Lead Management System Database Schema & Seed Data
-- Database Engine: MySQL / MariaDB (XAMPP Compatible)
-- =========================================================

CREATE DATABASE IF NOT EXISTS `lead_management_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `lead_management_db`;

-- ---------------------------------------------------------
-- 1. Roles Table
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `role_name` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `roles` (`id`, `role_name`) VALUES 
(1, 'admin'),
(2, 'user')
ON DUPLICATE KEY UPDATE `role_name`=`role_name`;

-- ---------------------------------------------------------
-- 2. Users Table
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `number` VARCHAR(20) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role_id` INT NOT NULL DEFAULT 2,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Admin (Password: admin123) and Standard User (Password: user123)
-- Uses password_hash() in PHP. Default seed hashes below:
-- 'admin123' -> $2y$10$89W1Fm78e9W2eB0i7wUjme0Q6BfL1xGqGz4.e9Nq9y9Fm78e9W2e
-- We will auto-seed admin if table is empty via setup script or SQL below:
INSERT INTO `users` (`id`, `name`, `number`, `email`, `username`, `password`, `role_id`) VALUES
(1, 'System Administrator', '9876543210', 'admin@crm.com', 'admin', '$2y$10$qV9F3.1Jb9hT21Wd5fH/veT/bN6WlC7zS4PzV4kO3fB1rX2gQ3/4e', 1),
(2, 'John Salesman', '9123456780', 'john@crm.com', 'john_user', '$2y$10$qV9F3.1Jb9hT21Wd5fH/veT/bN6WlC7zS4PzV4kO3fB1rX2gQ3/4e', 2)
ON DUPLICATE KEY UPDATE `username`=`username`;

-- ---------------------------------------------------------
-- 3. Countries Table
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `countries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `country_name` VARCHAR(100) NOT NULL UNIQUE,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `countries` (`id`, `country_name`) VALUES 
(1, 'India'),
(2, 'United States'),
(3, 'United Kingdom'),
(4, 'Canada'),
(5, 'Australia')
ON DUPLICATE KEY UPDATE `country_name`=`country_name`;

-- ---------------------------------------------------------
-- 4. States Table
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `states` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `country_id` INT NOT NULL,
    `state_name` VARCHAR(100) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`country_id`) REFERENCES `countries`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY `unique_state_per_country` (`country_id`, `state_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `states` (`id`, `country_id`, `state_name`) VALUES 
(1, 1, 'Maharashtra'),
(2, 1, 'Delhi'),
(3, 1, 'Karnataka'),
(4, 1, 'Gujarat'),
(5, 2, 'California'),
(6, 2, 'New York'),
(7, 2, 'Texas')
ON DUPLICATE KEY UPDATE `state_name`=`state_name`;

-- ---------------------------------------------------------
-- 5. Cities Table
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cities` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `state_id` INT NOT NULL,
    `city_name` VARCHAR(100) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`state_id`) REFERENCES `states`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY `unique_city_per_state` (`state_id`, `city_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `cities` (`id`, `state_id`, `city_name`) VALUES 
(1, 1, 'Mumbai'),
(2, 1, 'Pune'),
(3, 2, 'New Delhi'),
(4, 3, 'Bengaluru'),
(5, 4, 'Ahmedabad'),
(6, 5, 'Los Angeles'),
(7, 5, 'San Francisco'),
(8, 6, 'New York City')
ON DUPLICATE KEY UPDATE `city_name`=`city_name`;

-- ---------------------------------------------------------
-- 6. Lead Types Table
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lead_types` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `type_name` VARCHAR(100) NOT NULL UNIQUE,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `lead_types` (`id`, `type_name`) VALUES 
(1, 'Inbound Web Query'),
(2, 'Cold Calling'),
(3, 'Referral'),
(4, 'Social Media Campaign'),
(5, 'Email Outreach')
ON DUPLICATE KEY UPDATE `type_name`=`type_name`;

-- ---------------------------------------------------------
-- 7. Lead Statuses Table
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lead_statuses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `status_name` VARCHAR(100) NOT NULL UNIQUE,
    `color_code` VARCHAR(10) DEFAULT '#6c757d',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `lead_statuses` (`id`, `status_name`, `color_code`) VALUES 
(1, 'Fresh', '#3b82f6'),
(2, 'Follow Up', '#f59e0b'),
(3, 'Matured', '#10b981'),
(4, 'Closed', '#ef4444')
ON DUPLICATE KEY UPDATE `status_name`=`status_name`;

-- ---------------------------------------------------------
-- 8. Leads Table
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `leads` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_name` VARCHAR(150) NOT NULL,
    `owner_name` VARCHAR(100) NOT NULL,
    `mobile` VARCHAR(20) NOT NULL,
    `official_email` VARCHAR(100) NOT NULL,
    `personal_email` VARCHAR(100) DEFAULT NULL,
    `country_id` INT NOT NULL,
    `state_id` INT NOT NULL,
    `city_id` INT NOT NULL,
    `lead_type_id` INT NOT NULL,
    `lead_status_id` INT NOT NULL,
    `follow_up_date` DATE DEFAULT NULL,
    `remarks` TEXT DEFAULT NULL,
    `created_by` INT NOT NULL,
    `assigned_to` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`country_id`) REFERENCES `countries`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`state_id`) REFERENCES `states`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`city_id`) REFERENCES `cities`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`lead_type_id`) REFERENCES `lead_types`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`lead_status_id`) REFERENCES `lead_statuses`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- 9. Lead Documents Table (Up to 30 attachments per lead)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lead_documents` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `lead_id` INT NOT NULL,
    `document_title` VARCHAR(150) NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_type` VARCHAR(100) NOT NULL,
    `file_size` INT NOT NULL,
    `uploaded_by` INT NOT NULL,
    `uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
