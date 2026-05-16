const slider   = document.getElementById('authSlider');
const loginForm    = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');

document.getElementById('goRegister').addEventListener('click', () => {
  slider.classList.add('show-register');
});

document.getElementById('goLogin').addEventListener('click', () => {
  slider.classList.remove('show-register');
});

function setError(inputId, errId, msg) {
  const el = document.getElementById(inputId);
  const err = document.getElementById(errId);
  if (el) el.classList.add('has-error');
  if (err) err.textContent = msg;
}

function clearError(inputId, errId) {
  const el = document.getElementById(inputId);
  const err = document.getElementById(errId);
  if (el) { el.classList.remove('has-error'); el.classList.remove('is-valid'); }
  if (err) err.textContent = '';
}

function setValid(inputId) {
  const el = document.getElementById(inputId);
  if (el) { el.classList.remove('has-error'); el.classList.add('is-valid'); }
}

function showServerMsg(containerId, msg, isError) {
  const el = document.getElementById(containerId);
  el.textContent = msg;
  el.className = 'server-msg ' + (isError ? 'is-error' : 'is-success');
  el.style.display = 'block';
}

function setLoading(btnId, loading) {
  const btn = document.getElementById(btnId);
  const label = btn.querySelector('.btn-label');
  const spinner = btn.querySelector('.btn-spinner');
  btn.disabled = loading;
  label.style.display = loading ? 'none' : 'inline';
  spinner.style.display = loading ? 'inline-flex' : 'none';
}

function isValidEmail(v) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
}

document.querySelectorAll('.toggle-pass').forEach(btn => {
  btn.addEventListener('click', () => {
    const input = document.getElementById(btn.dataset.target);
    const show = btn.querySelector('.eye-show');
    const hide = btn.querySelector('.eye-hide');
    if (input.type === 'password') {
      input.type = 'text';
      show.style.display = 'none';
      hide.style.display = 'inline';
    } else {
      input.type = 'password';
      show.style.display = 'inline';
      hide.style.display = 'none';
    }
  });
});

const regPassInput = document.getElementById('regPass');
if (regPassInput) {
  regPassInput.addEventListener('input', () => {
    const v = regPassInput.value;
    const bar = document.getElementById('psBar');
    let strength = 0;
    if (v.length >= 8) strength++;
    if (/[A-Z]/.test(v)) strength++;
    if (/[0-9]/.test(v)) strength++;
    if (/[^A-Za-z0-9]/.test(v)) strength++;
    const colors = ['', '#e74c3c', '#e67e22', '#f1c40f', '#2d7a4e'];
    const widths = ['0%', '25%', '50%', '75%', '100%'];
    bar.style.width = widths[strength];
    bar.style.background = colors[strength];
  });
}

loginForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  let valid = true;

  clearError('loginEmail', 'loginEmailErr');
  clearError('loginPass', 'loginPassErr');

  const email = document.getElementById('loginEmail').value.trim();
  const pass  = document.getElementById('loginPass').value;

  if (!email) {
    setError('loginEmail', 'loginEmailErr', 'Email is required.');
    valid = false;
  } else if (!isValidEmail(email)) {
    setError('loginEmail', 'loginEmailErr', 'Enter a valid email address.');
    valid = false;
  } else {
    setValid('loginEmail');
  }

  if (!pass) {
    setError('loginPass', 'loginPassErr', 'Password is required.');
    valid = false;
  } else {
    setValid('loginPass');
  }

  if (!valid) return;

  setLoading('loginBtn', true);
  document.getElementById('loginServerMsg').style.display = 'none';

  try {
    const res = await fetch('php/login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ email, password: pass })
    });
    const data = await res.json();

    if (data.success) {
      if (data.user) {
        sessionStorage.setItem('gf_user_name', data.user.name || '');
        sessionStorage.setItem('gf_user_role', data.user.role || '');
        sessionStorage.setItem('gf_user_email', data.user.email || '');
      }
      showServerMsg('loginServerMsg', 'Signing you in...', false);
      setTimeout(() => {
        window.location.href = data.redirect;
      }, 600);
    } else {
      showServerMsg('loginServerMsg', data.message || 'Invalid email or password.', true);
      setLoading('loginBtn', false);
    }
  } catch {
    showServerMsg('loginServerMsg', 'Connection error. Please try again.', true);
    setLoading('loginBtn', false);
  }
});

registerForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  let valid = true;

  ['regFirst','regLast','regEmail','regStudentId','regPass'].forEach(id => {
    clearError(id, id + 'Err');
  });
  clearError('regTerms', 'regTermsErr');

  const first     = document.getElementById('regFirst').value.trim();
  const last      = document.getElementById('regLast').value.trim();
  const email     = document.getElementById('regEmail').value.trim();
  const studentId = document.getElementById('regStudentId').value.trim();
  const pass      = document.getElementById('regPass').value;
  const terms     = document.getElementById('regTerms').checked;

  if (!first) { setError('regFirst', 'regFirstErr', 'First name is required.'); valid = false; }
  else setValid('regFirst');

  if (!last) { setError('regLast', 'regLastErr', 'Last name is required.'); valid = false; }
  else setValid('regLast');

  if (!email) { setError('regEmail', 'regEmailErr', 'Email is required.'); valid = false; }
  else if (!isValidEmail(email)) { setError('regEmail', 'regEmailErr', 'Enter a valid email address.'); valid = false; }
  else setValid('regEmail');

  if (!studentId) { setError('regStudentId', 'regStudentIdErr', 'Student ID is required.'); valid = false; }
  else if (!/^[A-Z]{3}\d{3}-\d{4}\/\d{4}$/i.test(studentId)) { setError('regStudentId', 'regStudentIdErr', 'Format: SCM211-XXXX/YYYY (e.g. SCM211-0320/2024)'); valid = false; }
  else setValid('regStudentId');

  if (!pass) { setError('regPass', 'regPassErr', 'Password is required.'); valid = false; }
  else if (pass.length < 8) { setError('regPass', 'regPassErr', 'Password must be at least 8 characters.'); valid = false; }
  else setValid('regPass');

  if (!terms) { document.getElementById('regTermsErr').textContent = 'You must agree to the terms.'; valid = false; }

  if (!valid) return;

  setLoading('registerBtn', true);
  document.getElementById('registerServerMsg').style.display = 'none';

  try {
    const res = await fetch('php/register.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ first_name: first, last_name: last, email, student_id: studentId, password: pass })
    });
    const data = await res.json();

    if (data.success) {
      showServerMsg('registerServerMsg', 'Account created! Redirecting...', false);
      setTimeout(() => { window.location.href = data.redirect; }, 800);
    } else {
      showServerMsg('registerServerMsg', data.message || 'Registration failed. Try again.', true);
      setLoading('registerBtn', false);
    }
  } catch {
    showServerMsg('registerServerMsg', 'Connection error. Please try again.', true);
    setLoading('registerBtn', false);
  }
});