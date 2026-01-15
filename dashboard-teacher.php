<?php
require_once __DIR__ . '/php/functions.php';
require_role('teacher');
$lang = get_lang();
$db = getDB();

// Fetch fresh user data including avatar and bio
$stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$stmt->bind_param('i', $_SESSION['user']['id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

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

// Recent Submissions for teacher's courses
$recent_submissions = [];
if (!empty($courses)) {
    $cids = array_column($courses, 'id');
    $placeholders = implode(',', array_fill(0, count($cids), '?'));
    $stmt = $db->prepare("SELECT s.id, s.title, s.submitted_at, u.name as student_name, c.title as course_title FROM submissions s JOIN enrollments e ON s.enrollment_id = e.id JOIN users u ON e.user_id = u.id JOIN courses c ON e.course_id = c.id WHERE e.course_id IN ($placeholders) ORDER BY s.submitted_at DESC LIMIT 5");
    $types = str_repeat('i', count($cids));
    $stmt->bind_param($types, ...$cids);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $recent_submissions[] = $r;
    $stmt->close();
}

// Announcements
$announcements = [];
$res = $db->query("SELECT title, content, created_at FROM announcements WHERE target_role IN ('all', 'teacher') ORDER BY created_at DESC LIMIT 3");
while ($r = $res->fetch_assoc()) $announcements[] = $r;

// Handle Grading
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grade_submission'])) {
    $sid = intval($_POST['submission_id']);
    $grade = $_POST['grade'];
    $feedback = $_POST['feedback'];
    $stmt = $db->prepare('UPDATE submissions SET grade = ?, feedback = ? WHERE id = ?');
    $stmt->bind_param('ssi', $grade, $feedback, $sid);
    if ($stmt->execute()) {
        $message = "Submission graded successfully.";
        // Notify student
        $s_stmt = $db->prepare('SELECT e.user_id, c.title FROM submissions s JOIN enrollments e ON s.enrollment_id = e.id JOIN courses c ON e.course_id = c.id WHERE s.id = ?');
        $s_stmt->bind_param('i', $sid);
        $s_stmt->execute();
        $sub_info = $s_stmt->get_result()->fetch_assoc();
        $s_stmt->close();
        
        if ($sub_info) {
            add_notification($sub_info['user_id'], "Your submission for " . $sub_info['title'] . " has been graded.");
        }
    }
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
            <div class="user-avatar" style="background: var(--highlight); overflow: hidden;">
                <?php if (!empty($user['avatar'])): ?>
                    <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                <?php endif; ?>
            </div>
            <div style="text-align: center;">
                <h3 style="margin-bottom: 5px;"><?php echo htmlspecialchars($user['name']); ?></h3>
                <p class="muted" style="font-size: 0.85rem;"><?php echo t('mentor'); ?> • <?php echo htmlspecialchars($user['email']); ?></p>
                <?php if (!empty($user['bio'])): ?>
                    <p class="muted" style="font-size: 0.8rem; margin-top: 8px; font-style: italic;"><?php echo htmlspecialchars($user['bio']); ?></p>
                <?php endif; ?>
            </div>
            
            <nav class="sidebar-nav">
                <a href="#" class="active"><?php echo t('teaching_overview'); ?></a>
                <a href="/art-school-website/profile-teacher.php"><?php echo t('profile_settings'); ?></a>
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
            <div class="grid-2">
                <div class="left-col">
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

                    <!-- Recent Submissions -->
                    <div class="content-card reveal active" style="margin-top: 20px;">
                        <h3>Recent Submissions</h3>
                        <ul class="dashboard-list">
                            <?php if (empty($recent_submissions)): ?>
                                <p class="muted">No recent submissions.</p>
                            <?php else: ?>
                                <?php foreach ($recent_submissions as $s): ?>
                                    <li>
                                        <div class="course-meta">
                                            <span style="font-weight: 700;"><?php echo htmlspecialchars($s['title']); ?></span>
                                            <span class="teacher"><?php echo htmlspecialchars($s['student_name']); ?> • <?php echo htmlspecialchars($s['course_title']); ?></span>
                                        </div>
                                        <button class="btn premium-btn" style="padding: 6px 12px; font-size: 0.75rem;" onclick="openGradeModal(<?php echo htmlspecialchars(json_encode($s)); ?>)">Grade</button>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
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
                </div>
            </div>

            <!-- Grade Modal -->
            <div id="gradeModal" class="admin-modal">
                <div class="modal-content">
                    <h3 id="gradeModalTitle">Grade Submission</h3>
                    <form method="POST" style="margin-top: 20px;">
                        <input type="hidden" name="grade_submission" value="1">
                        <input type="hidden" name="submission_id" id="grade_sid">
                        <div class="input-group">
                            <input type="text" name="grade" required placeholder=" ">
                            <label>Grade (e.g. A, 95, Pass)</label>
                        </div>
                        <div class="input-group">
                            <textarea name="feedback" placeholder=" " style="width:100%; min-height:100px; border:2px solid #eee; border-radius:12px; padding:12px;"></textarea>
                            <label>Feedback</label>
                        </div>
                        <div style="display:flex; gap:10px; margin-top:20px;">
                            <button type="submit" class="btn premium-btn">Submit Grade</button>
                            <button type="button" class="btn outline-btn" onclick="closeModal('gradeModal')" style="color:#000; border-color:#000;">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="/art-school-website/js/main.js"></script>
<script>
    function openGradeModal(submission) {
        document.getElementById('grade_sid').value = submission.id;
        document.getElementById('gradeModalTitle').innerText = 'Grade: ' + submission.title;
        document.getElementById('gradeModal').classList.add('active');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }
</script>
</body>
</html>