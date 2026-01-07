<?php namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ProcessEmailQueue extends BaseCommand
{
    protected $group = 'Queue';
    protected $name  = 'queue:process';
    protected $description = 'Process email_queue in batches';

    public function run(array $params = [])
    {
        // 1. Setting Worker
        // Naikkan batch size sedikit karena kita hanya jalan sekali per cron run
        $batchSize = 50; 
        $maxAttempts = 5;
        $sleepBetweenEmails = 0.5; // Jeda antar email (detik)
        $backoffSeconds = 60;

        $db = \Config\Database::connect();
        $karyawanModel = new \App\Models\SlipGajiModel();
        $emailService = \Config\Services::email();

        CLI::write('Worker started check at ' . date('Y-m-d H:i:s'), 'green');

        // --- HAPUS: while (true) { ---
        
        try {
            $db->transStart();
            $now = date('Y-m-d H:i:s');
            
            // 2. Ambil Task (Locking sederhana via Update status dulu agar tidak diambil cron berikutnya)
            // Kita ubah urutannya: Select dulu ID-nya
            $tasksToProcess = $db->query(
                "SELECT * FROM email_queue 
                WHERE status = 'pending' 
                AND available_at <= ? 
                ORDER BY id ASC 
                LIMIT ?",
                [$now, $batchSize]
            )->getResultArray();

            if (!empty($tasksToProcess)) {
                $ids = array_column($tasksToProcess, 'id');
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                
                // Langsung update jadi processing dalam transaksi yang sama
                $db->query(
                    "UPDATE email_queue 
                    SET status = 'processing', updated_at = ? 
                    WHERE id IN ($placeholders)",
                    array_merge([$now], $ids)
                );
            }

            $db->transComplete();

            // 3. Cek apakah ada tugas?
            if (empty($tasksToProcess)) {
                CLI::write('No pending tasks. Exiting.', 'yellow');
                return; // --- GANTI: Langsung exit program ---
            }

            CLI::write('Processing ' . count($tasksToProcess) . ' tasks...', 'cyan');

            // 4. Proses Loop Task
            foreach ($tasksToProcess as $task) {
                // (Logic processTask Anda tetap sama, tidak perlu diubah)
                $this->processTask($task, $db, $karyawanModel, $emailService, $maxAttempts, $backoffSeconds);
                
                // Istirahat sebentar antar email agar SMTP tidak memblokir
                usleep((int)($sleepBetweenEmails * 1e6));
            }

            CLI::write('Batch finished. Exiting.', 'green');

        } catch (\Exception $e) {
            CLI::write('Error: ' . $e->getMessage(), 'red');
            log_message('error', 'Queue worker error: ' . $e->getMessage());
        }
        
    }

    private function processTask($task, $db, $karyawanModel, $emailService, $maxAttempts, $backoffSeconds)
    {
        $taskId = $task['id'];
        $queue = $db->table('email_queue');

        try {
            // Fetch karyawan data
            $karyawan = $karyawanModel->find($task['karyawan_id']);
            
            if (!$karyawan) {
                $queue->update($taskId, [
                    'status' => 'failed',
                    'last_error' => 'Karyawan tidak ditemukan',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                CLI::write("Failed (not found): {$task['email']}", 'red');
                return;
            }

            // Skip if already sent in slip_gaji table
            if (isset($karyawan['status_kirim']) && $karyawan['status_kirim'] === 'sent') {
                $queue->update($taskId, [
                    'status' => 'sent',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                CLI::write("Skipped (already sent): {$task['email']}", 'yellow');
                return;
            }

            // Generate PDF
            $slipDir = WRITEPATH . 'slips/';
            if (!is_dir($slipDir)) {
                mkdir($slipDir, 0755, true);
            }
            
            $filename = $slipDir . 'slip_' . $karyawan['id'] . '_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $karyawan['bulan']) . '.pdf';

            if (!file_exists($filename)) {
                $html = view('Payroll/slip_gaji/slip_pdf', ['karyawan' => $karyawan]);
                $options = new \Dompdf\Options();
                $options->set('isRemoteEnabled', true);
                $options->set('defaultFont', 'DejaVu Sans');
                $dompdf = new \Dompdf\Dompdf($options);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                file_put_contents($filename, $dompdf->output());
                unset($dompdf);
                gc_collect_cycles();
            }

            // Send email
            $emailService->clear(true);
            $emailService->setMailType('html');
            $emailService->setFrom('payroll@bagongbis.com', 'PT Bagong Dekaka Makmur');
            $emailService->setTo($task['email']);
            $emailService->setSubject($task['subject'] ?? 'Slip Gaji - ' . $karyawan['bulan']);
            
            $msg = "Yth. {$karyawan['nama']},<br><br>Terlampir slip gaji bulan {$karyawan['bulan']}.<br><br>Terima kasih.<br><br>Hormat kami,<br>Payroll PT Bagong Dekaka Makmur";
            $emailService->setMessage($msg);
            $emailService->attach($filename);

            if ($emailService->send()) {
                // Update both tables
                $karyawanModel->update($task['karyawan_id'], [
                    'status_kirim' => 'sent',
                    'tanggal_kirim' => date('Y-m-d H:i:s')
                ]);
                
                $queue->update($taskId, [
                    'status' => 'sent',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                CLI::write("Sent: {$task['email']}", 'green');
            } else {
                $this->handleFailure($task, $queue, $emailService, $maxAttempts, $backoffSeconds);
            }

        } catch (\Exception $e) {
            $this->handleException($task, $queue, $e, $maxAttempts, $backoffSeconds);
        }
    }

    private function handleFailure($task, $queue, $emailService, $maxAttempts, $backoffSeconds)
    {
        $debug = $emailService->printDebugger(['headers']);
        $attempts = $task['attempts'] + 1;
        $nextAvailable = date('Y-m-d H:i:s', time() + ($backoffSeconds * $attempts));

        if ($attempts >= $maxAttempts) {
            $queue->update($task['id'], [
                'status' => 'failed',
                'attempts' => $attempts,
                'last_error' => substr($debug, 0, 1000),
                'available_at' => $nextAvailable,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            CLI::write("Failed (max attempts): {$task['email']}", 'red');
        } else {
            $queue->update($task['id'], [
                'status' => 'pending',
                'attempts' => $attempts,
                'last_error' => substr($debug, 0, 1000),
                'available_at' => $nextAvailable,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            CLI::write("Failed, will retry ({$attempts}/{$maxAttempts}): {$task['email']}", 'yellow');
        }
    }

    private function handleException($task, $queue, $exception, $maxAttempts, $backoffSeconds)
    {
        $attempts = $task['attempts'] + 1;
        $nextAvailable = date('Y-m-d H:i:s', time() + ($backoffSeconds * $attempts));

        $status = ($attempts >= $maxAttempts) ? 'failed' : 'pending';
        
        $queue->update($task['id'], [
            'status' => $status,
            'attempts' => $attempts,
            'last_error' => substr($exception->getMessage(), 0, 1000),
            'available_at' => $nextAvailable,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        CLI::write("Exception for {$task['email']}: " . $exception->getMessage(), 'red');
        log_message('error', 'Queue processing error: ' . $exception->getMessage());
    }
}