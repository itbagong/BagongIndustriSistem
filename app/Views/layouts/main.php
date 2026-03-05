<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Dashboard') ?> - Bagong Industri Sistem</title>

    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('logo/LogoBulat.png') ?>">

    <!-- ① VENDOR CSS — dimuat PERTAMA -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- ② CUSTOM CSS — dimuat SETELAH vendor agar bisa override -->
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css?v=1') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/sidebar-addon.css?v=1') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/employee.css?v=1') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/users.css?v=1') ?>">

    <!-- ③ PATCH — paling terakhir agar override semua di atas -->
    <link rel="stylesheet" href="<?= base_url('assets/css/sidebar-patch.css?v=3') ?>">

    <?= $this->renderSection('css') ?>
</head>
<body>

    <!-- Sidebar -->
    <?= $this->include('layouts/sidebar') ?>

    <!-- Overlay (inject otomatis oleh sidebar.js, tapi taruh di sini juga aman) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content -->
    <div class="main-wrapper" id="mainWrapper">

        <!-- Navbar / Topbar -->
        <?= $this->include('layouts/navbar') ?>

        <main class="content">
            <?= $this->include('layouts/alerts') ?>
            <?= $this->renderSection('content') ?>
        </main>

        <?= $this->include('layouts/footer') ?>
    </div>

    <!-- ===== JS — urutan wajib benar ===== -->

    <!-- ① jQuery WAJIB PERTAMA -->
     <link rel="stylesheet"
    href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- ② Popper -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>

    <!-- ③ Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

    <!-- ④ Custom JS -->
    <script src="<?= base_url('assets/js/dashboard.js?v=1') ?>"></script>
    <script src="<?= base_url('assets/js/sidebar.js?v=1') ?>"></script>

    <?= $this->renderSection('js') ?>
</body>
</html>