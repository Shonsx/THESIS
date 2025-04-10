import './bootstrap';

document.addEventListener('DOMContentLoaded', function () {
    const dropdownBtn = document.getElementById('dropdownBtn');
    const dropdownMenu = document.getElementById('dropdownMenu');

    dropdownBtn?.addEventListener('click', function (event) {
        event.stopPropagation();
        dropdownMenu.classList.toggle('hidden');
    });

    document.addEventListener('click', function (event) {
        if (!dropdownBtn.contains(event.target) && !dropdownMenu.contains(event.target)) {
            dropdownMenu.classList.add('hidden');
        }
    });
});
document.getElementById("menu-btn").addEventListener("click", function() {
    document.getElementById("mobile-menu").classList.toggle("hidden");
});

document.getElementById('dropdownBtn')?.addEventListener('click', function() {
    document.getElementById('dropdownMenu').classList.toggle('hidden');
});