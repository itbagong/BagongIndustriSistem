/**
 * sidebar.js — FINAL
 * ERP Bagong Industri Sistem
 *
 * Topbar navbar: <header class="topbar"> dengan <button id="menuToggle">
 * Sidebar:       <aside class="sidebar" id="sidebar">
 * Topbar height: 62px (--hdr dari navbar CSS)
 */
document.addEventListener('DOMContentLoaded', function () {

    const sidebar   = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('menuToggle');
    const body      = document.body;

    if (!sidebar)   { console.warn('❌ #sidebar tidak ditemukan');   return; }
    if (!toggleBtn) { console.warn('❌ #menuToggle tidak ditemukan'); return; }

    const W_FULL      = 260;
    const W_COLLAPSED = 70;
    const isMobile    = () => window.innerWidth <= 768;
    const isCollapsed = () => sidebar.classList.contains('collapsed');

    // ── mainWrapper ─────────────────────────
    const mainWrapper = document.querySelector('.main-wrapper');

    function setMainMargin() {
        if (!mainWrapper) return;
        if (isMobile()) {
            mainWrapper.style.marginLeft = '0';
        } else {
            mainWrapper.style.marginLeft = (isCollapsed() ? W_COLLAPSED : W_FULL) + 'px';
        }
    }

    // ── Overlay ─────────────────────────────
    let overlay = document.getElementById('sidebarOverlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id        = 'sidebarOverlay';
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    }

    // ── Flyout (desktop collapsed) ───────────
    const flyout = document.createElement('div');
    flyout.className    = 'sidebar-flyout';
    flyout.style.display = 'none';
    document.body.appendChild(flyout);

    let activeFlyoutGroup = null;

    function openFlyout(menuGroup) {
        const submenu = menuGroup.querySelector('.submenu');
        if (!submenu) return;
        flyout.innerHTML     = submenu.innerHTML;
        const rect           = menuGroup.getBoundingClientRect();
        flyout.style.top     = rect.top + 'px';
        flyout.style.left    = W_COLLAPSED + 'px';
        flyout.style.display = 'block';
        activeFlyoutGroup    = menuGroup;
        menuGroup.classList.add('active');
    }

    function closeFlyout() {
        flyout.style.display = 'none';
        flyout.innerHTML     = '';
        if (activeFlyoutGroup) {
            activeFlyoutGroup.classList.remove('active');
            activeFlyoutGroup = null;
        }
    }

    // ── Restore desktop collapsed state ─────
    if (!isMobile() && localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
        body.classList.add('sidebar-collapsed');
    }
    setMainMargin();

    // ── HAMBURGER CLICK ──────────────────────
    toggleBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        e.preventDefault();

        if (isMobile()) {
            const isOpen = sidebar.classList.toggle('active');
            body.classList.toggle('sidebar-active', isOpen);
            showOverlay(isOpen);
            toggleBtn.classList.toggle('is-open', isOpen);
            toggleBtn.setAttribute('aria-expanded', String(isOpen));
        } else {
            const collapsed = sidebar.classList.toggle('collapsed');
            body.classList.toggle('sidebar-collapsed', collapsed);
            localStorage.setItem('sidebarCollapsed', String(collapsed));
            if (collapsed) closeFlyout();
            else closeAllSubmenus();
            setMainMargin();
        }
    });

    // ── Overlay ──────────────────────────────
    function showOverlay(show) {
        if (show) {
            overlay.style.display = 'block';
            requestAnimationFrame(() => { overlay.style.opacity = '1'; });
        } else {
            overlay.style.opacity = '0';
            setTimeout(() => { overlay.style.display = 'none'; }, 300);
        }
    }

    overlay.addEventListener('click', closeMobileSidebar);

    function closeMobileSidebar() {
        sidebar.classList.remove('active');
        body.classList.remove('sidebar-active');
        showOverlay(false);
        toggleBtn.classList.remove('is-open');
        toggleBtn.setAttribute('aria-expanded', 'false');
    }

    // ── Submenu toggle (override fungsi di sidebar.php) ──
    window.toggleMenu = function (element) {
        const parent = element.closest('.menu-group');
        if (!parent) return;

        // Desktop collapsed → flyout
        if (!isMobile() && isCollapsed()) {
            activeFlyoutGroup === parent ? closeFlyout() : (closeFlyout(), openFlyout(parent));
            return;
        }

        // Normal accordion
        const submenu = parent.querySelector('.submenu');
        if (!submenu) return;
        const wasOpen = parent.classList.contains('active');
        closeAllSubmenus();
        if (!wasOpen) {
            parent.classList.add('active');
            submenu.style.display = 'block';
        }
    };

    function closeAllSubmenus() {
        document.querySelectorAll('.menu-group.active').forEach(g => {
            g.classList.remove('active');
            const s = g.querySelector('.submenu');
            if (s) s.style.display = 'none';
        });
    }

    // ── Tutup flyout saat klik luar ──────────
    document.addEventListener('click', e => {
        if (!sidebar.contains(e.target) && !flyout.contains(e.target)) {
            closeFlyout();
        }
    });
    sidebar.addEventListener('click', e => e.stopPropagation());
    flyout.addEventListener('click',  e => e.stopPropagation());

    // ── Auto expand menu aktif ───────────────
    const activeChild = document.querySelector('.submenu .menu-item.active');
    if (activeChild) {
        const pg = activeChild.closest('.menu-group');
        if (pg) {
            pg.classList.add('active');
            const s = pg.querySelector('.submenu');
            if (s) s.style.display = 'block';
        }
    }

    // ── Data-title tooltip ───────────────────
    document.querySelectorAll('.menu-item').forEach(item => {
        const t = item.querySelector('.text');
        if (t && !item.getAttribute('data-title'))
            item.setAttribute('data-title', t.textContent.trim());
    });

    // ── Tutup mobile setelah klik link ───────
    sidebar.querySelectorAll('a.menu-item').forEach(item => {
        item.addEventListener('click', () => {
            if (isMobile()) setTimeout(closeMobileSidebar, 250);
        });
    });

    // ── Keyboard ────────────────────────────
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') { closeMobileSidebar(); closeFlyout(); }
        if (e.ctrlKey && e.key === 'b') { e.preventDefault(); toggleBtn.click(); }
    });

    // ── Resize ──────────────────────────────
    let rt;
    window.addEventListener('resize', () => {
        clearTimeout(rt);
        rt = setTimeout(() => {
            if (!isMobile()) { sidebar.classList.remove('active'); showOverlay(false); }
            closeFlyout();
            setMainMargin();
        }, 200);
    });

    console.log('✅ Sidebar FINAL ready — menuToggle connected');
});