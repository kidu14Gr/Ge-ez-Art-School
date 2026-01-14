// Ge'ez Art School - Main Scripts
document.addEventListener('DOMContentLoaded', function() {
    document.body.classList.add('js-ready');
    
    // 1. Header Scroll Effect
    const header = document.querySelector('.site-header');
    if (header) {
        // Only pages with the 'transparent' class should toggle the 'scrolled' class on scroll
        const isDynamic = header.classList.contains('transparent');
        
        const handleScroll = () => {
            if (isDynamic) {
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            }
        };
        window.addEventListener('scroll', handleScroll);
        handleScroll(); // Initial check
    }

    // 2. Scroll Reveal Animation
    const reveals = document.querySelectorAll('.reveal');
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                revealObserver.unobserve(entry.target); // Stop observing once revealed
            }
        });
    }, { 
        threshold: 0.05, // Lower threshold for easier trigger
        rootMargin: '0px 0px -50px 0px' // Trigger slightly before it enters view
    });

    reveals.forEach(el => {
        // If element is already in view on load, show it immediately
        const rect = el.getBoundingClientRect();
        if (rect.top < window.innerHeight && rect.bottom > 0) {
            el.classList.add('active');
        } else {
            revealObserver.observe(el);
        }
    });

    // 3. Lightbox Gallery
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = lightbox.querySelector('.lightbox-img');
    const galleryItems = document.querySelectorAll('.gallery-item img');
    const closeLightbox = document.querySelector('.close-lightbox');

    galleryItems.forEach(img => {
        img.addEventListener('click', () => {
            const fullSrc = img.getAttribute('data-full');
            lightboxImg.src = fullSrc;
            lightbox.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent scroll
        });
    });

    closeLightbox.addEventListener('click', () => {
        lightbox.classList.remove('active');
        document.body.style.overflow = 'auto';
    });

    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) {
            lightbox.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    });

    // 4. Smooth Scrolling for Nav Links (Standard behavior is fine with CSS, but JS handles edge cases)
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                window.scrollTo({
                    top: target.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });

    // 5. Preservation: Login and Register Form Validation
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email');
            const pass = document.getElementById('password');
            if (!email.value || !pass.value) {
                e.preventDefault();
                alert('Please enter email and password');
            }
        });
    }

    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const name = document.getElementById('name');
            const email = document.getElementById('email');
            const pass = document.getElementById('password');
            if (!name.value || !email.value || !pass.value) {
                e.preventDefault();
                alert('Please fill all fields');
            } else if (pass.value.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters');
            }
        });
    }

    // 6. Tour Form Submission (Mock)
    const tourForm = document.getElementById('tour-form');
    if (tourForm) {
        tourForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const email = tourForm.querySelector('input').value;
            alert(`Thank you! A studio tour invitation has been sent to ${email}.`);
            tourForm.reset();
        });
    }
});
