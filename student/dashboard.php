<?php
$required_role = 'student';
require_once __DIR__ . '/../php/auth_guard.php';

$fullName  = htmlspecialchars($_SESSION['user_full_name'] ?? $_SESSION['user_name'] ?? 'Student', ENT_QUOTES, 'UTF-8');
$studentId = htmlspecialchars($_SESSION['student_id'] ?? '', ENT_QUOTES, 'UTF-8');
$firstName = htmlspecialchars($_SESSION['user_name'] ?? 'Student', ENT_QUOTES, 'UTF-8');
$email     = htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES, 'UTF-8');
$userId    = (int) $_SESSION['user_id'];
$parts     = explode(' ', trim($fullName));
$initials  = strtoupper(substr($parts[0] ?? 'S', 0, 1) . substr($parts[1] ?? '', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Student Portal &mdash; Greenfield Institute</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/dashboard.css"/>
</head>
<body class="dash-body" data-role="student">

  <div class="dash-layout">

    <aside class="dash-sidebar student-sidebar">
      <div class="sidebar-brand">
        <a href="../index.html">
          <img src="../images/logo.svg" alt="Greenfield Institute"/>
        </a>
        <p class="sidebar-role">Student Portal</p>
      </div>
      <nav class="sidebar-nav">
        <a href="#section-dashboard" data-section="section-dashboard" class="active">
          <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="2" width="7" height="7" rx="1.5"/><rect x="11" y="2" width="7" height="4" rx="1.5"/><rect x="11" y="9" width="7" height="9" rx="1.5"/><rect x="2" y="12" width="7" height="6" rx="1.5"/></svg>
          Dashboard
        </a>
        <a href="#section-mycourses" data-section="section-mycourses">
          <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 5h14M3 10h14M3 15h9" stroke-linecap="round"/></svg>
          My Courses
        </a>
        <a href="#section-browse" data-section="section-browse">
          <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="9" r="5"/><path d="M14 14l3 3" stroke-linecap="round"/></svg>
          Browse Courses
        </a>
        <a href="#section-fees" data-section="section-fees">
          <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="5" width="16" height="12" rx="2"/><path d="M2 9h16M5 13h2" stroke-linecap="round"/></svg>
          My Fees
        </a>
        <a href="#section-profile" data-section="section-profile">
          <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="10" cy="7" r="3"/><path d="M4 17c0-3 2.5-5 6-5s6 2 6 5" stroke-linecap="round"/></svg>
          My Profile
        </a>
      </nav>
      <div class="sidebar-footer">
        <a href="../php/logout.php" class="btn-signout" style="display:block;text-align:center;text-decoration:none;">Sign Out</a>
      </div>
    </aside>

    <div class="dash-main">
      <header class="dash-topbar student-topbar">
        <p class="student-greeting">Good Morning, <?= $firstName ?>! 👋</p>
        <div class="topbar-right">
          <label class="topbar-search" aria-label="Search">
            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="9" cy="9" r="6"/><path d="M13.5 13.5L18 18" stroke-linecap="round"/></svg>
            <input type="search" placeholder="Search anything here..." aria-label="Search dashboard"/>
          </label>
          <button type="button" class="icon-btn" aria-label="Notifications">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 17h5l-1.4-1.4a2 2 0 01-.6-1.4V10a6 6 0 10-12 0v4.2a2 2 0 01-.6 1.4L4 17h5"/><path d="M9.5 20a2.5 2.5 0 005 0" stroke-linecap="round"/></svg>
          </button>
          <div class="topbar-avatar"><?= $initials ?></div>
        </div>
      </header>

      <main class="dash-content">

        <section id="section-dashboard" class="dash-section active">
          <article class="panel panel-hover student-profile-strip">
            <div class="student-profile-avatar"><?= $initials ?></div>
            <div class="student-profile-meta">
              <h3 id="profileName"><?= $fullName ?></h3>
              <p>Student ID: <strong id="profileStudentId"><?= $studentId ?></strong></p>
              <p>Email: <strong id="profileEmail"><?= $email ?></strong></p>
            </div>
          </article>

          <div class="stats-row student-stats-row">
            <article class="dash-stat-card card-1">
              <svg class="stat-watermark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 5h14M3 10h14M3 15h9" stroke-linecap="round"/></svg>
              <p class="stat-label">Enrolled Courses</p>
              <p class="stat-value" id="statEnrolled">0</p>
              <p class="stat-note">as of today</p>
            </article>
            <article class="dash-stat-card card-2">
              <svg class="stat-watermark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/><rect x="3" y="3" width="18" height="18" rx="4"/></svg>
              <p class="stat-label">Completed Courses</p>
              <p class="stat-value" id="statCompleted">0</p>
              <p class="stat-note">as of today</p>
            </article>
            <article class="dash-stat-card card-3">
              <svg class="stat-watermark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M3 10h18" stroke-linecap="round"/></svg>
              <p class="stat-label">Pending Fees (KES)</p>
              <p class="stat-value" id="statPendingFees">0</p>
              <p class="stat-note">as of today</p>
            </article>
            <article class="dash-stat-card card-4">
              <svg class="stat-watermark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3" stroke-linecap="round"/></svg>
              <p class="stat-label">Profile Complete</p>
              <p class="stat-value" id="statProfileComplete">0%</p>
              <p class="stat-note">as of today</p>
            </article>
          </div>

          <div class="dashboard-grid dashboard-grid-main student-main-grid">
            <section class="panel panel-hover">
              <div class="panel-head">
                <h3>My Courses</h3>
                <div class="panel-tabs" id="studentCourseTabs">
                  <button type="button" class="active" data-filter="all">All</button>
                  <button type="button" data-filter="ongoing">Ongoing</button>
                  <button type="button" data-filter="completed">Completed</button>
                </div>
              </div>
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Course Name</th>
                    <th>Department</th>
                    <th>Credits</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody id="enrolledFullBody">
                  <tr><td colspan="4" class="table-empty">Loading...</td></tr>
                </tbody>
              </table>
            </section>

            <section class="panel panel-hover">
              <div class="panel-head"><h3>My Activity</h3></div>
              <div class="chart-wrap">
                <canvas id="studentActivityChart"></canvas>
              </div>
            </section>
          </div>

          <div class="dashboard-grid dashboard-grid-bottom student-bottom-grid">
            <section class="panel panel-hover">
              <div class="panel-head"><h3>Announcements</h3></div>
              <div id="studentAnnouncementsList" class="announcement-list">
                <p class="table-empty">Loading announcements...</p>
              </div>
            </section>

            <section class="panel panel-hover">
              <div class="panel-head"><h3>Courses by Department</h3></div>
              <div class="chart-wrap chart-wrap-donut">
                <canvas id="studentDeptDonutChart"></canvas>
              </div>
            </section>
          </div>
        </section>

        <section id="section-mycourses" class="dash-section">
          <div class="section-head">
            <h2>My Courses</h2>
            <p>Detailed list of your current and completed courses.</p>
          </div>
          <section class="panel panel-hover">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Course Name</th>
                  <th>Department</th>
                  <th>Credits</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody id="myCoursesBody">
                <tr><td colspan="4" class="table-empty">Loading...</td></tr>
              </tbody>
            </table>
          </section>
        </section>

        <section id="section-fees" class="dash-section">
          <div class="section-head">
            <h2>My Fees</h2>
            <p>Your payment summary and credit usage.</p>
          </div>
          <section class="panel panel-hover">
            <div class="fees-summary-card">
              <div class="fee-line"><span>Total Fees Paid</span><strong id="f_paid">KES 0</strong></div>
              <div class="fee-line"><span>Cost per Credit</span><strong id="f_cpc">KES 0</strong></div>
              <div class="fee-line"><span>Credits Available</span><strong id="f_avail">0</strong></div>
              <div class="fee-line"><span>Credits Used</span><strong id="f_used">0</strong></div>
              <div class="fee-line"><span>Credits Remaining</span><strong class="accent" id="f_left">0</strong></div>
              <div class="fee-line"><span>Pending Fees</span><strong id="f_pending">KES 0</strong></div>
              <div class="fee-bar-wrap">
                <div class="fee-bar-top"><span>Credit usage</span><span id="f_pct">0%</span></div>
                <div class="fee-bar-track"><div class="fee-bar-fill" id="f_bar"></div></div>
              </div>
            </div>
            <div class="panel-head"><h3>Courses Linked to Fees</h3></div>
            <table class="data-table">
              <thead>
                <tr>
                  <th>Course</th>
                  <th>Department</th>
                  <th>Credits</th>
                </tr>
              </thead>
              <tbody id="feesEnrolledBody">
                <tr><td colspan="3" class="table-empty">Loading...</td></tr>
              </tbody>
            </table>
          </section>
        </section>

        <section id="section-browse" class="dash-section">
          <div class="section-head">
            <h2>Browse Courses</h2>
            <p>Find and enroll in available courses.</p>
          </div>
          <section class="panel panel-hover">
            <div class="browse-search-bar">
              <input type="search" id="courseSearch" placeholder="Search title, department, instructor..."/>
              <select id="deptFilter"><option value="">All departments</option></select>
            </div>
            <div class="course-cards-grid" id="courseCardsGrid">
              <p class="no-results-msg">Loading courses...</p>
            </div>
          </section>
        </section>

        <section id="section-profile" class="dash-section">
          <div class="section-head">
            <h2>My Profile</h2>
            <p>Your Greenfield account details.</p>
          </div>
          <section class="panel panel-hover">
            <div class="pv-photo-section">
              <label for="photoUploadProfile" style="cursor:pointer">
                <div class="pv-photo-avatar" id="pvAvatar"><?= $initials ?></div>
              </label>
              <input type="file" id="photoUploadProfile" accept="image/jpeg,image/png,image/webp" style="display:none"/>
              <p class="pv-photo-hint">Click to upload a profile photo</p>
            </div>
            <div class="profile-view-grid">
              <div class="pv-row"><span class="pv-label">Full Name</span><span class="pv-value"><?= $fullName ?></span></div>
              <div class="pv-row"><span class="pv-label">Student ID</span><span class="pv-value"><?= $studentId ?></span></div>
              <div class="pv-row"><span class="pv-label">Email</span><span class="pv-value"><?= $email ?></span></div>
              <div class="pv-row"><span class="pv-label">Role</span><span class="pv-value">Student</span></div>
              <div class="pv-row">
                <span class="pv-label">Password</span>
                <a href="../change-password.html" style="font-size:0.8rem;color:var(--accent);font-weight:600;text-decoration:none;">Change password</a>
              </div>
            </div>
          </section>
        </section>

      </main>
    </div>
  </div>

  <div id="dashToast" class="toast" role="status"></div>

  <script>window.DASH_USER_ID = <?= $userId ?>;</script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
  <script src="../js/animations.js"></script>
  <script src="../js/dashboard.js"></script>
  <script>
  (function () {
    const API = '../php/';
    let allCourses = [];
    let feesData = null;
    let activeTab = 'all';
    let activityChart = null;
    let deptChart = null;

    function showToast(message, isError) {
      if (window.DashboardAPI && typeof window.DashboardAPI.showToast === 'function') {
        window.DashboardAPI.showToast(message, !!isError);
        return;
      }
      const toast = document.getElementById('dashToast');
      if (!toast) return;
      toast.textContent = message;
      toast.className = 'toast visible' + (isError ? ' error' : '');
      clearTimeout(showToast._t);
      showToast._t = setTimeout(() => toast.classList.remove('visible'), 3200);
    }

    function esc(str) {
      const d = document.createElement('div');
      d.textContent = str ?? '';
      return d.innerHTML;
    }

    function formatKes(value) {
      return 'KES ' + Number(value || 0).toLocaleString('en-KE', { maximumFractionDigits: 0 });
    }

    function normalizeDate(value) {
      if (!value) return null;
      const d = new Date(String(value).replace(' ', 'T'));
      return Number.isNaN(d.getTime()) ? null : d;
    }

    function getCourseStatus(course) {
      const d = normalizeDate(course.registered_at);
      if (!d) return 'ongoing';
      const diffDays = (Date.now() - d.getTime()) / 86400000;
      return diffDays >= 120 ? 'completed' : 'ongoing';
    }

    function statusBadge(status) {
      if (status === 'completed') {
        return '<span class="status-badge inactive">Completed</span>';
      }
      return '<span class="status-badge active">Ongoing</span>';
    }

    function visibleCourses() {
      if (activeTab === 'all') return allCourses;
      return allCourses.filter((course) => getCourseStatus(course) === activeTab);
    }

    function renderDashboardCourses() {
      const tbody = document.getElementById('enrolledFullBody');
      if (!tbody) return;
      const rows = visibleCourses();
      if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="4" class="table-empty">No courses in this filter.</td></tr>';
        return;
      }
      tbody.innerHTML = rows.map((course) =>
        '<tr>' +
          '<td><strong>' + esc(course.title) + '</strong></td>' +
          '<td>' + esc(course.department) + '</td>' +
          '<td>' + parseInt(course.credits || 0, 10) + '</td>' +
          '<td>' + statusBadge(getCourseStatus(course)) + '</td>' +
        '</tr>'
      ).join('');
    }

    function renderMyCoursesPage() {
      const tbody = document.getElementById('myCoursesBody');
      if (!tbody) return;
      if (!allCourses.length) {
        tbody.innerHTML = '<tr><td colspan="4" class="table-empty">No courses enrolled yet.</td></tr>';
        return;
      }
      tbody.innerHTML = allCourses.map((course) =>
        '<tr>' +
          '<td><strong>' + esc(course.title) + '</strong></td>' +
          '<td>' + esc(course.department) + '</td>' +
          '<td>' + parseInt(course.credits || 0, 10) + '</td>' +
          '<td>' + statusBadge(getCourseStatus(course)) + '</td>' +
        '</tr>'
      ).join('');
    }

    function renderFeesCourses() {
      const tbody = document.getElementById('feesEnrolledBody');
      if (!tbody) return;
      if (!allCourses.length) {
        tbody.innerHTML = '<tr><td colspan="3" class="table-empty">No courses enrolled yet.</td></tr>';
        return;
      }
      tbody.innerHTML = allCourses.map((course) =>
        '<tr>' +
          '<td>' + esc(course.title) + '</td>' +
          '<td>' + esc(course.department) + '</td>' +
          '<td>' + parseInt(course.credits || 0, 10) + '</td>' +
        '</tr>'
      ).join('');
    }

    function renderStatsAndFees() {
      const enrolledCount = allCourses.length;
      const completedCount = allCourses.filter((course) => getCourseStatus(course) === 'completed').length;
      const creditsUsed = allCourses.reduce((sum, course) => sum + parseInt(course.credits || 0, 10), 0);

      const feesPaid = Number(feesData?.fees_paid || 0);
      const costPerCredit = Number(feesData?.cost_per_credit || 0);
      const creditsAvailable = Number(feesData?.credits_available || 0);
      const creditsRemaining = Math.max(0, creditsAvailable - creditsUsed);
      const pendingFees = Math.max(0, (creditsUsed * costPerCredit) - feesPaid);
      const usagePct = creditsAvailable ? Math.min(100, Math.round((creditsUsed / creditsAvailable) * 100)) : 0;

      const profileFields = [
        document.getElementById('profileName')?.textContent.trim(),
        document.getElementById('profileStudentId')?.textContent.trim(),
        document.getElementById('profileEmail')?.textContent.trim()
      ];
      const profileComplete = Math.round((profileFields.filter(Boolean).length / profileFields.length) * 100);

      const statEnrolled = document.getElementById('statEnrolled');
      const statCompleted = document.getElementById('statCompleted');
      const statPendingFees = document.getElementById('statPendingFees');
      const statProfileComplete = document.getElementById('statProfileComplete');
      if (statEnrolled) statEnrolled.textContent = enrolledCount.toLocaleString();
      if (statCompleted) statCompleted.textContent = completedCount.toLocaleString();
      if (statPendingFees) statPendingFees.textContent = Math.round(pendingFees).toLocaleString();
      if (statProfileComplete) statProfileComplete.textContent = profileComplete + '%';

      const map = {
        f_paid: formatKes(feesPaid),
        f_cpc: formatKes(costPerCredit),
        f_avail: creditsAvailable.toLocaleString(),
        f_used: creditsUsed.toLocaleString(),
        f_left: creditsRemaining.toLocaleString(),
        f_pending: formatKes(pendingFees)
      };
      Object.entries(map).forEach(([id, value]) => {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
      });

      const pct = document.getElementById('f_pct');
      const bar = document.getElementById('f_bar');
      if (pct) pct.textContent = usagePct + '%';
      if (bar) bar.style.width = usagePct + '%';
    }

    function buildMonthlySeries() {
      const labels = [];
      const values = [];
      const now = new Date();
      for (let i = 5; i >= 0; i--) {
        const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
        const key = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
        labels.push(d.toLocaleString('en-US', { month: 'short' }));
        values.push(allCourses.filter((course) => {
          const rd = normalizeDate(course.registered_at);
          if (!rd) return false;
          const rKey = rd.getFullYear() + '-' + String(rd.getMonth() + 1).padStart(2, '0');
          return rKey === key;
        }).length);
      }
      if (values.every((v) => v === 0)) {
        return { labels, values: [2, 3, 4, 3, 5, 4] };
      }
      return { labels, values };
    }

    function buildDepartmentSeries() {
      const map = {};
      allCourses.forEach((course) => {
        const key = course.department || 'Other';
        map[key] = (map[key] || 0) + 1;
      });
      const labels = Object.keys(map);
      const values = labels.map((key) => map[key]);
      if (!labels.length) {
        return { labels: ['No Data'], values: [1] };
      }
      return { labels, values };
    }

    function renderCharts() {
      if (!window.Chart) return;

      const activityCanvas = document.getElementById('studentActivityChart');
      if (activityCanvas) {
        const ctx = activityCanvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 260);
        gradient.addColorStop(0, 'rgba(78, 203, 113, .4)');
        gradient.addColorStop(1, 'rgba(78, 203, 113, .04)');
        const monthly = buildMonthlySeries();
        if (activityChart) activityChart.destroy();
        activityChart = new Chart(ctx, {
          type: 'line',
          data: {
            labels: monthly.labels,
            datasets: [{
              data: monthly.values,
              borderColor: '#4ecb71',
              backgroundColor: gradient,
              fill: true,
              tension: 0.35,
              pointRadius: 3,
              pointHoverRadius: 4,
              pointBackgroundColor: '#4ecb71'
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
              x: { grid: { display: false }, ticks: { color: '#4e5e52' } },
              y: { beginAtZero: true, ticks: { color: '#4e5e52' }, grid: { color: 'rgba(45,122,78,.12)' } }
            }
          }
        });
      }

      const donutCanvas = document.getElementById('studentDeptDonutChart');
      if (donutCanvas) {
        const ctx = donutCanvas.getContext('2d');
        const dept = buildDepartmentSeries();
        if (deptChart) deptChart.destroy();
        deptChart = new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: dept.labels,
            datasets: [{
              data: dept.values,
              backgroundColor: ['#1a2e1e', '#2d4a32', '#2d7a4e', '#4ecb71', '#3a9e68', '#a8d5b5'],
              borderWidth: 0
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: {
              legend: {
                position: 'right',
                labels: { boxWidth: 12, color: '#4e5e52', font: { size: 11 } }
              }
            }
          }
        });
      }
    }

    function initTabs() {
      const tabs = document.querySelectorAll('#studentCourseTabs button');
      tabs.forEach((btn) => {
        btn.addEventListener('click', () => {
          tabs.forEach((item) => item.classList.remove('active'));
          btn.classList.add('active');
          activeTab = btn.dataset.filter || 'all';
          renderDashboardCourses();
        });
      });
    }

    async function loadData() {
      try {
        const [enrolledRes, feesRes] = await Promise.all([
          fetch(API + 'registrations.php?user_id=' + encodeURIComponent(window.DASH_USER_ID), { credentials: 'include' }).then((r) => r.json()),
          fetch(API + 'fees.php?action=student&id=' + encodeURIComponent(window.DASH_USER_ID), { credentials: 'include' }).then((r) => r.json())
        ]);

        allCourses = enrolledRes.success ? (enrolledRes.courses || []) : [];
        feesData = (feesRes.success && feesRes.data) ? feesRes.data : null;

        renderDashboardCourses();
        renderMyCoursesPage();
        renderFeesCourses();
        renderStatsAndFees();
        renderCharts();
      } catch {
        showToast('Could not load student dashboard data.', true);
      }
    }

    async function loadAnnouncements() {
      const container = document.getElementById('studentAnnouncementsList');
      if (!container) return;
      try {
        const res = await fetch(API + 'announcements.php?action=list', { credentials: 'include' });
        const data = await res.json();
        if (!data.success || !data.announcements.length) {
          container.innerHTML = '<p class="table-empty">No announcements yet.</p>';
          return;
        }
        container.innerHTML = data.announcements.map((a) => {
          const date = new Date(a.created_at).toLocaleDateString('en-KE', { day:'numeric', month:'short', year:'numeric' });
          return '<div class="announce-card">' +
            '<div class="announce-card-head">' +
              '<strong class="announce-title">' + esc(a.title) + '</strong>' +
              '<span class="announce-meta">' + esc(a.admin_name) + ' &bull; ' + date + '</span>' +
            '</div>' +
            '<p class="announce-body">' + esc(a.message) + '</p>' +
          '</div>';
        }).join('');
      } catch {
        container.innerHTML = '<p class="table-empty">Could not load announcements.</p>';
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      initTabs();
      loadData();
      loadAnnouncements();
    });
  })();
  </script>
</body>
</html>