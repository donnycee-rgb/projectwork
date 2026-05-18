# Greenfield Institute — Setup & Run Guide

This guide walks you through installing and running the Greenfield course registration system on your machine using **XAMPP** (Apache + MySQL + PHP). The stack is designed for local development on Windows, but the same steps apply on macOS or Linux if you use XAMPP or a similar AMP stack.

---

## What this project is

Greenfield Institute is a three-tier web application:

| Layer | Technology | Role |
|-------|------------|------|
| UI | HTML, CSS, vanilla JavaScript | Landing page, login/register, student and admin dashboards |
| Business logic | PHP | Validation, authentication, enrollment rules, JSON APIs |
| Data | MySQL (via PDO) | Users, courses, registrations |

There is **no sample course or student data** in the database script. After setup you only have an **admin account**; courses and registrations are created through the admin and student portals.

---

## Requirements

- **XAMPP** (or equivalent) with:
  - **Apache** (port **80** by default)
  - **MySQL** / MariaDB
  - **PHP 8.0+** (7.4+ may work; 8.x recommended)
- A modern browser (Chrome, Firefox, Edge)
- The project folder placed where Apache can serve it

### Recommended XAMPP download

- [Apache Friends — XAMPP for Windows](https://www.apachefriends.org/)

Install XAMPP to a path **without spaces** if possible (e.g. `C:\xampp`).

---

## Project folder location

Apache serves files from the **htdocs** directory. Place the project so the site root is the `greenfield` folder.

**Typical path:**

```
C:\xampp\htdocs\greenfield\
```

If your project is currently on the Desktop, either:

1. **Copy** the entire `greenfield` folder into `C:\xampp\htdocs\`, or  
2. **Move** it there.

Your workspace path may be:

```
C:\Users\<YourName>\Desktop\greenfield
```

After copying to htdocs, the public URL becomes:

```
http://localhost/greenfield/
```

> **Important:** All URLs in this guide assume the project lives at `http://localhost/greenfield/`. Adjust if you use a virtual host or a different folder name.

---

## Folder structure (reference)

```
greenfield/
├── index.html              # Public landing page
├── login.html              # Sign in / register (slide panels)
├── SETUP.md                # This file
├── admin/
│   ├── dashboard.html      # Admin overview
│   ├── manage-courses.html # Course CRUD
│   ├── registrations.html  # All registrations
│   └── dashboard.php       # Legacy/alternate PHP dashboard (optional)
├── student/
│   └── dashboard.php       # Student portal
├── css/
│   ├── landing.css
│   ├── auth.css
│   └── dashboard.css
├── js/
│   ├── main.js
│   ├── animations.js
│   ├── auth.js
│   └── dashboard.js
├── php/
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   ├── session.php
│   ├── courses.php
│   ├── registrations.php
│   ├── xml_export.php
│   ├── auth_guard.php
│   └── install_admin.php   # Run once, then delete
├── config/
│   └── db.php              # Database credentials
├── sql/
│   └── greenfield.sql      # Schema (no seed data)
├── xml/
│   └── courses.xml         # Static XML sample (assignment)
└── images/
    ├── logo.png
    ├── hero-bg.jpg
    └── image3.jpg … image8.jpg
```

---

## Step 1 — Start XAMPP services

1. Open **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.

Both should show a green “Running” status.

If Apache fails to start, port 80 is often in use (Skype, IIS, another web server). Either stop that program or change Apache’s port in `httpd.conf` and use `http://localhost:8080/greenfield/` instead.

---

## Step 2 — Create the database

You can use **phpMyAdmin** or the **MySQL command line**.

### Option A — phpMyAdmin (easiest)

1. In the browser, open: `http://localhost/phpmyadmin`
2. Click the **Import** tab.
3. Choose file: `greenfield/sql/greenfield.sql`
4. Click **Go**.

This creates:

- Database: `greenfield_db`
- Tables: `users`, `courses`, `registrations`

No users or courses are inserted by the SQL file itself.

### Option B — MySQL CLI

```bash
cd C:\xampp\mysql\bin
mysql -u root -p < C:\xampp\htdocs\greenfield\sql\greenfield.sql
```

Press Enter if your root user has an **empty password** (XAMPP default). Otherwise enter your MySQL password.

### Verify

In phpMyAdmin, select **greenfield_db** on the left. You should see three empty tables (or empty rows).

---

## Step 3 — Configure database connection

Edit `config/db.php` if your MySQL settings differ from XAMPP defaults:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'greenfield_db');
define('DB_USER', 'root');       // your MySQL username
define('DB_PASS', '');           // your MySQL password (empty on default XAMPP)
```

| Setting | XAMPP default |
|---------|----------------|
| Host | `localhost` |
| Database | `greenfield_db` |
| User | `root` |
| Password | *(empty)* |

Save the file after any changes.

---

## Step 4 — Create the admin account (required)

The database ships **without** an admin user. Run the installer **once**:

1. In the browser, open:  
   `http://localhost/greenfield/php/install_admin.php`
2. You should see plain text confirming the account:

   - **Email:** `admin@greenfield.edu`  
   - **Password:** `Admin@1234`

3. **Delete** `php/install_admin.php` from the project after success (security best practice).

If you need to reset the admin password later, temporarily restore `install_admin.php`, run it again (it uses `ON DUPLICATE KEY UPDATE`), then delete it again.

---

## Step 5 — Run the application

With Apache and MySQL running, use these URLs:

