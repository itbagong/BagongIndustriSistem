<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ERP Pergudangan</title>
    
    <!-- External CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/login.css?=v2') ?>">
    
    <!-- Optional: Add favicon -->
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/favicon.png') ?>">
</head>
<body>
    <div class="login-container">
        <!-- Left Panel - Info Section -->
        <div class="login-left">
            <h1>Bagong Industri Sistem</h1>
            <p>Kelola operasional perusahaan Anda dengan mudah dan efisien.</p>
            <ul class="feature-list">
                <li>Manajemen Work Order Real-time</li>
                <li>Tracking Inventori Otomatis</li>
                <li>Laporan & Analitik Lengkap</li>
                <li>Multi-warehouse Support</li>
            </ul>
        </div>

        <!-- Right Panel - Login Form -->
        <div class="login-right">
            <div class="logo">
                    <img src="<?= base_url('assets/img/logo.png') ?>" alt="Logo Perusahaan">
                </div>
            <div class="login-header">
                
                <h2>Selamat Datang</h2>
                <p>Silakan login untuk melanjutkan</p>
            </div>

            <!-- Error Message -->
            <?php if (session()->getFlashdata('error')): ?>
            <div class="alert">
                ⚠️ <?= esc(session()->getFlashdata('error')) ?>
            </div>
            <?php endif ?>

            <!-- Success Message (Optional) -->
            <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                ✓ <?= esc(session()->getFlashdata('success')) ?>
            </div>
            <?php endif ?>

            <!-- Login Form -->
            <form method="post" action="<?= base_url('login') ?>">
                <?= csrf_field() ?>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-wrapper">
                        <span class="input-icon">📧</span>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="nama@perusahaan.com"
                            value="<?= old('email') ?>"
                            required
                            autocomplete="email"
                            autofocus
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Masukkan password"
                            required
                            autocomplete="current-password"
                        >
                        <span class="password-toggle" onclick="togglePassword()">👁️</span>
                    </div>
                </div>

                <div class="remember-forgot">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" value="1">
                        <span class="checkmark"></span>
                        <span>Ingat saya</span>
                    </label>
                    <a href="<?= base_url('forgot-password') ?>" class="forgot-password">
                        Lupa password?
                    </a>
                </div>

                <button type="submit" class="btn-login">
                    Login
                </button>
            </form>
        </div>
    </div>

    <!-- External JavaScript -->
    <script src="<?= base_url('assets/js/login.js') ?>"></script>
</body>
</html>