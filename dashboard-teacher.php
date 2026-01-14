<?php
require_once __DIR__ . '/php/functions.php';
require_role('teacher');
$lang = get_lang();
$user = current_user();
$db = getDB();

$courses = [];
$stmt = $db->prepare('SELECT id, title, description FROM courses WHERE teacher_id = ?');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) $courses[] = $r;
$stmt->close();

$total_students = 0;
foreach ($courses as $c) {
    $stmt = $db->prepare('SELECT COUNT(*) FROM enrollments WHERE course_id = ?');
    $stmt->bind_param('i', $c['id']);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $total_students += $count;
    $stmt->close();
}
?>
<!doctype html>
<html lang="<?php echo $lang === 'am' ? 'am' : 'en'; ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo t('dash_teacher_title'); ?> - <?php echo t('site_title'); ?></title>
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
            <div class="user-avatar" style="background: var(--highlight);">
                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
            </div>
            <div style="text-align: center;">
                <h3 style="margin-bottom: 5px;"><?php echo htmlspecialchars($user['name']); ?></h3>
                <p class="muted" style="font-size: 0.85rem;"><?php echo t('mentor'); ?> • <?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            
            <nav class="sidebar-nav">
                <a href="#" class="active"><?php echo t('teaching_overview'); ?></a>
                <a href="#"><?php echo t('my_schedule'); ?></a>
                <a href="#"><?php echo t('curriculum_manager'); ?></a>
                <a href="#"><?php echo t('student_feedback'); ?></a>
                <a href="#"><?php echo t('settings'); ?></a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="dashboard-content">
            <h2 style="margin-bottom: 30px;"><?php echo t('mentor_portal'); ?></h2>
            
            <!-- Stats -->
            <div class="stats-row">
                <div class="stat-card reveal active">
                    <h4><?php echo t('active_courses'); ?></h4>
                    <div class="value"><?php echo count($courses); ?></div>
                </div>
                <div class="stat-card reveal active" style="transition-delay: 0.1s;">
                    <h4><?php echo t('total_students'); ?></h4>
                    <div class="value"><?php echo $total_students; ?></div>
                </div>
                <div class="stat-card reveal active" style="transition-delay: 0.2s;">
                    <h4><?php echo t('weekly_hours'); ?></h4>
                    <div class="value">18</div>
                </div>
            </div>

            <!-- Teaching Courses -->
            <div class="content-card reveal active" style="transition-delay: 0.3s;">
                <h3 style="margin-bottom: 20px;"><?php echo t('my_programs'); ?></h3>
                <?php if (empty($courses)): ?>
                    <p class="muted"><?php echo t('no_assigned_courses'); ?></p>
                <?php else: ?>
                    <ul class="dashboard-list">
                        <?php foreach ($courses as $c): ?>
                            <li>
                                <div class="course-meta">
                                    <span style="font-weight: 700; font-size: 1.1rem;"><?php echo htmlspecialchars($c['title']); ?></span>
                                    <?php
                                    $stmt = $db->prepare('SELECT COUNT(*) FROM enrollments WHERE course_id = ?');
                                    $stmt->bind_param('i', $c['id']);
                                    $stmt->execute();
                                    $stmt->bind_result($s_count);
                                    $stmt->fetch();
                                    $stmt->close();
                                    ?>
                                    <span class="teacher"><?php echo $s_count; ?> <?php echo t('students_enrolled'); ?></span>
                                </div>
                                <div style="display: flex; gap: 10px;">
                                    <a href="#" class="btn outline-btn" style="padding: 8px 16px; font-size: 0.8rem; color: var(--text-main); border-color: #eee;"><?php echo t('edit'); ?></a>
                                    <a href="#" class="btn premium-btn" style="padding: 8px 16px; font-size: 0.8rem;"><?php echo t('view_class'); ?></a>
                                </div>
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