<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <img src="<?= base_url('assets/img/logo.png') ?>" alt="Logo">
        </div>
        <h3>Bagong Industri Sistem</h3>
    </div>

    <nav class="sidebar-menu">
        <?php foreach ($menus as $menu): ?>

            <?php
            $currentUrl = uri_string();
            $isActive   = false;

            if (empty($menu['children'])) {
                // Menu tunggal: cek route, skip kalau kosong atau '#'
                $route = trim($menu['route'] ?? '', '/');
                if ($route !== '' && $route !== '#') {
                    $isActive = (strpos($currentUrl, $route) !== false);
                }
            } else {
                // Parent: aktif kalau salah satu anak-nya aktif
                foreach ($menu['children'] as $child) {
                    $childRoute = trim($child['route'] ?? '', '/');
                    if ($childRoute !== '' && $childRoute !== '#') {
                        if (strpos($currentUrl, $childRoute) !== false) {
                            $isActive = true;
                            break;
                        }
                    }
                }
            }
            ?>

            <?php if (empty($menu['children'])): ?>
                <!-- Menu tunggal (tanpa submenu) -->
                <a href="<?= ($menu['route'] && $menu['route'] !== '#') ? base_url($menu['route']) : '#' ?>" 
                   class="menu-item <?= $isActive ? 'active' : '' ?>">
                    <span class="icon"><i class="<?= esc($menu['icon']) ?>"></i></span>
                    <span class="text"><?= esc($menu['name']) ?></span>
                </a>

            <?php else: ?>
                <!-- Menu parent (ada submenu) -->
                <div class="menu-group <?= $isActive ? 'active open' : '' ?>">
                    <div class="menu-item has-submenu" onclick="toggleMenu(this)">
                        <span class="icon"><i class="<?= esc($menu['icon']) ?>"></i></span>
                        <span class="text"><?= esc($menu['name']) ?></span>
                        <span class="arrow">▼</span>
                    </div>

                    <div class="submenu" style="<?= $isActive ? 'display:block' : 'display:none' ?>">
                        <?php foreach ($menu['children'] as $child): ?>
                            <?php
                            $childRoute  = trim($child['route'] ?? '', '/');
                            $childActive = ($childRoute !== '' && $childRoute !== '#')
                                ? (strpos($currentUrl, $childRoute) !== false)
                                : false;
                            ?>
                            <a href="<?= ($child['route'] && $child['route'] !== '#') ? base_url($child['route']) : '#' ?>" 
                               class="menu-item <?= $childActive ? 'active' : '' ?>">
                                <span class="icon"><i class="<?= esc($child['icon']) ?>"></i></span>
                                <span class="text"><?= esc($child['name']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php endforeach; ?>
    </nav>

<script>
function toggleMenu(element) {
    let parent  = element.closest('.menu-group');
    let submenu = parent.querySelector('.submenu');

    parent.classList.toggle('active');

    if (submenu.style.display === 'block') {
        submenu.style.display = 'none';
    } else {
        submenu.style.display = 'block';
    }
}
</script>

    <div class="sidebar-footer">
        <a href="<?= base_url('logout') ?>" class="menu-item logout">
            <span class="icon">🚪</span>
            <span class="text">Logout</span>
        </a>
    </div>
</aside>