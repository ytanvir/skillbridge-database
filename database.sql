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

-- Interest / Connection Requests
CREATE TABLE IF NOT EXISTS requests (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    skill_id     INT NOT NULL,
    sender_id    INT NOT NULL,
    message      TEXT,
    status       ENUM('pending','accepted','declined') DEFAULT 'pending',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (skill_id)  REFERENCES skills(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id)  ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reviewer_id INT NOT NULL,
    reviewed_id INT NOT NULL,
    skill_id INT NOT NULL,
    rating TINYINT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS notifications (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL,
    message      VARCHAR(255) NOT NULL,
    link         VARCHAR(255),
    is_read      TINYINT DEFAULT 0,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
