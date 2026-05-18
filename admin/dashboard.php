<?php
$required_role = 'admin';
require_once __DIR__ . '/../php/auth_guard.php';

$fullName = htmlspecialchars($_SESSION['user_full_name'] ?? $_SESSION['user_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
$firstName = htmlspecialchars($_SESSION['user_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard &mdash; Greenfield Institute</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/dashboard.css"/>
</head>
<body class="dash-body" data-role="admin">

  <div class="dash-layout">
    <aside class="dash-sidebar admin-sidebar">
      <div class="sidebar-brand">
        <a href="../index.html">
          <img src="../images/logo.png" alt="Greenfield Institute"/>
        </a>
        <p class="sidebar-role">Administration</p>
      </div>
      <nav class="sidebar-nav">
        <a href="#section-overview" data-section="section-overview" class="active">
          <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="2" y="2" width="7" height="7" rx="1.5"/><rect x="11" y="2" width="7" height="4" rx="1.5"/><rect x="11" y="9" width="7" height="9" rx="1.5"/><rect x="2" y="12" width="7" height="6" rx="1.5"/></svg>
          Overview
        </a>
        <a href="#section-courses" data-section="section-courses">
          <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M3 5h14M3 10h14M3 15h9" stroke-linecap="round"/></svg>
          Manage Courses
        </a>
        <a href="#section-students" data-section="section-students">
          <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.4"><circle cx="8" cy="7" r="3"/><path d="M2 17c0-3 2.5-5 6-5s6 2 6 5" stroke-linecap="round"/></svg>
          Students
        </a>
        <a href="#section-registrations" data-section="section-registrations">
          <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="3" y="4" width="14" height="13" rx="2"/><path d="M7 4V2M13 4V2M3 9h14" stroke-linecap="round"/></svg>
          Registrations
        </a>
      </nav>
      <div class="sidebar-footer">
        <button type="button" class="btn-signout" id="btnSignOut">Sign Out</button>
      </div>
    </aside>

    <div class="dash-main">
      <header class="dash-topbar">
        <p class="topbar-greeting">Signed in as <strong><?= $fullName ?></strong></p>
        <p class="topbar-date" id="topbarDate"></p>
      </header>

      <main class="dash-content">

        <section id="section-overview" class="dash-section active">
          <div class="section-head" data-scroll-reveal>
            <h2>Overview</h2>
            <p>Institute-wide registration metrics at a glance, <?= $firstName ?>.</p>
          </div>

          <div class="stats-row four" data-scroll-reveal>
            <div class="stat-card admin-stat">
              <p class="stat-label">Total Students</p>
              <p class="stat-value" id="statStudents" data-count="0">0</p>
            </div>
            <div class="stat-card admin-stat">
              <p class="stat-label">Total Courses</p>
              <p class="stat-value" id="statCourses" data-count="0">0</p>
            </div>
            <div class="stat-card admin-stat">
              <p class="stat-label">Registrations</p>
              <p class="stat-value" id="statRegs" data-count="0">0</p>
            </div>
            <div class="stat-card admin-stat">
              <p class="stat-label">Departments</p>
              <p class="stat-value" id="statDepts" data-count="0">0</p>
            </div>
          </div>

          <div class="panel" data-scroll-reveal>
            <div class="panel-head">
              <h3>Quick Actions</h3>
              <div class="panel-actions">
                <button type="button" class="btn-outline" id="btnExportXml">Export as XML</button>
                <button type="button" class="btn-primary" id="btnAddCourseQuick">Add Course</button>
              </div>
            </div>
            <p style="padding:1rem 1.35rem;font-size:0.84rem;color:var(--text-mid)">
              Export live course data as XML or jump to course management to update the catalogue.
            </p>
          </div>
        </section>

        <section id="section-courses" class="dash-section">
          <div class="section-head">
            <h2>Manage Courses</h2>
            <p>Add, edit, or remove courses. Deletion is blocked while students are enrolled.</p>
          </div>

          <div class="panel" id="adminCoursesPanel" data-scroll-reveal>
            <div class="panel-head">
              <h3>Course Catalogue</h3>
              <div class="panel-actions">
                <button type="button" class="btn-primary" id="btnAddCourse">Add Course</button>
              </div>
            </div>

            <div class="course-form-panel" id="courseFormPanel">
              <h4 id="courseFormTitle" style="font-family:var(--font-serif);margin-bottom:0.85rem">Add Course</h4>
              <form class="course-form" id="courseForm">
                <input type="hidden" name="id" id="courseFormId"/>
                <input type="hidden" id="courseFormAction" value="add"/>
                <div class="form-field">
                  <label for="courseTitle">Title</label>
                  <input type="text" id="courseTitle" name="title" required/>
                </div>
                <div class="form-field">
                  <label for="courseCode">Course Code</label>
                  <input type="text" id="courseCode" name="code" required placeholder="SCI-101"/>
                </div>
                <div class="form-field">
                  <label for="courseDept">Department</label>
                  <input type="text" id="courseDept" name="department" required/>
                </div>
                <div class="form-field">
                  <label for="courseInstructor">Instructor</label>
                  <input type="text" id="courseInstructor" name="instructor" required/>
                </div>
                <div class="form-field">
                  <label for="courseCredits">Credits</label>
                  <input type="number" id="courseCredits" name="credits" min="1" max="12" value="3" required/>
                </div>
                <div class="form-field">
                  <label for="courseCapacity">Capacity</label>
                  <input type="number" id="courseCapacity" name="capacity" min="1" max="500" value="30" required/>
                </div>
                <div class="form-field full">
                  <label for="courseDesc">Description</label>
                  <textarea id="courseDesc" name="description"></textarea>
                </div>
                <div class="form-actions">
                  <button type="submit" class="btn-primary">Save Course</button>
                  <button type="button" class="btn-outline" id="btnCancelCourse">Cancel</button>
                </div>
              </form>
            </div>

            <table class="data-table">
              <thead>
                <tr>
                  <th>Title</th>
                  <th>Code</th>
                  <th>Department</th>
                  <th>Instructor</th>
                  <th>Credits</th>
                  <th>Enrollment</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="adminCoursesBody">
                <tr><td colspan="7" class="table-empty">Loading...</td></tr>
              </tbody>
            </table>
          </div>
        </section>

        <section id="section-students" class="dash-section">
          <div class="section-head">
            <h2>Students</h2>
            <p>All registered students and their enrollment counts.</p>
          </div>
          <div class="panel" data-scroll-reveal>
            <table class="data-table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Student ID</th>
                  <th>Enrolled</th>
                  <th>Registered</th>
                </tr>
              </thead>
              <tbody id="adminStudentsBody">
                <tr><td colspan="5" class="table-empty">Loading...</td></tr>
              </tbody>
            </table>
          </div>
        </section>

        <section id="section-registrations" class="dash-section">
          <div class="section-head">
            <h2>Registrations</h2>
            <p>Complete log of course enrollments across the institute.</p>
          </div>
          <div class="panel" data-scroll-reveal>
            <table class="data-table">
              <thead>
                <tr>
                  <th>Student</th>
                  <th>Course</th>
                  <th>Date Enrolled</th>
                </tr>
              </thead>
              <tbody id="adminRegsBody">
                <tr><td colspan="3" class="table-empty">Loading...</td></tr>
              </tbody>
            </table>
          </div>
        </section>

      </main>
    </div>
  </div>

  <div id="dashToast" class="toast" role="status"></div>

  <script src="../js/animations.js"></script>
  <script src="../js/dashboard.js"></script>
  <script>
    document.getElementById('btnAddCourseQuick')?.addEventListener('click', () => {
      document.querySelector('[data-section="section-courses"]')?.click();
      setTimeout(() => document.getElementById('btnAddCourse')?.click(), 200);
    });
  </script>
</body>
</html>
