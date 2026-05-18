

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
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id      INT UNSIGNED NOT NULL,
  course_id    INT UNSIGNED NOT NULL,
  registered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_course (user_id, course_id),
  FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed: default admin account (password: Admin@1234)
INSERT IGNORE INTO users (first_name, last_name, email, student_id, password_hash, role)
VALUES (
  'Admin', 'Greenfield',
  'admin@greenfield.edu',
  'ADM000-0000/2024',
  '$2y$12$YKdGkB7VNjBm6cPjFz1nAuHl4J5wJ3R2wD8EqVlMmRpFXnB0aGnAe',
  'admin'
);

-- Seed: sample courses
INSERT IGNORE INTO courses (title, code, department, instructor, credits, capacity) VALUES
  ('Environmental Biology',  'SCI-101', 'Science',     'Dr. Amara Nkosi',  3, 25),
  ('Web Development I',      'TEC-201', 'Technology',  'Dr. Lena Kimura',  4, 30),
  ('Applied Statistics',     'MAT-301', 'Mathematics', 'Prof. James Osei', 3, 25),
  ('Digital Media Design',   'ART-101', 'Arts',        'Ms. Priya Shah',   2, 20),
  ('Business Analytics',     'BUS-401', 'Business',    'Dr. Kofi Mensah',  3, 20);