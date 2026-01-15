<?php
require_once __DIR__ . '/php/functions.php';
require_role('teacher');

$lang = get_lang();
$user = current_user();
$db = getDB();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create' || $action === 'update') {
        $title = $_POST['title'];
        $category = $_POST['category'];
        $desc = $_POST['description'];
        $syllabus = $_POST['syllabus'];
        $materials = $_POST['materials'];
        $slug = strtolower(str_replace(' ', '-', $title));
        
        // Handle Material Upload
        $material_path = $_POST['existing_material_path'] ?? '';
        if (isset($_FILES['material_file']) && $_FILES['material_file']['error'] === UPLOAD_ERR_OK) {
            $target_dir = __DIR__ . '/uploads/materials/';
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $filename = uniqid('mat_') . '_' . basename($_FILES['material_file']['name']);
            if (move_uploaded_file($_FILES['material_file']['tmp_name'], $target_dir . $filename)) {
                $material_path = 'uploads/materials/' . $filename;
            }
        }

        if ($action === 'create') {
            $stmt = $db->prepare('INSERT INTO courses (title, slug, description, category, syllabus, materials, material_path, teacher_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, "pending")');
            $stmt->bind_param('sssssssi', $title, $slug, $desc, $category, $syllabus, $materials, $material_path, $user['id']);
        } else {
            $cid = intval($_POST['course_id']);
            $stmt = $db->prepare('UPDATE courses SET title = ?, slug = ?, description = ?, category = ?, syllabus = ?, materials = ?, material_path = ? WHERE id = ? AND teacher_id = ?');
            $stmt->bind_param('sssssssii', $title, $slug, $desc, $category, $syllabus, $materials, $material_path, $cid, $user['id']);
        }
        
        if ($stmt->execute()) {
            $message = "Course " . ($action === 'create' ? "created and pending approval." : "updated.");
        }
        $stmt->close();
    }
}

$my_courses = [];
$stmt = $db->prepare('SELECT * FROM courses WHERE teacher_id = ?');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) $my_courses[] = $r;
$stmt->close();
?>
<!doctype html>
<html lang="<?php echo $lang === 'am' ? 'am' : 'en'; ?>">
<head>
    <meta charset="utf-8">
    <title><?php echo t('curriculum_manager'); ?> - <?php echo t('site_title'); ?></title>
    <link rel="stylesheet" href="/art-school-website/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.style.colorScheme = 'dark';
            document.documentElement.classList.add('dark-mode-pending');
        }
    </script>
</head>
<body class="dashboard-wrapper">

<header class="site-header scrolled">
    <div class="container header-inner">
        <a href="/art-school-website/index.php" class="brand">
            <img src="images/logo.png" alt="Logo" class="logo">
            <span class="site-name"><?php echo t('site_title'); ?></span>
        </a>
        <nav class="nav">
            <a href="/art-school-website/dashboard-teacher.php"><?php echo t('dashboard'); ?></a>
            <a href="/art-school-website/logout.php" class="nav-login"><?php echo t('logout'); ?></a>
        </nav>
    </div>
</header>

<main class="container">
    <div class="dashboard-grid">
        <aside class="sidebar-card reveal active">
             <nav class="sidebar-nav">
                <a href="/art-school-website/dashboard-teacher.php"><?php echo t('overview'); ?></a>
                <a href="/art-school-website/profile-teacher.php"><?php echo t('profile_settings'); ?></a>
                <a href="#" class="active"><?php echo t('curriculum_manager'); ?></a>
            </nav>
        </aside>

        <div class="dashboard-content">
            <div class="section-header-flex" style="display:flex; justify-content: space-between; align-items:center; margin-bottom: 30px;">
                <h2><?php echo t('curriculum_manager'); ?></h2>
                <button class="btn premium-btn" onclick="openCourseModal()"><?php echo t('add_new_course'); ?></button>
            </div>

            <?php if ($message): ?>
                <div class="flash"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="content-card">
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
                        <?php foreach ($my_courses as $c): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($c['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($c['category']); ?></td>
                            <td><span class="badge badge-<?php echo $c['status']; ?>"><?php echo t('status_'.$c['status']); ?></span></td>
                            <td>
                                <button class="icon-btn" onclick='editCourse(<?php echo json_encode($c); ?>)'>Edit</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<div id="courseModal" class="admin-modal">
    <div class="modal-content" style="max-width: 800px;">
        <h3 id="modalTitle">Add New Course</h3>
        <form method="POST" enctype="multipart/form-data" style="margin-top: 20px;">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="course_id" id="courseId">
            <input type="hidden" name="existing_material_path" id="existingMaterialPath">
            <div class="grid-2">
                <div class="input-group">
                    <input type="text" name="title" id="courseTitle" required placeholder=" ">
                    <label>Course Title</label>
                </div>
                <div class="input-group">
                    <select name="category" id="courseCategory" required>
                        <option value="Painting"><?php echo t('traditional_painting'); ?></option>
                        <option value="Calligraphy"><?php echo t('calligraphy'); ?></option>
                        <option value="Sculpture"><?php echo t('sculpture'); ?></option>
                        <option value="Digital Art"><?php echo t('digital_art'); ?></option>
                        <option value="Fashion"><?php echo t('fashion_textile'); ?></option>
                    </select>
                    <label>Category</label>
                </div>
            </div>
            <div class="input-group full-width">
                <textarea name="description" id="courseDesc" placeholder=" " rows="3"></textarea>
                <label>Description</label>
            </div>
            <div class="grid-2">
                <div class="input-group">
                    <textarea name="syllabus" id="courseSyllabus" placeholder=" " rows="5"></textarea>
                    <label>Syllabus (Topics covered)</label>
                </div>
                <div class="input-group">
                    <textarea name="materials" id="courseMaterials" placeholder=" " rows="2"></textarea>
                    <label>Required Materials (Text)</label>
                    <div style="margin-top: 15px;">
                        <label style="font-size: 0.8rem; color: var(--muted); position: static;">Upload PDF Material</label>
                        <input type="file" name="material_file" accept=".pdf,.doc,.docx">
                        <div id="currentMaterial" style="font-size: 0.75rem; margin-top: 5px;"></div>
                    </div>
                </div>
            </div>
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="btn premium-btn">Save Course</button>
                <button type="button" class="btn outline-btn" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCourseModal() {
    document.getElementById('formAction').value = 'create';
    document.getElementById('modalTitle').innerText = 'Add New Course';
    document.getElementById('courseModal').classList.add('active');
}
function editCourse(c) {
    document.getElementById('formAction').value = 'update';
    document.getElementById('courseId').value = c.id;
    document.getElementById('courseTitle').value = c.title;
    document.getElementById('courseCategory').value = c.category;
    document.getElementById('courseDesc').value = c.description;
    document.getElementById('courseSyllabus').value = c.syllabus;
    document.getElementById('courseMaterials').value = c.materials;
    document.getElementById('existingMaterialPath').value = c.material_path || '';
    document.getElementById('currentMaterial').innerText = c.material_path ? 'Current: ' + c.material_path.split('/').pop() : '';
    document.getElementById('modalTitle').innerText = 'Edit Course';
    document.getElementById('courseModal').classList.add('active');
}
function closeModal() {
    document.getElementById('courseModal').classList.remove('active');
}
</script>
</body>
</html>
