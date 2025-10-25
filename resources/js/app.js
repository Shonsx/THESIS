import './bootstrap';

function initLayout() {
  // Mobile menu toggle
  const menuBtn = document.getElementById('menu-btn');
  const mobileMenu = document.getElementById('mobile-menu');
  if (menuBtn && mobileMenu) {
    menuBtn.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
    });
  }

  // Dropdowns (desktop and mobile)
  const dropdownBtnDesktop = document.getElementById('dropdownBtnDesktop');
  const dropdownMenuDesktop = document.getElementById('dropdownMenuDesktop');
  const dropdownBtnMobile = document.getElementById('dropdownBtnMobile');
  const dropdownMenuMobile = document.getElementById('dropdownMenuMobile');

  function setupDropdown(btn, menu) {
    if (!btn || !menu) return;
    btn.addEventListener('click', (event) => {
      event.stopPropagation();
      menu.classList.toggle('hidden');
    });
  }

  setupDropdown(dropdownBtnDesktop, dropdownMenuDesktop);
  setupDropdown(dropdownBtnMobile, dropdownMenuMobile);

  // Close dropdowns when clicking outside (safe guards for nulls)
  document.addEventListener('click', (event) => {
    const pairs = [
      [dropdownBtnDesktop, dropdownMenuDesktop],
      [dropdownBtnMobile, dropdownMenuMobile],
    ];
    pairs.forEach(([btn, menu]) => {
      if (!btn || !menu) return;
      if (!btn.contains(event.target) && !menu.contains(event.target)) {
        menu.classList.add('hidden');
      }
    });
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initLayout);
} else {
  initLayout();
}