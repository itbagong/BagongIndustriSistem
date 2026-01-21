/**
 * Sidebar Toggle & Dropdown Management
 * ERP Pergudangan System
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ========================================
    // 1. TOGGLE SIDEBAR COLLAPSE (Desktop)
    // ========================================
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const toggleIcon = document.getElementById('toggleIcon');
    const body = document.body;

    if (!sidebar || !toggleBtn) {
        console.warn('Sidebar elements not found');
        return;
    }

    // Load saved state from localStorage
    const savedState = localStorage.getItem('sidebarCollapsed');
    if (savedState === 'true') {
        sidebar.classList.add('collapsed');
        body.classList.add('sidebar-collapsed');
        if (toggleIcon) toggleIcon.textContent = '▶';
    }

    // Toggle button click
    toggleBtn.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        body.classList.toggle('sidebar-collapsed');
        
        // Update icon
        if (sidebar.classList.contains('collapsed')) {
            if (toggleIcon) toggleIcon.textContent = '▶';
            localStorage.setItem('sidebarCollapsed', 'true');
        } else {
            if (toggleIcon) toggleIcon.textContent = '◀';
            localStorage.setItem('sidebarCollapsed', 'false');
        }
    });

    // ========================================
    // 2. DROPDOWN MENU TOGGLE
    // ========================================
    window.toggleMenu = function(element) {
        const parent = element.closest('.menu-group');
        const submenu = parent.querySelector('.submenu');
        
        // Close other open menus (accordion style)
        document.querySelectorAll('.menu-group').forEach(group => {
            if (group !== parent && group.classList.contains('active')) {
                group.classList.remove('active');
                const otherSubmenu = group.querySelector('.submenu');
                if (otherSubmenu) {
                    otherSubmenu.style.display = 'none';
                }
            }
        });
        
        // Toggle current menu
        parent.classList.toggle('active');
        
        if (submenu) {
            if (submenu.style.display === 'block') {
                submenu.style.display = 'none';
            } else {
                submenu.style.display = 'block';
            }
        }
    };

    // ========================================
    // 3. MOBILE SIDEBAR TOGGLE
    // ========================================
    const overlay = document.getElementById('sidebarOverlay');
    
    // For mobile menu toggle button (hamburger)
    const mobileToggle = document.querySelector('.menu-toggle');
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            body.classList.toggle('sidebar-active');
        });
    }

    // Close sidebar when clicking overlay
    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            body.classList.remove('sidebar-active');
        });
    }

    // ========================================
    // 4. AUTO-EXPAND ACTIVE MENU
    // ========================================
    const activeItem = document.querySelector('.submenu .menu-item.active');
    if (activeItem) {
        const parentGroup = activeItem.closest('.menu-group');
        if (parentGroup) {
            parentGroup.classList.add('active');
            const submenu = parentGroup.querySelector('.submenu');
            if (submenu) {
                submenu.style.display = 'block';
            }
        }
    }

    // ========================================
    // 5. ADD DATA-TITLE FOR TOOLTIP
    // ========================================
    document.querySelectorAll('.menu-item').forEach(item => {
        const textEl = item.querySelector('.text');
        if (textEl) {
            item.setAttribute('data-title', textEl.textContent.trim());
        }
    });

    // ========================================
    // 6. PREVENT DROPDOWN CLOSE ON CHILD CLICK
    // ========================================
    document.querySelectorAll('.submenu .menu-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });

    // ========================================
    // 7. KEYBOARD NAVIGATION
    // ========================================
    document.addEventListener('keydown', function(e) {
        // ESC to close sidebar on mobile
        if (e.key === 'Escape' && window.innerWidth <= 768) {
            sidebar.classList.remove('active');
            body.classList.remove('sidebar-active');
        }
        
        // Ctrl + B to toggle sidebar
        if (e.ctrlKey && e.key === 'b') {
            e.preventDefault();
            toggleBtn.click();
        }
    });

    // ========================================
    // 8. WINDOW RESIZE HANDLER
    // ========================================
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
                body.classList.remove('sidebar-active');
            }
        }, 250);
    });

    // ========================================
    // 9. CLOSE MOBILE SIDEBAR AFTER CLICKING
    // ========================================
    document.querySelectorAll('.menu-item[href]').forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                setTimeout(() => {
                    sidebar.classList.remove('active');
                    body.classList.remove('sidebar-active');
                }, 300);
            }
        });
    });

    console.log('✅ Sidebar system initialized');
});