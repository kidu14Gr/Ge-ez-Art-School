<?php
require_once __DIR__ . '/php/functions.php';
require_role('admin');

$lang = get_lang();
$db = getDB();

// Fetch fresh user data including avatar and bio
$stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$stmt->bind_param('i', $_SESSION['user']['id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$section = $_GET['section'] ?? 'overview';
$message = '';

// Handle AJAX requests
if (isset($_GET['ajax'])) {
    if ($_GET['ajax'] === 'get_submissions') {
        $eid = intval($_GET['enrollment_id']);
        $subs = $db->query("SELECT title, content, submitted_at, grade FROM submissions WHERE enrollment_id = $eid ORDER BY submitted_at DESC");
        $data = [];
        while($s = $subs->fetch_assoc()) $data[] = $s;
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_user_status') {
        $uid = intval($_POST['user_id']);
        $status = $_POST['status'];
        $stmt = $db->prepare('UPDATE users SET status = ? WHERE id = ?');
        $stmt->bind_param('si', $status, $uid);
        $stmt->execute();
        $stmt->close();
        $message = "User status updated.";
    } elseif ($action === 'edit_user') {
        $uid = intval($_POST['user_id']);
        $name = $_POST['name'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $stmt = $db->prepare('UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?');
        $stmt->bind_param('sssi', $name, $email, $role, $uid);
        $stmt->execute();
        $stmt->close();
        $message = "User updated.";
    } elseif ($action === 'create_user') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        $stmt = $db->prepare('INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, "active")');
        $stmt->bind_param('ssss', $name, $email, $password, $role);
        if ($stmt->execute()) {
            $message = "User created successfully.";
        } else {
            $message = "Error creating user: " . $db->error;
        }
        $stmt->close();
    } elseif ($action === 'approve_course') {
        $cid = intval($_POST['course_id']);
        $stmt = $db->prepare("UPDATE courses SET status = 'active' WHERE id = ?");
        $stmt->bind_param('i', $cid);
        $stmt->execute();
        $stmt->close();
        $message = "Course approved.";
    } elseif ($action === 'delete_course') {
        $cid = intval($_POST['course_id']);
        $stmt = $db->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->bind_param('i', $cid);
        $stmt->execute();
        $stmt->close();
        $message = "Course deleted.";
    } elseif ($action === 'update_artwork') {
        $aid = intval($_POST['artwork_id']);
        $status = $_POST['status'];
        $stmt = $db->prepare('UPDATE artworks SET status = ? WHERE id = ?');
        $stmt->bind_param('si', $status, $aid);
        $stmt->execute();
        $stmt->close();
        $message = "Artwork status updated.";
    } elseif ($action === 'delete_artwork') {
        $aid = intval($_POST['artwork_id']);
        $stmt = $db->prepare('DELETE FROM artworks WHERE id = ?');
        $stmt->bind_param('i', $aid);
        $stmt->execute();
        $stmt->close();
        $message = "Artwork removed.";
    } elseif ($action === 'add_announcement') {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $target = $_POST['target_role'];
        $stmt = $db->prepare('INSERT INTO announcements (title, content, target_role) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $title, $content, $target);
        $stmt->execute();
        $stmt->close();
        $message = "Announcement posted.";
    } elseif ($action === 'add_event') {
        $title = $_POST['title'];
        $desc = $_POST['description'];
        $date = $_POST['event_date'];
        $loc = $_POST['location'];
        $stmt = $db->prepare('INSERT INTO events (title, description, event_date, location) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $title, $desc, $date, $loc);
        $stmt->execute();
        $stmt->close();
        $message = "Event added.";
    }
}

// Fetch Stats for Overview
$stats = [
    'users' => $db->query("SELECT COUNT(*) FROM users WHERE role != 'admin'")->fetch_row()[0],
    'courses' => $db->query("SELECT COUNT(*) FROM courses")->fetch_row()[0],
    'enrollments' => $db->query("SELECT COUNT(*) FROM enrollments")->fetch_row()[0],
    'pending_courses' => $db->query("SELECT COUNT(*) FROM courses WHERE status = 'pending'")->fetch_row()[0],
];

?>
<!doctype html>
<html lang="<?php echo $lang === 'am' ? 'am' : 'en'; ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo t('dash_admin_title'); ?> - <?php echo t('site_title'); ?></title>
    <link rel="stylesheet" href="/art-school-website/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.style.colorScheme = 'dark';
            document.documentElement.classList.add('dark-mode-pending');
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .admin-sidebar { height: calc(100vh - 120px); position: sticky; top: 120px; }
        .sidebar-nav a { display: flex; align-items: center; gap: 10px; }
        .nav-icon { width: 20px; height: 20px; display: inline-block; background: #eee; border-radius: 4px; }
    </style>
</head>
<body class="dashboard-wrapper">

<header class="site-header scrolled">
    <div class="container header-inner">
        <a href="/art-school-website/index.php" class="brand">
            <img src="images/logo.png" alt="<?php echo t('site_title'); ?>" class="logo">
            <span class="site-name"><?php echo t('site_title'); ?></span>
        </a>
        <nav class="nav">
            <div class="lang-nav">
                <a href="?section=<?php echo $section; ?>&lang=en" class="lang-btn <?php echo $lang === 'en' ? 'active' : ''; ?>">EN</a>
                <a href="?section=<?php echo $section; ?>&lang=am" class="lang-btn <?php echo $lang === 'am' ? 'active' : ''; ?>">አማ</a>
            </div>
            <div class="nav-divider"></div>
            <div class="theme-toggle" id="themeToggle">
                <button class="theme-toggle-btn">🌙</button>
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
        <aside class="sidebar-card admin-sidebar reveal active">
            <div class="user-avatar" style="overflow: hidden;">
                <?php if (!empty($user['avatar'])): ?>
                    <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                <?php endif; ?>
            </div>
            <div style="text-align: center;">
                <h3 style="margin-bottom: 5px;"><?php echo htmlspecialchars($user['name']); ?></h3>
                <p class="muted" style="font-size: 0.85rem;">System Administrator</p>
                <?php if (!empty($user['bio'])): ?>
                    <p class="muted" style="font-size: 0.8rem; margin-top: 8px; font-style: italic;"><?php echo htmlspecialchars($user['bio']); ?></p>
                <?php endif; ?>
            </div>
            
            <nav class="sidebar-nav">
                <a href="?section=overview" class="<?php echo $section === 'overview' ? 'active' : ''; ?>"><?php echo t('overview'); ?></a>
                <a href="?section=users" class="<?php echo $section === 'users' ? 'active' : ''; ?>"><?php echo t('manage_users'); ?></a>
                <a href="?section=courses" class="<?php echo $section === 'courses' ? 'active' : ''; ?>"><?php echo t('manage_courses'); ?></a>
                <a href="?section=enrollments" class="<?php echo $section === 'enrollments' ? 'active' : ''; ?>"><?php echo t('enrollments_submissions'); ?></a>
                <a href="?section=gallery" class="<?php echo $section === 'gallery' ? 'active' : ''; ?>"><?php echo t('gallery_oversight'); ?></a>
                <a href="?section=announcements" class="<?php echo $section === 'announcements' ? 'active' : ''; ?>"><?php echo t('announcements'); ?></a>
                <a href="?section=events" class="<?php echo $section === 'events' ? 'active' : ''; ?>"><?php echo t('events_calendar'); ?></a>
                <a href="?section=analytics" class="<?php echo $section === 'analytics' ? 'active' : ''; ?>"><?php echo t('analytics_reports'); ?></a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="dashboard-content">
            
            <?php if ($section === 'overview'): ?>
                <h2 style="margin-bottom: 30px;"><?php echo t('welcome_back'); ?>, Admin!</h2>
                <div class="stats-row">
                    <div class="stat-card reveal active">
                        <h4><?php echo t('active_members'); ?></h4>
                        <div class="value"><?php echo $stats['users']; ?></div>
                    </div>
                    <div class="stat-card reveal active">
                        <h4><?php echo t('courses'); ?></h4>
                        <div class="value"><?php echo $stats['courses']; ?></div>
                    </div>
                    <div class="stat-card reveal active">
                        <h4><?php echo t('pending_approvals'); ?></h4>
                        <div class="value"><?php echo $stats['pending_courses']; ?></div>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="content-card reveal active">
                        <h3>Recent Enrollments</h3>
                        <div class="admin-table-container">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Course</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $recent = $db->query("SELECT u.name, c.title, e.created_at FROM enrollments e JOIN users u ON e.user_id = u.id JOIN courses c ON e.course_id = c.id ORDER BY e.created_at DESC LIMIT 5");
                                    while ($r = $recent->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($r['name']); ?></td>
                                        <td><?php echo htmlspecialchars($r['title']); ?></td>
                                        <td><?php echo date('M d', strtotime($r['created_at'])); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="content-card reveal active">
                        <h3>System Status</h3>
                        <ul class="dashboard-list">
                            <li><span>Server Health</span> <span class="badge badge-active">Optimal</span></li>
                            <li><span>DB Connections</span> <span class="badge badge-active">Active</span></li>
                            <li><span>Pending Artworks</span> <span class="badge badge-pending">
                                <?php echo $db->query("SELECT COUNT(*) FROM artworks WHERE status = 'pending'")->fetch_row()[0]; ?>
                            </span></li>
                        </ul>
                    </div>
                </div>

            <?php elseif ($section === 'users'): ?>
                <div class="section-header-flex" style="display:flex; justify-content: space-between; align-items:center; margin-bottom: 30px;">
                    <h2><?php echo t('manage_users'); ?></h2>
                    <button class="btn premium-btn" onclick="document.getElementById('addUserModal').classList.add('active')"><?php echo t('add_user'); ?></button>
                </div>
                <div class="search-bar">
                    <input type="text" id="userSearch" placeholder="<?php echo t('search_placeholder'); ?>" onkeyup="filterTable('userTable', 0)">
                </div>
                <div class="content-card">
                    <div class="admin-table-container">
                        <table class="admin-table" id="userTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $users_list = $db->query("SELECT id, name, email, role, status FROM users WHERE role != 'admin' ORDER BY created_at DESC");
                                while ($u = $users_list->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($u['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><span class="badge"><?php echo ucfirst($u['role']); ?></span></td>
                                    <td><span class="badge badge-<?php echo $u['status']; ?>"><?php echo t('status_'.$u['status']); ?></span></td>
                                    <td class="action-btns">
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                            <input type="hidden" name="action" value="update_user_status">
                                            <?php if ($u['status'] === 'active'): ?>
                                                <button name="status" value="inactive" class="icon-btn" title="Deactivate">🚫</button>
                                            <?php else: ?>
                                                <button name="status" value="active" class="icon-btn" title="Activate">✅</button>
                                            <?php endif; ?>
                                        </form>
                                        <button class="icon-btn" onclick="openEditUserModal(<?php echo htmlspecialchars(json_encode($u)); ?>)">Edit</button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Edit User Modal -->
                <div id="editUserModal" class="admin-modal">
                    <div class="modal-content">
                        <h3>Edit User</h3>
                        <form method="POST" style="margin-top:20px;">
                            <input type="hidden" name="action" value="edit_user">
                            <input type="hidden" name="user_id" id="edit_uid">
                            <div class="input-group">
                                <input type="text" name="name" id="edit_name" required placeholder=" ">
                                <label><?php echo t('full_name'); ?></label>
                            </div>
                            <div class="input-group">
                                <input type="email" name="email" id="edit_email" required placeholder=" ">
                                <label><?php echo t('email_address'); ?></label>
                            </div>
                            <div class="input-group">
                                <select name="role" id="edit_role" required>
                                    <option value="student"><?php echo t('role_student'); ?></option>
                                    <option value="teacher"><?php echo t('role_teacher'); ?></option>
                                    <option value="admin">Admin</option>
                                </select>
                                <label><?php echo t('role'); ?></label>
                            </div>
                            <div style="display:flex; gap:10px; margin-top:30px;">
                                <button type="submit" class="btn premium-btn">Update User</button>
                                <button type="button" class="btn outline-btn" onclick="closeModal('editUserModal')" style="color:#000; border-color:#000;">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Add User Modal -->
                <div id="addUserModal" class="admin-modal">
                    <div class="modal-content">
                        <h3><?php echo t('add_user'); ?></h3>
                        <form method="POST" style="margin-top:20px;">
                            <input type="hidden" name="action" value="create_user">
                            <div class="input-group">
                                <input type="text" name="name" required placeholder=" ">
                                <label><?php echo t('full_name'); ?></label>
                            </div>
                            <div class="input-group">
                                <input type="email" name="email" required placeholder=" ">
                                <label><?php echo t('email_address'); ?></label>
                            </div>
                            <div class="input-group">
                                <input type="password" name="password" required placeholder=" " minlength="6">
                                <label><?php echo t('password'); ?></label>
                            </div>
                            <div class="input-group">
                                <select name="role" required>
                                    <option value="student"><?php echo t('role_student'); ?></option>
                                    <option value="teacher"><?php echo t('role_teacher'); ?></option>
                                    <option value="admin">Admin</option>
                                </select>
                                <label><?php echo t('role'); ?></label>
                            </div>
                            <div style="display:flex; gap:10px; margin-top:30px;">
                                <button type="submit" class="btn premium-btn"><?php echo t('submit'); ?></button>
                                <button type="button" class="btn outline-btn" onclick="closeModal('addUserModal')" style="color:#000; border-color:#000;">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

            <?php elseif ($section === 'courses'): ?>
                <h2><?php echo t('manage_courses'); ?></h2>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $courses_list = $db->query("SELECT id, title, category, status FROM courses ORDER BY created_at DESC");
                            while ($c = $courses_list->fetch_assoc()):
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($c['title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($c['category']); ?></td>
                                <td><span class="badge badge-<?php echo $c['status']; ?>"><?php echo t('status_'.$c['status']); ?></span></td>
                                <td class="action-btns">
                                    <?php if ($c['status'] === 'pending'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="course_id" value="<?php echo $c['id']; ?>">
                                        <input type="hidden" name="action" value="approve_course">
                                        <button class="icon-btn" title="Approve">✅</button>
                                    </form>
                                    <?php endif; ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('<?php echo t('confirm_delete'); ?>')">
                                        <input type="hidden" name="course_id" value="<?php echo $c['id']; ?>">
                                        <input type="hidden" name="action" value="delete_course">
                                        <button class="icon-btn" title="Delete">🗑️</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($section === 'enrollments'): ?>
                <h2><?php echo t('enrollments_submissions'); ?></h2>
                <div class="content-card">
                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Course</th>
                                    <th>Enrolled At</th>
                                    <th>Submissions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $enr_list = $db->query("SELECT e.id, u.name as student_name, c.title as course_title, e.created_at, (SELECT COUNT(*) FROM submissions WHERE enrollment_id = e.id) as sub_count FROM enrollments e JOIN users u ON e.user_id = u.id JOIN courses c ON e.course_id = c.id ORDER BY e.created_at DESC");
                                while ($e = $enr_list->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($e['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($e['course_title']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($e['created_at'])); ?></td>
                                    <td>
                                        <span class="badge badge-active"><?php echo $e['sub_count']; ?></span>
                                        <?php if ($e['sub_count'] > 0): ?>
                                            <button class="icon-btn" onclick="viewSubmissions(<?php echo $e['id']; ?>)">View</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Submissions Modal -->
                <div id="submissionsModal" class="admin-modal">
                    <div class="modal-content" style="max-width: 800px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                            <h3>Course Submissions</h3>
                            <button class="icon-btn" onclick="closeModal('submissionsModal')">✕</button>
                        </div>
                        <div id="submissionsList"></div>
                    </div>
                </div>

            <?php elseif ($section === 'gallery'): ?>
                <h2><?php echo t('gallery_oversight'); ?></h2>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Artwork</th>
                                <th>Student</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $art_list = $db->query("SELECT a.id, a.title, u.name as student_name, a.status, a.image_path FROM artworks a JOIN users u ON a.student_id = u.id ORDER BY a.created_at DESC");
                            while ($a = $art_list->fetch_assoc()):
                            ?>
                            <tr>
                                <td><img src="<?php echo htmlspecialchars($a['image_path']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"> <?php echo htmlspecialchars($a['title']); ?></td>
                                <td><?php echo htmlspecialchars($a['student_name']); ?></td>
                                <td><span class="badge badge-<?php echo $a['status']; ?>"><?php echo t('status_'.$a['status']); ?></span></td>
                                <td class="action-btns">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="artwork_id" value="<?php echo $a['id']; ?>">
                                        <input type="hidden" name="action" value="update_artwork">
                                        <button name="status" value="approved" class="icon-btn" title="Approve">✅</button>
                                        <button name="status" value="featured" class="icon-btn" title="Feature">⭐</button>
                                    </form>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Remove this artwork?')">
                                        <input type="hidden" name="artwork_id" value="<?php echo $a['id']; ?>">
                                        <input type="hidden" name="action" value="delete_artwork">
                                        <button class="icon-btn" title="Reject/Delete">🗑️</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($section === 'announcements'): ?>
                <h2><?php echo t('announcements'); ?></h2>
                <div class="content-card" style="margin-bottom: 30px;">
                    <form method="POST">
                        <input type="hidden" name="action" value="add_announcement">
                        <div class="input-group">
                            <input type="text" name="title" required placeholder=" ">
                            <label>Title</label>
                        </div>
                        <div class="input-group">
                            <textarea name="content" required style="width:100%; border:2px solid #eee; border-radius:12px; padding:12px; min-height:100px;"></textarea>
                        </div>
                        <div class="input-group">
                            <select name="target_role">
                                <option value="all">All</option>
                                <option value="student">Students Only</option>
                                <option value="teacher">Teachers Only</option>
                            </select>
                            <label>Target Audience</label>
                        </div>
                        <button type="submit" class="btn premium-btn">Post Announcement</button>
                    </form>
                </div>

            <?php elseif ($section === 'events'): ?>
                <h2><?php echo t('events_calendar'); ?></h2>
                <div class="content-card">
                    <form method="POST">
                        <input type="hidden" name="action" value="add_event">
                        <div class="grid-2">
                            <div class="input-group">
                                <input type="text" name="title" required placeholder=" ">
                                <label>Event Title</label>
                            </div>
                            <div class="input-group">
                                <input type="datetime-local" name="event_date" required>
                            </div>
                        </div>
                        <div class="input-group">
                            <input type="text" name="location" placeholder=" ">
                            <label>Location</label>
                        </div>
                        <button type="submit" class="btn premium-btn">Add Event</button>
                    </form>
                </div>

            <?php elseif ($section === 'analytics'): ?>
                <?php
                // Enrollment Growth (Last 6 months)
                $months = [];
                $enrollment_counts = [];
                for ($i = 5; $i >= 0; $i--) {
                    $month_label = date('M', strtotime("-$i months"));
                    $month_val = date('Y-m', strtotime("-$i months"));
                    $months[] = $month_label;
                    
                    $q = "SELECT COUNT(*) FROM enrollments WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month_val'";
                    $enrollment_counts[] = $db->query($q)->fetch_row()[0];
                }

                // Popular Courses
                $course_labels = [];
                $course_data = [];
                $popular = $db->query("SELECT c.title, COUNT(e.id) as count FROM courses c LEFT JOIN enrollments e ON c.id = e.course_id GROUP BY c.id ORDER BY count DESC LIMIT 5");
                while($p = $popular->fetch_assoc()) {
                    $course_labels[] = $p['title'];
                    $course_data[] = $p['count'];
                }
                ?>
                <h2><?php echo t('analytics_reports'); ?></h2>
                <div class="grid-2">
                    <div class="content-card">
                        <h3>Enrollment Growth</h3>
                        <canvas id="enrollmentChart"></canvas>
                    </div>
                    <div class="content-card">
                        <h3>Popular Courses</h3>
                        <canvas id="courseChart"></canvas>
                    </div>
                </div>
                <script>
                    const ctx1 = document.getElementById('enrollmentChart').getContext('2d');
                    new Chart(ctx1, {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode($months); ?>,
                            datasets: [{
                                label: 'Enrollments',
                                data: <?php echo json_encode($enrollment_counts); ?>,
                                borderColor: '#8b2f2f',
                                tension: 0.1,
                                fill: true,
                                backgroundColor: 'rgba(139, 47, 47, 0.1)'
                            }]
                        },
                        options: { scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
                    });

                    const ctx2 = document.getElementById('courseChart').getContext('2d');
                    new Chart(ctx2, {
                        type: 'doughnut',
                        data: {
                            labels: <?php echo json_encode($course_labels); ?>,
                            datasets: [{
                                data: <?php echo json_encode($course_data); ?>,
                                backgroundColor: ['#8b2f2f', '#d9a066', '#1a1a1a', '#5a5a5a', '#c0c0c0']
                            }]
                        }
                    });
                </script>
            <?php endif; ?>

        </div>
    </div>
</main>

<script src="/art-school-website/js/main.js"></script>
<script>
    function filterTable(tableId, colIndex) {
        let input = document.getElementById(tableId === 'userTable' ? 'userSearch' : '');
        let filter = input.value.toUpperCase();
        let table = document.getElementById(tableId);
        let tr = table.getElementsByTagName("tr");
        for (let i = 1; i < tr.length; i++) {
            let td = tr[i].getElementsByTagName("td")[colIndex];
            if (td) {
                let txtValue = td.textContent || td.innerText;
                tr[i].style.display = txtValue.toUpperCase().indexOf(filter) > -1 ? "" : "none";
            }
        }
    }

    function viewSubmissions(eid) {
        const modal = document.getElementById('submissionsModal');
        const container = document.getElementById('submissionsList');
        container.innerHTML = '<p>Loading...</p>';
        modal.classList.add('active');
        
        fetch(`?ajax=get_submissions&enrollment_id=${eid}`)
            .then(res => res.json())
            .then(data => {
                if (data.length === 0) {
                    container.innerHTML = '<p>No submissions found.</p>';
                    return;
                }
                let html = '<table class="admin-table"><thead><tr><th>Title</th><th>Date</th><th>Grade</th></tr></thead><tbody>';
                data.forEach(s => {
                    html += `<tr>
                        <td><strong>${s.title}</strong><br><small>${s.content.substring(0, 50)}...</small></td>
                        <td>${new Date(s.submitted_at).toLocaleDateString()}</td>
                        <td><span class="badge">${s.grade || 'N/A'}</span></td>
                    </tr>`;
                });
                html += '</tbody></table>';
                container.innerHTML = html;
            });
    }

    function openEditUserModal(user) {
        document.getElementById('edit_uid').value = user.id;
        document.getElementById('edit_name').value = user.name;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_role').value = user.role;
        document.getElementById('editUserModal').classList.add('active');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
    }
</script>
</body>
</html>