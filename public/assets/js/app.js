document.addEventListener('DOMContentLoaded', function () {

    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');

    if (!menuToggle || !sidebar) {
        console.error('Mobile menu elements not found.');
        return;
    }

    // Open / close sidebar
    menuToggle.addEventListener('click', function (event) {

        event.preventDefault();
        event.stopPropagation();

        sidebar.classList.toggle('mobile-open');

        const isOpen = sidebar.classList.contains('mobile-open');

        menuToggle.setAttribute(
            'aria-expanded',
            isOpen ? 'true' : 'false'
        );

        menuToggle.setAttribute(
            'aria-label',
            isOpen ? 'Close navigation menu' : 'Open navigation menu'
        );
    });


    // Close sidebar after selecting a menu item
    const sidebarLinks = sidebar.querySelectorAll('a');

    sidebarLinks.forEach(function (link) {

        link.addEventListener('click', function () {

            if (window.innerWidth <= 768) {

                sidebar.classList.remove('mobile-open');

                menuToggle.setAttribute(
                    'aria-expanded',
                    'false'
                );

                menuToggle.setAttribute(
                    'aria-label',
                    'Open navigation menu'
                );
            }
        });
    });


    // Close sidebar when clicking outside
    document.addEventListener('click', function (event) {

        if (window.innerWidth > 768) {
            return;
        }

        if (
            sidebar.classList.contains('mobile-open') &&
            !sidebar.contains(event.target) &&
            !menuToggle.contains(event.target)
        ) {

            sidebar.classList.remove('mobile-open');

            menuToggle.setAttribute(
                'aria-expanded',
                'false'
            );

            menuToggle.setAttribute(
                'aria-label',
                'Open navigation menu'
            );
        }
    });


    // Reset when changing to desktop
    window.addEventListener('resize', function () {

        if (window.innerWidth > 768) {

            sidebar.classList.remove('mobile-open');

            menuToggle.setAttribute(
                'aria-expanded',
                'false'
            );

            menuToggle.setAttribute(
                'aria-label',
                'Open navigation menu'
            );
        }
    });

});