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


INSERT INTO users (name, email, password, bio, department, semester, role) VALUES
('Admin',         'admin@skillbridge.com', 'password', 'Platform admin', 'Administration', 'N/A', 'admin'),
('Anika Islam',   'anika@example.com',     'password', 'Passionate about web dev and UI/UX design.', 'Computer Science', '6th', 'student'),
('Rafiq Hossain', 'rafiq@example.com',     'password', 'Data science enthusiast and Python lover.', 'CSE', '4th', 'student'),
('Mitu Akter',    'mitu@example.com',      'password', 'Graphic designer & photography lover.', 'Fine Arts', '3rd', 'student'),
('Tanvir Ahmed',  'tanvir@example.com',    'password', 'Mobile app developer, Flutter enthusiast.', 'Software Eng', '7th', 'student'),
('Nadia Rahman',  'nadia@example.com',     'password', 'English tutor and content writer.', 'English', '5th', 'student');

INSERT INTO skills (user_id, title, description, category, tags, skill_type) VALUES
(2, 'Web Development with HTML, CSS & JS',     'I can teach you building websites from scratch. Covers HTML5, CSS3, Flexbox, Grid, and vanilla JavaScript. Perfect for beginners.', 'Programming',  'html,css,javascript,web',      'offer'),
(2, 'React.js Basics to Intermediate',         'Learn React from components to hooks. I will guide you through projects and real-world examples.',                                   'Programming',  'react,javascript,frontend',    'offer'),
(3, 'Python & Data Analysis',                  'Tutoring in Python basics, pandas, matplotlib, and data cleaning. Great for beginners into data science.',                          'Data Science', 'python,pandas,data,analysis',  'offer'),
(4, 'Graphic Design with Canva & Figma',       'Help you design logos, social media posts, and UI mockups using Canva and Figma. Beginner friendly.',                              'Design',       'canva,figma,design,ui',        'offer'),
(5, 'Flutter Mobile App Development',          'Sessions on building cross-platform mobile apps with Flutter and Dart. From UI to API integration.',                               'Mobile',       'flutter,dart,mobile,android',  'offer'),
(6, 'Creative Writing & Blogging',             'Effective English writing, blog post structure, SEO basics, and content strategy.',                                                'Language',     'writing,english,blog,content', 'offer'),
(3, 'Looking for Photography Basics Tutor',    'I want to learn DSLR photography — composition, lighting, and editing. Anyone who can teach please connect!',                     'Photography',  'photography,dslr,editing',     'request'),
(5, 'Need help with Machine Learning basics',  'Looking for someone to guide me through ML algorithms, scikit-learn, and model evaluation.',                                       'Data Science', 'ml,machine-learning,python',   'request');

INSERT INTO reviews (reviewer_id, reviewed_id, skill_id, rating, comment) VALUES
(3, 2, 1, 5, 'Anika explained everything so clearly. Highly recommend!'),
(4, 2, 2, 4, 'Great sessions on React. Very patient teacher.'),
(2, 3, 3, 5, 'Rafiq is an excellent Python tutor. Learned a lot!'),
(6, 4, 4, 5, 'Mitu is super creative and explains Figma beautifully.');

INSERT INTO requests (skill_id, sender_id, message, status) VALUES
(1, 3, 'Hi! I am very interested in learning web development from you. Can we connect?', 'pending'),
(3, 2, 'Would love to learn Python from you!', 'accepted');

INSERT INTO notifications (user_id, message, link) VALUES
(2, 'Rafiq expressed interest in your Web Development skill!', 'pages/skills/view.php?id=1'),
(3, 'New review posted on your Python skill.', 'pages/skills/view.php?id=3');
