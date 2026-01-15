<?php
require_once __DIR__ . '/php/functions.php';
require_login();

$lang = get_lang();
$user = current_user();
$db = getDB();

// Mark all as read
$db->query("UPDATE notifications SET is_read = 1 WHERE user_id = " . $user['id']);

$notifs = [];
$stmt = $db->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$res = $stmt->get_result();
while($row = $res->fetch_assoc()) $notifs[] = $row;
$stmt->close();
?>
<!doctype html>
<html lang="<?php echo $lang === 'am' ? 'am' : 'en'; ?>">
<head>
    <meta charset="utf-8">
    <title><?php echo t('notifications'); ?> - <?php echo t('site_title'); ?></title>
    <link rel="stylesheet" href="/art-school-website/css/style.css">
</head>
<body class="dashboard-wrapper">
<header class="site-header scrolled">
    <div class="container header-inner">
        <a href="/art-school-website/index.php" class="brand">
            <img src="images/logo.png" alt="Logo" class="logo">
            <span class="site-name"><?php echo t('site_title'); ?></span>
        </a>
        <nav class="nav">
            <a href="/art-school-website/dashboard-<?php echo $user['role']; ?>.php"><?php echo t('dashboard'); ?></a>
            <a href="/art-school-website/logout.php" class="nav-login"><?php echo t('logout'); ?></a>
        </nav>
    </div>
</header>

<main class="container" style="padding-top: 140px;">
    <div class="content-card reveal active">
        <h2><?php echo t('notifications'); ?></h2>
        
        <div class="notifications-list" style="margin-top: 30px;">
            <?php foreach ($notifs as $n): ?>
                <div class="notification-item <?php echo $n['is_read'] ? '' : 'unread'; ?>" style="padding: 20px; border-bottom: 1px solid #eee;">
                    <p style="margin:0;"><?php echo htmlspecialchars($n['message']); ?></p>
                    <small class="muted"><?php echo date('M d, Y H:i', strtotime($n['created_at'])); ?></small>
                </div>
            <?php endforeach; ?>
            <?php if (empty($notifs)): ?>
                <p class="muted"><?php echo t('no_notifications'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</main>
</body>
</html>
