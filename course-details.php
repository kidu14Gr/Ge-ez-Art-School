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

$course_id = $_GET['id'] ?? null;
if (!$course_id) {
    header('Location: /art-school-website/courses.php');
    exit;
}

$stmt = $db->prepare('SELECT c.*, u.name AS teacher_name, u.bio AS teacher_bio, u.avatar AS teacher_avatar FROM courses c LEFT JOIN users u ON c.teacher_id = u.id WHERE c.id = ?');
$stmt->bind_param('i', $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$course) {
    header('Location: /art-school-website/courses.php');
    exit;
}

// Enrollment logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll'])) {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
        header('Location: /art-school-website/login.php');
        exit;
    }
    if (enroll_course($_SESSION['user']['id'], $course_id)) {
        $_SESSION['flash'] = t('flash_enroll_success');
    }
    header('Location: /art-school-website/course-details.php?id=' . $course_id);
    exit;
}

// Comment logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_comment'])) {
    if (empty($_SESSION['user'])) {
        header('Location: /art-school-website/login.php');
        exit;
    }
    $content = $_POST['comment_content'];
    $stmt = $db->prepare('INSERT INTO comments (course_id, user_id, content) VALUES (?, ?, ?)');
    $stmt->bind_param('iis', $course_id, $_SESSION['user']['id'], $content);
    $stmt->execute();
    $stmt->close();
    header('Location: /art-school-website/course-details.php?id=' . $course_id . '#discussions');
    exit;
}

// Fetch comments
$comments = [];
$c_stmt = $db->prepare('SELECT c.*, u.name, u.avatar FROM comments c JOIN users u ON c.user_id = u.id WHERE c.course_id = ? ORDER BY c.created_at DESC');
$c_stmt->bind_param('i', $course_id);
$c_stmt->execute();
$c_res = $c_stmt->get_result();
while($row = $c_res->fetch_assoc()) $comments[] = $row;
$c_stmt->close();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="<?php echo $lang === 'am' ? 'am' : 'en'; ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo htmlspecialchars($course['title']); ?> - <?php echo t('site_title'); ?></title>
    <link rel="stylesheet" href="/art-school-website/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.style.colorScheme = 'dark';
            document.documentElement.classList.add('dark-mode-pending');
        }
    </script>
</head>
<body class="<?php echo (!empty($_SESSION['user']) && $_SESSION['user']['role'] === 'student') ? 'dashboard-wrapper' : 'smooth-scroll'; ?>">

