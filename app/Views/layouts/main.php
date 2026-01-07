<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Dashboard') ?> - ERP Pergudangan</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css?v=1') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/employee.css?v=1') ?>">
    <?= $this->renderSection('css') ?>
</head>
<body>
    <!-- Sidebar -->
    <?= $this->include('layouts/sidebar') ?>

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
    <script src="<?= base_url('assets/js/employee.js?v=1') ?>"></script>
    <?= $this->renderSection('js') ?>
</body>
</html>