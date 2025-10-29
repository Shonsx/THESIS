// Basic JavaScript functionality for the application

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

    btn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      menu.classList.toggle('hidden');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
      if (!btn.contains(e.target) && !menu.contains(e.target)) {
        menu.classList.add('hidden');
      }
    });
  }

  setupDropdown(dropdownBtnDesktop, dropdownMenuDesktop);
  setupDropdown(dropdownBtnMobile, dropdownMenuMobile);

  // Close mobile menu when clicking outside
  document.addEventListener('click', (e) => {
    if (mobileMenu && !mobileMenu.contains(e.target) && !menuBtn?.contains(e.target)) {
      mobileMenu.classList.add('hidden');
    }
  });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', initLayout);

// Re-initialize on page navigation (for SPA-like behavior)
document.addEventListener('turbo:load', initLayout);

// Basic form validation
function validateForm(formId) {
  const form = document.getElementById(formId);
  if (!form) return false;

  const requiredFields = form.querySelectorAll('[required]');
  let isValid = true;

  requiredFields.forEach(field => {
    if (!field.value.trim()) {
      field.classList.add('error');
      isValid = false;
    } else {
      field.classList.remove('error');
    }
  });

  return isValid;
}

// Export functions for global use
window.initLayout = initLayout;
window.validateForm = validateForm;