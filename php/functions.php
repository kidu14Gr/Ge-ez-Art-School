<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/translations.php';

session_start();

function get_lang() {
    if (isset($_GET['lang'])) {
        $lang = $_GET['lang'];
        $_SESSION['lang'] = $lang;
    } elseif (isset($_SESSION['lang'])) {
        $lang = $_SESSION['lang'];
    } else {
        $lang = 'en';
    }
    return in_array($lang, ['en','am']) ? $lang : 'en';
}

function t($key) {
    global $TRANSLATIONS;
    $lang = get_lang();
    return $TRANSLATIONS[$lang][$key] ?? $key;
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function require_login() {
    if (empty($_SESSION['user'])) {
        header('Location: /art-school-website/login.php');
        exit;
    }
}

function require_role($role) {
    require_login();
    if ($_SESSION['user']['role'] !== $role) {
        http_response_code(403);
        echo "<h2>Access denied</h2>";
        exit;
    }
}

function enroll_course($user_id, $course_id) {
    $db = getDB();
    $stmt = $db->prepare('SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?');
    $stmt->bind_param('ii', $user_id, $course_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        return false; // already enrolled
    }
    $stmt->close();
    $stmt = $db->prepare('INSERT INTO enrollments (user_id, course_id, created_at) VALUES (?, ?, NOW())');
    $stmt->bind_param('ii', $user_id, $course_id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function is_enrolled($user_id, $course_id) {
    $db = getDB();
    $stmt = $db->prepare('SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?');
    $stmt->bind_param('ii', $user_id, $course_id);
    $stmt->execute();
    $stmt->store_result();
    $res = $stmt->num_rows > 0;
    $stmt->close();
    return $res;
}
?>