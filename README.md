# 🎨 Ge'ez Art School - Full-Stack Educational Platform

A complete full-stack web application for Ethiopian traditional and contemporary art education. Built with vanilla PHP, MySQL, HTML5, CSS3, and plain JavaScript—no frameworks required.

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Directory Structure](#directory-structure)
- [Installation & Setup](#installation--setup)
- [Usage Guide](#usage-guide)
- [User Roles & Workflows](#user-roles--workflows)
- [Feature Details](#feature-details)
- [Database Schema](#database-schema)
- [Localization (i18n)](#localization-i18n)
- [Security](#security)
- [API Documentation](#api-documentation)
- [Troubleshooting](#troubleshooting)
- [Future Enhancements](#future-enhancements)

---

## Overview

**Ge'ez Art School** is a professional educational platform designed to:

- Showcase Ethiopian art traditions (calligraphy, traditional painting, sculpture, fashion & textiles, digital art)
- Enable students to enroll in courses and track their progress
- Allow teachers to create, manage courses and view enrolled students
- Provide administrators with system oversight and content management
- Support bilingual content (English & Amharic)
- Deliver a beautiful, responsive user experience with modern UI features

The application is production-ready with proper authentication, database security, multi-language support, and role-based access control.

---

## Features

### 🎯 Core Features

- **Multi-Role Authentication** - Student, Teacher, and Admin roles with session management
- **Course Management** - Browse, search, filter, and enroll in courses by category
- **Student Dashboard** - Track enrollments, submit artwork, view recent submissions
- **Teacher Dashboard** - Manage courses, view enrolled students, create/edit curriculum
- **Admin Dashboard** - System status, user management, artwork approval workflow
- **Gallery System** - Browse and showcase approved student artwork with lightbox preview
- **Notifications System** - Announcements and event updates

### ✨ UI/UX Features

- **Dark Mode** - System-wide dark mode with persistent localStorage
  - Smooth transitions between light and dark themes
  - Applies instantly on page load (no flash)
  - Works across all pages and components
- **Password Visibility Toggle** - Eye icon to show/hide passwords during login/registration
- **Responsive Design** - Mobile-first, works on all devices (768px breakpoint)
- **Smooth Animations** - Scroll reveal effects, header animations, transitions
- **Floating Labels** - Modern input design with animated labels
- **Lightbox Gallery** - Click images to view full-screen with smooth transitions
- **Header Scroll Effects** - Dynamic header that adapts on scroll (landing page)

### 🌍 Localization

- **Bilingual Support** - Full English and Amharic interface
- **Language Switching** - Easy toggle between languages in header
- **Translated Content** - All UI strings, forms, messages, and notifications in both languages

### 🔒 Security Features

- **Password Hashing** - bcrypt via PHP's `password_hash()`
- **Prepared Statements** - All database queries use mysqli prepared statements to prevent SQL injection
- **Session Management** - Secure server-side sessions with role validation
- **CSRF Protection** - Form submissions validated against session state
- **Input Validation** - Server-side validation for all forms

---

## Technology Stack

### Frontend
| Technology | Purpose |
|-----------|---------|
| **HTML5** | Semantic markup, accessibility |
| **CSS3** | Advanced styling, CSS Grid/Flexbox, animations, dark mode via CSS variables |
| **JavaScript (Vanilla)** | DOM manipulation, interactivity, localStorage, event handling |

### Backend
| Technology | Purpose |
|-----------|---------|
| **PHP 7.4+** | Server-side logic, routing, authentication |
| **MySQLi** | Database interaction with prepared statements |
| **Sessions** | User authentication and state management |

### Database
| Technology | Purpose |
|-----------|---------|
| **MySQL 5.7+** | Relational database for users, courses, enrollments, artwork, comments |

### Server
| Technology | Purpose |
|-----------|---------|
| **Apache/XAMPP** | Local development server |
| **LAMP/WAMP** | Production deployment stack |

---

## Directory Structure

```
art-school-website/
├── index.php                    # Landing page with hero section
├── login.php                    # Login form with role selection
├── register.php                 # Student registration
├── logout.php                   # Session termination
│
├── courses.php                  # Course catalog with search & filters
├── course-details.php           # Individual course page with enrollment & comments
├── gallery.php                  # Public artwork gallery with lightbox
│
├── dashboard-student.php        # Student profile & enrollment tracking
├── dashboard-teacher.php        # Teacher course management dashboard
├── dashboard-admin.php          # Admin control panel with analytics
│
├── profile-student.php          # Student profile editor (bio, avatar, contact)
├── profile-teacher.php          # Teacher profile editor
├── manage-courses.php           # Teacher course creation/editing interface
├── notifications.php            # Announcements & events display
│
├── css/
│   └── style.css               # Complete styling (1000+ lines)
│                               # - Dark mode with CSS variables
│                               # - Responsive grid layouts
│                               # - Animations & transitions
│                               # - Component styles
│
├── js/
│   └── main.js                 # Client-side functionality (190 lines)
│                               # - Dark mode toggle with localStorage
│                               # - Password visibility toggle
│                               # - Scroll animations & reveals
│                               # - Lightbox gallery
│                               # - Header scroll effects
│                               # - Smooth scrolling
│
├── php/
│   ├── db.php                  # Database connection singleton
│   ├── db.example.php          # Connection template
│   ├── auth.php                # Login/registration handlers
│   ├── functions.php           # Helper functions & utilities
│   ├── translations.php        # i18n arrays (English & Amharic)
│   └── setup.php               # Database initialization script
│
├── images/
│   ├── logo.png                # School logo
│   ├── hero.png                # Landing page hero image
│   ├── *.png                   # Course category images
│   ├── avatars/                # User profile pictures
│   └── gallery/                # Student artwork submissions
│
├── uploads/
│   └── materials/              # Course material files
│
├── .gitignore                  # Git exclusions
└── README.md                   # This file
```

---

## Installation & Setup

### Prerequisites

- **Apache** with PHP 7.4+ (XAMPP, WAMP, or LAMP)
- **MySQL** 5.7 or later
- **Modern Web Browser** (Chrome, Firefox, Safari, Edge)

### Step 1: Download & Place Files

1. Clone or download the project
2. Extract to your web server root:
   - XAMPP: `C:\xampp\htdocs\` (Windows) or `/Applications/XAMPP/xamppfiles/htdocs/` (Mac)
   - WAMP: `C:\wamp64\www\` (Windows)
   - Linux: `/var/www/html/`

### Step 2: Configure Database Connection

1. Open `php/db.php`
2. Update credentials to match your MySQL setup:

```php
$host = 'localhost';      // MySQL host
$user = 'root';           // MySQL username
$password = '';           // MySQL password
$database = 'art_school_db';
```

### Step 3: Initialize Database

**Option A: Using Browser** (Easiest)
1. Start Apache and MySQL
2. Visit: `http://localhost/art-school-website/php/setup.php`
3. Click the setup button
4. Check success message

**Option B: Using CLI**

```bash
cd /path/to/art-school-website
php php/setup.php
```

This creates:
- `art_school_db` database
- All required tables (users, courses, enrollments, etc.)
- Demo users and sample courses

### Step 4: Verify Installation

1. Visit: `http://localhost/art-school-website/`
2. Check that the landing page loads
3. Try logging in with demo credentials below

### Demo Accounts

| Role | Email | Password |
|------|-------|----------|
| Student | student@example.com | studentpass |
| Teacher | teacher@example.com | teacherpass |
| Admin | admin@example.com | adminpass |

---

## Usage Guide

### 🎓 For Students

1. **Visit Landing Page**
   - Explore featured courses and school information
   - Toggle dark mode using moon button (🌙) in top-right

2. **Browse Courses**
   - Navigate to `Courses` section
   - Search by title/keyword
   - Filter by category
   - View course details and teacher information

3. **Enroll in Course**
   - Click course to view details
   - Click `Enroll` button
   - Automatic redirection to dashboard after enrollment

4. **Dashboard**
   - View all enrolled courses
   - Upload artwork for submission
   - Track submission status (pending/approved/featured)
   - View recent announcements and upcoming events

5. **Profile Management**
   - Edit personal information (name, bio, contact)
   - Upload/change profile avatar
   - Update password

6. **Gallery**
   - Browse approved student artwork
   - Click images to view full-size in lightbox
   - Filter by featured or all artworks

### 👨‍🏫 For Teachers

1. **Dashboard**
   - View all courses you teach
   - See enrolled students for each course
   - Monitor recent submissions from students

2. **Manage Courses**
   - Navigate to `Curriculum Manager`
   - Create new courses with:
     - Title, description, category
     - Syllabus content
     - Course materials (PDF, documents)
   - Edit existing courses
   - View course status (pending approval, active, etc.)

3. **Student Submissions**
   - Review submitted artwork
   - Approve or reject submissions
   - Mark as featured gallery items

4. **Profile**
   - Update biography/credentials
   - Add professional avatar
   - Manage contact information

### 👨‍💼 For Administrators

1. **Admin Dashboard**
   - View system health metrics
   - Monitor database connections
   - See pending artworks requiring approval
   - Track recent enrollments

2. **User Management**
   - View all users (students, teachers, admins)
   - Edit user roles and information
   - Deactivate accounts if needed

3. **Content Moderation**
   - Approve/reject student artwork submissions
   - Feature exceptional work in gallery
   - Manage announcements

4. **System Status**
   - Check server health
   - Monitor database performance
   - View system logs

### 🌐 Language Switching

- Click `EN` or `አማ` (Amharic) in the top navigation
- Entire interface switches language
- All content updates in selected language

---

## User Roles & Workflows

### Authentication Flow

```
Landing Page
    ↓
    ├─→ Login (existing user)
    │   ├─→ Student Dashboard
    │   ├─→ Teacher Dashboard
    │   └─→ Admin Dashboard
    │
    └─→ Register (new student)
        → Email verification (optional)
        → Student Dashboard
```

### Student Workflow

```
Browse Courses → Enroll → Dashboard (view enrollments)
                           ↓
                    Upload Artwork → Submission Status
                           ↓
                    Edit Profile
                           ↓
                    View Gallery
```

### Teacher Workflow

```
Dashboard → Manage Courses → Create/Edit
                ↓
            View Students → Review Submissions → Approve/Reject
                ↓
            Update Profile
```

### Admin Workflow

```
Dashboard → System Status
            ↓
        User Management
            ↓
        Content Moderation
            ↓
        Reports & Analytics
```

---

## Feature Details

### 🌙 Dark Mode

**How It Works:**
- CSS variables (`--bg`, `--text-main`, `--accent`, etc.) switch values based on `.dark-mode` class
- User preference stored in localStorage with key `darkMode` (value: `'true'` or `'false'`)
- Inline script in `<head>` of every page applies theme instantly before page renders (prevents flash)

**Technical Implementation:**
```css
:root {
  --bg: #fff;        /* Light mode */
  --text-main: #1a1a1a;
}

body.dark-mode {
  --bg: #1a1a1a;     /* Dark mode */
  --text-main: #e4e4e4;
}
```

**JavaScript:**
```javascript
// Toggle theme
document.body.classList.toggle('dark-mode');
localStorage.setItem('darkMode', isDark ? 'true' : 'false');
```

### 👁️ Password Visibility Toggle

**Feature:**
- Eye icon (👁️) appears on all password inputs
- Click to toggle between hidden (password dots) and visible (plain text) states
- Opacity changes to indicate state (0.6 = hidden, 1.0 = visible)

### 🖼️ Lightbox Gallery

**Feature:**
- Click any gallery image to open full-screen lightbox
- Click outside image or close button to exit
- Smooth fade-in animation
- Prevents background scroll while lightbox is open

### 📱 Responsive Design

**Breakpoints:**
- Desktop: 1200px (full layout)
- Tablet: 768px-1199px (adjusted spacing)
- Mobile: < 768px (single column, stacked layout)

### ✨ Scroll Animations

- `.reveal` elements fade in and slide up as they enter viewport
- Header transforms on scroll (changes background and styling)
- Smooth scroll to anchor links

---

## Database Schema

### Users Table
```sql
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  name VARCHAR(255) NOT NULL,
  role ENUM('student', 'teacher', 'admin'),
  bio TEXT,
  phone VARCHAR(20),
  avatar VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Courses Table
```sql
CREATE TABLE courses (
  id INT PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) UNIQUE,
  description TEXT,
  category VARCHAR(100),
  teacher_id INT,
  syllabus TEXT,
  materials TEXT,
  material_path VARCHAR(255),
  status ENUM('pending', 'active', 'archived'),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (teacher_id) REFERENCES users(id)
);
```

### Enrollments Table
```sql
CREATE TABLE enrollments (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  course_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (course_id) REFERENCES courses(id),
  UNIQUE KEY (user_id, course_id)
);
```

### Artworks Table
```sql
CREATE TABLE artworks (
  id INT PRIMARY KEY AUTO_INCREMENT,
  student_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  status ENUM('pending', 'approved', 'featured') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES users(id)
);
```

### Comments Table
```sql
CREATE TABLE comments (
  id INT PRIMARY KEY AUTO_INCREMENT,
  course_id INT NOT NULL,
  user_id INT NOT NULL,
  content TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Announcements Table
```sql
CREATE TABLE announcements (
  id INT PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  target_role ENUM('all', 'student', 'teacher', 'admin'),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Events Table
```sql
CREATE TABLE events (
  id INT PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  event_date DATETIME NOT NULL,
  location VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## Localization (i18n)

### Supported Languages
- **English** (en)
- **Amharic** (am) - Full Ethiopian language support

### How It Works

All UI strings are defined in `php/translations.php`:

```php
$translations = [
  'en' => [
    'site_title' => 'Ge\'ez Art School',
    'home' => 'Home',
    'courses' => 'Courses',
    // ... more strings
  ],
  'am' => [
    'site_title' => 'የግእዝ ስነ-ጥበብ ትምህርት ቤት',
    'home' => 'መነሻ',
    'courses' => 'ስልጠናዎች',
    // ... more strings
  ]
];
```

### Adding Translations

1. Open `php/translations.php`
2. Add new key-value pair in both `'en'` and `'am'` arrays:

```php
'en' => [
  'new_feature' => 'New Feature Label',
  ...
],
'am' => [
  'new_feature' => 'አዲስ ባህሪ ምልክት',
  ...
]
```

3. Use in PHP: `<?php echo t('new_feature'); ?>`
4. Language is detected from `$_GET['lang']` or defaults to 'en'

### Language Switching

```php
$lang = get_lang(); // Returns 'en' or 'am'
t('key'); // Returns translated string
```

---

## Security

### ✅ Implemented Security Measures

1. **Password Hashing**
   ```php
   $hashed = password_hash($password, PASSWORD_DEFAULT);
   password_verify($input, $hashed);
   ```

2. **Prepared Statements** (Prevent SQL Injection)
   ```php
   $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
   $stmt->bind_param('s', $email);
   $stmt->execute();
   ```

3. **Session Management**
   ```php
   session_start();
   $_SESSION['user'] = [
     'id' => $user_id,
     'role' => $role
   ];
   ```

4. **Role-Based Access Control**
   ```php
   function require_role($required_role) {
     if ($_SESSION['user']['role'] !== $required_role) {
       header('Location: /login.php');
       exit;
     }
   }
   ```

5. **Input Validation**
   - Email format validation
   - Password strength checks
   - File type validation for uploads

### ⚠️ Recommendations for Production

1. **Enable HTTPS** - Use SSL/TLS certificates
2. **Environment Variables** - Store DB credentials in `.env` file
3. **Rate Limiting** - Implement login attempt throttling
4. **File Upload Security**
   - Validate file MIME types
   - Store uploads outside web root
   - Scan for malicious content
5. **CSRF Tokens** - Implement for all form submissions
6. **Logging** - Log authentication attempts and errors
7. **Database Backups** - Regular automated backups
8. **Admin Notifications** - Alert on suspicious activity

---

## API Documentation

### Authentication

**POST** `/php/auth.php`
- **Parameters:** `email`, `password`, `role` (and `name` for registration)
- **Response:** Redirects to dashboard or login on error
- **Action:** `register` to create new student account

### File Uploads

**Course Material Upload**
- Endpoint: `manage-courses.php` (POST)
- Accepts: PDF, DOCX, XLSX files
- Stored: `uploads/materials/`

**Avatar Upload**
- Endpoint: `profile-student.php` or `profile-teacher.php`
- Accepts: JPG, PNG, GIF images
- Stored: `images/avatars/`

**Artwork Submission**
- Endpoint: `dashboard-student.php` (POST)
- Accepts: JPG, PNG, GIF images
- Stored: `images/gallery/`
- Status: Pending until admin approval

### Database Queries

All queries use prepared statements with parameter binding:

```php
// Safe query with prepared statement
$stmt = $db->prepare('INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)');
$stmt->bind_param('ii', $user_id, $course_id);
$stmt->execute();
```

---

## Troubleshooting

### 🔧 Common Issues & Solutions

**Issue: "MySQL connection failed"**
- Check credentials in `php/db.php`
- Verify MySQL is running
- Ensure database user has correct permissions

**Issue: "Database not found" after setup**
- Run `http://localhost/art-school-website/php/setup.php` in browser
- Or execute: `php php/setup.php` from CLI
- Check MySQL error logs

**Issue: "Cannot modify header information" error**
- Ensure no whitespace before `<?php` tag
- Remove BOM (Byte Order Mark) from PHP files
- Check for output before `header()` calls

**Issue: Dark mode not persisting**
- Check browser localStorage is enabled
- Clear browser cache and reload
- Verify JavaScript is enabled

**Issue: Images not loading**
- Check image paths are relative (e.g., `images/logo.png`)
- Verify image files exist in correct directories
- Check file permissions (755 for directories, 644 for files)

**Issue: Course upload fails**
- Verify `uploads/materials/` directory exists and is writable
- Check PHP `upload_max_filesize` and `post_max_size` settings
- Ensure correct file MIME type is being uploaded

**Issue: Session expires immediately**
- Check PHP session timeout settings
- Verify `php.ini` has `session.gc_maxlifetime` set appropriately
- Check if domain cookies are properly configured

### 📋 Debugging Tips

1. **Enable Error Logging**
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

2. **Check PHP Version**
   ```bash
   php -v
   ```

3. **Verify Database Connection**
   ```bash
   mysql -u root -p art_school_db
   ```

4. **View Browser Console**
   - Press F12 → Console tab
   - Check for JavaScript errors

5. **Inspect Network Tab**
   - Press F12 → Network tab
   - Check for failed requests and response codes

---

## Future Enhancements

### 🚀 Planned Features

**v2.0 (Phase 1)**
- [ ] Email notifications for enrollments and submissions
- [ ] Student progress tracking and grades
- [ ] Course prerequisites and learning paths
- [ ] Discussion forums per course
- [ ] Live chat support
- [ ] Course certificates on completion

**v2.5 (Phase 2)**
- [ ] Video course content with streaming
- [ ] Assignment submissions and grading rubrics
- [ ] Student portfolio pages
- [ ] Advanced search with Elasticsearch
- [ ] Analytics dashboard for teachers
- [ ] API for mobile applications

**v3.0 (Phase 3)**
- [ ] Payment integration for premium courses
- [ ] Teacher verification and credentials
- [ ] Peer review system for student work
- [ ] AI-powered artwork classification
- [ ] Social features (likes, comments, follows)
- [ ] Mobile native apps (iOS/Android)

### 🔧 Technical Improvements

- [ ] Implement REST API layer
- [ ] Add unit and integration tests
- [ ] Docker containerization
- [ ] Database query optimization
- [ ] Caching layer (Redis)
- [ ] Message queue for async tasks
- [ ] CDN for static assets
- [ ] Database replication for HA

### 📱 UI/UX Enhancements

- [ ] Accessibility improvements (WCAG 2.1 AA)
- [ ] Performance optimization (reduce to < 3s FCP)
- [ ] PWA support for offline access
- [ ] Progressive image loading
- [ ] Advanced filtering and sorting
- [ ] Personalized dashboard recommendations

---

## Contributing

This is a portfolio project. Contributions and suggestions are welcome!

**To contribute:**
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## License

This project is open source and available under the MIT License.

---

## Support & Contact

For issues, questions, or suggestions:
- Open an issue on the repository
- Contact: support@geeartschool.local

---

## Acknowledgments

- Ethiopian cultural heritage inspiration
- Modern web development best practices
- Bootstrap & Tailwind CSS frameworks (design reference)
- Font: Playfair Display & Inter

---

**Last Updated:** January 2026
**Version:** 1.0.0
**Status:** Production Ready

---

### Quick Start Reference

```bash
# 1. Extract files to web root
cp -r art-school-website /path/to/webroot/

# 2. Configure database
nano /path/to/webroot/art-school-website/php/db.php

# 3. Initialize database
php /path/to/webroot/art-school-website/php/setup.php

# 4. Start web server
# XAMPP: Start Apache & MySQL
# Linux: sudo systemctl start apache2 mysql

# 5. Visit in browser
# http://localhost/art-school-website/

# 6. Login with demo account
# Email: student@example.com
# Password: studentpass
```

---

**Enjoy exploring the Ge'ez Art School platform!** 🎨