<header class="site-header scrolled">
    <div class="container header-inner">
        <a href="/art-school-website/index.php" class="brand">
            <img src="images/logo.png" alt="Logo" class="logo">
            <span class="site-name"><?php echo t('site_title'); ?></span>
        </a>
        <nav class="nav">
            <a href="/art-school-website/index.php"><?php echo t('home'); ?></a>
            <a href="/art-school-website/courses.php"><?php echo t('nav_programs'); ?></a>
            <div class="nav-divider"></div>
            <div class="theme-toggle" id="themeToggle">
                <button class="theme-toggle-btn">🌙</button>
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
                    <a href="/art-school-website/courses.php"><?php echo t('explore_courses'); ?></a>
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

    <?php if ($flash): ?><div class="flash"><?php echo htmlspecialchars($flash); ?></div><?php endif; ?>

    <div class="course-header-banner reveal active">
        <div class="course-header-content">
            <span class="badge"><?php echo htmlspecialchars($course['category']); ?></span>
            <h1><?php echo htmlspecialchars($course['title']); ?></h1>
            <p class="large"><?php echo htmlspecialchars($course['description']); ?></p>
            
            <div class="course-meta-inline">
                <span>By <strong><?php echo htmlspecialchars($course['teacher_name'] ?? 'TBD'); ?></strong></span>
                <span class="divider">|</span>
                <span>Status: <strong><?php echo ucfirst($course['status']); ?></strong></span>
            </div>

            <div style="margin-top: 30px;">
                <?php if (!empty($_SESSION['user']) && $_SESSION['user']['role'] === 'student'): ?>
                    <?php if (is_enrolled($_SESSION['user']['id'], $course['id'])): ?>
                        <a href="/art-school-website/dashboard-student.php" class="btn premium-btn"><?php echo t('go_to_dashboard'); ?></a>
                    <?php else: ?>
                        <form method="post">
                            <button type="submit" name="enroll" class="btn premium-btn"><?php echo t('enroll_now'); ?></button>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="/art-school-website/login.php" class="btn premium-btn"><?php echo t('login_to_enroll'); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="grid-2-3" style="margin-top: 60px;">
        <div class="course-main-info">
            <section class="reveal active">
                <h3><?php echo t('syllabus'); ?></h3>
                <div class="content-text">
                    <?php echo nl2br(htmlspecialchars($course['syllabus'] ?? 'Syllabus content coming soon.')); ?>
                </div>
            </section>

            <section class="reveal active" style="margin-top: 40px;">
                <h3><?php echo t('required_materials'); ?></h3>
                <div class="content-text">
                    <?php echo nl2br(htmlspecialchars($course['materials'] ?? 'List of materials coming soon.')); ?>
                </div>
                <?php if ($course['material_path']): ?>
                    <div style="margin-top: 20px; padding: 20px; background: #f9f9f9; border-radius: 12px; display: flex; align-items: center; gap: 15px;">
                        <span style="font-size: 2rem;">📄</span>
                        <div>
                            <p style="margin: 0; font-weight: 600;"><?php echo t('download_material'); ?></p>
                            <a href="<?php echo htmlspecialchars($course['material_path']); ?>" class="btn premium-btn" style="padding: 5px 15px; font-size: 0.8rem; margin-top: 5px;" download>Download</a>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <aside class="course-sidebar">
            <div class="content-card reveal active">
                <h3><?php echo t('instructor'); ?></h3>
                <div class="instructor-preview">
                    <div class="user-avatar small">
                        <?php if ($course['teacher_avatar']): ?>
                            <img src="<?php echo htmlspecialchars($course['teacher_avatar']); ?>" alt="Teacher">
                        <?php else: ?>
                            <?php echo strtoupper(substr($course['teacher_name'] ?? 'T', 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <strong><?php echo htmlspecialchars($course['teacher_name'] ?? 'Senior Mentor'); ?></strong>
                    </div>
                </div>
                <p class="muted small" style="margin-top: 15px;">
                    <?php echo htmlspecialchars($course['teacher_bio'] ?? 'Dedicated mentor with years of experience in traditional and modern arts.'); ?>
                </p>
            </div>
            
            <div class="content-card reveal active" style="margin-top: 20px;">
                <h3>Course Features</h3>
                <ul class="check-list">
                    <li>Bilingual Instruction</li>
                    <li>Studio Support</li>
                    <li>Personalized Feedback</li>
                    <li>Community Discussions</li>
                </ul>
            </div>
        </aside>
    </div>

    <section id="discussions" class="reveal active" style="margin-top: 80px; max-width: 900px;">
        <h3><?php echo t('course_discussions'); ?></h3>
        
        <?php if (!empty($_SESSION['user'])): ?>
            <div class="comment-form-card content-card" style="margin-top: 20px;">
                <form method="POST">
                    <input type="hidden" name="post_comment" value="1">
                    <div class="input-group">
                        <textarea name="comment_content" placeholder=" " required style="width:100%; min-height:100px; border:2px solid #eee; border-radius:12px; padding:15px; margin-bottom:15px; font-family:inherit;"></textarea>
                        <label><?php echo t('write_comment_placeholder'); ?></label>
                    </div>
                    <button type="submit" class="btn premium-btn"><?php echo t('post_comment'); ?></button>
                </form>
            </div>
        <?php else: ?>
            <p class="muted"><?php echo t('login_to_comment'); ?></p>
        <?php endif; ?>

        <div class="comments-list" style="margin-top: 40px;">
            <?php foreach ($comments as $comment): ?>
                <div class="comment-item">
                    <div class="user-avatar xsmall">
                        <?php if ($comment['avatar']): ?>
                            <img src="<?php echo htmlspecialchars($comment['avatar']); ?>" alt="">
                        <?php else: ?>
                            <?php echo strtoupper(substr($comment['name'], 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <div class="comment-body" style="background: #fdfdfd; padding: 20px; border-radius: 12px; border: 1px solid #eee;">
                        <div class="comment-header" style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <strong><?php echo htmlspecialchars($comment['name']); ?></strong>
                            <span class="date" style="font-size: 0.8rem; color: #999;"><?php echo date('M d, Y H:i', strtotime($comment['created_at'])); ?></span>
                        </div>
                        <p><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($comments)): ?>
                <p class="muted"><?php echo t('no_comments_yet'); ?></p>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!empty($_SESSION['user']) && $_SESSION['user']['role'] === 'student'): ?>
            </div>
        </div>
    <?php else: ?>
        </div>
    <?php endif; ?>
</main>

<script src="/art-school-website/js/main.js"></script>
</body>
</html>
