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
  <title>Admin Portal &mdash; Greenfield Institute</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/dashboard.css"/>
</head>
<body class="dash-body" data-role="admin" data-admin-spa="1">

  <div class="dash-layout">
    <aside class="dash-sidebar admin-sidebar">
      <div class="sidebar-brand">
        <a href="../index.html">
          <img src="../images/logo.svg" alt="Greenfield Institute"/>
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
        <a href="#section-fees" data-section="section-fees">
          <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="2" y="5" width="16" height="12" rx="2"/><path d="M2 9h16M5 13h2" stroke-linecap="round"/></svg>
          Student Fees
        </a>
        <a href="#section-admins" data-section="section-admins">
          <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M10 2l2.5 5 5.5.8-4 3.9.9 5.5L10 14.8 5.1 17.2l.9-5.5-4-3.9 5.5-.8L10 2z" stroke-linejoin="round"/></svg>
          Admin Accounts
        </a>
      </nav>
      <div class="sidebar-footer">
        <a href="../php/logout.php" class="btn-signout" style="display:block;text-align:center;text-decoration:none;">Sign Out</a>
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
            <p>Institute-wide metrics at a glance, <?= $firstName ?>.</p>
          </div>
          <div id="overviewError" class="section-error" hidden></div>

          <div class="admin-stats-editorial" data-scroll-reveal>
            <article class="stat-card featured">
              <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5"><circle cx="9" cy="8" r="3.5"/><path d="M3 20c0-3.5 2.5-5.5 6-5.5s6 2 6 5.5"/></svg>
              </div>
              <p class="stat-label">Total Students</p>
              <p class="stat-value" id="statStudents" data-count="0">0</p>
            </article>
            <div class="stat-stack">
              <article class="stat-card stat-compact">
                <div class="stat-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5"><rect x="4" y="5" width="16" height="14" rx="2"/><path d="M8 5V3M16 5V3M4 11h16" stroke-linecap="round"/></svg>
                </div>
                <div class="stat-body">
                  <p class="stat-label">Total Courses</p>
                  <p class="stat-value" id="statCourses" data-count="0">0</p>
                </div>
              </article>
              <article class="stat-card stat-compact">
                <div class="stat-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5"><path d="M6 6h12v12H6z"/><path d="M9 12l2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div class="stat-body">
                  <p class="stat-label">Registrations</p>
                  <p class="stat-value" id="statRegs" data-count="0">0</p>
                </div>
              </article>
              <article class="stat-card stat-compact">
                <div class="stat-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5"><path d="M4 18V8l8-4 8 4v10"/><path d="M9 18v-6h6v6"/></svg>
                </div>
                <div class="stat-body">
                  <p class="stat-label">Departments</p>
                  <p class="stat-value" id="statDepts" data-count="0">0</p>
                </div>
              </article>
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
          </div>

          <section class="panel" data-scroll-reveal>
            <div class="panel-head"><h3>Enrollment Capacity</h3></div>
            <div class="capacity-list" id="capacityList">
              <p class="table-empty">Loading courses...</p>
            </div>
          </section>

          <section class="panel" data-scroll-reveal>
            <div class="panel-head"><h3>Recent Registrations</h3></div>
            <table class="data-table">
              <thead>
                <tr>
                  <th>Student</th>
                  <th>Course</th>
                  <th>Department</th>
                  <th>Date Enrolled</th>
                </tr>
              </thead>
              <tbody id="recentRegsBody">
                <tr><td colspan="4" class="table-empty">Loading...</td></tr>
              </tbody>
            </table>
          </section>
        </section>

        <section id="section-courses" class="dash-section">
          <div class="section-head">
            <h2>Manage Courses</h2>
            <p>Add, edit, or remove courses. Deletion is blocked while students are enrolled.</p>
          </div>
          <div id="coursesError" class="section-error" hidden></div>

          <div class="page-header-row">
            <h3 style="font-family:var(--font-serif);font-size:1.15rem">Course Catalogue</h3>
            <button type="button" class="btn-primary" id="btnAddCourse">Add Course</button>
          </div>

          <section class="panel">
            <div class="course-form-panel" id="addCoursePanel">
              <form class="course-form" id="addCourseForm">
                <div class="form-field">
                  <label for="addTitle">Course Title</label>
                  <input type="text" id="addTitle" name="title" required/>
                </div>
                <div class="form-field">
                  <label for="addCode">Course Code</label>
                  <input type="text" id="addCode" name="code" required placeholder="SCI-101"/>
                </div>
                <div class="form-field">
                  <label for="addDept">Department</label>
                  <input type="text" id="addDept" name="department" required/>
                </div>
                <div class="form-field">
                  <label for="addInstructor">Instructor</label>
                  <input type="text" id="addInstructor" name="instructor" required/>
                </div>
                <div class="form-field">
                  <label for="addCredits">Credits</label>
                  <input type="number" id="addCredits" name="credits" min="1" max="12" value="3" required/>
                </div>
                <div class="form-field">
                  <label for="addCapacity">Capacity</label>
                  <input type="number" id="addCapacity" name="capacity" min="1" max="500" value="30" required/>
                </div>
                <div class="form-field full">
                  <label for="addDesc">Description</label>
                  <textarea id="addDesc" name="description"></textarea>
                </div>
                <div class="form-actions">
                  <button type="submit" class="btn-primary">Save Course</button>
                  <button type="button" class="btn-cancel-link" id="btnCancelAdd">Cancel</button>
                </div>
              </form>
            </div>

            <table class="data-table">
              <thead>
                <tr>
                  <th>Code</th>
                  <th>Title</th>
                  <th>Department</th>
                  <th>Instructor</th>
                  <th>Credits</th>
                  <th>Enrolled / Capacity</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="coursesTableBody">
                <tr><td colspan="7" class="table-empty">Loading...</td></tr>
              </tbody>
            </table>
          </section>
        </section>

        <section id="section-students" class="dash-section">
          <div class="section-head">
            <h2>Students</h2>
            <p>All registered students and their enrollment counts.</p>
          </div>
          <div id="studentsError" class="section-error" hidden></div>
          <section class="panel" data-scroll-reveal>
            <table class="data-table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Student ID</th>
                  <th>Enrolled</th>
                  <th>Fees Paid (KES)</th>
                  <th>Registered</th>
                </tr>
              </thead>
              <tbody id="adminStudentsBody">
                <tr><td colspan="6" class="table-empty">Loading...</td></tr>
              </tbody>
            </table>
          </section>
        </section>

        <section id="section-registrations" class="dash-section">
          <div class="section-head">
            <h2>Registrations</h2>
            <p>Complete log of course enrollments across the institute.</p>
          </div>
          <div id="regsError" class="section-error" hidden></div>

          <dl class="summary-strip">
            <div>
              <dt>Total Registrations</dt>
              <dd id="sumTotal">0</dd>
            </div>
            <div>
              <dt>Most Popular Course</dt>
              <dd id="sumPopular">—</dd>
            </div>
            <div>
              <dt>Departments Represented</dt>
              <dd id="sumDepts">0</dd>
            </div>
          </dl>

          <section class="panel">
            <div class="export-strip">
              <button type="button" class="btn-outline" id="btnExportXmlRegs">Export as XML</button>
            </div>
            <div class="filter-bar">
              <input type="search" id="regSearch" placeholder="Search by student or course name..." aria-label="Search registrations"/>
              <select id="regDeptFilter" aria-label="Filter by department">
                <option value="">All departments</option>
              </select>
            </div>
            <table class="data-table" id="regsTable">
              <thead>
                <tr>
                  <th class="sortable" data-sort="student">Student Name <span class="sort-arrow">↕</span></th>
                  <th class="sortable" data-sort="student_id">Student ID <span class="sort-arrow">↕</span></th>
                  <th class="sortable" data-sort="course">Course Title <span class="sort-arrow">↕</span></th>
                  <th class="sortable" data-sort="department">Department <span class="sort-arrow">↕</span></th>
                  <th class="sortable" data-sort="credits">Credits <span class="sort-arrow">↕</span></th>
                  <th class="sortable" data-sort="registered_at">Date Enrolled <span class="sort-arrow active">▼</span></th>
                </tr>
              </thead>
              <tbody id="regsTableBody">
                <tr><td colspan="6" class="table-empty">Loading...</td></tr>
              </tbody>
            </table>
          </section>
        </section>

        <section id="section-fees" class="dash-section">
          <div class="section-head">
            <h2>Student Fees</h2>
            <p>Manage fee payments and the cost-per-credit setting used for enrollment limits.</p>
          </div>
          <div id="feesError" class="section-error" hidden></div>

          <section class="panel" data-scroll-reveal>
            <div class="panel-head">
              <h3>Institute Settings</h3>
            </div>
            <form class="course-form" id="feesSettingsForm" style="padding:0 1.35rem 1.35rem;max-width:420px">
              <div class="form-field">
                <label for="costPerCredit">Cost per credit (KES)</label>
                <input type="number" id="costPerCredit" name="cost_per_credit" min="1" step="0.01" required/>
              </div>
              <div class="form-actions">
                <button type="submit" class="btn-primary">Save Setting</button>
              </div>
            </form>
          </section>

          <section class="panel" data-scroll-reveal>
            <div class="panel-head"><h3>Student Fee Balances</h3></div>
            <table class="data-table">
              <thead>
                <tr>
                  <th>Student</th>
                  <th>Student ID</th>
                  <th>Fees Paid (KES)</th>
                  <th>Credits Available</th>
                  <th>Credits Used</th>
                  <th>Remaining</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="feesTableBody">
                <tr><td colspan="7" class="table-empty">Loading...</td></tr>
              </tbody>
            </table>
          </section>
        </section>

        <section id="section-admins" class="dash-section">
          <div class="section-head">
            <h2>Admin Accounts</h2>
            <p>Create or remove administrator accounts. You cannot delete your own account or the seeded primary admin.</p>
          </div>
          <div id="adminsError" class="section-error" hidden></div>

          <div class="page-header-row">
            <h3 style="font-family:var(--font-serif);font-size:1.15rem">Administrators</h3>
            <button type="button" class="btn-primary" id="btnAddAdmin">Add Admin</button>
          </div>

          <section class="panel">
            <div class="course-form-panel" id="addAdminPanel">
              <form class="course-form" id="addAdminForm">
                <div class="form-field">
                  <label for="adminFirst">First name</label>
                  <input type="text" id="adminFirst" name="first_name" required/>
                </div>
                <div class="form-field">
                  <label for="adminLast">Last name</label>
                  <input type="text" id="adminLast" name="last_name" required/>
                </div>
                <div class="form-field">
                  <label for="adminEmail">Email</label>
                  <input type="email" id="adminEmail" name="email" required/>
                </div>
                <div class="form-field">
                  <label for="adminPass">Temporary password</label>
                  <input type="password" id="adminPass" name="password" minlength="8" required/>
                </div>
                <div class="form-actions">
                  <button type="submit" class="btn-primary">Create Admin</button>
                  <button type="button" class="btn-cancel-link" id="btnCancelAdmin">Cancel</button>
                </div>
              </form>
            </div>

            <table class="data-table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Created</th>
                  <th>Created By</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="adminsTableBody">
                <tr><td colspan="5" class="table-empty">Loading...</td></tr>
              </tbody>
            </table>
          </section>
        </section>

      </main>
    </div>
  </div>

  <div id="feeEditModal" class="fee-edit-modal" hidden>
    <div class="fee-edit-backdrop" id="feeEditBackdrop"></div>
    <div class="fee-edit-dialog" role="dialog" aria-labelledby="feeEditTitle">
      <h4 id="feeEditTitle" style="font-family:var(--font-serif);margin-bottom:0.75rem">Update fees</h4>
      <p id="feeEditStudentName" style="font-size:0.84rem;color:var(--text-mid);margin-bottom:1rem"></p>
      <form id="feeEditForm">
        <input type="hidden" id="feeEditStudentId"/>
        <div class="form-field">
          <label for="feeEditAmount">Fees paid (KES)</label>
          <input type="number" id="feeEditAmount" min="0" step="0.01" required/>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-primary">Save</button>
          <button type="button" class="btn-outline" id="feeEditCancel">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <div id="dashToast" class="toast" role="status"></div>

  <style>
    .page-header-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: 1rem;
      flex-wrap: wrap;
    }
    .fee-edit-modal {
      position: fixed;
      inset: 0;
      z-index: 200;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
    }
    .fee-edit-modal[hidden] { display: none !important; }
    .fee-edit-backdrop {
      position: absolute;
      inset: 0;
      background: rgba(26, 46, 30, 0.55);
    }
    .fee-edit-dialog {
      position: relative;
      background: var(--white);
      border-radius: 12px;
      padding: 1.35rem;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    }
  </style>

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
