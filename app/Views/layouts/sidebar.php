<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <img src="<?= base_url('assets/img/logo.png') ?>" alt="Logo">
        </div>
        <h3>ERP Pergudangan</h3>
    </div>

    <nav class="sidebar-menu">
        <a href="<?= base_url('dashboard') ?>" class="menu-item <?= (uri_string() == 'dashboard') ? 'active' : '' ?>">
            <span class="icon">🏠</span>
            <span class="text">Dashboard</span>
        </a>
        <a href="<?= base_url('slip-gaji') ?>" class="menu-item <?= (strpos(uri_string(), 'slip-gaji') !== false) ? 'active' : '' ?>">
            <span class="icon">📋</span>
            <span class="text">Slip Gaji</span>
        </a>
        <a href="<?= base_url('inventory') ?>" class="menu-item <?= (strpos(uri_string(), 'inventory') !== false) ? 'active' : '' ?>">
            <span class="icon">📦</span>
            <span class="text">Inventory</span>
        </a>
        <a href="<?= base_url('employees') ?>" class="menu-item <?= (strpos(uri_string(), 'employees') !== false) ? 'active' : '' ?>">
            <span class="icon">👥</span>
            <span class="text">Karyawan</span>
        </a>
        <a href="<?= base_url('reports') ?>" class="menu-item <?= (strpos(uri_string(), 'reports') !== false) ? 'active' : '' ?>">
            <span class="icon">📊</span>
            <span class="text">Reports</span>
        </a>
        <?php if (session()->get('user_role') === 'admin'): ?>
        <a href="<?= base_url('settings') ?>" class="menu-item <?= (strpos(uri_string(), 'settings') !== false) ? 'active' : '' ?>">
            <span class="icon">⚙️</span>
            <span class="text">Settings</span>
        </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="<?= base_url('logout') ?>" class="menu-item logout">
            <span class="icon">🚪</span>
            <span class="text">Logout</span>
        </a>
    </div>
</aside>