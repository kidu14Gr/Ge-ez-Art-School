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
    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.style.colorScheme = 'dark';
            document.documentElement.classList.add('dark-mode-pending');
        }
    </script>
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

            <div class="input-group">
                <input id="confirm_password" name="confirm_password" type="password" placeholder=" " required>
                <label for="confirm_password"><?php echo t('confirm_password'); ?></label>
                <span id="password_match_status" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-weight: bold; color: #ff4d4d;">✘</span>
            </div>

            <input type="hidden" name="role" value="student">

            <button type="submit" class="btn premium-btn" style="width:100%;"><?php echo t('create_account'); ?></button>
            <p style="text-align: center; margin-top: 20px; font-size: 0.9rem; color: #666;">
                <?php echo t('already_have_account'); ?> <a href="/art-school-website/login.php" style="color: var(--accent); font-weight: 600;"><?php echo t('login_here'); ?></a>
            </p>
        </form>
    </div>
</div>
<script src="/art-school-website/js/main.js"></script>
<script>
    const password = document.getElementById('password');
    const confirm_password = document.getElementById('confirm_password');
    const status = document.getElementById('password_match_status');
    const form = document.getElementById('registerForm');

    function checkMatch() {
        if (confirm_password.value === '') {
            status.textContent = '✘';
            status.style.color = '#ff4d4d';
        } else if (password.value === confirm_password.value) {
            status.textContent = '✔';
            status.style.color = '#2ecc71';
        } else {
            status.textContent = '✘';
            status.style.color = '#ff4d4d';
        }
    }

    password.addEventListener('input', checkMatch);
    confirm_password.addEventListener('input', checkMatch);

    form.addEventListener('submit', function(e) {
        if (password.value !== confirm_password.value) {
            e.preventDefault();
            alert('Passwords do not match!');
        }
    });
</script>
</body>
</html>