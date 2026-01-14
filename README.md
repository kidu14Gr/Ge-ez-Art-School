# Ethiopian Art School Website

Portfolio project: a simple full-stack Art School website built with PHP, MySQL, HTML, CSS, and plain JavaScript.

## Purpose
This project demonstrates a small educational site focused on Ethiopian art traditions and contemporary practice. It includes:
- Multi-language support (English & Amharic)
- Authentication with Student and Teacher roles
- Student enrollment and teacher view of enrolled students
- Responsive, semantic frontend without frameworks

## Tech Stack
- Frontend: HTML5, CSS3, Vanilla JavaScript
- Backend: PHP (no frameworks)
- Database: MySQL

## Files
- `index.php` — Landing page
- `courses.php` — Courses list & enrollments
- `login.php` — Login form
- `dashboard-student.php` — Student dashboard
- `dashboard-teacher.php` — Teacher dashboard
- `logout.php` — Logs out session
- `css/style.css` — Styles
- `js/main.js` — Simple client JS
- `images/` — Images (logo.png, hero.jpg, gallery/)
- `php/db.php` — DB connection
- `php/auth.php` — Login handler
- `php/functions.php` — Helpers, session, enrollment
- `php/translations.php` — Translation arrays (English & Amharic)
- `php/setup.php` — Convenience script to create DB and seed demo data

## Setup (XAMPP / WAMP / LAMP)
1. Place the `art-school-website` folder into your web server document root (e.g., `htdocs` for XAMPP).
2. Ensure PHP and MySQL are running.
3. Edit DB credentials in `php/db.php` if your MySQL uses a non-default username/password.
4. Run the setup script once to create the database and seed demo data:

   - In browser: `http://localhost/art-school-website/php/setup.php`
   - Or via CLI (from project root):

```bash
php php/setup.php
```

This will create the `art_school_db` database and seed sample users and courses. Demo accounts:
- Student: `student@example.com` / `studentpass`
- Teacher: `teacher@example.com` / `teacherpass`

## Usage
- Visit `/art-school-website/index.php` to view the landing page.
- Click `Courses` to view available courses. Students can enroll when logged in.
- Login at `/art-school-website/login.php`. Choose role (Student/Teacher).
- Student dashboard shows enrolled courses. Teacher dashboard lists courses taught and enrolled students.

## Localization
- Language can be switched using the links in the header. Translations are in `php/translations.php` using PHP arrays.

## Security Notes
- Passwords are hashed using `password_hash` and verified with `password_verify`.
- DB queries use prepared statements to avoid SQL injection.
- For production, set strict DB credentials and HTTPS, and validate inputs thoroughly.

## Extending
- Replace placeholder images in `/images` with real assets.
- Add registration flow, admin user, file uploads, and richer student profiles.

## Troubleshooting
- If DB connection fails, confirm credentials in `php/db.php` and that MySQL is running.
- Run `php php/setup.php` from CLI to view any errors during setup.

Enjoy exploring the Ethiopian Art School demo site!