| Page | URL |
|------|-----|
| Landing | http://localhost/greenfield/ |
| Login / Register | http://localhost/greenfield/login.html |
| Admin overview | http://localhost/greenfield/admin/dashboard.html |
| Manage courses | http://localhost/greenfield/admin/manage-courses.html |
| Registrations | http://localhost/greenfield/admin/registrations.html |
| Student dashboard | http://localhost/greenfield/student/dashboard.php |

### First-time admin workflow

1. Open `login.html`.
2. Sign in with `admin@greenfield.edu` / `Admin@1234`.
3. You are redirected to **Admin Overview** (`admin/dashboard.html`).
4. Go to **Manage Courses** and add courses (title, code, department, instructor, credits, capacity, description).
5. Open **Registrations** to view enrollments once students register.

### Student workflow

1. On `login.html`, use **Create Account** (slide panel).
2. Fill in name, email, student ID, password.  
   Student ID format: `SCM211-XXXX/YYYY` (e.g. `SCM211-0320/2024`).
3. After registration you are signed in and sent to `student/dashboard.php`.
4. Browse available courses and **Enroll** / **Unenroll** from the dashboard.

Students **cannot** access admin HTML pages or admin API actions; PHP enforces `role === 'admin'`.

---

## How authentication works

- **Login** posts to `php/login.php` and returns JSON with a redirect URL.
- PHP sets a **server session** (`$_SESSION['user_id']`, `user_role`, etc.).
- **Admin HTML pages** call `php/session.php` via JavaScript; if unauthorized, you are sent back to `login.html`.
- **Student dashboard** (`dashboard.php`) uses `php/auth_guard.php` at the top of the file.
- **Sign out:** sidebar link to `php/logout.php` destroys the session and redirects to `login.html`.

Login also stores the display name in **sessionStorage** for the admin sidebar label (`#adminName`).

---

## API endpoints (for debugging)

All JSON APIs expect an active PHP session (cookie). Admin-only routes return `403` for students.

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `php/login.php` | POST | Sign in |
| `php/register.php` | POST | Create student account |
| `php/logout.php` | GET | Sign out |
| `php/session.php` | GET | Admin session check |
| `php/courses.php` | GET | List courses |
| `php/courses.php?action=admin` | GET | Admin course list |
| `php/courses.php` | POST | Add / edit / delete course |
| `php/registrations.php?action=all` | GET | All registrations (admin) |
| `php/registrations.php?action=recent` | GET | 10 latest (admin overview) |
| `php/registrations.php?action=stats` | GET | Stats (admin or student) |
| `php/xml_export.php` | GET | Download courses XML (admin) |

Use the browser **Network** tab if AJAX calls fail; check for `401` (not logged in) or `500` (database/config).

---

## XML requirement

- **Static sample:** `xml/courses.xml` — example structure for coursework.
- **Live export:** Admin **Export as XML** buttons call `php/xml_export.php`, which builds XML from the database.

You must be logged in as admin for the export to work.

---

## Troubleshooting

### “Database connection failed”

- MySQL is not running in XAMPP.
- Wrong `DB_USER` / `DB_PASS` in `config/db.php`.
- Database `greenfield_db` was not created — re-import `sql/greenfield.sql`.

### Login succeeds but admin pages redirect to login

- Cookies blocked for `localhost`.
- Session not persisting — ensure you use the same host always (`localhost`, not mixing `127.0.0.1`).
- Apache `session.save_path` misconfigured (rare on default XAMPP).

### Blank page or PHP code shown in browser

- Apache is not processing PHP. Confirm files are under `htdocs` and Apache is running.
- Open a test file: `http://localhost/greenfield/php/session.php` — should return JSON, not raw `<?php`.

### Admin overview shows zeros and empty tables

- Expected on a fresh install. Add courses under **Manage Courses**.
- Registrations appear only after students enroll.

### Cannot delete a course

- Deletion is blocked if any student is enrolled. Unenroll students first or keep the course.

### Student ID rejected on register

- Format must match: three letters, three digits, hyphen, four digits, slash, four digits.  
  Example: `SCM211-0320/2024`.

### `install_admin.php` not found or 404

- Confirm project path: `http://localhost/greenfield/php/install_admin.php`.
- File must exist under `htdocs/greenfield/php/`.

### Images or CSS missing

- Open the site as `http://localhost/greenfield/...`, not as a `file://` path.
- Check that `images/` and `css/` folders are present next to `index.html`.

---

## Security notes (local development)

- Change the default admin password after first login if you deploy beyond localhost.
- Remove `php/install_admin.php` after creating the admin user.
- Do not commit real passwords to version control.
- `config/db.php` uses an empty root password — acceptable for local XAMPP only, not for production.

---

## Quick checklist

- [ ] XAMPP Apache and MySQL running  
- [ ] Project in `C:\xampp\htdocs\greenfield\`  
- [ ] `greenfield.sql` imported into MySQL  
- [ ] `config/db.php` credentials correct  
- [ ] `install_admin.php` run once, then deleted  
- [ ] `http://localhost/greenfield/` loads the landing page  
- [ ] Admin login works and **Manage Courses** can add a course  
- [ ] Student can register and enroll on `student/dashboard.php`  

---

## Summary

1. Install XAMPP and start **Apache** + **MySQL**.  
2. Copy `greenfield` into **htdocs**.  
3. Import **`sql/greenfield.sql`**.  
4. Set **`config/db.php`** if needed.  
5. Run **`php/install_admin.php`** once, then delete it.  
6. Open **`http://localhost/greenfield/`** and sign in as admin or register as a student.

For questions about assignment features (XML, three-tier design, session guards), refer to your course brief and the PHP files under `php/` and dashboards under `admin/` and `student/`.
