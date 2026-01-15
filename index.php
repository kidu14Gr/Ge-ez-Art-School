<?php
require_once __DIR__ . '/php/functions.php';
$lang = get_lang();
?>
<!doctype html>
<html lang="<?php echo $lang === 'am' ? 'am' : 'en'; ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo t('site_title'); ?> - <?php echo t('page_tagline'); ?></title>
    <link rel="stylesheet" href="/art-school-website/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Inter:wght@100..900&display=swap" rel="stylesheet">
</head>
<body class="smooth-scroll">

<header class="site-header transparent">
    <div class="container header-inner">
        <a href="/art-school-website/index.php" class="brand">
            <img src="images/logo.png" alt="<?php echo t('site_title'); ?>" class="logo">
            <span class="site-name"><?php echo t('site_title'); ?></span>
        </a>
        <nav class="nav">
            <a href="#programs"><?php echo t('nav_programs'); ?></a>
            <a href="#features"><?php echo t('nav_edge'); ?></a>
            <a href="#gallery"><?php echo t('nav_gallery'); ?></a>
            <a href="#testimonials"><?php echo t('nav_voices'); ?></a>
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
<main>
    <section class="hero-premium">
        <div class="hero-overlay"></div>
        <div class="container hero-content-v2">
            <h1 class="masked-text"><?php echo t('hero_title'); ?></h1>
            <p class="sub-headline"><?php echo t('hero_subtitle'); ?></p>
            <div class="hero-ctas">
                <a href="#programs" class="btn premium-btn"><?php echo t('hero_btn_view'); ?></a>
                <a href="/art-school-website/register.php" class="btn outline-btn"><?php echo t('hero_btn_apply'); ?></a>
            </div>
        </div>
    </section>

    <section id="programs" class="programs-section container">
        <div class="section-header">
            <span class="eyebrow"><?php echo t('section_programs_eyebrow'); ?></span>
            <h2><?php echo t('section_programs_title'); ?></h2>
        </div>
        <div class="programs-grid">
            <div class="program-card">
                <div class="card-media">
                    <img src="images/traditional_painting.png" alt="<?php echo t('traditional_painting'); ?>">
                </div>
                <div class="card-info">
                    <h3><?php echo t('traditional_painting'); ?></h3>
                    <p><?php echo t('traditional_painting_desc'); ?></p>
                </div>
            </div>
            <div class="program-card">
                <div class="card-media">
                    <img src="images/calligraphy.png" alt="<?php echo t('calligraphy'); ?>">
                </div>
                <div class="card-info">
                    <h3><?php echo t('calligraphy'); ?></h3>
                    <p><?php echo t('calligraphy_desc'); ?></p>
                </div>
            </div>
            <div class="program-card">
                <div class="card-media">
                    <img src="images/sculpture.png" alt="<?php echo t('sculpture'); ?>">
                </div>
                <div class="card-info">
                    <h3><?php echo t('sculpture'); ?></h3>
                    <p><?php echo t('sculpture_desc'); ?></p>
                </div>
            </div>
            <div class="program-card">
                <div class="card-media">
                    <img src="images/digital_art.png" alt="<?php echo t('digital_art'); ?>">
                </div>
                <div class="card-info">
                    <h3><?php echo t('digital_art'); ?></h3>
                    <p><?php echo t('digital_art_desc'); ?></p>
                </div>
            </div>
            <div class="program-card">
                <div class="card-media">
                    <img src="images/fashion_textile.png" alt="<?php echo t('fashion_textile'); ?>">
                </div>
                <div class="card-info">
                    <h3><?php echo t('fashion_textile'); ?></h3>
                    <p><?php echo t('fashion_textile_desc'); ?></p>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="features-premium">
        <div class="container">
            <div class="section-header center">
                <span class="eyebrow"><?php echo t('section_features_eyebrow'); ?></span>
                <h2><?php echo t('section_features_title'); ?></h2>
            </div>
            <div class="features-grid">
                <div class="feature-item reveal">
                    <div class="feature-icon">24/7</div>
                    <h3><?php echo t('feature_studio_title'); ?></h3>
                    <p><?php echo t('feature_studio_desc'); ?></p>
                </div>
                <div class="feature-item reveal">
                    <div class="feature-icon">M</div>
                    <h3><?php echo t('feature_mentorship_title'); ?></h3>
                    <p><?php echo t('feature_mentorship_desc'); ?></p>
                </div>
                <div class="feature-item reveal">
                    <div class="feature-icon">E</div>
                    <h3><?php echo t('feature_exhibition_title'); ?></h3>
                    <p><?php echo t('feature_exhibition_desc'); ?></p>
                </div>
            </div>
        </div>
    </section>

    <section id="gallery" class="gallery-premium container">
        <div class="section-header">
            <span class="eyebrow"><?php echo t('section_gallery_eyebrow'); ?></span>
            <h2><?php echo t('section_gallery_title'); ?></h2>
        </div>
        <div class="gallery-grid-v2">
            <?php
            $db = getDB();
            $gallery_res = $db->query("SELECT title, image_path FROM artworks WHERE status IN ('approved', 'featured') ORDER BY (status = 'featured') DESC, created_at DESC LIMIT 6");
            if ($gallery_res && $gallery_res->num_rows > 0):
                while ($art = $gallery_res->fetch_assoc()):
            ?>
                <div class="gallery-item reveal">
                    <img src="<?php echo htmlspecialchars($art['image_path']); ?>" alt="<?php echo htmlspecialchars($art['title']); ?>" data-full="<?php echo htmlspecialchars($art['image_path']); ?>">
                    <div class="gallery-caption"><?php echo htmlspecialchars($art['title']); ?></div>
                </div>
            <?php 
                endwhile;
            else:
            ?>
                <div class="gallery-item reveal"><img src="images/gallary1.png" alt="Student Work" data-full="images/gallary1.png"></div>
                <div class="gallery-item reveal"><img src="images/gallary2.png" alt="Student Work" data-full="images/gallary2.png"></div>
                <div class="gallery-item reveal"><img src="images/calligraphy.png" alt="Student Work" data-full="images/calligraphy.png"></div>
                <div class="gallery-item reveal"><img src="images/fashion_textile.png" alt="Student Work" data-full="images/fashion_textile.png"></div>
            <?php endif; ?>
        </div>
        <div style="text-align: center; margin-top: 40px;">
            <a href="gallery.php" class="btn outline-btn"><?php echo t('view_all_gallery'); ?></a>
        </div>
    </section>

    <section id="testimonials" class="testimonials-section">
        <div class="container">
            <div class="testimonial-slider">
                <div class="testimonial-item reveal">
                    <p>“<?php echo t('testimonial_1'); ?>”</p>
                    <cite>— Ana R., Painter</cite>
                </div>
                <div class="testimonial-item reveal">
                    <p>“<?php echo t('testimonial_2'); ?>”</p>
                    <cite>— Marcus L., Sculptor</cite>
                </div>
                <div class="testimonial-item reveal">
                    <p>“<?php echo t('testimonial_3'); ?>”</p>
                    <cite>— Keiko N., Digital Artist</cite>
                </div>
            </div>
        </div>
    </section>

    <section id="visit" class="visit-section container">
        <div class="visit-card reveal">
            <div class="visit-content">
                <h2><?php echo t('section_visit_title'); ?></h2>
                <p><?php echo t('section_visit_desc'); ?></p>
                <form id="tour-form" class="newsletter-form">
                    <input type="email" placeholder="<?php echo t('newsletter_placeholder'); ?>" required>
                    <button type="submit" class="btn primary"><?php echo t('newsletter_btn'); ?></button>
                </form>
            </div>
        </div>
    </section>
</main>

<footer class="minimal-footer">
    <div class="container footer-content">
        <div class="footer-brand">
            <img src="images/logo.png" alt="<?php echo t('site_title'); ?> logo" class="footer-logo">
            <span><?php echo t('site_title'); ?></span>
        </div>
        <div class="footer-links">
            <a href="#">Instagram</a>
            <a href="#">Facebook</a>
            <a href="#">Twitter</a>
        </div>
        <div class="footer-newsletter">
            <p><?php echo t('footer_stay_inspired'); ?></p>
            <form class="mini-form">
                <input type="email" placeholder="<?php echo t('email'); ?>">
                <button type="submit">→</button>
            </form>
        </div>
    </div>
    <div class="footer-bottom container">
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