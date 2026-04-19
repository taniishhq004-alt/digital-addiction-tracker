-- ============================================================
-- Digital Addiction Tracking Database
-- DBMS Minor Project - BCA 2nd Semester
-- Jaypee Institute of Information Technology
-- ============================================================

CREATE DATABASE IF NOT EXISTS digital_addiction_db;
USE digital_addiction_db;

-- -------------------------------------------------------
-- TABLE 1: users
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    user_id     INT AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(100) NOT NULL,
    age         INT NOT NULL,
    age_group   VARCHAR(50) NOT NULL,
    occupation  VARCHAR(50) NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------
-- TABLE 2: categories
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    cat_id   INT AUTO_INCREMENT PRIMARY KEY,
    cat_name VARCHAR(50) NOT NULL,
    icon     VARCHAR(10) NOT NULL
);

-- -------------------------------------------------------
-- TABLE 3: usage_records
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS usage_records (
    record_id  INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    cat_id     INT NOT NULL,
    app_name   VARCHAR(100),
    usage_date DATE NOT NULL,
    hours_used DECIMAL(5,2) NOT NULL,
    note       VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (cat_id)  REFERENCES categories(cat_id) ON DELETE CASCADE
);

-- -------------------------------------------------------
-- TABLE 4: usage_limits
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS usage_limits (
    limit_id    INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    cat_id      INT NOT NULL,
    daily_limit DECIMAL(5,2) NOT NULL DEFAULT 0,
    UNIQUE KEY uq_user_cat (user_id, cat_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (cat_id)  REFERENCES categories(cat_id) ON DELETE CASCADE
);

-- -------------------------------------------------------
-- INSERT: categories (9-10 types)
-- -------------------------------------------------------
INSERT INTO categories (cat_name, icon) VALUES
('Social Media',  '📱'),
('Video / OTT',   '🎬'),
('Gaming',        '🎮'),
('Work / Study',  '💻'),
('News / Reading','📰'),
('Music / Audio', '🎵'),
('Shopping',      '🛍️'),
('Health & Fitness','🏃'),
('Messaging',     '💬'),
('Web Browsing',  '🌐');

-- -------------------------------------------------------
-- INSERT: sample users (your team)
-- -------------------------------------------------------
INSERT INTO users (full_name, age, age_group, occupation) VALUES
('Tanishq Gupta',  19, 'Young Adult (18-25)', 'Student'),
('Rajat Singh',    20, 'Young Adult (18-25)', 'Student'),
('Shrestha Tiwari',19, 'Young Adult (18-25)', 'Student'),
('Abhinav Rathore',20, 'Young Adult (18-25)', 'Student');

-- -------------------------------------------------------
-- INSERT: sample usage records (last 7 days)
-- -------------------------------------------------------
INSERT INTO usage_records (user_id, cat_id, app_name, usage_date, hours_used, note) VALUES
-- Tanishq
(1, 1, 'Instagram',   CURDATE() - INTERVAL 0 DAY, 3.5, 'Reels scrolling'),
(1, 2, 'YouTube',     CURDATE() - INTERVAL 0 DAY, 2.0, 'Music videos'),
(1, 3, 'BGMI',        CURDATE() - INTERVAL 0 DAY, 1.5, 'Evening session'),
(1, 9, 'WhatsApp',    CURDATE() - INTERVAL 0 DAY, 1.0, ''),
(1, 1, 'Instagram',   CURDATE() - INTERVAL 1 DAY, 2.8, ''),
(1, 2, 'Netflix',     CURDATE() - INTERVAL 1 DAY, 3.0, 'Web series'),
(1, 3, 'BGMI',        CURDATE() - INTERVAL 2 DAY, 2.5, ''),
(1, 4, 'Google Docs', CURDATE() - INTERVAL 2 DAY, 1.5, 'Assignment'),
(1, 1, 'Twitter',     CURDATE() - INTERVAL 3 DAY, 2.0, ''),
(1, 6, 'Spotify',     CURDATE() - INTERVAL 3 DAY, 1.5, ''),
(1, 2, 'YouTube',     CURDATE() - INTERVAL 4 DAY, 2.5, ''),
(1, 9, 'Telegram',    CURDATE() - INTERVAL 4 DAY, 0.8, ''),
(1, 3, 'Candy Crush', CURDATE() - INTERVAL 5 DAY, 1.0, ''),
(1, 10,'Chrome',      CURDATE() - INTERVAL 5 DAY, 2.0, 'Research'),
(1, 2, 'Prime Video', CURDATE() - INTERVAL 6 DAY, 2.0, ''),
(1, 1, 'Instagram',   CURDATE() - INTERVAL 6 DAY, 3.0, ''),
-- Rajat
(2, 1, 'Instagram',   CURDATE() - INTERVAL 0 DAY, 2.0, ''),
(2, 4, 'VS Code',     CURDATE() - INTERVAL 0 DAY, 4.0, 'Project work'),
(2, 2, 'Netflix',     CURDATE() - INTERVAL 0 DAY, 1.5, ''),
(2, 3, 'Free Fire',   CURDATE() - INTERVAL 0 DAY, 2.5, 'Gaming with friends'),
(2, 4, 'VS Code',     CURDATE() - INTERVAL 1 DAY, 5.0, 'Coding'),
(2, 2, 'YouTube',     CURDATE() - INTERVAL 1 DAY, 1.0, ''),
(2, 3, 'Free Fire',   CURDATE() - INTERVAL 2 DAY, 3.0, ''),
(2, 9, 'WhatsApp',    CURDATE() - INTERVAL 2 DAY, 0.5, ''),
(2, 1, 'Twitter',     CURDATE() - INTERVAL 3 DAY, 1.5, ''),
(2, 10,'Chrome',      CURDATE() - INTERVAL 3 DAY, 2.0, 'Browsing'),
(2, 2, 'Netflix',     CURDATE() - INTERVAL 4 DAY, 2.0, ''),
(2, 3, 'BGMI',        CURDATE() - INTERVAL 4 DAY, 2.0, ''),
(2, 4, 'Zoom',        CURDATE() - INTERVAL 5 DAY, 1.5, 'Online class'),
(2, 6, 'Spotify',     CURDATE() - INTERVAL 5 DAY, 1.0, ''),
(2, 1, 'Instagram',   CURDATE() - INTERVAL 6 DAY, 2.5, ''),
(2, 2, 'YouTube',     CURDATE() - INTERVAL 6 DAY, 1.5, ''),
-- Shrestha
(3, 4, 'Google Docs', CURDATE() - INTERVAL 0 DAY, 5.0, 'Assignment work'),
(3, 1, 'Twitter',     CURDATE() - INTERVAL 0 DAY, 1.5, ''),
(3, 6, 'Spotify',     CURDATE() - INTERVAL 0 DAY, 2.0, 'Study music'),
(3, 10,'Chrome',      CURDATE() - INTERVAL 0 DAY, 1.0, ''),
(3, 4, 'Google Docs', CURDATE() - INTERVAL 1 DAY, 4.5, ''),
(3, 2, 'YouTube',     CURDATE() - INTERVAL 1 DAY, 1.5, ''),
(3, 6, 'Spotify',     CURDATE() - INTERVAL 2 DAY, 2.5, ''),
(3, 1, 'Instagram',   CURDATE() - INTERVAL 2 DAY, 1.0, ''),
(3, 7, 'Amazon',      CURDATE() - INTERVAL 3 DAY, 0.8, 'Shopping'),
(3, 8, 'HealthifyMe', CURDATE() - INTERVAL 3 DAY, 0.5, 'Workout tracker'),
(3, 4, 'Notion',      CURDATE() - INTERVAL 4 DAY, 3.0, 'Notes'),
(3, 5, 'News18',      CURDATE() - INTERVAL 4 DAY, 1.0, ''),
(3, 2, 'Netflix',     CURDATE() - INTERVAL 5 DAY, 2.0, ''),
(3, 9, 'WhatsApp',    CURDATE() - INTERVAL 5 DAY, 1.5, ''),
(3, 1, 'Twitter',     CURDATE() - INTERVAL 6 DAY, 2.0, ''),
(3, 6, 'Spotify',     CURDATE() - INTERVAL 6 DAY, 2.5, ''),
-- Abhinav
(4, 3, 'Valorant',    CURDATE() - INTERVAL 0 DAY, 4.0, 'Ranked match'),
(4, 1, 'Instagram',   CURDATE() - INTERVAL 0 DAY, 2.0, ''),
(4, 2, 'YouTube',     CURDATE() - INTERVAL 0 DAY, 2.0, 'Gaming videos'),
(4, 9, 'Telegram',    CURDATE() - INTERVAL 0 DAY, 1.5, ''),
(4, 3, 'Valorant',    CURDATE() - INTERVAL 1 DAY, 5.0, ''),
(4, 1, 'Instagram',   CURDATE() - INTERVAL 1 DAY, 2.5, ''),
(4, 2, 'Netflix',     CURDATE() - INTERVAL 2 DAY, 3.0, ''),
(4, 3, 'BGMI',        CURDATE() - INTERVAL 2 DAY, 2.0, ''),
(4, 10,'Chrome',      CURDATE() - INTERVAL 3 DAY, 1.5, ''),
(4, 5, 'Times of India', CURDATE()-INTERVAL 3 DAY,1.0,''),
(4, 3, 'Valorant',    CURDATE() - INTERVAL 4 DAY, 4.5, ''),
(4, 9, 'WhatsApp',    CURDATE() - INTERVAL 4 DAY, 1.0, ''),
(4, 1, 'Twitter',     CURDATE() - INTERVAL 5 DAY, 1.5, ''),
(4, 2, 'YouTube',     CURDATE() - INTERVAL 5 DAY, 2.5, ''),
(4, 3, 'BGMI',        CURDATE() - INTERVAL 6 DAY, 3.0, ''),
(4, 6, 'Spotify',     CURDATE() - INTERVAL 6 DAY, 1.0, '');

-- -------------------------------------------------------
-- INSERT: usage limits
-- -------------------------------------------------------
INSERT INTO usage_limits (user_id, cat_id, daily_limit) VALUES
(1,1,3.0),(1,2,2.0),(1,3,2.0),
(2,1,2.0),(2,3,3.0),
(3,1,2.0),(3,2,2.0),
(4,3,3.0),(4,1,3.0),(4,2,3.0);

-- ============================================================
-- USEFUL SQL QUERIES (for viva / demonstration)
-- ============================================================

-- Q1: Total screen time per user
SELECT u.full_name, ROUND(SUM(r.hours_used),2) AS total_hours
FROM users u
JOIN usage_records r ON u.user_id = r.user_id
GROUP BY u.user_id, u.full_name
ORDER BY total_hours DESC;

-- Q2: Category-wise usage (all users)
SELECT c.icon, c.cat_name, ROUND(SUM(r.hours_used),2) AS total_hours
FROM categories c
JOIN usage_records r ON c.cat_id = r.cat_id
GROUP BY c.cat_id, c.cat_name, c.icon
ORDER BY total_hours DESC;

-- Q3: Today's usage per user
SELECT u.full_name, c.cat_name, r.app_name, r.hours_used
FROM usage_records r
JOIN users u ON r.user_id = u.user_id
JOIN categories c ON r.cat_id = c.cat_id
WHERE r.usage_date = CURDATE()
ORDER BY r.hours_used DESC;

-- Q4: Users who exceeded their daily limit today
SELECT u.full_name, c.cat_name,
       ROUND(SUM(r.hours_used),2) AS used_today,
       l.daily_limit
FROM usage_records r
JOIN users u   ON r.user_id = u.user_id
JOIN categories c ON r.cat_id = c.cat_id
JOIN usage_limits l ON r.user_id = l.user_id AND r.cat_id = l.cat_id
WHERE r.usage_date = CURDATE()
  AND l.daily_limit > 0
GROUP BY u.user_id, c.cat_id, l.daily_limit
HAVING used_today >= l.daily_limit;

-- Q5: Most addicted user (highest weekly screen time)
SELECT u.full_name, ROUND(SUM(r.hours_used),2) AS week_hours
FROM users u
JOIN usage_records r ON u.user_id = r.user_id
WHERE r.usage_date >= CURDATE() - INTERVAL 7 DAY
GROUP BY u.user_id
ORDER BY week_hours DESC
LIMIT 1;
