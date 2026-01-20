-- DPS unified database schema (customer/agent/admin)
-- MySQL / MariaDB

DROP DATABASE IF EXISTS dps;
CREATE DATABASE dps CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dps;

-- ==========================
-- Users (all roles that can login)
-- ==========================
CREATE TABLE users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id VARCHAR(32) NOT NULL,
  name VARCHAR(100) NOT NULL,
  phone VARCHAR(15) NOT NULL,
  email VARCHAR(120) NOT NULL,
  dob DATE NULL,
  gender ENUM('male','female','other') NULL,
  role ENUM('admin','agent','customer') NOT NULL DEFAULT 'customer',
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'approved',
  password VARCHAR(100) NOT NULL,
  profile_image VARCHAR(255) NULL,
  balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_user_id (user_id),
  UNIQUE KEY uq_users_phone (phone),
  UNIQUE KEY uq_users_email (email),
  KEY idx_users_role (role),
  KEY idx_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================
-- Bill providers (NOT login users)
-- ==========================
CREATE TABLE bill_providers (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  provider_code VARCHAR(50) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================
-- Transactions (send money, cash out, pay bill)
-- For pay bill: receiver_id can be NULL and bill_provider_id/provider_name is used.
-- ==========================
CREATE TABLE transactions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  type VARCHAR(30) NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  fee DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  sender_id INT UNSIGNED NOT NULL,
  receiver_id INT UNSIGNED NULL,
  bill_provider_id INT UNSIGNED NULL,
  provider_name VARCHAR(100) NULL,
  provider_code VARCHAR(50) NULL,
  reference VARCHAR(64) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_tx_sender (sender_id),
  KEY idx_tx_receiver (receiver_id),
  KEY idx_tx_provider (bill_provider_id),
  KEY idx_tx_created (created_at),
  CONSTRAINT fk_tx_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_tx_receiver FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_tx_provider FOREIGN KEY (bill_provider_id) REFERENCES bill_providers(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================
-- Loan requests
-- ==========================
CREATE TABLE loan_requests (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  duration_months INT UNSIGNED NOT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_lr_user (user_id),
  KEY idx_lr_status (status),
  KEY idx_lr_created (created_at),
  CONSTRAINT fk_lr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================
-- Password reset tokens
-- (still hashed token; user passwords are plain as requested)
-- ==========================
CREATE TABLE password_resets (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_pr_user (user_id),
  KEY idx_pr_expires (expires_at),
  CONSTRAINT fk_pr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================
-- Seed demo accounts (plain passwords)
-- ==========================
INSERT INTO users (user_id, name, phone, email, dob, gender, role, status, password, balance) VALUES
('admin',   'System Admin',  '01700000000', 'admin@dps.com',    NULL, NULL, 'admin',    'approved', '123456', 0.00),
('agent1',  'Demo Agent',    '01700000001', 'agent@dps.com',    NULL, NULL, 'agent',    'approved', '123456', 1000.00),
('cust1',   'Demo Customer', '01700000002', 'customer@dps.com', NULL, NULL, 'customer', 'approved', '123456', 4200.00);

INSERT INTO bill_providers (name, provider_code) VALUES
('Electricity Co', 'ELEC'),
('Water Authority', 'WATER'),
('Internet Provider', 'NET');
