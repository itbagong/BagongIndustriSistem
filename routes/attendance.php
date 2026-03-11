<?php
// ─────────────────────────────────────────────────────────────
// Tambahkan ke app/Config/Routes.php
// ─────────────────────────────────────────────────────────────

// ── Karyawan (user biasa) ────────────────────────────────────
$routes->group('attendance', ['filter' => 'permission:attendance.access'], function ($routes) {
    $routes->get('/',       'Attendance\AttendanceController::index');
    $routes->post('store',  'Attendance\AttendanceController::store');
    $routes->get('history', 'Attendance\AttendanceController::history');
});

// ── Admin ────────────────────────────────────────────────────
$routes->group('attendance/admin', ['filter' => 'permission:admin.attendance'], function ($routes) {
    // Halaman dashboard
    $routes->get('/',                  'Attendance\Attendanceadmincontroller::dashboard');

    // Halaman kehadiran per tanggal
    $routes->get('present',            'Attendance\Attendanceadmincontroller::present');
    $routes->get('present/export',     'Attendance\Attendanceadmincontroller::presentExport');

    // Tabel riwayat (view)
    $routes->get('history',            'Attendance\Attendanceadmincontroller::history');

    // DataTable server-side (dipanggil via AJAX dari admin_index.php)
    $routes->post('data',              'Attendance\Attendanceadmincontroller::data');

    // Export CSV
    $routes->get('export',             'Attendance\Attendanceadmincontroller::export');

    // Hapus satu record
    $routes->post('delete/(:num)',     'Attendance\Attendanceadmincontroller::delete/$1');
});