<?php
$required_role = 'student';
require_once __DIR__ . '/../php/auth_guard.php';

$fullName   = htmlspecialchars($_SESSION['user_full_name'] ?? $_SESSION['user_name'] ?? 'Student', ENT_QUOTES, 'UTF-8');
$studentId  = htmlspecialchars($_SESSION['student_id'] ?? '', ENT_QUOTES, 'UTF-8');
$firstName  = htmlspecialchars($_SESSION['user_name'] ?? 'Student', ENT_QUOTES, 'UTF-8');
$userId     = (int) $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Student Dashboard &mdash; Greenfield Institute</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/dashboard.css"/>
</head>
<body class="dash-body" data-role="student">

  <div class="dash-layout">
    <aside class="dash-sidebar">
      <div class="sidebar-brand">
        <a href="../index.html">
          <img src="../images/logo.png" alt="Greenfield Institute"/>
        </a>
        <p class="sidebar-role">Student Portal</p>
      </div>
      <nav class="sidebar-nav">
        <a href="#section-dashboard" data-section="section-dashboard" class="active">
          <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="2" y="2" width="7" height="7" rx="1.5"/><rect x="11" y="2" width="7" height="4" rx="1.5"/><rect x="11" y="9" width="7" height="9" rx="1.5"/><rect x="2" y="12" width="7" height="6" rx="1.5"/></svg>
          Dashboard
        </a>
        <a href="#section-enrolled" data-section="section-enrolled">
          <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M3 5h14M3 10h14M3 15h9" stroke-linecap="round"/></svg>
          My Courses
        </a>
        <a href="#section-browse" data-section="section-browse">
          <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.4"><circle cx="9" cy="9" r="5.5"/><path d="M13.5 13.5L17 17" stroke-linecap="round"/></svg>
          Browse Courses
        </a>
        <a href="#section-profile" data-section="section-profile">
          <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.4"><circle cx="10" cy="7" r="3.5"/><path d="M4 17c0-3.31 2.69-6 6-6s6 2.69 6 6" stroke-linecap="round"/></svg>
          Profile
        </a>
      </nav>
      <div class="sidebar-footer">
        <button type="button" class="btn-signout" id="btnSignOut">Sign Out</button>
      </div>
    </aside>

    <div class="dash-main">
      <header class="dash-topbar">
        <p class="topbar-greeting">Hello, <strong><?= $fullName ?></strong></p>
        <p class="topbar-date" id="topbarDate"></p>
      </header>

      <main class="dash-content">

        <section id="section-dashboard" class="dash-section active">
          <div class="welcome-card" data-scroll-reveal>
            <p class="eyebrow">Student Portal</p>
            <h1>Welcome back, <?= $firstName ?>.</h1>
            <p class="student-id">Student ID: <?= $studentId ?></p>
          </div>

          <div class="stats-row" data-scroll-reveal>
            <div class="stat-card">
              <p class="stat-label">Enrolled Courses</p>
              <p class="stat-value" id="statEnrolled" data-count="0">0</p>
            </div>
            <div class="stat-card accent-2">
              <p class="stat-label">Total Credits</p>
              <p class="stat-value" id="statCredits" data-count="0">0</p>
            </div>
            <div class="stat-card accent-3">
              <p class="stat-label">Open Slots Available</p>
              <p class="stat-value" id="statSlots" data-count="0">0</p>
              <p class="stat-note">Across courses you can still join</p>
            </div>
          </div>

          <div class="panel" id="enrolledPanel" data-scroll-reveal>
            <div class="panel-head">
              <h3>My Enrolled Courses</h3>
            </div>
            <table class="data-table">
              <thead>
                <tr>
                  <th>Course</th>
                  <th>Department</th>
                  <th>Instructor</th>
                  <th>Credits</th>
                  <th>Capacity</th>
                  <th></th>
                </tr>
              </thead>
              <tbody id="enrolledBody">
                <tr><td colspan="6" class="table-empty">Loading...</td></tr>
              </tbody>
            </table>
          </div>
        </section>

        <section id="section-enrolled" class="dash-section">
          <div class="section-head">
            <h2>My Courses</h2>
            <p>Every course you are currently registered for this term.</p>
          </div>
          <div class="panel" id="enrolledPanelAlt">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Course</th>
                  <th>Department</th>
                  <th>Instructor</th>
                  <th>Credits</th>
                  <th>Capacity</th>
                  <th></th>
                </tr>
              </thead>
              <tbody id="enrolledBodyAlt">
                <tr><td colspan="6" class="table-empty">Use Dashboard to load courses.</td></tr>
              </tbody>
            </table>
          </div>
        </section>

        <section id="section-browse" class="dash-section">
          <div class="section-head">
            <h2>Browse Courses</h2>
            <p>Courses with open seats that you have not yet enrolled in.</p>
          </div>
          <div class="panel" id="availablePanel" data-scroll-reveal>
            <table class="data-table">
              <thead>
                <tr>
                  <th>Course</th>
                  <th>Department</th>
                  <th>Instructor</th>
                  <th>Credits</th>
                  <th>Slots Left</th>
                  <th></th>
                </tr>
              </thead>
              <tbody id="availableBody">
                <tr><td colspan="6" class="table-empty">Loading...</td></tr>
              </tbody>
            </table>
          </div>
        </section>

        <section id="section-profile" class="dash-section">
          <div class="section-head">
            <h2>Profile</h2>
            <p>Your account details on the Greenfield platform.</p>
          </div>
          <div class="panel profile-card" data-scroll-reveal>
            <div class="profile-row"><span>Full name</span><strong><?= $fullName ?></strong></div>
            <div class="profile-row"><span>Student ID</span><strong><?= $studentId ?></strong></div>
            <div class="profile-row"><span>Email</span><strong><?= htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong></div>
          </div>
        </section>

      </main>
    </div>
  </div>

  <div id="dashToast" class="toast" role="status"></div>

  <script>window.DASH_USER_ID = <?= $userId ?>;</script>
  <script src="../js/animations.js"></script>
  <script src="../js/dashboard.js"></script>
</body>
</html>
