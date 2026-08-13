function toggleMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.getElementById('menuToggle');

    if (!sidebar || !menuToggle) {
        return;
    }

    const isOpen = sidebar.classList.toggle('mobile-open');

    menuToggle.setAttribute(
        'aria-expanded',
        isOpen ? 'true' : 'false'
    );

    menuToggle.setAttribute(
        'aria-label',
        isOpen
            ? 'Close navigation menu'
            : 'Open navigation menu'
    );
}