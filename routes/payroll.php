<?php
// Worker dipanggil hanya via cron + pakai secret key
$routes->get('slip-gaji/run-worker', 'Payroll\SlipGajiController::runWorker');
$routes->group('slip-gaji', ['filter' => 'auth'], function($routes) {
    // Tampilan & upload
    $routes->get('/', 'Payroll\SlipGajiController::index');                 // list + form upload
    $routes->post('upload', 'Payroll\SlipGajiController::upload');          // upload excel/csv

    // Preview / generate single PDF
    $routes->get('preview/(:num)', 'Payroll\SlipGajiController::preview/$1');
    $routes->get('generate-pdf/(:num)', 'Payroll\SlipGajiController::generatePDF/$1');

    // Kirim email per karyawan (dipanggil via AJAX POST)
    $routes->post('send-email/(:num)', 'Payroll\SlipGajiController::sendEmail/$1');

    // Kirim email batch — GANTI ke POST agar tidak bisa ter-trigger oleh GET
    // (pertimbangkan tambahan filter seperti 'role:hr' jika ada)
    $routes->post('send-all-emails', 'Payroll\SlipGajiController::sendAllEmails');

    // Enqueue email batch untuk diproses background
    $routes->post('enqueue-all', 'Payroll\SlipGajiController::enqueueAllEmails');
    $routes->get('queue-status', 'Payroll\SlipGajiController::queueStatus');

    // Hapus data — gunakan DELETE atau POST dengan _method override
    $routes->delete('delete/(:num)', 'Payroll\SlipGajiController::delete/$1');
    // Jika form HTML tidak pakai method override, gunakan:
    // $routes->post('delete/(:num)', 'SlipGajiController::delete/$1');
    

});
