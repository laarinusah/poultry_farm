function setupMobileMenu() {
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');

    if (!menuToggle || !sidebar) return;

    menuToggle.addEventListener('click', function () {
        sidebar.classList.toggle('mobile-open');
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupMobileMenu);
} else {
    setupMobileMenu();
}