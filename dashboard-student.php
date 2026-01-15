<?php
require_once __DIR__ . '/php/functions.php';
require_role('student');
$lang = get_lang();
$db = getDB();

// Fetch fresh user data including avatar
$stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$stmt->bind_param('i', $_SESSION['user']['id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Update session with fresh avatar
$_SESSION['user']['avatar'] = $user['avatar'];

$enrolled = [];
$stmt = $db->prepare('SELECT c.id, c.title, c.description, u.name AS teacher_name, e.created_at FROM enrollments e JOIN courses c ON e.course_id = c.id LEFT JOIN users u ON c.teacher_id = u.id WHERE e.user_id = ?');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) $enrolled[] = $r;
$stmt->close();

// Fetch Announcements
$announcements = [];
$res = $db->query("SELECT title, content, created_at FROM announcements WHERE target_role IN ('all', 'student') ORDER BY created_at DESC LIMIT 3");
while ($r = $res->fetch_assoc()) $announcements[] = $r;

// Fetch Events
$events = [];
$res = $db->query("SELECT title, description, event_date, location FROM events WHERE event_date >= NOW() ORDER BY event_date ASC LIMIT 2");
while ($r = $res->fetch_assoc()) $events[] = $r;

// Handle Artwork Upload
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_artwork'])) {
    $title = $_POST['title'];
    // Move uploaded file
    $target_dir = __DIR__ . '/images/gallery/';
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    
    $file_ext = strtolower(pathinfo($_FILES['artwork']['name'], PATHINFO_EXTENSION));
    $filename = uniqid('art_') . '.' . $file_ext;
    $target_file = $target_dir . $filename;
    $db_path = 'images/gallery/' . $filename;

    if (move_uploaded_file($_FILES['artwork']['tmp_name'], $target_file)) {
        $stmt = $db->prepare('INSERT INTO artworks (student_id, title, image_path) VALUES (?, ?, ?)');
        $stmt->bind_param('iss', $user['id'], $title, $db_path);
        if ($stmt->execute()) {
            $message = "Artwork submitted for approval!";
        }
        $stmt->close();
    } else {
        $message = "Error uploading file.";
    }
}
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
    <?php if ($message): ?>
        <div class="flash"><?php echo $message; ?></div>
    <?php endif; ?>
    <div class="dashboard-grid">
        <!-- Sidebar -->
        <aside class="sidebar-card reveal active">
            <div class="user-avatar" style="overflow: hidden;">
                <?php if (!empty($user['avatar'])): ?>
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
                <a href="#" class="active"><?php echo t('overview'); ?></a>
                <a href="/art-school-website/profile-student.php"><?php echo t('profile_settings'); ?></a>
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
                    <h4><?php echo t('submissions'); ?></h4>
                    <div class="value">
                        <?php echo $db->query("SELECT COUNT(*) FROM submissions s JOIN enrollments e ON s.enrollment_id = e.id WHERE e.user_id = " . $user['id'])->fetch_row()[0]; ?>
                    </div>
                </div>
                <div class="stat-card reveal active" style="transition-delay: 0.2s;">
                    <h4>My Artworks</h4>
                    <div class="value">
                        <?php echo $db->query("SELECT COUNT(*) FROM artworks WHERE student_id = " . $user['id'])->fetch_row()[0]; ?>
                    </div>
                </div>
            </div>

            <div class="grid-2">
                <div class="left-col">
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

                    <!-- Upload Artwork -->
                    <div class="content-card reveal active" style="margin-top: 20px;">
                        <h3>Submit to Gallery</h3>
                        <form method="POST" enctype="multipart/form-data" style="margin-top: 15px;">
                            <input type="hidden" name="upload_artwork" value="1">
                            <div class="input-group">
                                <input type="text" name="title" required placeholder=" ">
                                <label>Artwork Title</label>
                            </div>
                            <div class="input-group">
                                <input type="file" name="artwork" required>
                            </div>
                            <button type="submit" class="btn premium-btn">Upload for Approval</button>
                        </form>
                    </div>
                </div>

                <div class="right-col">
                    <!-- Announcements -->
                    <div class="content-card reveal active">
                        <h3><?php echo t('announcements'); ?></h3>
                        <ul class="dashboard-list">
                            <?php foreach ($announcements as $ann): ?>
                                <li>
                                    <div class="course-meta">
                                        <span style="font-weight: 700;"><?php echo htmlspecialchars($ann['title']); ?></span>
                                        <p style="font-size: 0.85rem; margin-top: 5px;"><?php echo htmlspecialchars($ann['content']); ?></p>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Upcoming Events -->
                    <div class="content-card reveal active" style="margin-top: 20px;">
                        <h3>Upcoming Events</h3>
                        <ul class="dashboard-list">
                            <?php foreach ($events as $ev): ?>
                                <li>
                                    <div class="course-meta">
                                        <span style="font-weight: 700;"><?php echo htmlspecialchars($ev['title']); ?></span>
                                        <p style="font-size: 0.85rem; margin-top: 5px;">📅 <?php echo date('M d, H:i', strtotime($ev['event_date'])); ?><br>📍 <?php echo htmlspecialchars($ev['location']); ?></p>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="/art-school-website/js/main.js"></script>
</body>
</html>