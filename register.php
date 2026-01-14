<?php
require_once __DIR__ . '/php/functions.php';
$lang = get_lang();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="<?php echo $lang === 'am' ? 'am' : 'en'; ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo t('apply'); ?> - <?php echo t('site_title'); ?></title>
    <link rel="stylesheet" href="/art-school-website/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <a href="/art-school-website/index.php">
                <img src="images/logo.png" alt="<?php echo t('site_title'); ?>">
            </a>
        </div>
        <h2><?php echo t('apply_title'); ?></h2>
        <?php if ($flash): ?><div class="flash"><?php echo htmlspecialchars($flash); ?></div><?php endif; ?>
        <form id="registerForm" action="/art-school-website/php/auth.php?action=register" method="post" novalidate>
            <div class="input-group">
                <input id="name" name="name" type="text" placeholder=" " required>
                <label for="name"><?php echo t('full_name'); ?></label>
            </div>

            <div class="input-group">
                <input id="email" name="email" type="email" placeholder=" " required>
                <label for="email"><?php echo t('email_address'); ?></label>
            </div>

            <div class="input-group">
                <input id="password" name="password" type="password" placeholder=" " required minlength="6">
                <label for="password"><?php echo t('create_password'); ?></label>
            </div>

            <input type="hidden" name="role" value="student">

            <button type="submit" class="btn primary"><?php echo t('create_account'); ?></button>
            <p style="text-align: center; margin-top: 20px; font-size: 0.9rem; color: #666;">
                <?php echo t('already_have_account'); ?> <a href="/art-school-website/login.php" style="color: var(--accent); font-weight: 600;"><?php echo t('login_here'); ?></a>
            </p>
        </form>
    </div>
</div>
<script src="/art-school-website/js/main.js"></script>
</body>
</html>