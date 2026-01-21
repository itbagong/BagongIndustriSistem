<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Dashboard') ?> - Bagong Industri Sistem</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css?v=1') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/sidebar-addon.css?v=1') ?>"> <!-- ✅ TAMBAH INI -->
    <link rel="stylesheet" href="<?= base_url('assets/css/employee.css?v=1') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/users.css?v=1') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?= $this->renderSection('css') ?>
</head>
<body>
    <!-- Sidebar -->
    <?= $this->include('layouts/sidebar') ?>
     <!-- ✅ TAMBAH INI - Sidebar Toggle Button & Overlay -->
    <button class="sidebar-toggle-btn" id="sidebarToggle" title="Toggle Sidebar">
        <span id="toggleIcon">◀</span>
    </button>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content -->
    <div class="main-wrapper">
        <!-- Top Navbar -->
        <?= $this->include('layouts/navbar') ?>

        <!-- Dashboard Content -->
        <main class="content">
            <!-- Flash Messages -->
            <?= $this->include('layouts/alerts') ?>

            <!-- Main Content Section -->
            <?= $this->renderSection('content') ?>
        </main>

        <!-- Footer -->
        <?= $this->include('layouts/footer') ?>
    </div>

    <script src="<?= base_url('assets/js/dashboard.js?v=1') ?>"></script>
    <script src="<?= base_url('assets/js/sidebar.js?v=1') ?>"></script>
    <?= $this->renderSection('js') ?>
</body>
</html>