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
            // Cek apakah menu ini aktif
            $currentUrl = uri_string();
            $isActive = false;
            
            // Logika Active State
            if (empty($menu['children'])) {
                // Jika menu tunggal, cek route-nya persis atau mengandung
                $isActive = (strpos($currentUrl, trim($menu['route'], '/')) !== false);
            } else {
                // Jika punya anak, Parent jadi aktif KALO salah satu anaknya aktif
                foreach ($menu['children'] as $child) {
                    if (strpos($currentUrl, trim($child['route'], '/')) !== false) {
                        $isActive = true;
                        break; 
                    }
                }
            }
            ?>

            <?php if (empty($menu['children'])): ?>
                <a href="<?= $menu['route'] ? base_url($menu['route']) : '#' ?>" class="menu-item <?= $isActive ? 'active' : '' ?>">
                    <span class="icon"><i class="<?= esc($menu['icon']) ?>"></i></span> <span class="text"><?= esc($menu['name']) ?></span>
                </a>

            <?php else: ?>
                <div class="menu-group <?= $isActive ? 'active open' : '' ?>"> 
                    <div class="menu-item has-submenu" onclick="toggleMenu(this)">
                        <span class="icon"><i class="<?= esc($menu['icon']) ?>"></i></span>
                        <span class="text"><?= esc($menu['name']) ?></span>
                        <span class="arrow">▼</span> 
                    </div>

                    <div class="submenu" style="<?= $isActive ? 'display:block' : 'display:none' ?>">
                        <?php foreach ($menu['children'] as $child): ?>
                            <?php 
                            $childActive = (strpos($currentUrl, trim($child['route'], '/')) !== false); 
                            ?>
                            <a href="<?= $child['route'] ? base_url($child['route']) : '#' ?>" class="menu-item <?= $childActive ? 'active' : '' ?>">
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
    // Cari parent .menu-group
    let parent = element.closest('.menu-group');
    let submenu = parent.querySelector('.submenu');
    
    // Toggle class active
    parent.classList.toggle('active');
    
    // Toggle display submenu (bisa diganti dengan slideToggle jQuery kalau pakai)
    if (submenu.style.display === "block") {
        submenu.style.display = "none";
    } else {
        submenu.style.display = "block";
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
