<!-- =====================================================
     app/Views/layouts/components/sidebar-mobile.php
     ===================================================== -->

<style>
/* Overlay HANYA di mobile */
@media (min-width: 769px) {
    .sidebar-overlay {
        display: none !important;
        pointer-events: none !important;
    }
}

@media (max-width: 768px) {
    .topbar {
        position: sticky !important;
        top: 0 !important;
        z-index: 9999 !important;
    }

    #menuToggle, .menu-toggle {
        position: relative !important;
        z-index: 10000 !important;
        pointer-events: auto !important;
        cursor: pointer !important;
        min-width: 44px !important;
        min-height: 44px !important;
        touch-action: manipulation !important;
    }

    .sidebar-overlay {
        display: none !important;
        position: fixed !important;
        top: 62px !important;
        left: 0 !important;
        width: 100% !important;
        height: calc(100vh - 62px) !important;
        background: rgba(0,0,0,0.45) !important;
        z-index: 4000 !important;
        opacity: 0 !important;
        transition: opacity 0.3s ease !important;
        pointer-events: none !important;
    }

    .sidebar-overlay.visible {
        display: block !important;
        opacity: 1 !important;
        pointer-events: auto !important;
    }
}
</style>

<script>
(function () {
    'use strict';

    var sidebar = document.getElementById('sidebar');
    var toggle  = document.getElementById('menuToggle');

    if (!sidebar || !toggle) return;

    var overlay = document.querySelector('.sidebar-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    }

    overlay.classList.remove('visible');
    overlay.style.cssText = '';

    function isMobile() {
        return window.innerWidth <= 768;
    }

    function openSidebar() {
        sidebar.classList.add('active');
        if (isMobile()) {
            overlay.style.display = 'block';
            requestAnimationFrame(function () {
                overlay.classList.add('visible');
            });
            document.body.style.overflow = 'hidden';
        }
        toggle.setAttribute('aria-expanded', 'true');
    }

    function closeSidebar() {
        sidebar.classList.remove('active');
        overlay.classList.remove('visible');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
        setTimeout(function () { overlay.style.display = 'none'; }, 310);
    }

    function toggleDesktop() {
        var isCollapsed = sidebar.classList.contains('collapsed');
        if (isCollapsed) {
            sidebar.classList.remove('collapsed');
            document.body.classList.remove('sidebar-collapsed');
        } else {
            sidebar.classList.add('collapsed');
            document.body.classList.add('sidebar-collapsed');
        }
    }

    toggle.addEventListener('click', function (e) {
        e.stopPropagation();
        if (isMobile()) {
            sidebar.classList.contains('active') ? closeSidebar() : openSidebar();
        } else {
            toggleDesktop();
        }
    });

    overlay.addEventListener('click', function () {
        if (isMobile()) closeSidebar();
    });

    document.addEventListener('click', function (e) {
        if (
            isMobile() &&
            sidebar.classList.contains('active') &&
            !sidebar.contains(e.target) &&
            !toggle.contains(e.target)
        ) {
            closeSidebar();
        }
    });

    var tx = 0;
    sidebar.addEventListener('touchstart', function (e) { tx = e.touches[0].clientX; }, { passive: true });
    sidebar.addEventListener('touchend',   function (e) {
        if (isMobile() && tx - e.changedTouches[0].clientX > 60) closeSidebar();
    }, { passive: true });

    window.addEventListener('resize', function () {
        if (!isMobile()) {
            sidebar.classList.remove('active');
            overlay.classList.remove('visible');
            overlay.style.display = 'none';
            document.body.style.overflow = '';
        }
    });

})();
</script>