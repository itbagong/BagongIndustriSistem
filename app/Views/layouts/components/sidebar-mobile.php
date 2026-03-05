<!-- app/Views/layouts/components/sidebar-mobile.php -->
<!-- Khusus mobile saja — desktop toggle sudah diurus sidebar-toggle-btn -->

<style>
/* Overlay tidak pernah muncul di desktop */
.sidebar-overlay {
    display: none !important;
    pointer-events: none !important;
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
    .sidebar-overlay.visible {
        display: block !important;
        pointer-events: auto !important;
        position: fixed !important;
        top: 62px !important;
        left: 0 !important;
        width: 100% !important;
        height: calc(100vh - 62px) !important;
        background: rgba(0,0,0,0.45) !important;
        z-index: 4000 !important;
        opacity: 1 !important;
    }
}
</style>

<script>
(function () {
    'use strict';

    // Hanya jalankan di mobile
    if (window.innerWidth > 768) return;

    var sidebar = document.getElementById('sidebar');
    var toggle  = document.getElementById('menuToggle');
    if (!sidebar || !toggle) return;

    var overlay = document.querySelector('.sidebar-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    }

    function openSidebar() {
        sidebar.classList.add('active');
        overlay.classList.add('visible');
        document.body.style.overflow = 'hidden';
        toggle.setAttribute('aria-expanded', 'true');
    }

    function closeSidebar() {
        sidebar.classList.remove('active');
        overlay.classList.remove('visible');
        document.body.style.overflow = '';
        toggle.setAttribute('aria-expanded', 'false');
    }

    toggle.addEventListener('click', function (e) {
        e.stopPropagation();
        sidebar.classList.contains('active') ? closeSidebar() : openSidebar();
    });

    overlay.addEventListener('click', closeSidebar);

    document.addEventListener('click', function (e) {
        if (
            sidebar.classList.contains('active') &&
            !sidebar.contains(e.target) &&
            !toggle.contains(e.target)
        ) {
            closeSidebar();
        }
    });

    // Swipe kiri → tutup
    var tx = 0;
    sidebar.addEventListener('touchstart', function (e) {
        tx = e.touches[0].clientX;
    }, { passive: true });
    sidebar.addEventListener('touchend', function (e) {
        if (tx - e.changedTouches[0].clientX > 60) closeSidebar();
    }, { passive: true });

    // Resize ke desktop → bersihkan state
    window.addEventListener('resize', function () {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('active');
            overlay.classList.remove('visible');
            document.body.style.overflow = '';
        }
    });

})();
</script>