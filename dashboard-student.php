<?php
require_once __DIR__ . '/php/functions.php';
require_role('student');
$lang = get_lang();
$user = current_user();
$db = getDB();

$enrolled = [];
$stmt = $db->prepare('SELECT c.id, c.title, c.description, u.name AS teacher_name, e.created_at FROM enrollments e JOIN courses c ON e.course_id = c.id LEFT JOIN users u ON c.teacher_id = u.id WHERE e.user_id = ?');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) $enrolled[] = $r;
$stmt->close();
?>
<!doctype html>
<html lang="<?php echo $lang === 'am' ? 'am' : 'en'; ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo t('dash_student_title'); ?> - <?php echo t('site_title'); ?></title>
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
            <a href="/art-school-website/index.php"><?php echo t('home'); ?></a>
            <a href="/art-school-website/courses.php"><?php echo t('nav_programs'); ?></a>
            <div class="nav-divider"></div>
            <div class="lang-nav">
                <a href="?lang=en" class="lang-btn <?php echo $lang === 'en' ? 'active' : ''; ?>">EN</a>
                <a href="?lang=am" class="lang-btn <?php echo $lang === 'am' ? 'active' : ''; ?>">አማ</a>
            </div>
            <div class="nav-divider"></div>
            <a href="/art-school-website/logout.php" class="nav-login"><?php echo t('logout'); ?></a>
        </nav>
    </div>
</header>

<main class="container">
    <div class="dashboard-grid">
        <!-- Sidebar -->
        <aside class="sidebar-card reveal active">
            <div class="user-avatar">
                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
            </div>
            <div style="text-align: center;">
                <h3 style="margin-bottom: 5px;"><?php echo htmlspecialchars($user['name']); ?></h3>
                <p class="muted" style="font-size: 0.85rem;"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            
            <nav class="sidebar-nav">
                <a href="#" class="active"><?php echo t('overview'); ?></a>
                <a href="/art-school-website/courses.php"><?php echo t('explore_courses'); ?></a>
                <a href="#"><?php echo t('my_assignments'); ?></a>
                <a href="#"><?php echo t('studio_bookings'); ?></a>
                <a href="#"><?php echo t('settings'); ?></a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="dashboard-content">
            <h2 style="margin-bottom: 30px;"><?php echo t('welcome_back'); ?>, <?php echo explode(' ', $user['name'])[0]; ?>!</h2>
            
            <!-- Stats -->
            <div class="stats-row">
                <div class="stat-card reveal active">
                    <h4><?php echo t('courses'); ?></h4>
                    <div class="value"><?php echo count($enrolled); ?></div>
                </div>
                <div class="stat-card reveal active" style="transition-delay: 0.1s;">
                    <h4><?php echo t('notifications'); ?></h4>
                    <div class="value">3</div>
                </div>
                <div class="stat-card reveal active" style="transition-delay: 0.2s;">
                    <h4><?php echo t('art_points'); ?></h4>
                    <div class="value">120</div>
                </div>
            </div>

            <!-- Enrolled Courses -->
            <div class="content-card reveal active" style="transition-delay: 0.3s;">
                <h3 style="margin-bottom: 20px;"><?php echo t('my_active_programs'); ?></h3>
                <?php if (empty($enrolled)): ?>
                    <p class="muted"><?php echo t('no_enrollments'); ?> <a href="/art-school-website/courses.php" style="color: var(--accent); font-weight: 600;"><?php echo t('start_exploring'); ?></a>.</p>
                <?php else: ?>
                    <ul class="dashboard-list">
                        <?php foreach ($enrolled as $c): ?>
                            <li>
                                <div class="course-meta">
                                    <span style="font-weight: 700; font-size: 1.1rem;"><?php echo htmlspecialchars($c['title']); ?></span>
                                    <span class="teacher"><?php echo t('lead_by'); ?> <?php echo htmlspecialchars($c['teacher_name'] ?? 'Senior Mentor'); ?></span>
                                </div>
                                <a href="#" class="btn premium-btn" style="padding: 8px 16px; font-size: 0.8rem;"><?php echo t('continue_learning'); ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script src="/art-school-website/js/main.js"></script>
</body>
</html>