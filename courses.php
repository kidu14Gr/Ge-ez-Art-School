<?php
require_once __DIR__ . '/php/functions.php';
$lang = get_lang();
$db = getDB();

// Update session with fresh avatar for logged-in students
if (!empty($_SESSION['user']) && $_SESSION['user']['role'] === 'student') {
    $stmt = $db->prepare('SELECT avatar FROM users WHERE id = ?');
    $stmt->bind_param('i', $_SESSION['user']['id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $_SESSION['user']['avatar'] = $result['avatar'];
    $stmt->close();
}

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

// handle search and filters
$search = $_GET['q'] ?? '';
$category = $_GET['cat'] ?? '';

$sql = "SELECT c.id, c.title, c.slug, c.description, c.category, u.name AS teacher_name FROM courses c LEFT JOIN users u ON c.teacher_id = u.id WHERE c.status = 'active'";

if ($search) {
    $s = "%$search%";
    $sql .= " AND (c.title LIKE ? OR c.description LIKE ?)";
}
if ($category) {
    $sql .= " AND c.category = ?";
}
$sql .= " ORDER BY c.created_at DESC";

$stmt = $db->prepare($sql);
if ($search && $category) {
    $stmt->bind_param('sss', $s, $s, $category);
} elseif ($search) {
    $stmt->bind_param('ss', $s, $s);
} elseif ($category) {
    $stmt->bind_param('s', $category);
}
$stmt->execute();
$res = $stmt->get_result();
$courses = [];
while ($r = $res->fetch_assoc()) $courses[] = $r;
$stmt->close();

// Fetch categories for filter
$categories = [];
$cat_res = $db->query("SELECT DISTINCT category FROM courses WHERE category IS NOT NULL AND status = 'active'");
while($cr = $cat_res->fetch_assoc()) $categories[] = $cr['category'];

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
<body class="<?php echo (!empty($_SESSION['user']) && $_SESSION['user']['role'] === 'student') ? 'dashboard-wrapper' : 'smooth-scroll'; ?>">

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
            <div class="theme-toggle" id="themeToggle">
                <button class="theme-toggle-btn active" data-theme="light">☀️</button>
                <button class="theme-toggle-btn" data-theme="dark">🌙</button>
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

<main class="container" style="<?php echo (!empty($_SESSION['user']) && $_SESSION['user']['role'] === 'student') ? '' : 'padding-top: 140px;'; ?>">
    <?php if (!empty($_SESSION['user']) && $_SESSION['user']['role'] === 'student'): ?>
        <div class="dashboard-grid">
            <!-- Sidebar -->
            <aside class="sidebar-card reveal active">
                <div class="user-avatar" style="overflow: hidden;">
                    <?php if (!empty($_SESSION['user']['avatar'])): ?>
                        <img src="<?php echo htmlspecialchars($_SESSION['user']['avatar']); ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <?php echo strtoupper(substr($_SESSION['user']['name'], 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <div style="text-align: center;">
                    <h3 style="margin-bottom: 5px;"><?php echo htmlspecialchars($_SESSION['user']['name']); ?></h3>
                    <p class="muted" style="font-size: 0.85rem;"><?php echo htmlspecialchars($_SESSION['user']['email']); ?></p>
                    <?php 
                    $bio_stmt = $db->prepare('SELECT bio FROM users WHERE id = ?');
                    $bio_stmt->bind_param('i', $_SESSION['user']['id']);
                    $bio_stmt->execute();
                    $bio_result = $bio_stmt->get_result()->fetch_assoc();
                    $bio_stmt->close();
                    if (!empty($bio_result['bio'])): ?>
                        <p class="muted" style="font-size: 0.8rem; margin-top: 8px; font-style: italic;"><?php echo htmlspecialchars($bio_result['bio']); ?></p>
                    <?php endif; ?>
                </div>
                
                <nav class="sidebar-nav">
                    <a href="/art-school-website/dashboard-student.php"><?php echo t('overview'); ?></a>
                    <a href="/art-school-website/profile-student.php"><?php echo t('profile_settings'); ?></a>
                    <a href="/art-school-website/courses.php" class="active"><?php echo t('explore_courses'); ?></a>
                    <a href="#"><?php echo t('my_assignments'); ?></a>
                    <a href="#"><?php echo t('studio_bookings'); ?></a>
                    <a href="#"><?php echo t('settings'); ?></a>
                </nav>
            </aside>

            <!-- Main Content -->
            <div class="dashboard-content">
    <?php else: ?>
        <div style="padding-top: 140px;">
    <?php endif; ?>

    <div class="section-header">
        <span class="eyebrow"><?php echo t('academic_catalog'); ?></span>
        <h2><?php echo t('courses_catalog_title'); ?></h2>
    </div>

    <!-- Search and Filter Bar -->
    <div class="content-card" style="margin-bottom: 40px; padding: 25px;">
        <form method="GET" style="display: grid; grid-template-columns: 1fr auto auto; gap: 15px; align-items: end;">
            <input type="hidden" name="lang" value="<?php echo $lang; ?>">
            <div class="input-group" style="margin: 0;">
                <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder=" " style="padding-right: 40px;">
                <label>Search by title or keyword</label>
            </div>
            <div class="input-group" style="margin: 0;">
                <select name="cat" onchange="this.form.submit()">
                    <option value="" disabled selected></option>
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label>Category</label>
            </div>
            <button type="submit" class="btn premium-btn" style="height: 50px;">Apply</button>
        </form>
    </div>

    <?php if ($flash): ?><div class="flash"><?php echo htmlspecialchars($flash); ?></div><?php endif; ?>

    <div class="programs-grid">
        <?php if (empty($courses)): ?>
            <p class="muted" style="grid-column: 1/-1; text-align: center; padding: 40px;">No courses found matching your criteria.</p>
        <?php endif; ?>
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
                    <p style="margin-bottom: 12px;"><?php echo htmlspecialchars($c['description']); ?></p>
                    
                    <a href="/art-school-website/course-details.php?id=<?php echo $c['id']; ?>" class="link-btn" style="display:inline-block; margin-bottom: 15px; font-weight: 600; font-size: 0.85rem; color: var(--accent);"><?php echo t('view_details'); ?> →</a>

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

    <?php if (!empty($_SESSION['user']) && $_SESSION['user']['role'] === 'student'): ?>
            </div>
        </div>
    <?php else: ?>
        </div>
    <?php endif; ?>
</main>

<footer class="minimal-footer">
    <div class="footer-bottom container">
        <p>&copy; <?php echo date('Y'); ?> <?php echo t('footer_copyright'); ?></p>
    </div>
</footer>

<script src="/art-school-website/js/main.js"></script>
</body>
</html>