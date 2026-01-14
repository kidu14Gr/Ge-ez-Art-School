<?php
// Run this file from browser or CLI to create DB schema and seed demo data.
require_once __DIR__ . '/db.php';
$db = new mysqli(DB_HOST, DB_USER, DB_PASS);
if ($db->connect_error) {
    die('Connection error: ' . $db->connect_error);
}

// Create database if not exists
$db->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$db->select_db(DB_NAME);

// Create tables
$users = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(200) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student','teacher') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$db->query($users);

$courses = "CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    description TEXT,
    teacher_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$db->query($courses);

$enrollments = "CREATE TABLE IF NOT EXISTS enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$db->query($enrollments);

// Seed sample users and courses if none exist
$res = $db->query('SELECT COUNT(*) AS c FROM users');
$count = 0;
if ($res) {
    $row = $res->fetch_assoc();
    $count = intval($row['c']);
}

if ($count === 0) {
    $password_student = password_hash('studentpass', PASSWORD_DEFAULT);
    $password_teacher = password_hash('teacherpass', PASSWORD_DEFAULT);

    $stmt = $db->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
    $name = 'Demo Student'; $email = 'student@example.com'; $role = 'student';
    $stmt->bind_param('ssss', $name, $email, $password_student, $role);
    $stmt->execute();

    $name = 'Demo Teacher'; $email = 'teacher@example.com'; $role = 'teacher';
    $stmt->bind_param('ssss', $name, $email, $password_teacher, $role);
    $stmt->execute();
    $stmt->close();

    // Insert courses assigned to teacher
    $teacher_id = $db->insert_id; // last inserted (teacher)
    $courses_data = [
        ['Traditional Ethiopian Painting', 'traditional-ethiopian-painting', 'Learn iconographic and traditional painting techniques.'],
        ['Ethiopian Calligraphy', 'ethiopian-calligraphy', 'Study the rhythm of the script and illuminated manuscripts.'],
        ['Sculpture', 'sculpture', 'Hands-on material and form exploration.'],
        ['Digital Art', 'digital-art', 'Modern techniques with Ethiopian motifs.'],
        ['Ethiopian Fashion & Textile Art', 'fashion-textile', 'Weaving, textile patterns, and fashion design.']
    ];
    $stmt = $db->prepare('INSERT INTO courses (title, slug, description, teacher_id) VALUES (?, ?, ?, ?)');
    foreach ($courses_data as $c) {
        $stmt->bind_param('sssi', $c[0], $c[1], $c[2], $teacher_id);
        $stmt->execute();
    }
    $stmt->close();

    echo "Setup complete. Created sample users and courses.\n";
} else {
    echo "Users already exist; skipping seed.\n";
}

echo "Database: " . DB_NAME . " ready.\n";

// Close
$db->close();
?>