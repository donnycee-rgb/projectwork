const navbar = document.getElementById('navbar');

function updateNav() {
  if (window.scrollY > 80) {
    navbar.classList.add('solid');
  } else {
    navbar.classList.remove('solid');
  }
}

updateNav();
window.addEventListener('scroll', updateNav, { passive: true });

document.querySelectorAll('[data-scroll-reveal]').forEach((el, i) => {
  el.style.transitionDelay = `${(i % 4) * 0.09}s`;
});