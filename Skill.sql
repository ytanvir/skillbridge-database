CREATE TABLE IF NOT EXISTS skills (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL,
    title        VARCHAR(150) NOT NULL,
    description  TEXT NOT NULL,
    category     VARCHAR(80) NOT NULL,
    tags         VARCHAR(255),
    skill_type   ENUM('offer','request') DEFAULT 'offer',
    status       ENUM('active','closed') DEFAULT 'active',
    views        INT DEFAULT 0,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
