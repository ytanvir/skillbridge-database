CREATE DATABASE IF NOT EXISTS skillbridge;
USE skillbridge;

CREATE TABLE IF NOT EXISTS users (
  id  INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email  VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  bio  TEXT,
  department  VARCHAR(100),
  semester  VARCHAR(20),
  role ENUM('student','admin') DEFAULT 'student',
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );
