<?php
$required_role = 'admin';
require_once __DIR__ . '/../php/auth_guard.php';

$fullName  = htmlspecialchars($_SESSION['user_full_name'] ?? $_SESSION['user_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
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
  <style>
    /* ── Page header row ─────────────────────────────── */
    .page-header-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: 1rem;
      flex-wrap: wrap;
    }

    /* ── Fee edit modal ──────────────────────────────── */
    .modal-overlay {
      position: fixed;
      inset: 0;
      z-index: 200;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
    }
    .modal-overlay[hidden] { display: none !important; }
    .modal-backdrop {
      position: absolute;
      inset: 0;
      background: rgba(13, 61, 38, 0.55);
      backdrop-filter: blur(2px);
    }
    .modal-dialog {
      position: relative;
      background: var(--white);
      border-radius: 16px;
      padding: 1.75rem;
      width: 100%;
      max-width: 420px;
      box-shadow: 0 24px 64px rgba(0,0,0,0.18);
      animation: modalIn .2s ease;
    }
    @keyframes modalIn {
      from { opacity: 0; transform: translateY(16px) scale(.97); }
      to   { opacity: 1; transform: none; }
    }
    .modal-dialog h4 {
      font-family: var(--font-serif);
      font-size: 1.15rem;
      margin-bottom: .5rem;
    }
    .modal-dialog .modal-sub {
      font-size: .82rem;
      color: var(--text-mid);
      margin-bottom: 1.25rem;
    }

    /* ── Password reveal modal ───────────────────────── */
    .pwd-modal-dialog {
      max-width: 480px;
    }
    .pwd-warning {
      display: flex;
      align-items: flex-start;
      gap: .75rem;
      background: #fff8e1;
      border: 1.5px solid #f9a825;
      border-radius: 10px;
      padding: .85rem 1rem;
      margin-bottom: 1.25rem;
    }
    .pwd-warning-icon {
      width: 20px; height: 20px;
      background: #f9a825;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
      margin-top: .05rem;
    }
    .pwd-warning-icon svg { width: 12px; height: 12px; stroke: #fff; fill: none; stroke-width: 2.5; stroke-linecap: round; }
    .pwd-warning p {
      font-size: .78rem;
      color: #7a5800;
      line-height: 1.5;
      font-weight: 500;
    }
    .pwd-admin-info {
      font-size: .8rem;
      color: var(--text-mid);
      margin-bottom: .85rem;
      padding: .65rem .9rem;
      background: var(--off-white, #f8faf8);
      border-radius: 8px;
      border: 1px solid var(--border);
    }
    .pwd-admin-info strong { color: var(--text); }
    .pwd-box-wrap {
      position: relative;
      margin-bottom: 1.25rem;
    }
    .pwd-box {
      width: 100%;
      background: var(--forest, #0d3d26);
      color: #4ade80;
      font-family: 'Courier New', Courier, monospace;
      font-size: 1.05rem;
      font-weight: 700;
      letter-spacing: .08em;
      padding: 1rem 3.5rem 1rem 1.1rem;
      border-radius: 10px;
      border: none;
      outline: none;
      word-break: break-all;
      user-select: all;
      line-height: 1.5;
    }
    .pwd-copy-btn {
      position: absolute;
      top: 50%; right: .75rem;
      transform: translateY(-50%);
      background: rgba(74,222,128,.15);
      border: 1.5px solid rgba(74,222,128,.35);
      border-radius: 7px;
      color: #4ade80;
      width: 36px; height: 36px;
      display: flex; align-items: center; justify-content: center;
      cursor: pointer;
      transition: all .2s;
    }
    .pwd-copy-btn:hover { background: rgba(74,222,128,.3); }
    .pwd-copy-btn.copied { background: #4ade80; color: var(--forest, #0d3d26); border-color: #4ade80; }
    .pwd-copy-btn svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .pwd-strength {
      display: flex;
      align-items: center;
      gap: .5rem;
      font-size: .7rem;
      color: #4ade80;
      margin-bottom: 1.25rem;
    }
    .pwd-strength-dots {
      display: flex; gap: 3px;
    }
    .pwd-strength-dot {
      width: 8px; height: 8px;
      border-radius: 50%;
      background: #4ade80;
    }
    .pwd-close-btn {
      width: 100%;
      padding: .75rem;
      background: var(--accent, #27ae60);
      color: #fff;
      border: none;
      border-radius: 10px;
      font-family: var(--font-sans);
      font-size: .88rem;
      font-weight: 600;
      cursor: pointer;
      transition: background .2s;
      position: relative;
    }
    .pwd-close-btn:hover { background: var(--forest, #0d3d26); }
    .pwd-close-btn:disabled { opacity: .6; cursor: not-allowed; }
    .pwd-close-countdown {
      display: inline-block;
      margin-left: .4rem;
      font-size: .78rem;
      opacity: .75;
    }
    .pwd-copied-toast {
      text-align: center;
      font-size: .72rem;
      color: #4ade80;
      margin-bottom: .75rem;
      height: 16px;
      transition: opacity .2s;
    }

    /* ── Admin table badges ──────────────────────────── */
    .admin-you-badge {
      display: inline-block;
      font-size: .6rem;
      font-weight: 700;
      letter-spacing: .6px;
      text-transform: uppercase;
      background: rgba(39,174,96,.12);
      color: var(--accent, #27ae60);
      border: 1px solid rgba(39,174,96,.25);
      border-radius: 50px;
      padding: .15rem .5rem;
      margin-left: .4rem;
      vertical-align: middle;
    }
    .btn-reset-pwd {
      display: inline-flex;
      align-items: center;
      gap: .3rem;
      font-size: .72rem;
      font-weight: 600;
      padding: .3rem .7rem;
      border-radius: 6px;
      border: 1.5px solid var(--border);
      background: var(--white);
      color: var(--text-mid);
      cursor: pointer;
      transition: all .2s;
      margin-right: .35rem;
    }
    .btn-reset-pwd:hover { border-color: var(--accent); color: var(--accent); }
    .btn-reset-pwd svg { width: 12px; height: 12px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; }
  </style>
</head>
<body class="dash-body" data-role="admin" data-admin-spa="1">

  <div class="dash-layout">

    <!-- ── SIDEBAR ──────────────────────────────────────────────── -->
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

    <!-- ── MAIN ─────────────────────────────────────────────────── -->
    <div class="dash-main">
      <header class="dash-topbar">
        <p class="topbar-greeting">Signed in as <strong><?= $fullName ?></strong></p>
        <p class="topbar-date" id="topbarDate"></p>
      </header>

      <main class="dash-content">

        <!-- OVERVIEW -->
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

        <!-- COURSES -->
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
                  <th>Code</th><th>Title</th><th>Department</th>
                  <th>Instructor</th><th>Credits</th><th>Enrolled / Capacity</th><th>Actions</th>
                </tr>
              </thead>
              <tbody id="coursesTableBody">
                <tr><td colspan="7" class="table-empty">Loading...</td></tr>
              </tbody>
            </table>
          </section>
        </section>

        <!-- STUDENTS -->
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
                  <th>Name</th><th>Email</th><th>Student ID</th>
                  <th>Enrolled</th><th>Fees Paid (KES)</th><th>Registered</th>
                </tr>
              </thead>
              <tbody id="adminStudentsBody">
                <tr><td colspan="6" class="table-empty">Loading...</td></tr>
              </tbody>
            </table>
          </section>
        </section>

        <!-- REGISTRATIONS -->
        <section id="section-registrations" class="dash-section">
          <div class="section-head">
            <h2>Registrations</h2>
            <p>Complete log of course enrollments across the institute.</p>
          </div>
          <div id="regsError" class="section-error" hidden></div>
          <dl class="summary-strip">
            <div><dt>Total Registrations</dt><dd id="sumTotal">0</dd></div>
            <div><dt>Most Popular Course</dt><dd id="sumPopular">—</dd></div>
            <div><dt>Departments Represented</dt><dd id="sumDepts">0</dd></div>
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

        <!-- FEES -->
        <section id="section-fees" class="dash-section">
          <div class="section-head">
            <h2>Student Fees</h2>
            <p>Manage fee payments and the cost-per-credit setting used for enrollment limits.</p>
          </div>
          <div id="feesError" class="section-error" hidden></div>
          <section class="panel" data-scroll-reveal>
            <div class="panel-head"><h3>Institute Settings</h3></div>
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
                  <th>Student</th><th>Student ID</th><th>Fees Paid (KES)</th>
                  <th>Credits Available</th><th>Credits Used</th><th>Remaining</th><th>Actions</th>
                </tr>
              </thead>
              <tbody id="feesTableBody">
                <tr><td colspan="7" class="table-empty">Loading...</td></tr>
              </tbody>
            </table>
          </section>
        </section>

        <!-- ADMIN ACCOUNTS -->
        <section id="section-admins" class="dash-section">
          <div class="section-head">
            <h2>Admin Accounts</h2>
            <p>Create or remove administrator accounts. You cannot delete your own account or the primary seeded admin.</p>
          </div>
          <div id="adminsError" class="section-error" hidden></div>

          <div class="page-header-row">
            <h3 style="font-family:var(--font-serif);font-size:1.15rem">Administrators</h3>
            <button type="button" class="btn-primary" id="btnAddAdmin">
              <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="width:14px;height:14px;vertical-align:middle;margin-right:4px"><path d="M8 3v10M3 8h10"/></svg>
              Add Admin
            </button>
          </div>

          <section class="panel">
            <!-- Create admin form (hidden until Add Admin clicked) -->
            <div class="course-form-panel" id="addAdminPanel">
              <form class="course-form" id="addAdminForm" autocomplete="off">
                <div class="form-field">
                  <label for="adminFirst">First name</label>
                  <input type="text" id="adminFirst" name="first_name" required autocomplete="off"/>
                </div>
                <div class="form-field">
                  <label for="adminLast">Last name</label>
                  <input type="text" id="adminLast" name="last_name" required autocomplete="off"/>
                </div>
                <div class="form-field">
                  <label for="adminEmail">Email address</label>
                  <input type="email" id="adminEmail" name="email" required autocomplete="off"/>
                </div>
                <p style="font-size:0.75rem;color:var(--text-mid);grid-column:1/-1;margin-top:-.5rem">
                  A strong password will be automatically generated. You must share it with the new admin securely — it will only be shown once.
                </p>
                <div class="form-actions">
                  <button type="submit" class="btn-primary" id="btnCreateAdmin">
                    Generate &amp; Create Admin
                  </button>
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

  <!-- ── FEE EDIT MODAL ─────────────────────────────────────────── -->
  <div id="feeEditModal" class="modal-overlay" hidden>
    <div class="modal-backdrop" id="feeEditBackdrop"></div>
    <div class="modal-dialog" role="dialog" aria-labelledby="feeEditTitle">
      <h4 id="feeEditTitle">Update fees</h4>
      <p id="feeEditStudentName" class="modal-sub"></p>
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

  <!-- ── PASSWORD REVEAL MODAL ──────────────────────────────────── -->
  <div id="pwdModal" class="modal-overlay" hidden>
    <div class="modal-backdrop"></div>
    <div class="modal-dialog pwd-modal-dialog" role="dialog" aria-labelledby="pwdModalTitle">

      <h4 id="pwdModalTitle">Admin Account Created</h4>

      <!-- Warning banner -->
      <div class="pwd-warning">
        <div class="pwd-warning-icon">
          <svg viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
        </div>
        <p>This password will <strong>never be shown again</strong>. Copy it now and share it securely with the new admin. Do not send via unencrypted email.</p>
      </div>

      <!-- Admin info -->
      <div class="pwd-admin-info">
        Account created for: <strong id="pwdAdminName">—</strong><br/>
        Email: <strong id="pwdAdminEmail">—</strong>
      </div>

      <!-- Password box -->
      <p style="font-size:.72rem;font-weight:600;letter-spacing:.8px;text-transform:uppercase;color:var(--text-mid);margin-bottom:.5rem;">Generated Password</p>
      <div class="pwd-box-wrap">
        <div class="pwd-box" id="pwdBox" role="textbox" aria-readonly="true" aria-label="Generated password"></div>
        <button class="pwd-copy-btn" id="pwdCopyBtn" title="Copy password" type="button">
          <!-- Copy icon -->
          <svg id="pwdIconCopy" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
          <!-- Check icon (hidden) -->
          <svg id="pwdIconCheck" viewBox="0 0 24 24" style="display:none"><polyline points="20 6 9 17 4 12"/></svg>
        </button>
      </div>

      <!-- Strength indicator -->
      <div class="pwd-strength">
        <div class="pwd-strength-dots">
          <div class="pwd-strength-dot"></div>
          <div class="pwd-strength-dot"></div>
          <div class="pwd-strength-dot"></div>
          <div class="pwd-strength-dot"></div>
        </div>
        Strong password — uppercase, lowercase, numbers &amp; symbols
      </div>

      <!-- Copied feedback -->
      <p class="pwd-copied-toast" id="pwdCopiedToast"></p>

      <!-- Close button (disabled for countdown) -->
      <button class="pwd-close-btn" id="pwdCloseBtn" type="button" disabled>
        I have copied the password
        <span class="pwd-close-countdown" id="pwdCountdown">(5)</span>
      </button>

    </div>
  </div>

  <div id="dashToast" class="toast" role="status"></div>

  <script src="../js/animations.js"></script>
  <script src="../js/dashboard.js"></script>

  <script>
  // ── Password generator ────────────────────────────────────────────────────
  function generatePassword(length) {
    length = length || 16;
    const upper   = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    const lower   = 'abcdefghjkmnpqrstuvwxyz';
    const digits  = '23456789';
    const symbols = '!@#$%&*+-=?';
    const all     = upper + lower + digits + symbols;

    // Guarantee at least one of each character class
    let pwd = [
      upper[Math.floor(Math.random() * upper.length)],
      upper[Math.floor(Math.random() * upper.length)],
      lower[Math.floor(Math.random() * lower.length)],
      lower[Math.floor(Math.random() * lower.length)],
      digits[Math.floor(Math.random() * digits.length)],
      digits[Math.floor(Math.random() * digits.length)],
      symbols[Math.floor(Math.random() * symbols.length)],
      symbols[Math.floor(Math.random() * symbols.length)],
    ];

    while (pwd.length < length) {
      pwd.push(all[Math.floor(Math.random() * all.length)]);
    }

    // Shuffle
    for (let i = pwd.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [pwd[i], pwd[j]] = [pwd[j], pwd[i]];
    }

    return pwd.join('');
  }

  // ── Password modal ────────────────────────────────────────────────────────
  let pwdCountdownInterval = null;

  function showPasswordModal(adminName, adminEmail, password) {
    document.getElementById('pwdAdminName').textContent  = adminName;
    document.getElementById('pwdAdminEmail').textContent = adminEmail;
    document.getElementById('pwdBox').textContent        = password;
    document.getElementById('pwdCopiedToast').textContent = '';

    // Reset copy button
    const copyBtn  = document.getElementById('pwdCopyBtn');
    const iconCopy = document.getElementById('pwdIconCopy');
    const iconCheck= document.getElementById('pwdIconCheck');
    copyBtn.classList.remove('copied');
    iconCopy.style.display = '';
    iconCheck.style.display = 'none';

    // Countdown on close button
    const closeBtn    = document.getElementById('pwdCloseBtn');
    const countdownEl = document.getElementById('pwdCountdown');
    closeBtn.disabled = true;
    let secs = 5;
    countdownEl.textContent = '(' + secs + ')';

    clearInterval(pwdCountdownInterval);
    pwdCountdownInterval = setInterval(() => {
      secs--;
      if (secs <= 0) {
        clearInterval(pwdCountdownInterval);
        closeBtn.disabled = false;
        countdownEl.textContent = '';
      } else {
        countdownEl.textContent = '(' + secs + ')';
      }
    }, 1000);

    document.getElementById('pwdModal').removeAttribute('hidden');
  }

  function closePasswordModal() {
    clearInterval(pwdCountdownInterval);
    document.getElementById('pwdModal').setAttribute('hidden', '');
    // Clear the password from DOM immediately
    document.getElementById('pwdBox').textContent = '••••••••••••••••';
  }

  // Copy button
  document.getElementById('pwdCopyBtn').addEventListener('click', () => {
    const pwd = document.getElementById('pwdBox').textContent;
    navigator.clipboard.writeText(pwd).then(() => {
      const copyBtn  = document.getElementById('pwdCopyBtn');
      const iconCopy = document.getElementById('pwdIconCopy');
      const iconCheck= document.getElementById('pwdIconCheck');
      copyBtn.classList.add('copied');
      iconCopy.style.display = 'none';
      iconCheck.style.display = '';
      document.getElementById('pwdCopiedToast').textContent = 'Password copied to clipboard!';
      // Re-enable close immediately once they've copied
      document.getElementById('pwdCloseBtn').disabled = false;
      document.getElementById('pwdCountdown').textContent = '';
      clearInterval(pwdCountdownInterval);
    }).catch(() => {
      document.getElementById('pwdCopiedToast').textContent = 'Could not copy — select and copy manually.';
    });
  });

  // Close button
  document.getElementById('pwdCloseBtn').addEventListener('click', closePasswordModal);

  // ── Override admin form submit to use generated password ──────────────────
  document.getElementById('addAdminForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const first    = document.getElementById('adminFirst').value.trim();
    const last     = document.getElementById('adminLast').value.trim();
    const email    = document.getElementById('adminEmail').value.trim();
    const password = generatePassword(16);

    if (!first || !last || !email) return;

    const btn = document.getElementById('btnCreateAdmin');
    btn.disabled = true;
    btn.textContent = 'Creating…';

    try {
      const res = await fetch('../php/admins.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'create', first_name: first, last_name: last, email, password })
      }).then(r => r.json());

      if (res.success) {
        this.reset();
        document.getElementById('addAdminPanel').style.display = 'none';
        // Show password modal
        showPasswordModal(first + ' ' + last, email, password);
        // Reload admin list in background
        if (typeof window.loadAdmins === 'function') window.loadAdmins();
      } else {
        alert(res.message || 'Could not create admin account.');
      }
    } catch {
      alert('Connection error. Please try again.');
    } finally {
      btn.disabled = false;
      btn.textContent = 'Generate & Create Admin';
    }
  });

  // ── Reset password for existing admin ─────────────────────────────────────
  document.addEventListener('click', async function(e) {
    const resetBtn = e.target.closest('.btn-reset-pwd');
    if (!resetBtn) return;

    const adminId    = parseInt(resetBtn.dataset.id);
    const adminName  = resetBtn.dataset.name;
    const adminEmail = resetBtn.dataset.email;

    if (!confirm('Reset password for ' + adminName + '? A new password will be generated and shown to you once.')) return;

    const newPassword = generatePassword(16);

    try {
      const res = await fetch('../php/admins.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'reset_password', id: adminId, password: newPassword })
      }).then(r => r.json());

      if (res.success) {
        showPasswordModal(adminName, adminEmail, newPassword);
      } else {
        alert(res.message || 'Could not reset password.');
      }
    } catch {
      alert('Connection error. Please try again.');
    }
  });

  // ── Quick action ──────────────────────────────────────────────────────────
  document.getElementById('btnAddCourseQuick')?.addEventListener('click', () => {
    document.querySelector('[data-section="section-courses"]')?.click();
    setTimeout(() => document.getElementById('btnAddCourse')?.click(), 200);
  });
  </script>
</body>
</html>