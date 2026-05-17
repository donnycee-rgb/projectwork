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
  <style>
    :root {
      --font-serif: 'Playfair Display', Georgia, serif;
      --font-sans:  'Inter', system-ui, sans-serif;
      --radius: 14px;
      --radius-sm: 10px;
      --sh-sm: 0 1px 8px rgba(0,0,0,0.06);
      --sh-md: 0 8px 28px rgba(0,0,0,0.1);
      --text-lt: #8a9e8e;
    }

    .dash-topbar { padding: 0 2rem; }
    .topbar-greeting { font-size: 0.84rem; color: var(--text-mid); }
    .topbar-greeting strong { color: var(--text); font-weight: 600; }

    .dash-content { padding: 1.6rem 2rem 3rem; }

    .student-hero {
      display: grid;
      grid-template-columns: 220px 1fr;
      gap: 1.25rem;
      margin-bottom: 1.25rem;
    }

    .profile-card {
      background: var(--forest);
      border-radius: var(--radius);
      padding: 1.6rem 1.25rem 1.25rem;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      color: #fff;
      position: relative;
      overflow: hidden;
    }

    .profile-card::before {
      content: '';
      position: absolute;
      top: -40px; right: -40px;
      width: 160px; height: 160px;
      background: radial-gradient(circle, rgba(78,203,113,0.18) 0%, transparent 70%);
      pointer-events: none;
    }

    .profile-avatar-wrap {
      position: relative;
      margin-bottom: 0.9rem;
    }

    .profile-ring {
      width: 90px; height: 90px;
      border-radius: 50%;
      padding: 3px;
      background: conic-gradient(var(--accent-br) var(--ring-pct, 0%), rgba(255,255,255,0.15) 0%);
      flex-shrink: 0;
    }

    .profile-ring-inner {
      width: 100%; height: 100%;
      border-radius: 50%;
      background: var(--forest-mid);
      display: flex; align-items: center; justify-content: center;
      font-family: var(--font-serif);
      font-size: 1.5rem; font-weight: 700;
      color: var(--accent-br);
      overflow: hidden;
    }

    .profile-ring-inner img {
      width: 100%; height: 100%;
      object-fit: cover; border-radius: 50%;
    }

    .profile-card h3 {
      font-family: var(--font-serif);
      font-size: 1.05rem; font-weight: 700;
      color: #fff; margin-bottom: 0.2rem;
    }

    .profile-card .profile-id {
      font-size: 0.68rem; color: rgba(255,255,255,0.45);
      letter-spacing: 0.5px; margin-bottom: 1rem;
    }

    .profile-ring-stats {
      display: flex; gap: 1rem;
      justify-content: center;
      margin-bottom: 1rem; width: 100%;
    }

    .prs-item { display: flex; flex-direction: column; align-items: center; }
    .prs-num {
      font-family: var(--font-serif);
      font-size: 1.1rem; font-weight: 700;
      color: #fff; line-height: 1;
    }
    .prs-label {
      font-size: 0.58rem; color: rgba(255,255,255,0.45);
      text-transform: uppercase; letter-spacing: 0.8px;
      margin-top: 2px;
    }

    .prs-sep { width: 1px; background: rgba(255,255,255,0.12); align-self: stretch; }

    .profile-change-link {
      font-size: 0.7rem; color: var(--accent-br);
      text-decoration: none; opacity: 0.75;
    }
    .profile-change-link:hover { opacity: 1; }

    .stat-cards-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      grid-template-rows: repeat(2, 1fr);
      gap: 1.25rem;
    }

    .scard {
      border-radius: var(--radius);
      padding: 1.3rem 1.4rem;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 110px;
      position: relative;
      overflow: hidden;
    }

    .scard::after {
      content: '';
      position: absolute;
      bottom: -20px; right: -20px;
      width: 90px; height: 90px;
      border-radius: 50%;
      background: rgba(255,255,255,0.07);
      pointer-events: none;
    }

    .scard-icon {
      width: 36px; height: 36px;
      border-radius: 10px;
      background: rgba(255,255,255,0.18);
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 0.5rem;
    }
    .scard-icon svg { width: 18px; height: 18px; stroke: #fff; }

    .scard-num {
      font-family: var(--font-serif);
      font-size: 2rem; font-weight: 700;
      color: #fff; letter-spacing: -1px; line-height: 1;
    }
    .scard-label {
      font-size: 0.68rem; color: rgba(255,255,255,0.75);
      text-transform: uppercase; letter-spacing: 0.8px;
      margin-top: 0.3rem;
    }

    .scard.c1 { background: linear-gradient(135deg, #2d7a4e 0%, #3a9e66 100%); }
    .scard.c2 { background: linear-gradient(135deg, #1a6b5a 0%, #24907a 100%); }
    .scard.c3 { background: linear-gradient(135deg, #1a4a6b 0%, #2467a0 100%); }
    .scard.c4 { background: linear-gradient(135deg, #4a3a1a 0%, #8a6a24 100%); }

    .dashboard-body {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.25rem;
    }

    .activity-feed { display: flex; flex-direction: column; gap: 0.7rem; padding: 1rem 1.25rem; }

    .activity-item {
      display: flex; align-items: flex-start; gap: 0.75rem;
      padding: 0.65rem 0;
      border-bottom: 1px solid var(--border);
      font-size: 0.8rem;
    }
    .activity-item:last-child { border-bottom: none; }

    .activity-dot {
      width: 8px; height: 8px; border-radius: 50%;
      background: var(--accent); flex-shrink: 0; margin-top: 5px;
    }

    .activity-dot.orange { background: #e67e22; }
    .activity-dot.blue { background: #2467a0; }

    .activity-text { color: var(--text); line-height: 1.4; }
    .activity-time { font-size: 0.68rem; color: var(--text-lt); margin-top: 2px; }

    .browse-search-bar {
      display: flex; gap: 0.75rem;
      padding: 1rem 1.25rem;
      border-bottom: 1px solid var(--border);
      background: var(--off-white);
    }

    .browse-search-bar input {
      flex: 1; padding: 0.6rem 0.85rem;
      border: 1.5px solid var(--border);
      border-radius: var(--radius-sm);
      font-family: var(--font-sans);
      font-size: 0.8rem; color: var(--text);
      background: var(--white); outline: none;
    }
    .browse-search-bar input:focus { border-color: var(--accent); }

    .browse-search-bar select {
      padding: 0.6rem 0.85rem;
      border: 1.5px solid var(--border);
      border-radius: var(--radius-sm);
      font-family: var(--font-sans);
      font-size: 0.8rem; color: var(--text);
      background: var(--white); outline: none;
    }

    .course-cards-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
      gap: 1rem; padding: 1.25rem;
    }

    .crs-card {
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 1.1rem 1.15rem;
      background: var(--white);
      display: flex; flex-direction: column; gap: 0.5rem;
      transition: box-shadow 0.2s, transform 0.2s;
    }
    .crs-card:hover { box-shadow: var(--sh-md); transform: translateY(-2px); }

    .crs-dept-tag {
      display: inline-block;
      font-size: 0.6rem; font-weight: 600;
      letter-spacing: 0.8px; text-transform: uppercase;
      color: var(--accent);
      background: rgba(45,122,78,0.09);
      border-radius: 50px; padding: 0.18rem 0.6rem;
      align-self: flex-start;
    }

    .crs-title {
      font-family: var(--font-serif);
      font-size: 0.95rem; font-weight: 700;
      color: var(--text); line-height: 1.25;
    }

    .crs-instructor { font-size: 0.72rem; color: var(--text-lt); }

    .crs-footer {
      display: flex; align-items: center;
      justify-content: space-between;
      margin-top: auto; padding-top: 0.7rem;
      border-top: 1px solid var(--border);
    }

    .crs-credits { font-size: 0.68rem; color: var(--text-lt); font-weight: 500; }

    .slots-pill {
      font-size: 0.65rem; font-weight: 600;
      padding: 0.18rem 0.55rem; border-radius: 50px;
    }
    .slots-pill.g { background: rgba(45,122,78,0.1); color: var(--accent); }
    .slots-pill.o { background: rgba(230,126,34,0.1); color: #e67e22; }
    .slots-pill.r { background: rgba(180,42,34,0.1); color: #b42a22; }

    .btn-enroll {
      background: var(--accent); color: #fff;
      border: none; border-radius: 50px;
      font-size: 0.7rem; font-weight: 600;
      padding: 0.3rem 0.8rem; cursor: pointer;
      transition: background 0.2s;
    }
    .btn-enroll:hover { background: var(--forest-mid); }
    .btn-enroll:disabled { opacity: 0.45; cursor: not-allowed; }

    .btn-sm { font-size: 0.72rem; padding: 0.3rem 0.7rem; }

    .fees-summary-card {
      padding: 1.25rem;
      display: grid; gap: 0.7rem;
    }

    .fee-line {
      display: flex; justify-content: space-between;
      align-items: center;
      padding: 0.6rem 0;
      border-bottom: 1px solid var(--border);
      font-size: 0.82rem;
    }
    .fee-line:last-of-type { border-bottom: none; }
    .fee-line span { color: var(--text-mid); }
    .fee-line strong { color: var(--text); font-weight: 600; }
    .fee-line strong.accent { color: var(--accent); }

    .fee-bar-wrap { margin-top: 0.5rem; }
    .fee-bar-top {
      display: flex; justify-content: space-between;
      font-size: 0.7rem; color: var(--text-lt);
      margin-bottom: 0.35rem;
    }
    .fee-bar-track {
      height: 10px; background: var(--border);
      border-radius: 5px; overflow: hidden;
    }
    .fee-bar-fill {
      height: 100%; width: 0;
      background: linear-gradient(90deg, var(--accent), var(--accent-br));
      border-radius: 5px; transition: width 0.6s ease;
    }

    .fee-info-box {
      margin-top: 0.5rem; padding: 0.85rem 1rem;
      background: var(--off-white);
      border-radius: var(--radius-sm);
      border: 1px solid var(--border);
      font-size: 0.76rem; color: var(--text-mid); line-height: 1.6;
    }

    .profile-view-grid {
      display: grid; gap: 0;
    }
    .pv-row {
      display: flex; justify-content: space-between;
      align-items: center;
      padding: 0.85rem 1.25rem;
      border-bottom: 1px solid var(--border);
      font-size: 0.82rem;
    }
    .pv-row:last-child { border-bottom: none; }
    .pv-label { color: var(--text-mid); }
    .pv-value { color: var(--text); font-weight: 600; }

    .pv-photo-section {
      display: flex; flex-direction: column;
      align-items: center; padding: 1.75rem;
      border-bottom: 1px solid var(--border); gap: 0.75rem;
    }
    .pv-photo-avatar {
      width: 88px; height: 88px; border-radius: 50%;
      background: var(--forest);
      display: flex; align-items: center; justify-content: center;
      font-family: var(--font-serif);
      font-size: 1.8rem; font-weight: 700;
      color: var(--accent-br); cursor: pointer;
      border: 3px solid rgba(78,203,113,0.3);
      overflow: hidden;
    }
    .pv-photo-avatar img { width:100%; height:100%; object-fit:cover; }
    .pv-photo-hint { font-size: 0.7rem; color: var(--text-lt); }

    .no-results-msg {
      padding: 2.5rem; text-align: center;
      color: var(--text-mid); font-size: 0.84rem;
      grid-column: 1 / -1;
    }
  </style>
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
          <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="2" width="7" height="7" rx="1.5"/><rect x="11" y="2" width="7" height="4" rx="1.5"/><rect x="11" y="9" width="7" height="9" rx="1.5"/><rect x="2" y="12" width="7" height="6" rx="1.5"/></svg>
          Dashboard
        </a>
        <a href="#section-mycourses" data-section="section-mycourses">
          <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 5h14M3 10h14M3 15h9" stroke-linecap="round"/></svg>
          My Courses
        </a>
        <a href="#section-browse" data-section="section-browse">
          <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="9" r="5.5"/><path d="M13.5 13.5L17 17" stroke-linecap="round"/></svg>
          Browse Courses
        </a>
        <a href="#section-fees" data-section="section-fees">
          <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="5" width="16" height="12" rx="2"/><path d="M2 9h16M5 13h2" stroke-linecap="round"/></svg>
          Fees
        </a>
        <a href="#section-profile" data-section="section-profile">
          <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="10" cy="7" r="3.5"/><path d="M4 17c0-3.31 2.69-6 6-6s6 2.69 6 6" stroke-linecap="round"/></svg>
          Profile
        </a>
      </nav>
      <div class="sidebar-footer">
        <a href="../php/logout.php" class="btn-signout" style="display:block;text-align:center;text-decoration:none;">Sign Out</a>
      </div>
    </aside>

    <div class="dash-main">
      <header class="dash-topbar">
        <p class="topbar-greeting">Hello, <strong><?= $fullName ?></strong></p>
        <p class="topbar-date" id="topbarDate"></p>
      </header>

      <main class="dash-content">

        <section id="section-dashboard" class="dash-section active">

          <div class="student-hero">

            <div class="profile-card">
              <div class="profile-avatar-wrap">
                <label for="photoUploadHero" style="cursor:pointer">
                  <div class="profile-ring" id="profileRing" style="--ring-pct:0%">
                    <div class="profile-ring-inner" id="profileRingInner"><?= $initials ?></div>
                  </div>
                </label>
                <input type="file" id="photoUploadHero" accept="image/jpeg,image/png,image/webp" style="display:none"/>
              </div>
              <h3><?= $fullName ?></h3>
              <p class="profile-id"><?= $studentId ?></p>
              <div class="profile-ring-stats">
                <div class="prs-item">
                  <span class="prs-num" id="prsEnrolled">0</span>
                  <span class="prs-label">Courses</span>
                </div>
                <div class="prs-sep"></div>
                <div class="prs-item">
                  <span class="prs-num" id="prsCredits">0</span>
                  <span class="prs-label">Credits</span>
                </div>
                <div class="prs-sep"></div>
                <div class="prs-item">
                  <span class="prs-num" id="prsLeft">0</span>
                  <span class="prs-label">Remaining</span>
                </div>
              </div>
              <a href="#section-profile" data-section="section-profile" class="profile-change-link sidebar-nav-trigger">Edit Profile</a>
            </div>

            <div class="stat-cards-grid">
              <div class="scard c1">
                <div class="scard-icon">
                  <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 5h14M3 10h14M3 15h9" stroke-linecap="round"/></svg>
                </div>
                <div>
                  <p class="scard-num" id="statEnrolled">0</p>
                  <p class="scard-label">Enrolled Courses</p>
                </div>
              </div>
              <div class="scard c2">
                <div class="scard-icon">
                  <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="10" cy="10" r="7"/><path d="M10 6v4l3 3" stroke-linecap="round"/></svg>
                </div>
                <div>
                  <p class="scard-num" id="statCreditsUsed">0</p>
                  <p class="scard-label">Credits Used</p>
                </div>
              </div>
              <div class="scard c3">
                <div class="scard-icon">
                  <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="5" width="16" height="12" rx="2"/><path d="M2 9h16" stroke-linecap="round"/></svg>
                </div>
                <div>
                  <p class="scard-num" id="statCreditsAvail">0</p>
                  <p class="scard-label">Credits Available</p>
                </div>
              </div>
              <div class="scard c4">
                <div class="scard-icon">
                  <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M10 3v14M5 8l5-5 5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div>
                  <p class="scard-num" id="statCreditsLeft">0</p>
                  <p class="scard-label">Credits Remaining</p>
                </div>
              </div>
            </div>

          </div>

          <div class="dashboard-body">

            <div class="panel">
              <div class="panel-head">
                <h3>My Enrolled Courses</h3>
              </div>
              <div class="activity-feed" id="enrolledMiniList">
                <p class="table-empty" style="text-align:center;padding:1rem;color:var(--text-mid)">Loading...</p>
              </div>
            </div>

            <div class="panel">
              <div class="panel-head">
                <h3>Fee Summary</h3>
              </div>
              <div class="fees-summary-card" id="feeSummaryWidget">
                <div class="fee-line"><span>Fees Paid</span><strong id="sw_paid">KES —</strong></div>
                <div class="fee-line"><span>Cost per Credit</span><strong id="sw_cpc">KES —</strong></div>
                <div class="fee-line"><span>Credits Available</span><strong id="sw_avail">—</strong></div>
                <div class="fee-line"><span>Credits Used</span><strong id="sw_used">—</strong></div>
                <div class="fee-line"><span>Credits Remaining</span><strong class="accent" id="sw_left">—</strong></div>
                <div class="fee-bar-wrap">
                  <div class="fee-bar-top"><span>Usage</span><span id="sw_pct">0%</span></div>
                  <div class="fee-bar-track"><div class="fee-bar-fill" id="sw_bar"></div></div>
                </div>
              </div>
            </div>

          </div>

        </section>

        <section id="section-mycourses" class="dash-section">
          <div class="section-head"><h2>My Courses</h2><p>All courses you are registered for this term.</p></div>
          <div class="panel">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Course</th><th>Department</th><th>Instructor</th>
                  <th>Credits</th><th>Enrolled/Cap</th><th></th>
                </tr>
              </thead>
              <tbody id="enrolledFullBody">
                <tr><td colspan="6" class="table-empty">Loading...</td></tr>
              </tbody>
            </table>
          </div>
        </section>

        <section id="section-browse" class="dash-section">
          <div class="section-head"><h2>Browse Courses</h2><p>Available courses you can still join.</p></div>
          <div class="panel">
            <div class="browse-search-bar">
              <input type="search" id="courseSearch" placeholder="Search title, department, instructor..."/>
              <select id="deptFilter"><option value="">All departments</option></select>
            </div>
            <div class="course-cards-grid" id="courseCardsGrid">
              <p class="no-results-msg">Loading courses...</p>
            </div>
          </div>
        </section>

        <section id="section-fees" class="dash-section">
          <div class="section-head"><h2>Fees</h2><p>Your payment status and credit allocation.</p></div>
          <div class="panel">
            <div class="fees-summary-card">
              <div class="fee-line"><span>Total Fees Paid</span><strong id="f_paid">KES —</strong></div>
              <div class="fee-line"><span>Cost per Credit</span><strong id="f_cpc">KES —</strong></div>
              <div class="fee-line"><span>Credits Available</span><strong id="f_avail">—</strong></div>
              <div class="fee-line"><span>Credits Used</span><strong id="f_used">—</strong></div>
              <div class="fee-line"><span>Credits Remaining</span><strong class="accent" id="f_left">—</strong></div>
              <div class="fee-bar-wrap">
                <div class="fee-bar-top"><span>Credit usage</span><span id="f_pct">0%</span></div>
                <div class="fee-bar-track" style="height:12px"><div class="fee-bar-fill" id="f_bar"></div></div>
              </div>
              <div class="fee-info-box">To enroll in courses you need sufficient credits. Credits are calculated from your fee payment. Contact the administration office to record or update your payment.</div>
            </div>
            <div class="panel-head" style="border-top:1px solid var(--border);margin-top:0"><h3>Enrolled Courses</h3></div>
            <table class="data-table">
              <thead><tr><th>Course</th><th>Department</th><th>Credits</th></tr></thead>
              <tbody id="feesEnrolledBody"><tr><td colspan="3" class="table-empty">No courses enrolled yet.</td></tr></tbody>
            </table>
          </div>
        </section>

        <section id="section-profile" class="dash-section">
          <div class="section-head"><h2>Profile</h2><p>Your Greenfield account details.</p></div>
          <div class="panel">
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
          </div>
        </section>

      </main>
    </div>
  </div>

  <div id="dashToast" class="toast" role="status"></div>
  <script>window.DASH_USER_ID = <?= $userId ?>;</script>
  <script src="../js/animations.js"></script>
  <script>
  (function() {
    const API = '../php/';

    function countUp(el, target, dur) {
      if (!el) return;
      dur = dur || 1600;
      const start = performance.now();
      function step(now) {
        const p = Math.min((now - start) / dur, 1);
        const ease = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.round(ease * target).toLocaleString();
        if (p < 1) requestAnimationFrame(step);
      }
      requestAnimationFrame(step);
    }

    function showToast(msg, isErr) {
      const t = document.getElementById('dashToast');
      if (!t) return;
      t.textContent = msg;
      t.className = 'toast visible' + (isErr ? ' error' : '');
      clearTimeout(showToast._t);
      showToast._t = setTimeout(() => t.classList.remove('visible'), 3200);
    }

    function esc(str) {
      const d = document.createElement('div');
      d.textContent = str ?? '';
      return d.innerHTML;
    }

    function pct(a, b) { return b ? Math.min(100, Math.round((a / b) * 100)) : 0; }

    function slotsPill(n) {
      n = parseInt(n) || 0;
      if (n <= 0) return '<span class="slots-pill r">Full</span>';
      if (n <= 5)  return '<span class="slots-pill r">' + n + ' left</span>';
      if (n <= 10) return '<span class="slots-pill o">' + n + ' left</span>';
      return '<span class="slots-pill g">' + n + ' left</span>';
    }

    function fmtKes(n) { return 'KES ' + parseInt(n || 0).toLocaleString(); }

    let allAvailable = [];

    async function loadAll() {
      try {
        const [statsRes, enrolledRes, availRes, feesRes] = await Promise.all([
          fetch(API + 'registrations.php?action=stats').then(r => r.json()).catch(() => ({})),
          fetch(API + 'registrations.php?user_id=' + window.DASH_USER_ID).then(r => r.json()).catch(() => ({})),
          fetch(API + 'registrations.php?action=available').then(r => r.json()).catch(() => ({})),
          fetch(API + 'fees.php?action=student&id=' + window.DASH_USER_ID).then(r => r.json()).catch(() => ({})),
        ]);

        const enrolled = enrolledRes.courses || [];
        const fees = (feesRes.success && feesRes.data) ? feesRes.data : null;
        const stats = (statsRes.success && statsRes.stats) ? statsRes.stats : null;

        const enrolledCount = enrolled.length;
        const creditsUsed = enrolled.reduce((s, c) => s + parseInt(c.credits || 0), 0);
        const creditsAvail = fees ? (parseInt(fees.credits_available) || 0) : 0;
        const creditsLeft = Math.max(0, creditsAvail - creditsUsed);
        const usagePct = pct(creditsUsed, creditsAvail);

        countUp(document.getElementById('statEnrolled'), enrolledCount);
        countUp(document.getElementById('statCreditsUsed'), creditsUsed);
        countUp(document.getElementById('statCreditsAvail'), creditsAvail);
        countUp(document.getElementById('statCreditsLeft'), creditsLeft);

        document.getElementById('prsEnrolled').textContent = enrolledCount;
        document.getElementById('prsCredits').textContent = creditsUsed;
        document.getElementById('prsLeft').textContent = creditsLeft;

        const ringEl = document.getElementById('profileRing');
        if (ringEl && creditsAvail > 0) {
          const deg = Math.round((creditsUsed / creditsAvail) * 360);
          ringEl.style.setProperty('--ring-pct', deg + 'deg');
        }

        const feeIds = {
          sw_paid: fmtKes(fees?.fees_paid), sw_cpc: fmtKes(fees?.cost_per_credit),
          sw_avail: creditsAvail, sw_used: creditsUsed, sw_left: creditsLeft,
          f_paid: fmtKes(fees?.fees_paid), f_cpc: fmtKes(fees?.cost_per_credit),
          f_avail: creditsAvail, f_used: creditsUsed, f_left: creditsLeft,
        };
        Object.entries(feeIds).forEach(([id, val]) => {
          const el = document.getElementById(id);
          if (el) el.textContent = val;
        });
        ['sw_pct','f_pct'].forEach(id => { const el = document.getElementById(id); if (el) el.textContent = usagePct + '%'; });
        ['sw_bar','f_bar'].forEach(id => { const el = document.getElementById(id); if (el) el.style.width = usagePct + '%'; });

        renderEnrolledMini(enrolled);
        renderEnrolledFull(enrolled);
        renderFeesEnrolled(enrolled);

        allAvailable = availRes.courses || [];
        populateDepts(allAvailable);
        renderCards(allAvailable);

      } catch(e) {
        showToast('Could not load dashboard data.', true);
      }
    }

    function renderEnrolledMini(courses) {
      const wrap = document.getElementById('enrolledMiniList');
      if (!wrap) return;
      if (!courses.length) {
        wrap.innerHTML = '<p style="text-align:center;padding:1.5rem;color:var(--text-mid);font-size:0.82rem;">No courses enrolled yet. Browse courses to get started.</p>';
        return;
      }
      wrap.innerHTML = courses.map(c =>
        '<div class="activity-item">' +
        '<div class="activity-dot"></div>' +
        '<div>' +
        '<div class="activity-text"><strong>' + esc(c.title) + '</strong> &middot; ' + esc(c.department) + '</div>' +
        '<div class="activity-time">' + c.credits + ' credits &middot; ' + esc(c.instructor) + '</div>' +
        '</div>' +
        '<button class="btn-danger btn-sm btn-unenroll" data-id="' + c.id + '" style="margin-left:auto;flex-shrink:0">Unenroll</button>' +
        '</div>'
      ).join('');
    }

    function renderEnrolledFull(courses) {
      const tbody = document.getElementById('enrolledFullBody');
      if (!tbody) return;
      if (!courses.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="table-empty">You have not enrolled in any courses yet.</td></tr>';
        return;
      }
      tbody.innerHTML = courses.map(c =>
        '<tr><td><strong>' + esc(c.title) + '</strong><br><span style="font-size:0.7rem;color:var(--text-lt)">' + esc(c.code) + '</span></td>' +
        '<td>' + esc(c.department) + '</td><td>' + esc(c.instructor) + '</td>' +
        '<td>' + c.credits + '</td><td>' + c.enrolled + '/' + c.capacity + '</td>' +
        '<td><button class="btn-danger btn-sm btn-unenroll" data-id="' + c.id + '">Unenroll</button></td></tr>'
      ).join('');
    }

    function renderFeesEnrolled(courses) {
      const tbody = document.getElementById('feesEnrolledBody');
      if (!tbody) return;
      if (!courses.length) { tbody.innerHTML = '<tr><td colspan="3" class="table-empty">No courses enrolled yet.</td></tr>'; return; }
      tbody.innerHTML = courses.map(c =>
        '<tr><td>' + esc(c.title) + '</td><td>' + esc(c.department) + '</td><td>' + c.credits + '</td></tr>'
      ).join('');
    }

    function populateDepts(courses) {
      const sel = document.getElementById('deptFilter');
      if (!sel) return;
      const depts = [...new Set(courses.map(c => c.department).filter(Boolean))].sort();
      sel.innerHTML = '<option value="">All departments</option>' +
        depts.map(d => '<option value="' + esc(d) + '">' + esc(d) + '</option>').join('');
    }

    function renderCards(courses) {
      const wrap = document.getElementById('courseCardsGrid');
      if (!wrap) return;
      if (!courses.length) { wrap.innerHTML = '<p class="no-results-msg">No courses match your search.</p>'; return; }
      wrap.innerHTML = courses.map(c =>
        '<div class="crs-card">' +
        '<span class="crs-dept-tag">' + esc(c.department) + '</span>' +
        '<p class="crs-title">' + esc(c.title) + '</p>' +
        '<p class="crs-instructor">' + esc(c.instructor) + '</p>' +
        '<div class="crs-footer">' +
        '<span class="crs-credits">' + c.credits + ' cr</span>' +
        slotsPill(c.slots_remaining) +
        '<button class="btn-enroll btn-enroll-course" data-id="' + c.id + '">Enroll</button>' +
        '</div></div>'
      ).join('');
    }

    function filterCards() {
      const q = (document.getElementById('courseSearch')?.value || '').trim().toLowerCase();
      const dept = document.getElementById('deptFilter')?.value || '';
      renderCards(allAvailable.filter(c => {
        const mq = !q || c.title.toLowerCase().includes(q) || (c.department || '').toLowerCase().includes(q) || (c.instructor || '').toLowerCase().includes(q);
        const md = !dept || c.department === dept;
        return mq && md;
      }));
    }

    document.getElementById('courseSearch')?.addEventListener('input', filterCards);
    document.getElementById('deptFilter')?.addEventListener('change', filterCards);

    async function doEnroll(id) {
      const res = await fetch(API + 'registrations.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({action:'enroll', course_id: id})
      }).then(r => r.json()).catch(() => ({success:false,message:'Connection error.'}));
      if (res.success) { showToast(res.message || 'Enrolled successfully.'); loadAll(); }
      else showToast(res.message || 'Could not enroll.', true);
    }

    async function doUnenroll(id) {
      const res = await fetch(API + 'registrations.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({action:'unenroll', course_id: id})
      }).then(r => r.json()).catch(() => ({success:false,message:'Connection error.'}));
      if (res.success) { showToast(res.message || 'Unenrolled successfully.'); loadAll(); }
      else showToast(res.message || 'Could not unenroll.', true);
    }

    document.addEventListener('click', e => {
      const enroll = e.target.closest('.btn-enroll-course');
      const unenroll = e.target.closest('.btn-unenroll');
      if (enroll && !enroll.disabled) { enroll.disabled = true; doEnroll(parseInt(enroll.dataset.id)); }
      if (unenroll && !unenroll.disabled) { unenroll.disabled = true; doUnenroll(parseInt(unenroll.dataset.id)); }
    });

    async function handlePhotoUpload(file, avatarIds) {
      if (!file) return;
      const form = new FormData();
      form.append('photo', file);
      const res = await fetch(API + 'profile.php?action=upload', {method:'POST', body:form}).then(r => r.json()).catch(() => ({success:false}));
      if (res.success) {
        const src = '../' + res.path;
        avatarIds.forEach(id => {
          const el = document.getElementById(id);
          if (!el) return;
          if (el.classList.contains('profile-ring-inner')) {
            el.innerHTML = '<img src="' + src + '"/>';
          } else {
            el.innerHTML = '<img src="' + src + '"/>';
          }
        });
        showToast('Photo updated.');
      } else showToast(res.message || 'Upload failed.', true);
    }

    document.getElementById('photoUploadHero')?.addEventListener('change', function() {
      handlePhotoUpload(this.files[0], ['profileRingInner', 'pvAvatar']);
    });
    document.getElementById('photoUploadProfile')?.addEventListener('change', function() {
      handlePhotoUpload(this.files[0], ['profileRingInner', 'pvAvatar']);
    });

    function initNav() {
      const links = document.querySelectorAll('.sidebar-nav a[data-section], .sidebar-nav-trigger[data-section]');
      const sections = document.querySelectorAll('.dash-section');
      function go(id) {
        document.querySelectorAll('.sidebar-nav a[data-section]').forEach(a => a.classList.toggle('active', a.dataset.section === id));
        sections.forEach(s => s.classList.toggle('active', s.id === id));
        history.replaceState(null, '', '#' + id);
      }
      links.forEach(a => a.addEventListener('click', e => { e.preventDefault(); go(a.dataset.section); }));
      const hash = location.hash.replace('#', '');
      if (hash && document.getElementById(hash)) go(hash);
    }

    document.getElementById('topbarDate').textContent = new Date().toLocaleDateString('en-GB', {
      weekday:'long', day:'numeric', month:'long', year:'numeric'
    });

    const obs = new IntersectionObserver(entries => {
      entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('revealed'); obs.unobserve(e.target); } });
    }, {threshold: 0.1});
    document.querySelectorAll('[data-scroll-reveal]').forEach(el => obs.observe(el));

    initNav();
    loadAll();

  })();
  </script>
</body>
</html>