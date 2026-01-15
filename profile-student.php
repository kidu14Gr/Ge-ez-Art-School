<?php
require_once __DIR__ . '/php/functions.php';
require_role('student');

$lang = get_lang();
$user = current_user();
$db = getDB();
$message = '';

// Re-fetch user to get latest data
$stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? $user['name'];
    $bio = $_POST['bio'] ?? $user['bio'];
    $phone = $_POST['phone'] ?? $user['phone'];
    
    // Handle Avatar Upload
    $avatar_path = $user['avatar'];
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/images/avatars/';
        $upload_dir_web = 'images/avatars/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $new_filename = $user['id'] . '_' . time() . '.' . $file_ext;
        $target_path = $upload_dir . $new_filename;
        $web_path = $upload_dir_web . $new_filename;
        
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_path)) {
            $avatar_path = $web_path;
        }
    }

    $stmt = $db->prepare('UPDATE users SET name = ?, bio = ?, phone = ?, avatar = ? WHERE id = ?');
    $stmt->bind_param('ssssi', $name, $bio, $phone, $avatar_path, $user['id']);
    
    if ($stmt->execute()) {
        $message = t('profile_updated');
        // Update session
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['avatar'] = $avatar_path;
        // Refresh local user data
        $user['name'] = $name;
        $user['bio'] = $bio;
        $user['phone'] = $phone;
        $user['avatar'] = $avatar_path;
    } else {
        $message = "Error: " . $db->error;
    }
    $stmt->close();
}
?>
<!doctype html>
<html lang="<?php echo $lang === 'am' ? 'am' : 'en'; ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo t('profile_settings'); ?> - <?php echo t('site_title'); ?></title>
    <link rel="stylesheet" href="/art-school-website/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="dashboard-wrapper">

<header class="site-header scrolled">
    <div class="container header-inner">
        <a href="/art-school-website/index.php" class="brand">
            <img src="images/logo.png" alt="<?php echo t('site_title'); ?>" class="logo">
            <span class="site-name"><?php echo t('site_title'); ?></span>
        </a>
        <nav class="nav">
            <a href="/art-school-website/dashboard-student.php"><?php echo t('dashboard'); ?></a>
            <div class="nav-divider"></div>
            <div class="lang-nav">
                <a href="?lang=en" class="lang-btn <?php echo $lang === 'en' ? 'active' : ''; ?>">EN</a>
                <a href="?lang=am" class="lang-btn <?php echo $lang === 'am' ? 'active' : ''; ?>">አማ</a>
            </div>
            <div class="nav-divider"></div>
            <div class="theme-toggle" id="themeToggle">
                <button class="theme-toggle-btn active" data-theme="light">☀️</button>
                <button class="theme-toggle-btn" data-theme="dark">🌙</button>
            </div>
            <div class="nav-divider"></div>
            <a href="/art-school-website/logout.php" class="nav-login"><?php echo t('logout'); ?></a>
        </nav>
    </div>
</header>

<main class="container">
    <div class="dashboard-grid">
        <aside class="sidebar-card reveal active">
            <div class="user-avatar" style="overflow: hidden;">
                <?php if ($user['avatar']): ?>
                    <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                <?php endif; ?>
            </div>
            <div style="text-align: center;">
                <h3 style="margin-bottom: 5px;"><?php echo htmlspecialchars($user['name']); ?></h3>
                <p class="muted" style="font-size: 0.85rem;"><?php echo htmlspecialchars($user['email']); ?></p>
                <?php if (!empty($user['bio'])): ?>
                    <p class="muted" style="font-size: 0.8rem; margin-top: 8px; font-style: italic;"><?php echo htmlspecialchars($user['bio']); ?></p>
                <?php endif; ?>
            </div>
            
            <nav class="sidebar-nav">
                <a href="/art-school-website/dashboard-student.php"><?php echo t('overview'); ?></a>
                <a href="/art-school-website/profile-student.php" class="active"><?php echo t('profile_settings'); ?></a>
                <a href="/art-school-website/courses.php"><?php echo t('explore_courses'); ?></a>
                <a href="#"><?php echo t('my_assignments'); ?></a>
                <a href="#"><?php echo t('studio_bookings'); ?></a>
                <a href="#"><?php echo t('settings'); ?></a>
            </nav>
        </aside>

        <div class="dashboard-content">
            <div class="content-card reveal active">
                <h2><?php echo t('profile_settings'); ?></h2>
                
                <?php if ($message): ?>
                    <div class="flash"><?php echo $message; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="profile-form">
                    <div class="form-grid">
                        <div class="input-group">
                            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required placeholder=" ">
                            <label><?php echo t('full_name'); ?></label>
                        </div>
                        <div class="input-group">
                            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled placeholder=" ">
                            <label><?php echo t('email_address'); ?> (<?php echo t('uneditable'); ?>)</label>
                        </div>
                        <div class="input-group">
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder=" ">
                            <label><?php echo t('phone_number'); ?></label>
                        </div>
                        <div class="input-group">
                            <input type="file" name="avatar" accept="image/*">
                            <label><?php echo t('profile_picture'); ?></label>
                        </div>
                        <div class="input-group full-width">
                            <textarea name="bio" placeholder=" " rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                            <label><?php echo t('bio'); ?></label>
                        </div>
                    </div>
                    
                    <div style="margin-top: 30px;">
                        <button type="submit" class="btn premium-btn"><?php echo t('save_changes'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script src="/art-school-website/js/main.js"></script>
</body>
</html>
