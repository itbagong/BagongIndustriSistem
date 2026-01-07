<header class="topbar">
    <button class="menu-toggle" id="menuToggle">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <div class="topbar-title">
        <h1><?= esc($page_title ?? 'Dashboard') ?></h1>
        <p class="breadcrumb"><?= esc($breadcrumb ?? 'Home / Dashboard') ?></p>
    </div>

    <div class="topbar-actions">
        <button class="notification-btn" title="Notifications">
            <span class="icon">🔔</span>
            <span class="badge">3</span>
        </button>

        <div class="user-menu">
            <div class="user-avatar">
                <?= strtoupper(substr(session()->get('user_name') ?? 'U', 0, 1)) ?>
            </div>
            <div class="user-info">
                <div class="user-name"><?= esc(session()->get('user_name') ?? 'User') ?></div>
                <div class="user-role"><?= esc(ucfirst(session()->get('user_role') ?? 'user')) ?></div>
            </div>
        </div>
    </div>
</header>