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
    <title><?php echo t('login'); ?> - <?php echo t('site_title'); ?></title>
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
        <h2><?php echo t('login'); ?></h2>
        <?php if ($flash): ?><div class="flash"><?php echo htmlspecialchars($flash); ?></div><?php endif; ?>
        <form id="loginForm" action="/art-school-website/php/auth.php" method="post" novalidate>
            <div class="input-group">
                <input id="email" name="email" type="email" placeholder=" " required>
                <label for="email"><?php echo t('email'); ?></label>
            </div>

            <div class="input-group">
                <input id="password" name="password" type="password" placeholder=" " required minlength="6">
                <label for="password"><?php echo t('password'); ?></label>
            </div>

            <div class="input-group">
                <select id="role" name="role" required>
                    <option value="" disabled selected hidden></option>
                    <option value="student"><?php echo t('role_student'); ?></option>
                    <option value="teacher"><?php echo t('role_teacher'); ?></option>
                    <option value="admin">Admin</option>
                </select>
                <label for="role"><?php echo t('role'); ?></label>
            </div>

            <button type="submit" class="btn premium-btn" style="width:100%;"><?php echo t('submit'); ?></button>
            <p style="text-align: center; margin-top: 20px; font-size: 0.9rem; color: #666;">
                <?php echo t('dont_have_account'); ?> <a href="/art-school-website/register.php" style="color: var(--accent); font-weight: 600;"><?php echo t('apply_here'); ?></a>
            </p>
        </form>
    </div>
</div>
<script src="/art-school-website/js/main.js"></script>
</body>
</html>