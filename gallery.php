<?php
require_once __DIR__ . '/php/functions.php';
$lang = get_lang();
$db = getDB();

$filter = $_GET['filter'] ?? 'all';
$query = "SELECT a.title, a.image_path, u.name as artist, a.status FROM artworks a JOIN users u ON a.student_id = u.id WHERE a.status IN ('approved', 'featured')";

if ($filter === 'featured') {
    $query .= " AND a.status = 'featured'";
}

$query .= " ORDER BY a.created_at DESC";
$artworks = $db->query($query);
?>
<!doctype html>
<html lang="<?php echo $lang === 'am' ? 'am' : 'en'; ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo t('nav_gallery'); ?> - <?php echo t('site_title'); ?></title>
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
            <a href="/art-school-website/gallery.php" class="active"><?php echo t('nav_gallery'); ?></a>
            <div class="nav-divider"></div>
            <div class="lang-nav">
                <a href="?filter=<?php echo $filter; ?>&lang=en" class="lang-btn <?php echo $lang === 'en' ? 'active' : ''; ?>">EN</a>
                <a href="?filter=<?php echo $filter; ?>&lang=am" class="lang-btn <?php echo $lang === 'am' ? 'active' : ''; ?>">አማ</a>
            </div>
        </nav>
    </div>
</header>

<main class="container" style="padding-top: 150px; padding-bottom: 100px;">
    <div class="section-header center">
        <span class="eyebrow"><?php echo t('section_gallery_eyebrow'); ?></span>
        <h2><?php echo t('section_gallery_title'); ?></h2>
        <div class="filter-tabs" style="margin-top: 30px; display: flex; justify-content: center; gap: 20px;">
            <a href="?filter=all" class="btn <?php echo $filter === 'all' ? 'premium-btn' : 'outline-btn'; ?>" style="padding: 10px 25px; font-size: 0.9rem;">All Works</a>
            <a href="?filter=featured" class="btn <?php echo $filter === 'featured' ? 'premium-btn' : 'outline-btn'; ?>" style="padding: 10px 25px; font-size: 0.9rem;">Featured</a>
        </div>
    </div>

    <div class="gallery-grid-v2" style="margin-top: 50px;">
        <?php if ($artworks && $artworks->num_rows > 0): ?>
            <?php while ($art = $artworks->fetch_assoc()): ?>
                <div class="gallery-item reveal">
                    <img src="<?php echo htmlspecialchars($art['image_path']); ?>" alt="<?php echo htmlspecialchars($art['title']); ?>" data-full="<?php echo htmlspecialchars($art['image_path']); ?>">
                    <div class="gallery-caption">
                        <strong><?php echo htmlspecialchars($art['title']); ?></strong><br>
                        <small>by <?php echo htmlspecialchars($art['artist']); ?></small>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="muted" style="text-align: center; grid-column: 1/-1;">No artworks found in this category.</p>
        <?php endif; ?>
    </div>
</main>

<footer class="minimal-footer">
    <div class="container footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> <?php echo t('footer_copyright'); ?></p>
    </div>
</footer>

<div id="lightbox" class="lightbox">
    <span class="close-lightbox">&times;</span>
    <img src="" alt="Enlarged Art" class="lightbox-img">
</div>

<script src="/art-school-website/js/main.js"></script>
</body>
</html>