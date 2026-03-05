<!-- app/Views/layouts/components/sidebar-desktop.php -->
<!-- Khusus desktop — 3 state: expanded → collapsed → hidden -->

<script>
(function () {
    'use strict';

    // Hanya jalankan di desktop
    if (window.innerWidth <= 768) return;

    var sidebar = document.getElementById('sidebar');
    var toggle  = document.getElementById('menuToggle');
    if (!sidebar || !toggle) return;

    // Desktop state: 1=expanded(default), 2=collapsed, 0=hidden
    var state = 1;

    function applyState(s) {
        state = s;

        sidebar.classList.remove('collapsed', 'hidden-sidebar');
        document.body.classList.remove('sidebar-collapsed', 'sidebar-hidden');

        if (s === 2) {
            sidebar.classList.add('collapsed');
            document.body.classList.add('sidebar-collapsed');
        } else if (s === 0) {
            sidebar.classList.add('hidden-sidebar');
            document.body.classList.add('sidebar-hidden');
        }
        // s === 1: tidak perlu class, default expanded
    }

    toggle.addEventListener('click', function () {
        // Cycle: 1 → 2 → 0 → 1
        if      (state === 1) applyState(2);
        else if (state === 2) applyState(0);
        else                  applyState(1);
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth <= 768) {
            // Biarkan mobile handler yang urus
            sidebar.classList.remove('collapsed', 'hidden-sidebar');
            document.body.classList.remove('sidebar-collapsed', 'sidebar-hidden');
        }
    });

})();
</script>