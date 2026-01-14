<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Handle login/register POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? 'login';

    if ($action === 'register') {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'student';

        if (empty($name) || empty($email) || empty($password)) {
            $_SESSION['flash'] = t('flash_fill_fields');
            header('Location: /art-school-website/register.php');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = t('flash_invalid_email');
            header('Location: /art-school-website/register.php');
            exit;
        }

        $db = getDB();
        
        // Check if email already exists
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->fetch()) {
            $stmt->close();
            $_SESSION['flash'] = t('flash_email_exists');
            header('Location: /art-school-website/register.php');
            exit;
        }
        $stmt->close();

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $status = ($role === 'teacher') ? 'pending' : 'active';
        $stmt = $db->prepare('INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $name, $email, $hash, $role, $status);
        
        if ($stmt->execute()) {
            if ($status === 'pending') {
                $_SESSION['flash'] = "Account created! Please wait for admin approval.";
            } else {
                $_SESSION['flash'] = t('flash_account_created');
            }
            header('Location: /art-school-website/login.php');
        } else {
            $_SESSION['flash'] = t('flash_registration_failed');
            header('Location: /art-school-website/register.php');
        }
        $stmt->close();
        exit;
    }

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash'] = t('flash_invalid_email');
        header('Location: /art-school-website/login.php');
        exit;
    }

    $db = getDB();
    $stmt = $db->prepare('SELECT id, name, email, password, role, status FROM users WHERE email = ? AND role = ? LIMIT 1');
    $stmt->bind_param('ss', $email, $role);
    $stmt->execute();
    $stmt->bind_result($id, $name, $email_db, $hash, $role_db, $status_db);
    if ($stmt->fetch()) {
        if (password_verify($password, $hash)) {
            if ($status_db !== 'active') {
                $stmt->close();
                $_SESSION['flash'] = "Your account is " . $status_db . ". Please contact admin.";
                header('Location: /art-school-website/login.php');
                exit;
            }
            // Login success
            $_SESSION['user'] = [
                'id' => $id,
                'name' => $name,
                'email' => $email_db,
                'role' => $role_db
            ];
            $stmt->close();
            if ($role_db === 'student') {
                header('Location: /art-school-website/dashboard-student.php');
                exit;
            } elseif ($role_db === 'teacher') {
                header('Location: /art-school-website/dashboard-teacher.php');
                exit;
            } elseif ($role_db === 'admin') {
                header('Location: /art-school-website/dashboard-admin.php');
                exit;
            } else {
                header('Location: /art-school-website/index.php');
                exit;
            }
        }
    }
    $stmt->close();
    $_SESSION['flash'] = t('flash_invalid_credentials');
    header('Location: /art-school-website/login.php');
    exit;
}

http_response_code(405);
echo 'Method not allowed';
?>