<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Dashboard') ?> - Bagong Industri Sistem</title>

    <!-- CUSTOM CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css?v=1') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/sidebar-addon.css?v=1') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/employee.css?v=1') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/users.css?v=1') ?>">

    <!-- BOOTSTRAP 4 CSS (WAJIB) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">



    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <?= $this->renderSection('css') ?>
</head>
<body>

    <!-- Sidebar -->
    <?= $this->include('layouts/sidebar') ?>

    <!-- Sidebar Toggle -->
    <button class="sidebar-toggle-btn" id="sidebarToggle" title="Toggle Sidebar">
        <span id="toggleIcon">◀</span>
    </button>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content -->
    <div class="main-wrapper">

        <!-- Navbar -->
        <?= $this->include('layouts/navbar') ?>

        <main class="content">
            <?= $this->include('layouts/alerts') ?>
            <?= $this->renderSection('content') ?>
        </main>

        <?= $this->include('layouts/footer') ?>
    </div>

    <!-- ================= JS WAJIB URUTAN BENAR ================= -->

    <!-- jQuery (WAJIB PERTAMA) -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <!-- Popper -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>

    <!-- Bootstrap 4 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

    <!-- CUSTOM JS -->
    <script src="<?= base_url('assets/js/dashboard.js?v=1') ?>"></script>
    <script src="<?= base_url('assets/js/sidebar.js?v=1') ?>"></script>

    <?= $this->renderSection('js') ?>
</body>
</html>
