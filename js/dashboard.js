(function () {
  const API = '../php/';
  const role = document.body.dataset.role || 'student';
  const adminPage = document.body.dataset.adminPage || '';

  function countUp(el, target, dur) {
    const start = performance.now();
    function step(now) {
      const p = Math.min((now - start) / dur, 1);
      const e = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.round(e * target).toLocaleString();
      if (p < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }

  function animateStat(el, value) {
    if (!el) return;
    const target = parseInt(value, 10) || 0;
    const stored = el.getAttribute('data-count');
    if (stored !== null || el.classList.contains('stat-value')) {
      countUp(el, target, stored ? 1800 : 1400);
    } else {
      el.textContent = target.toLocaleString();
    }
  }

  function showToast(msg, isError) {
    const toast = document.getElementById('dashToast');
    if (!toast) return;
    toast.textContent = msg;
    toast.className = 'toast visible' + (isError ? ' error' : '');
    clearTimeout(showToast._t);
    showToast._t = setTimeout(() => toast.classList.remove('visible'), 3200);
  }

  function showSectionError(id, msg) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = msg;
    el.hidden = !msg;
  }

  async function apiFetch(endpoint, options) {
    const res = await fetch(API + endpoint, options);
    const data = await res.json().catch(() => ({}));
    if (res.status === 401) {
      window.location.href = '../login.html';
      throw new Error('Unauthorized');
    }
    return data;
  }

  async function fetchCourses(admin) {
    const url = admin ? 'courses.php?action=admin' : 'courses.php';
    return apiFetch(url);
  }

  async function fetchRegistrations(action) {
    return apiFetch('registrations.php?action=' + encodeURIComponent(action));
  }

  async function fetchStats() {
    return apiFetch('registrations.php?action=stats');
  }

  function postJson(endpoint, body, queryAction) {
    const q = queryAction ? '?action=' + encodeURIComponent(queryAction) : '';
    return apiFetch(endpoint + q, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });
  }

  async function enrollCourse(courseId) {
    return postJson('registrations.php', { action: 'enroll', course_id: courseId });
  }

  async function unenrollCourse(courseId) {
    return postJson('registrations.php', { action: 'unenroll', course_id: courseId });
  }

  async function addCourse(formData) {
    return postJson('courses.php', Object.assign({ action: 'add' }, formData), 'add');
  }

  async function editCourse(formData) {
    return postJson('courses.php', Object.assign({ action: 'edit' }, formData), 'edit');
  }

  async function deleteCourse(courseId) {
    return postJson('courses.php', { action: 'delete', id: courseId }, 'delete');
  }

  function escapeHtml(str) {
    const d = document.createElement('div');
    d.textContent = str ?? '';
    return d.innerHTML;
  }

  function formatDate(iso) {
    if (!iso) return '—';
    const d = new Date(String(iso).replace(' ', 'T'));
    return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
  }

  function capacityPercent(enrolled, capacity) {
    if (!capacity) return 0;
    return Math.min(100, Math.round((enrolled / capacity) * 100));
  }

  function fillColor(pct) {
    if (pct >= 90) return '#e74c3c';
    if (pct >= 70) return '#e67e22';
    return 'var(--accent-br)';
  }

  function pillCapacity(enrolled, capacity) {
    const pct = capacityPercent(enrolled, capacity);
    return (
      '<span class="pill-capacity">' +
      '<span class="pill-capacity-bar"><span class="pill-capacity-fill" style="width:' +
      pct +
      '%;background:' +
      fillColor(pct) +
      '"></span></span>' +
      '<span class="pill-capacity-text">' +
      enrolled +
      '/' +
      capacity +
      '</span></span>'
    );
  }

  function capacityListBar(enrolled, capacity) {
    const pct = capacityPercent(enrolled, capacity);
    return (
      '<div class="capacity-track"><div class="capacity-track-fill" style="width:' +
      pct +
      '%"></div></div><span class="capacity-pct">' +
      pct +
      '%</span>'
    );
  }

  function setTopbarDate() {
    const el = document.getElementById('topbarDate');
    if (el) {
      el.textContent = new Date().toLocaleDateString('en-GB', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
      });
    }
  }

  function initScrollReveal() {
    const obs = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('revealed');
            obs.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.1 }
    );
    document.querySelectorAll('[data-scroll-reveal]').forEach((el) => obs.observe(el));
  }

  async function ensureAdminSession() {
    const cached = sessionStorage.getItem('gf_user_name');
    const nameEl = document.getElementById('adminName');
    if (cached && nameEl) nameEl.textContent = cached;

    try {
      const data = await apiFetch('session.php');
      if (!data.success) {
        window.location.href = '../login.html';
        return false;
      }
      if (nameEl) nameEl.textContent = data.name;
      sessionStorage.setItem('gf_user_name', data.name);
      sessionStorage.setItem('gf_user_role', 'admin');
      return true;
    } catch {
      if (!cached) {
        window.location.href = '../login.html';
        return false;
      }
      return true;
    }
  }

  async function initOverviewPage() {
    showSectionError('overviewError', '');
    try {
      const [statsRes, coursesRes, recentRes] = await Promise.all([
        fetchStats(),
        fetchCourses(true),
        fetchRegistrations('recent'),
      ]);

      if (statsRes.success && statsRes.stats) {
        const s = statsRes.stats;
        animateStat(document.getElementById('statStudents'), s.total_students);
        animateStat(document.getElementById('statCourses'), s.total_courses);
        animateStat(document.getElementById('statRegs'), s.total_registrations);
        animateStat(document.getElementById('statDepts'), s.departments_count);
      }

      const list = document.getElementById('capacityList');
      if (coursesRes.success && list) {
        const courses = coursesRes.courses || [];
        if (!courses.length) {
          list.innerHTML = '<p class="table-empty">No courses yet. Add courses from Manage Courses.</p>';
        } else {
          list.innerHTML = courses
            .map((c) => {
              return (
                '<div class="capacity-list-item">' +
                '<div class="capacity-list-name">' +
                escapeHtml(c.title) +
                '<span>' +
                escapeHtml(c.code) +
                ' · ' +
                escapeHtml(c.department) +
                '</span></div>' +
                capacityListBar(c.enrolled, c.capacity) +
                '</div>'
              );
            })
            .join('');
        }
      }

      const tbody = document.getElementById('recentRegsBody');
      if (recentRes.success && tbody) {
        const rows = recentRes.registrations || [];
        if (!rows.length) {
          tbody.innerHTML = '<tr><td colspan="4" class="table-empty">No registrations yet.</td></tr>';
        } else {
          tbody.innerHTML = rows
            .map(
              (r) =>
                '<tr><td>' +
                escapeHtml(r.first_name + ' ' + r.last_name) +
                '</td><td>' +
                escapeHtml(r.course_title) +
                '</td><td>' +
                escapeHtml(r.department) +
                '</td><td>' +
                formatDate(r.registered_at) +
                '</td></tr>'
            )
            .join('');
        }
      }
    } catch {
      showSectionError('overviewError', 'Could not load overview data. Check your connection and try again.');
    }

    document.getElementById('btnExportXml')?.addEventListener('click', () => {
      window.location.href = API + 'xml_export.php';
    });
  }

  let coursesCache = [];

  function renderCoursesTable(courses) {
    const tbody = document.getElementById('coursesTableBody');
    if (!tbody) return;
    coursesCache = courses;

    if (!courses.length) {
      tbody.innerHTML = '<tr><td colspan="7" class="table-empty">No courses yet. Use Add Course to create one.</td></tr>';
      return;
    }

    tbody.innerHTML = courses
      .map(
        (c) =>
          '<tr data-id="' +
          c.id +
          '" class="course-row">' +
          '<td>' +
          escapeHtml(c.code) +
          '</td>' +
          '<td><strong>' +
          escapeHtml(c.title) +
          '</strong></td>' +
          '<td>' +
          escapeHtml(c.department) +
          '</td>' +
          '<td>' +
          escapeHtml(c.instructor) +
          '</td>' +
          '<td>' +
          c.credits +
          '</td>' +
          '<td>' +
          pillCapacity(c.enrolled, c.capacity) +
          '</td>' +
          '<td class="course-actions">' +
          '<button type="button" class="btn-sm btn-outline btn-row-edit" data-id="' +
          c.id +
          '">Edit</button> ' +
          '<button type="button" class="btn-sm btn-danger btn-row-delete" data-id="' +
          c.id +
          '">Delete</button>' +
          '</td></tr>'
      )
      .join('');
  }

  function courseFormPayload(form) {
    return {
      title: form.title.value.trim(),
      code: form.code.value.trim(),
      department: form.department.value.trim(),
      instructor: form.instructor.value.trim(),
      credits: parseInt(form.credits.value, 10),
      capacity: parseInt(form.capacity.value, 10),
      description: form.description.value.trim(),
    };
  }

  function buildEditRow(course) {
    return (
      '<tr class="course-edit-row" data-id="' +
      course.id +
      '"><td colspan="7">' +
      '<form class="row-edit-form-inner" data-id="' +
      course.id +
      '">' +
      '<div class="row-edit-grid">' +
      '<div class="form-field"><label>Code</label><input name="code" value="' +
      escapeHtml(course.code) +
      '" required></div>' +
      '<div class="form-field"><label>Title</label><input name="title" value="' +
      escapeHtml(course.title) +
      '" required></div>' +
      '<div class="form-field"><label>Department</label><input name="department" value="' +
      escapeHtml(course.department) +
      '" required></div>' +
      '<div class="form-field"><label>Instructor</label><input name="instructor" value="' +
      escapeHtml(course.instructor) +
      '" required></div>' +
      '<div class="form-field"><label>Credits</label><input type="number" name="credits" min="1" max="12" value="' +
      course.credits +
      '" required></div>' +
      '<div class="form-field"><label>Capacity</label><input type="number" name="capacity" min="1" max="500" value="' +
      course.capacity +
      '" required></div>' +
      '<div class="form-field full"><label>Description</label><textarea name="description">' +
      escapeHtml(course.description || '') +
      '</textarea></div>' +
      '<div class="row-edit-actions">' +
      '<button type="submit" class="btn-primary btn-sm">Save</button>' +
      '<button type="button" class="btn-cancel-link btn-row-cancel">Cancel</button>' +
      '</div>' +
      '<p class="row-error" hidden></p>' +
      '</div></form></td></tr>'
    );
  }

  async function loadCoursesPage() {
    showSectionError('coursesError', '');
    try {
      const res = await fetchCourses(true);
      if (res.success) renderCoursesTable(res.courses || []);
      else showSectionError('coursesError', res.message || 'Failed to load courses.');
    } catch {
      showSectionError('coursesError', 'Could not load courses.');
    }
  }

  function initManageCoursesPage() {
    const panel = document.getElementById('addCoursePanel');
    const form = document.getElementById('addCourseForm');
    const tbody = document.getElementById('coursesTableBody');

    document.getElementById('btnAddCourse')?.addEventListener('click', () => {
      form?.reset();
      panel?.classList.add('open');
    });

    document.getElementById('btnCancelAdd')?.addEventListener('click', () => {
      panel?.classList.remove('open');
    });

    form?.addEventListener('submit', async (e) => {
      e.preventDefault();
      showSectionError('coursesError', '');
      try {
        const data = await addCourse(courseFormPayload(form));
        if (data.success) {
          showToast(data.message || 'Course added.');
          panel.classList.remove('open');
          form.reset();
          await loadCoursesPage();
        } else {
          showSectionError('coursesError', data.message || 'Could not add course.');
        }
      } catch {
        showSectionError('coursesError', 'Connection error while saving.');
      }
    });

    tbody?.addEventListener('click', async (e) => {
      const editBtn = e.target.closest('.btn-row-edit');
      const delBtn = e.target.closest('.btn-row-delete');
      const cancelBtn = e.target.closest('.btn-row-cancel');
      const yesBtn = e.target.closest('.btn-delete-yes');
      const noBtn = e.target.closest('.btn-delete-no');

      if (editBtn) {
        const id = parseInt(editBtn.dataset.id, 10);
        const course = coursesCache.find((c) => c.id === id);
        if (!course) return;
        const row = editBtn.closest('tr');
        if (document.querySelector('.course-edit-row')) return;
        row.insertAdjacentHTML('afterend', buildEditRow(course));
        row.style.display = 'none';
        return;
      }

      if (cancelBtn) {
        const editRow = cancelBtn.closest('.course-edit-row');
        const id = parseInt(editRow.dataset.id, 10);
        document.querySelector('tr.course-row[data-id="' + id + '"]').style.display = '';
        editRow.remove();
        return;
      }

      if (delBtn) {
        const cell = delBtn.closest('.course-actions');
        cell.innerHTML =
          '<span class="inline-confirm"><span style="font-size:0.72rem;color:var(--text-mid)">Are you sure?</span> ' +
          '<button type="button" class="confirm-yes btn-delete-yes" data-id="' +
          delBtn.dataset.id +
          '">Yes, delete</button> ' +
          '<button type="button" class="btn-cancel-link btn-delete-no" data-id="' +
          delBtn.dataset.id +
          '">Cancel</button></span>';
        return;
      }

      if (noBtn) {
        await loadCoursesPage();
        return;
      }

      if (yesBtn) {
        const id = parseInt(yesBtn.dataset.id, 10);
        const row = document.querySelector('tr.course-row[data-id="' + id + '"]');
        try {
          const data = await deleteCourse(id);
          if (data.success) {
            showToast(data.message || 'Deleted.');
            await loadCoursesPage();
          } else if (row) {
            const cell = row.querySelector('.course-actions');
            if (cell) {
              cell.innerHTML = '<span class="row-error">' + escapeHtml(data.message) + '</span> ' +
                '<button type="button" class="btn-sm btn-outline btn-row-edit" data-id="' + id + '">Edit</button> ' +
                '<button type="button" class="btn-sm btn-danger btn-row-delete" data-id="' + id + '">Delete</button>';
            }
          }
        } catch {
          showSectionError('coursesError', 'Delete request failed.');
        }
      }
    });

    tbody?.addEventListener('submit', async (e) => {
      const f = e.target.closest('.row-edit-form-inner');
      if (!f) return;
      e.preventDefault();
      const id = parseInt(f.dataset.id, 10);
      const err = f.querySelector('.row-error');
      const payload = Object.assign({ id }, courseFormPayload(f));
      try {
        const data = await editCourse(payload);
        if (data.success) {
          showToast(data.message || 'Course updated.');
          await loadCoursesPage();
        } else if (err) {
          err.textContent = data.message || 'Update failed.';
          err.hidden = false;
        }
      } catch {
        if (err) {
          err.textContent = 'Connection error.';
          err.hidden = false;
        }
      }
    });

    loadCoursesPage();
  }

  let allRegistrations = [];
  let sortKey = 'registered_at';
  let sortDir = -1;

  function computeRegSummary(rows) {
    const depts = new Set();
    const counts = {};
    rows.forEach((r) => {
      if (r.department) depts.add(r.department);
      const key = r.course_title;
      counts[key] = (counts[key] || 0) + 1;
    });
    let popular = '—';
    let max = 0;
    Object.keys(counts).forEach((k) => {
      if (counts[k] > max) {
        max = counts[k];
        popular = k;
      }
    });
    return {
      total: rows.length,
      popular: max > 0 ? popular : '—',
      depts: depts.size,
    };
  }

  function filterRegistrations(rows, search, dept) {
    const q = search.trim().toLowerCase();
    return rows.filter((r) => {
      const name = (r.first_name + ' ' + r.last_name).toLowerCase();
      const course = (r.course_title || '').toLowerCase();
      const matchSearch = !q || name.includes(q) || course.includes(q);
      const matchDept = !dept || r.department === dept;
      return matchSearch && matchDept;
    });
  }

  function sortRegistrations(rows) {
    const sorted = rows.slice();
    sorted.sort((a, b) => {
      let va;
      let vb;
      switch (sortKey) {
        case 'student':
          va = (a.first_name + ' ' + a.last_name).toLowerCase();
          vb = (b.first_name + ' ' + b.last_name).toLowerCase();
          break;
        case 'student_id':
          va = a.student_id;
          vb = b.student_id;
          break;
        case 'course':
          va = a.course_title;
          vb = b.course_title;
          break;
        case 'department':
          va = a.department;
          vb = b.department;
          break;
        case 'credits':
          va = parseInt(a.credits, 10);
          vb = parseInt(b.credits, 10);
          break;
        case 'registered_at':
        default:
          va = new Date(String(a.registered_at).replace(' ', 'T')).getTime();
          vb = new Date(String(b.registered_at).replace(' ', 'T')).getTime();
      }
      if (va < vb) return -1 * sortDir;
      if (va > vb) return 1 * sortDir;
      return 0;
    });
    return sorted;
  }

  function renderRegTable(rows) {
    const tbody = document.getElementById('regsTableBody');
    if (!tbody) return;
    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="6" class="table-empty">No registrations match your filters.</td></tr>';
      return;
    }
    tbody.innerHTML = rows
      .map(
        (r) =>
          '<tr><td>' +
          escapeHtml(r.first_name + ' ' + r.last_name) +
          '</td><td>' +
          escapeHtml(r.student_id) +
          '</td><td>' +
          escapeHtml(r.course_title) +
          '</td><td>' +
          escapeHtml(r.department) +
          '</td><td>' +
          r.credits +
          '</td><td>' +
          formatDate(r.registered_at) +
          '</td></tr>'
      )
      .join('');
  }

  function updateSortHeaders() {
    document.querySelectorAll('.data-table th.sortable').forEach((th) => {
      const arrow = th.querySelector('.sort-arrow');
      const active = th.dataset.sort === sortKey;
      if (arrow) {
        arrow.classList.toggle('active', active);
        arrow.textContent = active ? (sortDir > 0 ? '▲' : '▼') : '↕';
      }
    });
  }

  function applyRegFilters() {
    const search = document.getElementById('regSearch')?.value || '';
    const dept = document.getElementById('regDeptFilter')?.value || '';
    const filtered = filterRegistrations(allRegistrations, search, dept);
    const sorted = sortRegistrations(filtered);
    renderRegTable(sorted);
    updateSortHeaders();

    const summary = computeRegSummary(allRegistrations);
    const elTotal = document.getElementById('sumTotal');
    const elPop = document.getElementById('sumPopular');
    const elDept = document.getElementById('sumDepts');
    if (elTotal) elTotal.textContent = summary.total;
    if (elPop) elPop.textContent = summary.popular;
    if (elDept) elDept.textContent = summary.depts;
  }

  function formatKes(amount) {
    const n = parseFloat(amount);
    if (Number.isNaN(n)) return '0';
    return n.toLocaleString('en-KE', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
  }

  async function loadStudentsSection() {
    showSectionError('studentsError', '');
    const tbody = document.getElementById('adminStudentsBody');
    if (!tbody) return;
    try {
      const res = await fetchRegistrations('students');
      if (!res.success) {
        showSectionError('studentsError', res.message || 'Failed to load students.');
        return;
      }
      const rows = res.students || [];
      if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="table-empty">No students registered yet.</td></tr>';
        return;
      }
      tbody.innerHTML = rows
        .map(
          (s) =>
            '<tr><td>' +
            escapeHtml((s.first_name || '') + ' ' + (s.last_name || '')) +
            '</td><td>' +
            escapeHtml(s.email) +
            '</td><td>' +
            escapeHtml(s.student_id) +
            '</td><td>' +
            (s.enrolled_count || 0) +
            '</td><td>' +
            formatKes(s.fees_paid) +
            '</td><td>' +
            formatDate(s.created_at) +
            '</td></tr>'
        )
        .join('');
    } catch {
      showSectionError('studentsError', 'Could not load students.');
    }
  }

  let feesStudentCache = {};

  async function loadFeesSection() {
    showSectionError('feesError', '');
    const tbody = document.getElementById('feesTableBody');
    const costInput = document.getElementById('costPerCredit');
    if (!tbody) return;
    try {
      const res = await apiFetch('fees.php?action=all');
      if (!res.success) {
        showSectionError('feesError', res.message || 'Failed to load fees.');
        return;
      }
      if (costInput && res.cost_per_credit) {
        costInput.value = parseFloat(res.cost_per_credit);
      }
      const rows = res.students || [];
      feesStudentCache = {};
      if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="table-empty">No students found.</td></tr>';
        return;
      }
      tbody.innerHTML = rows
        .map((s) => {
          feesStudentCache[s.id] = s;
          return (
            '<tr data-id="' +
            s.id +
            '"><td>' +
            escapeHtml(s.name) +
            '</td><td>' +
            escapeHtml(s.student_id) +
            '</td><td>' +
            formatKes(s.fees_paid) +
            '</td><td>' +
            (s.credits_available || 0) +
            '</td><td>' +
            (s.credits_used || 0) +
            '</td><td>' +
            (s.credits_remaining || 0) +
            '</td><td><button type="button" class="btn-sm btn-outline btn-edit-fee" data-id="' +
            s.id +
            '">Update</button></td></tr>'
          );
        })
        .join('');
    } catch {
      showSectionError('feesError', 'Could not load fee data.');
    }
  }

  function openFeeModal(id, name, amount) {
    const modal = document.getElementById('feeEditModal');
    if (!modal) return;
    document.getElementById('feeEditStudentId').value = id;
    document.getElementById('feeEditStudentName').textContent = name;
    document.getElementById('feeEditAmount').value = parseFloat(amount) || 0;
    modal.hidden = false;
  }

  function closeFeeModal() {
    const modal = document.getElementById('feeEditModal');
    if (modal) modal.hidden = true;
  }

  function initFeesSection() {
    const settingsForm = document.getElementById('feesSettingsForm');
    const tbody = document.getElementById('feesTableBody');

    settingsForm?.addEventListener('submit', async (e) => {
      e.preventDefault();
      showSectionError('feesError', '');
      const val = parseFloat(document.getElementById('costPerCredit')?.value);
      try {
        const data = await postJson('fees.php', { action: 'settings', cost_per_credit: val }, 'settings');
        if (data.success) {
          showToast(data.message || 'Setting saved.');
          await loadFeesSection();
        } else {
          showSectionError('feesError', data.message || 'Could not save setting.');
        }
      } catch {
        showSectionError('feesError', 'Connection error.');
      }
    });

    document.getElementById('feeEditForm')?.addEventListener('submit', async (e) => {
      e.preventDefault();
      const studentId = parseInt(document.getElementById('feeEditStudentId')?.value, 10);
      const amount = parseFloat(document.getElementById('feeEditAmount')?.value);
      try {
        const data = await postJson(
          'fees.php',
          { action: 'update', student_id: studentId, amount },
          'update'
        );
        if (data.success) {
          showToast(data.message || 'Fees updated.');
          closeFeeModal();
          await loadFeesSection();
        } else {
          showToast(data.message || 'Update failed.', true);
        }
      } catch {
        showToast('Connection error.', true);
      }
    });

    document.getElementById('feeEditCancel')?.addEventListener('click', closeFeeModal);
    document.getElementById('feeEditBackdrop')?.addEventListener('click', closeFeeModal);

    tbody?.addEventListener('click', (e) => {
      const btn = e.target.closest('.btn-edit-fee');
      if (!btn) return;
      const student = feesStudentCache[btn.dataset.id];
      if (!student) return;
      openFeeModal(student.id, student.name, student.fees_paid);
    });
  }

  async function loadAdminsSection() {
    showSectionError('adminsError', '');
    const tbody = document.getElementById('adminsTableBody');
    if (!tbody) return;
    try {
      const res = await apiFetch('admins.php?action=all');
      if (!res.success) {
        showSectionError('adminsError', res.message || 'Failed to load admins.');
        return;
      }
      const rows = res.admins || [];
      if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="table-empty">No admin accounts.</td></tr>';
        return;
      }
      tbody.innerHTML = rows
        .map(
          (a) =>
            '<tr><td>' +
            escapeHtml(a.name) +
            '</td><td>' +
            escapeHtml(a.email) +
            '</td><td>' +
            formatDate(a.created_at) +
            '</td><td>' +
            escapeHtml(a.created_by) +
            '</td><td>' +
            '<button type="button" class="btn-sm btn-danger btn-delete-admin" data-id="' +
            a.id +
            '">Delete</button>' +
            '</td></tr>'
        )
        .join('');
    } catch {
      showSectionError('adminsError', 'Could not load admin accounts.');
    }
  }

  function initAdminsSection() {
    const panel = document.getElementById('addAdminPanel');
    const form = document.getElementById('addAdminForm');
    const tbody = document.getElementById('adminsTableBody');

    document.getElementById('btnAddAdmin')?.addEventListener('click', () => {
      form?.reset();
      panel?.classList.add('open');
    });

    document.getElementById('btnCancelAdmin')?.addEventListener('click', () => {
      panel?.classList.remove('open');
    });

    form?.addEventListener('submit', async (e) => {
      e.preventDefault();
      showSectionError('adminsError', '');
      const fd = new FormData(form);
      const body = {
        action: 'create',
        first_name: String(fd.get('first_name') || '').trim(),
        last_name: String(fd.get('last_name') || '').trim(),
        email: String(fd.get('email') || '').trim(),
        password: String(fd.get('password') || ''),
      };
      try {
        const data = await postJson('admins.php', body, 'create');
        if (data.success) {
          showToast(data.message || 'Admin created.');
          panel?.classList.remove('open');
          form.reset();
          await loadAdminsSection();
        } else {
          showSectionError('adminsError', data.message || 'Could not create admin.');
        }
      } catch {
        showSectionError('adminsError', 'Connection error.');
      }
    });

    tbody?.addEventListener('click', async (e) => {
      const btn = e.target.closest('.btn-delete-admin');
      if (!btn) return;
      if (!confirm('Delete this admin account? This cannot be undone.')) return;
      const id = parseInt(btn.dataset.id, 10);
      try {
        const data = await postJson('admins.php', { action: 'delete', id }, 'delete');
        if (data.success) {
          showToast(data.message || 'Admin deleted.');
          await loadAdminsSection();
        } else {
          showToast(data.message || 'Delete failed.', true);
        }
      } catch {
        showToast('Connection error.', true);
      }
    });
  }

  function initAdminSpa() {
    const loaded = {
      'section-overview': false,
      'section-courses': false,
      'section-students': false,
      'section-registrations': false,
      'section-fees': false,
      'section-admins': false,
    };

    async function loadSection(sectionId) {
      if (loaded[sectionId]) return;
      loaded[sectionId] = true;
      if (sectionId === 'section-overview') await initOverviewPage();
      else if (sectionId === 'section-courses') {
        initManageCoursesPage();
      } else if (sectionId === 'section-students') await loadStudentsSection();
      else if (sectionId === 'section-registrations') await initRegistrationsPage();
      else if (sectionId === 'section-fees') await loadFeesSection();
      else if (sectionId === 'section-admins') await loadAdminsSection();
    }

    initFeesSection();
    initAdminsSection();

    const links = document.querySelectorAll('.sidebar-nav a[data-section]');
    const sections = document.querySelectorAll('.dash-section');

    function go(sectionId) {
      links.forEach((a) => a.classList.toggle('active', a.dataset.section === sectionId));
      sections.forEach((s) => s.classList.toggle('active', s.id === sectionId));
      history.replaceState(null, '', '#' + sectionId);
      loadSection(sectionId);
    }

    links.forEach((a) => {
      a.addEventListener('click', (e) => {
        e.preventDefault();
        go(a.dataset.section);
      });
    });

    const hash = location.hash.replace('#', '');
    const initial = hash && document.getElementById(hash) ? hash : 'section-overview';
    go(initial);
  }

  async function initRegistrationsPage() {
    showSectionError('regsError', '');
    try {
      const res = await fetchRegistrations('all');
      if (!res.success) {
        showSectionError('regsError', res.message || 'Failed to load registrations.');
        return;
      }
      allRegistrations = res.registrations || [];

      const deptSelect = document.getElementById('regDeptFilter');
      if (deptSelect) {
        const depts = [...new Set(allRegistrations.map((r) => r.department).filter(Boolean))].sort();
        deptSelect.innerHTML =
          '<option value="">All departments</option>' +
          depts.map((d) => '<option value="' + escapeHtml(d) + '">' + escapeHtml(d) + '</option>').join('');
      }

      applyRegFilters();
    } catch {
      showSectionError('regsError', 'Could not load registrations.');
    }

    document.getElementById('regSearch')?.addEventListener('input', applyRegFilters);
    document.getElementById('regDeptFilter')?.addEventListener('change', applyRegFilters);

    document.querySelectorAll('.data-table th.sortable').forEach((th) => {
      th.addEventListener('click', () => {
        const key = th.dataset.sort;
        if (sortKey === key) sortDir *= -1;
        else {
          sortKey = key;
          sortDir = 1;
        }
        applyRegFilters();
      });
    });

    document.getElementById('btnExportXmlRegs')?.addEventListener('click', () => {
      window.location.href = API + 'xml_export.php';
    });
  }

  async function getEnrolled() {
    return apiFetch('registrations.php?user_id=' + encodeURIComponent(window.DASH_USER_ID));
  }

  async function getAvailable() {
    return apiFetch('registrations.php?action=available');
  }

  async function getStudentStats() {
    return apiFetch('registrations.php?action=stats');
  }

  function capacityBar(enrolled, capacity) {
    const pct = capacityPercent(enrolled, capacity);
    const cls = pct >= 100 ? 'full' : pct >= 80 ? 'high' : '';
    return (
      '<div class="capacity-bar-wrap"><div class="capacity-bar"><div class="capacity-fill ' +
      cls +
      '" style="width:' +
      pct +
      '%"></div></div><span class="capacity-label">' +
      enrolled +
      '/' +
      capacity +
      '</span></div>'
    ).replace('<div class="capacity-bar">', '<div class="capacity-bar">');
  }

  function renderStudentEnrolled(courses) {
    const tbody = document.getElementById('enrolledBody');
    if (!tbody) return;
    if (!courses.length) {
      tbody.innerHTML = '<tr><td colspan="6" class="table-empty">You have not enrolled in any courses yet.</td></tr>';
      return;
    }
    tbody.innerHTML = courses
      .map(
        (c) =>
          '<tr><td><strong>' +
          escapeHtml(c.title) +
          '</strong><br><span style="color:var(--text-lt);font-size:0.72rem">' +
          escapeHtml(c.code) +
          '</span></td><td>' +
          escapeHtml(c.department) +
          '</td><td>' +
          escapeHtml(c.instructor) +
          '</td><td>' +
          c.credits +
          '</td><td>' +
          capacityBar(c.enrolled, c.capacity) +
          '</td><td><button type="button" class="btn-sm btn-danger btn-unenroll" data-course-id="' +
          c.id +
          '">Unenroll</button></td></tr>'
      )
      .join('');
  }

  function renderStudentAvailable(courses) {
    const tbody = document.getElementById('availableBody');
    if (!tbody) return;
    if (!courses.length) {
      tbody.innerHTML = '<tr><td colspan="6" class="table-empty">No additional courses available.</td></tr>';
      return;
    }
    tbody.innerHTML = courses
      .map(
        (c) =>
          '<tr><td><strong>' +
          escapeHtml(c.title) +
          '</strong></td><td>' +
          escapeHtml(c.department) +
          '</td><td>' +
          escapeHtml(c.instructor) +
          '</td><td>' +
          c.credits +
          '</td><td>' +
          c.slots_remaining +
          '</td><td><button type="button" class="btn-enroll btn-enroll-course" data-course-id="' +
          c.id +
          '">Enroll</button></td></tr>'
      )
      .join('');
  }

  async function loadStudentDashboard() {
    try {
      const [statsRes, enrolledRes, availableRes] = await Promise.all([
        getStudentStats(),
        getEnrolled(),
        getAvailable(),
      ]);
      if (statsRes.success && statsRes.stats) {
        animateStat(document.getElementById('statEnrolled'), statsRes.stats.enrolled_courses);
        animateStat(document.getElementById('statCredits'), statsRes.stats.total_credits);
        animateStat(document.getElementById('statSlots'), statsRes.stats.available_slots);
      }
      if (enrolledRes.success) renderStudentEnrolled(enrolledRes.courses || []);
      if (availableRes.success) renderStudentAvailable(availableRes.courses || []);
    } catch {
      showToast('Could not load dashboard.', true);
    }
  }

  function initStudent() {
    document.getElementById('enrolledPanel')?.addEventListener('click', async (e) => {
      const btn = e.target.closest('.btn-unenroll');
      if (!btn) return;
      const data = await unenrollCourse(parseInt(btn.dataset.courseId, 10));
      if (data.success) {
        showToast(data.message || 'Unenrolled.');
        loadStudentDashboard();
      } else showToast(data.message || 'Failed.', true);
    });
    document.getElementById('availablePanel')?.addEventListener('click', async (e) => {
      const btn = e.target.closest('.btn-enroll-course');
      if (!btn) return;
      btn.disabled = true;
      const data = await enrollCourse(parseInt(btn.dataset.courseId, 10));
      if (data.success) {
        showToast(data.message || 'Enrolled.');
        loadStudentDashboard();
      } else {
        showToast(data.message || 'Failed.', true);
        btn.disabled = false;
      }
    });
    loadStudentDashboard();
  }

  function initNav() {
    const links = document.querySelectorAll('.sidebar-nav a[data-section]');
    const sections = document.querySelectorAll('.dash-section');
    if (!links.length) return;

    function go(sectionId) {
      links.forEach((a) => a.classList.toggle('active', a.dataset.section === sectionId));
      sections.forEach((s) => s.classList.toggle('active', s.id === sectionId));
      history.replaceState(null, '', '#' + sectionId);
    }

    links.forEach((a) => {
      a.addEventListener('click', (e) => {
        e.preventDefault();
        go(a.dataset.section);
      });
    });

    const hash = location.hash.replace('#', '');
    if (hash && document.getElementById(hash)) go(hash);
  }

  document.addEventListener('DOMContentLoaded', async () => {
    setTopbarDate();
    initScrollReveal();

    if (role === 'admin' && adminPage) {
      const ok = await ensureAdminSession();
      if (!ok) return;
      if (adminPage === 'overview') initOverviewPage();
      else if (adminPage === 'courses') initManageCoursesPage();
      else if (adminPage === 'registrations') initRegistrationsPage();
      return;
    }

    if (role === 'admin' && document.body.dataset.adminSpa === '1') {
      initAdminSpa();
      return;
    }

    if (role === 'admin') {
      initNav();
      return;
    }

    initNav();
    if (role === 'student') initStudent();
  });

  window.DashboardAPI = {
    fetchCourses,
    fetchRegistrations,
    fetchStats,
    enrollCourse,
    unenrollCourse,
    addCourse,
    editCourse,
    deleteCourse,
    countUp,
    showToast,
  };
})();
