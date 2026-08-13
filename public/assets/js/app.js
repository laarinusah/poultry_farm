document.addEventListener('DOMContentLoaded', function () {

    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');

    if (!menuToggle || !sidebar) {
        console.log('Mobile menu elements not found.');
        return;
    }

    menuToggle.addEventListener('click', function (event) {

        event.preventDefault();
        event.stopPropagation();

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

    });

});