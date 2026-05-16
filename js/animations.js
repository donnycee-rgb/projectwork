window.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-anim]').forEach(el => {
    el.classList.add('visible');
  });
});
const phrases = [
  'Your campus. Your schedule. Your future.',
  'Register for courses in seconds.',
  'Real-time slots. Zero paperwork.',
  'Built for students, loved by admins.',
];
let pi = 0, ci = 0, del = false, pause = 0;
const typedEl = document.getElementById('typewriter');
function typeLoop() {
  if (!typedEl) return;
  const cur = phrases[pi];
  if (pause > 0) { pause--; setTimeout(typeLoop, 38); return; }
  if (!del) {
    typedEl.textContent = cur.slice(0, ci + 1);
    ci++;
    if (ci === cur.length) { del = true; pause = 52; }
    setTimeout(typeLoop, 50);
  } else {
    typedEl.textContent = cur.slice(0, ci - 1);
    ci--;
    if (ci === 0) { del = false; pi = (pi + 1) % phrases.length; pause = 6; }
    setTimeout(typeLoop, 26);
  }
}
typeLoop();
const ticks = [
  '234 students enrolled this week',
  '48 courses now available',
  'New: Data Structures — CS Dept',
  'Registration closes this Friday',
  'Environmental Biology — 7 slots left',
];
let ti = 0;
const tickEl = document.getElementById('tickerText');
function rotateTick() {
  if (!tickEl) return;
  tickEl.style.opacity = '0';
  tickEl.style.transform = 'translateY(8px)';
  setTimeout(() => {
    ti = (ti + 1) % ticks.length;
    tickEl.textContent = ticks[ti];
    tickEl.style.opacity = '1';
    tickEl.style.transform = 'translateY(0)';
  }, 300);
}
setInterval(rotateTick, 3800);
const tTrack = document.getElementById('tcarTrack');
const tDotsWrap = document.getElementById('tDots');
const tPrev = document.getElementById('tPrev');
const tNext = document.getElementById('tNext');
if (tTrack) {
  const slides = tTrack.querySelectorAll('.tslide');
  const total = slides.length;
  let cur = 0, autoT;
  function buildTDots() {
    tDotsWrap.innerHTML = '';
    for (let i = 0; i < total; i++) {
      const d = document.createElement('button');
      d.className = 'tdot' + (i === 0 ? ' active' : '');
      d.setAttribute('aria-label', 'Slide ' + (i + 1));
      d.addEventListener('click', () => { goT(i); resetT(); });
      tDotsWrap.appendChild(d);
    }
  }
  function updateTDots() {
    tDotsWrap.querySelectorAll('.tdot').forEach((d, i) => {
      d.classList.toggle('active', i === cur);
    });
  }
  function goT(idx) {
    cur = ((idx % total) + total) % total;
    tTrack.style.transform = `translateX(-${cur * 100}%)`;
    updateTDots();
  }
  function startT() { autoT = setInterval(() => goT(cur + 1), 5000); }
  function resetT() { clearInterval(autoT); startT(); }
  buildTDots();
  startT();
  tPrev.addEventListener('click', () => { goT(cur - 1); resetT(); });
  tNext.addEventListener('click', () => { goT(cur + 1); resetT(); });
}
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
const obs = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (!entry.isIntersecting) return;
    const el = entry.target;
    el.classList.add('revealed');
    const c = el.getAttribute('data-count');
    if (c !== null) countUp(el, parseInt(c, 10), 1800);
    obs.unobserve(el);
  });
}, { threshold: 0.15 });
document.querySelectorAll('[data-scroll-reveal],[data-count]').forEach(el => obs.observe(el));
const wObs = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (!entry.isIntersecting) return;
    const el = entry.target;
    const c = el.getAttribute('data-count');
    if (c) countUp(el, parseInt(c, 10), 1600);
    wObs.unobserve(el);
  });
}, { threshold: 0.2 });
document.querySelectorAll('.wcount').forEach(el => wObs.observe(el));