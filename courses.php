<?php
require_once __DIR__ . '/php/functions.php';
$lang = get_lang();
$db = getDB();

// handle enrollment post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll_course_id'])) {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
        $_SESSION['flash'] = t('flash_login_required');
        header('Location: /art-school-website/login.php');
        exit;
    }
    $course_id = intval($_POST['enroll_course_id']);
    $user_id = $_SESSION['user']['id'];
    if (enroll_course($user_id, $course_id)) {
        $_SESSION['flash'] = t('flash_enroll_success');
    } else {
        $_SESSION['flash'] = t('flash_enroll_error');
    }
    header('Location: /art-school-website/courses.php');
    exit;
}

// pull courses from DB
$courses = [];
$res = $db->query('SELECT c.id, c.title, c.slug, c.description, u.name AS teacher_name FROM courses c LEFT JOIN users u ON c.teacher_id = u.id ORDER BY c.id');
if ($res) {
    while ($r = $res->fetch_assoc()) $courses[] = $r;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="<?php echo $lang === 'am' ? 'am' : 'en'; ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo t('nav_programs'); ?> - <?php echo t('site_title'); ?></title>
    <link rel="stylesheet" href="/art-school-website/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Inter:wght@100..900&display=swap" rel="stylesheet">
</head>
<body class="smooth-scroll">

<header class="site-header scrolled">
    <div class="container header-inner">
        <a href="/art-school-website/index.php" class="brand">
            <img src="images/logo.png" alt="<?php echo t('site_title'); ?>" class="logo">
            <span class="site-name"><?php echo t('site_title'); ?></span>
        </a>
        <nav class="nav">
            <a href="/art-school-website/index.php"><?php echo t('home'); ?></a>
            <a href="/art-school-website/courses.php"><?php echo t('nav_programs'); ?></a>
            <div class="nav-divider"></div>
            <div class="lang-nav">
                <a href="?lang=en" class="lang-btn <?php echo $lang === 'en' ? 'active' : ''; ?>">EN</a>
                <a href="?lang=am" class="lang-btn <?php echo $lang === 'am' ? 'active' : ''; ?>">አማ</a>
            </div>
            <div class="nav-divider"></div>
            <?php if (empty(current_user())): ?>
                <a href="/art-school-website/login.php" class="nav-login"><?php echo t('login'); ?></a>
            <?php else: ?>
                <a href="/art-school-website/logout.php" class="nav-login"><?php echo t('logout'); ?></a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container" style="padding-top: 140px;">
    <div class="section-header">
        <span class="eyebrow"><?php echo t('academic_catalog'); ?></span>
        <h2><?php echo t('courses_catalog_title'); ?></h2>
    </div>

    <?php if ($flash): ?><div class="flash"><?php echo htmlspecialchars($flash); ?></div><?php endif; ?>

    <div class="programs-grid">
        <?php
        $course_images = [
            'Traditional Painting' => 'traditional_painting.png',
            'Traditional Ethiopian Painting' => 'traditional_painting.png',
            'Calligraphy' => 'calligraphy.png',
            'Ethiopian Calligraphy' => 'calligraphy.png',
            'Sculpture' => 'sculpture.png',
            'Digital Art' => 'digital_art.png',
            'Fashion & Textile Art' => 'fashion_textile.png',
        ];
        foreach ($courses as $c): ?>
            <article class="program-card reveal active">
                <?php
                $img_file = 'logo.png';
                foreach ($course_images as $key => $val) {
                    if (stripos($c['title'], $key) !== false) {
                        $img_file = $val;
                        break;
                    }
                }
                ?>
                <div class="card-media">
                    <img src="images/<?php echo htmlspecialchars($img_file); ?>" alt="<?php echo htmlspecialchars($c['title']); ?>">
                </div>
                <div class="card-info">
                    <h3><?php echo htmlspecialchars($c['title']); ?></h3>
                    <p class="muted" style="margin-bottom: 8px;"><?php echo t('instructor'); ?>: <?php echo htmlspecialchars($c['teacher_name'] ?? 'TBD'); ?></p>
                    <p style="margin-bottom: 24px;"><?php echo htmlspecialchars($c['description']); ?></p>
                    
                    <?php if (!empty($_SESSION['user']) && $_SESSION['user']['role'] === 'student'): ?>
                        <?php if (is_enrolled($_SESSION['user']['id'], $c['id'])): ?>
                            <span class="btn outline-btn" style="color: var(--accent); border-color: var(--accent); width: 100%; text-align: center;"><?php echo t('enrolled'); ?></span>
                        <?php else: ?>
                            <form method="post">
                                <input type="hidden" name="enroll_course_id" value="<?php echo $c['id']; ?>">
                                <button class="btn premium-btn" type="submit" style="width: 100%;"><?php echo t('enroll_now'); ?></button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="/art-school-website/login.php" class="btn premium-btn" style="width: 100%; text-align: center;"><?php echo t('login_to_enroll'); ?></a>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</main>

<footer class="minimal-footer">
    <div class="footer-bottom container">
        <p>&copy; <?php echo date('Y'); ?> <?php echo t('footer_copyright'); ?></p>
    </div>
</footer>

<script src="/art-school-website/js/main.js"></script>
</body>
</html>