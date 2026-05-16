-- greenfield.sql
-- Run in phpMyAdmin or MySQL CLI: source greenfield.sql

CREATE DATABASE IF NOT EXISTS greenfield_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE greenfield_db;

CREATE TABLE IF NOT EXISTS users (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  first_name     VARCHAR(80)  NOT NULL,
  last_name      VARCHAR(80)  NOT NULL,
  email          VARCHAR(160) NOT NULL UNIQUE,
  student_id     VARCHAR(20)  NOT NULL UNIQUE,
  password_hash  VARCHAR(255) NOT NULL,
  role           ENUM('student','admin') NOT NULL DEFAULT 'student',
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS courses (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title        VARCHAR(200) NOT NULL,
  code         VARCHAR(20)  NOT NULL UNIQUE,
  department   VARCHAR(100) NOT NULL,
  instructor   VARCHAR(120) NOT NULL,
  credits      TINYINT UNSIGNED NOT NULL DEFAULT 3,
  capacity     SMALLINT UNSIGNED NOT NULL DEFAULT 30,
  enrolled     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  description  TEXT,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS registrations (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id       INT UNSIGNED NOT NULL,
  course_id     INT UNSIGNED NOT NULL,
  registered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_course (user_id, course_id),
  FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Default admin only: run php/install_admin.php once after import
-- Email: admin@greenfield.edu  Password: Admin@1234
