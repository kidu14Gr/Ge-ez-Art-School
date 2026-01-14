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

// Create tables if they don't exist
$users = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(200) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student','teacher','admin') NOT NULL DEFAULT 'student',
    status ENUM('active','inactive','pending') NOT NULL DEFAULT 'active',
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$db->query($users);

$courses = "CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    syllabus TEXT,
    materials TEXT,
    teacher_id INT,
    status ENUM('active','draft','archived','pending') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$db->query($courses);

$enrollments = "CREATE TABLE IF NOT EXISTS enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    status ENUM('active','completed','dropped') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$db->query($enrollments);

$submissions = "CREATE TABLE IF NOT EXISTS submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    title VARCHAR(200),
    content TEXT,
    file_path VARCHAR(255),
    feedback TEXT,
    grade VARCHAR(10),
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$db->query($submissions);

$artworks = "CREATE TABLE IF NOT EXISTS artworks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    title VARCHAR(200),
    image_path VARCHAR(255),
    status ENUM('pending','approved','featured') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$db->query($artworks);

$announcements = "CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    target_role ENUM('all','student','teacher') NOT NULL DEFAULT 'all',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$db->query($announcements);

$events = "CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATETIME NOT NULL,
    location VARCHAR(200),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$db->query($events);

// Seed sample data
$user_check = $db->query("SELECT id FROM users LIMIT 1");
if ($user_check && $user_check->num_rows === 0) {
    $password_admin = password_hash('adminpass', PASSWORD_DEFAULT);
    $password_teacher = password_hash('teacherpass', PASSWORD_DEFAULT);
    $password_student = password_hash('studentpass', PASSWORD_DEFAULT);

    // Admin
    $stmt = $db->prepare('INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)');
    $name = 'System Admin'; $email = 'admin@example.com'; $role = 'admin'; $status = 'active';
    $stmt->bind_param('sssss', $name, $email, $password_admin, $role, $status);
    $stmt->execute();

    // Teacher
    $name = 'Demo Teacher'; $email = 'teacher@example.com'; $role = 'teacher'; $status = 'active';
    $stmt->bind_param('sssss', $name, $email, $password_teacher, $role, $status);
    $stmt->execute();
    $teacher_id = $db->insert_id;

    // Student
    $name = 'Demo Student'; $email = 'student@example.com'; $role = 'student'; $status = 'active';
    $stmt->bind_param('sssss', $name, $email, $password_student, $role, $status);
    $stmt->execute();
    $student_id = $db->insert_id;
    $stmt->close();
    echo "Created sample users.\n";
} else {
    $admin = $db->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1")->fetch_assoc();
    $teacher = $db->query("SELECT id FROM users WHERE role = 'teacher' LIMIT 1")->fetch_assoc();
    $student = $db->query("SELECT id FROM users WHERE role = 'student' LIMIT 1")->fetch_assoc();
    $teacher_id = $teacher['id'] ?? null;
    $student_id = $student['id'] ?? null;
}

// Courses
$course_check = $db->query("SELECT id FROM courses LIMIT 1");
if ($course_check && $course_check->num_rows === 0 && $teacher_id) {
    $courses_data = [
        ['Traditional Ethiopian Painting', 'traditional-ethiopian-painting', 'Learn iconographic and traditional painting techniques.', 'Painting', 'active'],
        ['Ethiopian Calligraphy', 'ethiopian-calligraphy', 'Study the rhythm of the script and illuminated manuscripts.', 'Calligraphy', 'active'],
        ['Sculpture', 'sculpture', 'Hands-on material and form exploration.', 'Sculpture', 'pending'],
        ['Digital Art', 'digital-art', 'Modern techniques with Ethiopian motifs.', 'Digital Art', 'draft'],
        ['Ethiopian Fashion & Textile Art', 'fashion-textile', 'Weaving, textile patterns, and fashion design.', 'Fashion', 'archived']
    ];
    $stmt = $db->prepare('INSERT INTO courses (title, slug, description, category, teacher_id, status) VALUES (?, ?, ?, ?, ?, ?)');
    foreach ($courses_data as $c) {
        $stmt->bind_param('ssssis', $c[0], $c[1], $c[2], $c[3], $teacher_id, $c[4]);
        $stmt->execute();
    }
    $stmt->close();
    echo "Created sample courses.\n";
}

// Enrollments
$enr_check = $db->query("SELECT id FROM enrollments LIMIT 1");
if ($enr_check && $enr_check->num_rows === 0 && $student_id) {
    $course_ids = [];
    $res = $db->query("SELECT id FROM courses WHERE status = 'active'");
    while($row = $res->fetch_assoc()) $course_ids[] = $row['id'];
    
    if (!empty($course_ids)) {
        $stmt = $db->prepare('INSERT INTO enrollments (user_id, course_id, status) VALUES (?, ?, "active")');
        foreach ($course_ids as $cid) {
            $stmt->bind_param('ii', $student_id, $cid);
            $stmt->execute();
        }
        $stmt->close();
    }
    echo "Created sample enrollments.\n";
}

// Artworks
$art_check = $db->query("SELECT id FROM artworks LIMIT 1");
if ($art_check && $art_check->num_rows === 0 && $student_id) {
    $artworks_data = [
        ['Ancient Wisdom', 'images/gallary1.png', 'approved'],
        ['Modern Ge\'ez', 'images/gallary2.png', 'pending']
    ];
    $stmt = $db->prepare('INSERT INTO artworks (student_id, title, image_path, status) VALUES (?, ?, ?, ?)');
    foreach ($artworks_data as $a) {
        $stmt->bind_param('isss', $student_id, $a[0], $a[1], $a[2]);
        $stmt->execute();
    }
    $stmt->close();
    echo "Created sample artworks.\n";
}

// Announcements
$ann_check = $db->query("SELECT id FROM announcements LIMIT 1");
if ($ann_check && $ann_check->num_rows === 0) {
    $ann_data = [
        ['Welcome to the New Semester', 'We are excited to welcome all new students to the academy.', 'all'],
        ['Teacher Meeting', 'Monthly staff meeting this Friday at 4 PM.', 'teacher']
    ];
    $stmt = $db->prepare('INSERT INTO announcements (title, content, target_role) VALUES (?, ?, ?)');
    foreach ($ann_data as $ann) {
        $stmt->bind_param('sss', $ann[0], $ann[1], $ann[2]);
        $stmt->execute();
    }
    $stmt->close();
    echo "Created sample announcements.\n";
}

// Events
$ev_check = $db->query("SELECT id FROM events LIMIT 1");
if ($ev_check && $ev_check->num_rows === 0) {
    $events_data = [
        ['Annual Art Exhibition', 'Showcasing the best works of our students.', date('Y-m-d H:i:s', strtotime('+1 month')), 'Main Gallery'],
        ['Calligraphy Workshop', 'Introduction to Ge\'ez manuscript illumination.', date('Y-m-d H:i:s', strtotime('+2 weeks')), 'Room 102']
    ];
    $stmt = $db->prepare('INSERT INTO events (title, description, event_date, location) VALUES (?, ?, ?, ?)');
    foreach ($events_data as $e) {
        $stmt->bind_param('ssss', $e[0], $e[1], $e[2], $e[3]);
        $stmt->execute();
    }
    $stmt->close();
    echo "Created sample events.\n";
}

echo "Setup script finished.\n";

$db->close();
?>