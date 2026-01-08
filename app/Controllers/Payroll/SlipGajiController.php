<?php

namespace App\Controllers\Payroll;

use App\Models\SlipGajiModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Dompdf\Dompdf;
use Dompdf\Options;
use CodeIgniter\Controller;

class SlipGajiController extends Controller
{
    protected $karyawanModel;
    protected $email;
    protected $emailConfig;

    public function __construct()
    {
        $this->karyawanModel = new SlipGajiModel();
        $this->email = \Config\Services::email();
        $this->emailConfig = config('Email');
    }

    public function index()
    {
        $data['karyawan'] = $this->karyawanModel->findAll();
        return view('payroll/index', $data); // ← fix case sensitivity
    }

    public function upload()
    {
        $file = $this->request->getFile('file_excel');

        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'File tidak valid');
        }

        $extension = $file->getClientExtension();
        if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
            return redirect()->back()->with('error', 'Format file harus Excel atau CSV');
        }

        try {
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads', $newName);
            $filePath = WRITEPATH . 'uploads/' . $newName;

            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            array_shift($rows);

            $inserted = 0;
            foreach ($rows as $row) {
                $data = [
                    'tanggal_slip' => $row[1] ?? date('Y-m-d'),
                    'nik' => $row[2] ?? '',
                    'nama' => $row[3] ?? '',
                    'jabatan' => $row[4] ?? '',
                    'status' => $row[5] ?? '',
                    'bulan' => $row[6] ?? '',
                    'site' => $row[7] ?? '',
                    'umk' => $row[8] ?? 0,
                    'insentif_lain' => $row[9] ?? 0,
                    'insentif_pulsa' => $row[10] ?? 0,
                    'kompensasi_cuti' => $row[11] ?? 0,
                    'insentif_lembur' => $row[12] ?? 0,
                    'insentif_makan' => $row[13] ?? 0,
                    'uang_tunggu' => $row[14] ?? 0,
                    'gaji_prorate' => $row[15] ?? 0,
                    'total_pendapatan' => $row[16] ?? 0,
                    'bpjs_kes' => $row[17] ?? 0,
                    'bpjs_tk' => $row[18] ?? 0,
                    'pot_pph21' => $row[19] ?? 0,
                    'lainnya' => $row[20] ?? 0,
                    'total_pot' => $row[21] ?? 0,
                    'gaji_bersih' => $row[22] ?? 0,
                    'email' => $row[23] ?? ''
                ];

                if (!empty($data['nik']) && !empty($data['nama'])) {
                    $this->karyawanModel->insert($data);

                    // ambil ID terakhir yang diinsert
                    $karyawanId = $this->karyawanModel->getInsertID();

                    // Generate nomer slip gaji
                    $nomerSlip = "09.8.$karyawanId/HCGS-BDM/HO/SG/" . $data['bulan'] . "/" . date('Y');

                    $this->karyawanModel->update($karyawanId, ['nomor_slip' => $nomerSlip]);

                    $inserted++;
                }
            }

            unlink($filePath);
            return redirect()->to('/slip-gaji')->with('success', "Berhasil upload $inserted data karyawan");

        } catch (\Exception $e) {
            log_message('error', 'Upload Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error saat proses upload: ' . $e->getMessage());
        }
    }

    public function preview($id)
    {
        $data['karyawan'] = $this->karyawanModel->find($id);
        if (!$data['karyawan']) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }
        return view('payroll/slip_gaji/preview', $data); // ← fix case
    }

    protected function generatePdfForEmployee(array $karyawan): string
    {
        $html = view('payroll/slip_gaji/slip_pdf', ['karyawan' => $karyawan]); // ← fix case

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();

        unset($dompdf);
        gc_collect_cycles();

        $filename = 'SlipGaji_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $karyawan['nama']) . '_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $karyawan['bulan']) . '_' . uniqid() . '.pdf';
        $filepath = WRITEPATH . 'uploads/' . $filename;
        file_put_contents($filepath, $output);

        return $filepath;
    }

    public function generatePDF($id)
    {
        $karyawan = $this->karyawanModel->find($id);
        if (!$karyawan) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }

        try {
            $html = view('payroll/slip_gaji/slip_pdf', ['karyawan' => $karyawan]); // ← fix case
            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = 'SlipGaji_' . $karyawan['nama'] . '_' . $karyawan['bulan'] . '.pdf';
            $dompdf->stream($filename, ['Attachment' => true]);
        } catch (\Exception $e) {
            log_message('error', 'Generate PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }

    public function sendEmail($id)
    {
        $karyawan = $this->karyawanModel->find($id);
        if (!$karyawan || empty($karyawan['email'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data tidak ditemukan atau email kosong']);
        }

        $force = $this->request->getPost('force') ?? $this->request->getVar('force');
        if ($karyawan['status_kirim'] === 'sent' && !$force) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sudah terkirim sebelumnya. Gunakan opsi resend jika ingin kirim ulang.']);
        }

        $filepath = null;
        try {
            $filepath = $this->generatePdfForEmployee($karyawan);

            $fromEmail = $this->emailConfig->fromEmail ?: 'payroll@bagongbis.com';
            $fromName  = $this->emailConfig->fromName ?: 'PT Bagong Dekaka Makmur';

            $this->email->clear(true);
            $this->email->setMailType('html');
            $this->email->setFrom($fromEmail, $fromName);
            $this->email->setTo($karyawan['email']);
            $this->email->setSubject('Slip Gaji - ' . $karyawan['bulan']);

            $message = "
            Yth. Bapak/Ibu {$karyawan['nama']},<br><br>
            Terlampir slip gaji untuk bulan {$karyawan['bulan']}.<br><br>
            Terima kasih.<br><br>
            Hormat kami,<br>
            Payroll PT Bagong Dekaka Makmur
            ";

            $this->email->setMessage($message);
            $this->email->attach($filepath);

            if ($this->email->send()) {
                $this->karyawanModel->update($id, ['status_kirim' => 'sent', 'tanggal_kirim' => date('Y-m-d H:i:s')]);
                unlink($filepath);
                return $this->response->setJSON(['success' => true, 'message' => 'Email berhasil dikirim ke ' . $karyawan['email']]);
            } else {
                $debug = $this->email->printDebugger(['headers']);
                log_message('error', 'Email send failed for ' . $karyawan['email'] . ' debugger: ' . print_r($debug, true));
                if ($filepath && file_exists($filepath)) unlink($filepath);
                return $this->response->setJSON(['success' => false, 'message' => 'Gagal mengirim email. Cek log.']);
            }

        } catch (\Exception $e) {
            if ($filepath && file_exists($filepath)) unlink($filepath);
            log_message('error', 'SendEmail Error for ID ' . $id . ': ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Enqueue emails ke database untuk diproses background
     * User bisa langsung logout/tutup browser setelah ini
     */
    public function enqueueAllEmails()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success'=>false,'message'=>'AJAX only']);
        }

        $db = \Config\Database::connect();
        $queue = $db->table('email_queue');

        $karyawanList = $this->karyawanModel->where('email !=', '')->findAll();
        $now = date('Y-m-d H:i:s');

        // Ambil existing queue
        $existing = $db->table('email_queue')
                    ->select('karyawan_id')
                    ->whereIn('status', ['pending','processing'])
                    ->get()->getResultArray();
        $existingIds = array_column($existing, 'karyawan_id');

        $toInsert = [];
        foreach ($karyawanList as $k) {
            // Skip jika sudah sent
            if (isset($k['status_kirim']) && $k['status_kirim'] === 'sent') continue;

            // Skip jika sudah di queue
            if (in_array($k['id'], $existingIds)) continue;

            $toInsert[] = [
                'karyawan_id' => $k['id'],
                'email' => $k['email'],
                'subject' => 'Slip Gaji - ' . $k['bulan'],
                'status' => 'pending',
                'attempts' => 0,
                'available_at' => $now,
                'created_at' => $now,
                'updated_at' => $now
            ];
        }

        if (!empty($toInsert)) {
            $queue->insertBatch($toInsert);
        }

        // ✅ TRIGGER BACKGROUND WORKER OTOMATIS
        $this->triggerBackgroundWorker();

        return $this->response->setJSON([
            'success' => true,
            'message' => count($toInsert) . ' email ditambahkan ke queue dan worker dimulai',
            'queued' => count($toInsert)
        ]);
    }

    /**
     * Trigger worker via non-blocking HTTP request
     * Worker akan jalan di background tanpa perlu user tunggu
     */
    private function triggerBackgroundWorker()
    {
        $workerUrl = base_url('slip-gaji/run-worker?key=Cvbagong.1994&background=1');
        
        // Method 1: Using fsockopen (non-blocking, fastest)
        try {
            $parts = parse_url($workerUrl);
            $host = $parts['host'];
            $port = isset($parts['port']) ? $parts['port'] : 80;
            $path = $parts['path'] . '?' . $parts['query'];
            
            $fp = @fsockopen($host, $port, $errno, $errstr, 1);
            if ($fp) {
                $out = "GET $path HTTP/1.1\r\n";
                $out .= "Host: $host\r\n";
                $out .= "Connection: Close\r\n\r\n";
                fwrite($fp, $out);
                fclose($fp);
            }
        } catch (\Exception $e) {
            log_message('error', 'Trigger worker error: ' . $e->getMessage());
        }
    }

    /**
     * Queue status - untuk monitoring progress
     */
    public function queueStatus()
    {
        $db = \Config\Database::connect();

        // 1. FIX SINKRONISASI (Kasus: Email terkirim, tapi status queue belum update)
        // Ini mengatasi masalah screenshot Anda sebelumnya (Terkirim vs Diproses)
        $sqlSync = "UPDATE email_queue eq
                   JOIN slip_gaji sg ON eq.karyawan_id = sg.id
                   SET eq.status = 'sent', eq.updated_at = NOW()
                   WHERE eq.status = 'processing' AND sg.status_kirim = 'sent'";
        $db->query($sqlSync);

        // 2. FIX NYANTOL / CRASH (Kasus: Worker mati saat processing)
        // Jika status 'processing' tapi tidak ada update > 5 menit, kembalikan ke 'pending'
        // agar worker berikutnya bisa mencoba kirim ulang.
        $sqlReset = "UPDATE email_queue 
                     SET status = 'pending', updated_at = NOW(), last_error = 'Reset otomatis (Stuck Detected)'
                     WHERE status = 'processing' 
                     AND updated_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
        $db->query($sqlReset);

        // 3. AUTO-RESTART WORKER (Penting!)
        // Cek apakah ada antrean 'pending' tapi tidak ada yang 'processing' (artinya worker mati)?
        $pendingCount = $db->table('email_queue')->where('status', 'pending')->countAllResults();
        $processingCount = $db->table('email_queue')->where('status', 'processing')->countAllResults();
        
        // Jika ada kerjaan (pending) tapi gak ada kuli (processing 0), panggil kuli baru!
        if ($pendingCount > 0 && $processingCount == 0) {
            $this->triggerBackgroundWorker();
        }

        // 4. Hitung Statistik Terbaru (Setelah perbaikan di atas)
        $total = (int) $db->table('email_queue')->countAllResults();
        $sent = (int) $db->table('email_queue')->where('status', 'sent')->countAllResults();
        $failed = (int) $db->table('email_queue')->where('status', 'failed')->countAllResults();
        $pending = (int) $db->table('email_queue')->where('status', 'pending')->countAllResults();
        $processing = (int) $db->table('email_queue')->where('status', 'processing')->countAllResults();

        return $this->response->setJSON([
            'total' => $total,
            'sent' => $sent,
            'failed' => $failed,
            'pending' => $pending,
            'processing' => $processing,
        ]);
    }

    /**
     * Background Worker - Proses email queue secara kontinyu
     * Jalan terus sampai semua queue selesai
     */
    public function runWorker()
    {
        // Security check
        $key = $this->request->getGet('key');
        if ($key !== 'Cvbagong.1994') {
            return $this->response->setStatusCode(403)
                ->setJSON(['success' => false, 'message' => 'Forbidden']);
        }

        // Disable timeout untuk background mode
        if ($this->request->getGet('background')) {
            @set_time_limit(0);
            @ignore_user_abort(true);
            
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            } else {
                ob_end_clean();
                header("Connection: close");
                header("Content-Length: 0");
                flush();
            }
        }

        $db = \Config\Database::connect();
        
        $batchSize = 10; 
        $maxAttempts = 3;
        $backoffSeconds = 300; 
        
        $totalProcessed = 0;
        $maxRuns = 100;

        for ($run = 0; $run < $maxRuns; $run++) {
            try {
                $now = date('Y-m-d H:i:s');

                // === PERBAIKAN: Panggil table() DI DALAM LOOP agar builder selalu fresh ===
                $tasks = $db->table('email_queue')
                    ->where('status', 'pending')
                    ->where('available_at <=', $now)
                    ->orderBy('id', 'ASC')
                    ->limit($batchSize)
                    ->get()
                    ->getResultArray();

                // Jika tidak ada task, berhenti
                if (empty($tasks)) {
                    break;
                }

                $ids = array_column($tasks, 'id');
                
                // Gunakan Transaksi untuk update status massal
                $db->transStart();
                
                // Panggil table() lagi untuk query update yang bersih
                $db->table('email_queue') 
                   ->whereIn('id', $ids)
                   ->update([
                       'status' => 'processing',
                       'updated_at' => $now
                   ]);
                   
                $db->transComplete();

                // --- PROSES PENGIRIMAN ---
                foreach ($tasks as $task) {
                    $this->processEmailTask($task, $maxAttempts, $backoffSeconds);
                    $totalProcessed++;
                    
                    // Delay kecil untuk safety SMTP
                    usleep(500000); 
                }

                sleep(2); // Jeda antar batch

            } catch (\Exception $e) {
                log_message('error', 'runWorker error: ' . $e->getMessage());
                sleep(5);
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'processed' => $totalProcessed,
            'message' => "Processed {$totalProcessed} emails"
        ]);
    }

    /**
     * Process single email task
     */
    private function processEmailTask($task, $maxAttempts, $backoffSeconds)
    {
        $db = \Config\Database::connect();
        $queue = $db->table('email_queue');
        $taskId = $task['id'];

        try {
            $karyawan = $this->karyawanModel->find($task['karyawan_id']);

            if (!$karyawan) {
                $queue->where('id', $taskId)->update([
                    'status' => 'failed',
                    'last_error' => 'Data karyawan tidak ditemukan',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                return false;
            }

            // Skip if already sent
            if (isset($karyawan['status_kirim']) && $karyawan['status_kirim'] === 'sent') {
                $queue->where('id', $taskId)->update([
                    'status' => 'sent',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                return true;
            }

            // Generate PDF
            $filepath = $this->generatePdfForEmployee($karyawan);

            // Send email
            $this->email->clear(true);
            $this->email->setMailType('html');
            $this->email->setFrom('payroll@bagongbis.com', 'PT Bagong Dekaka Makmur');
            $this->email->setTo($task['email']);
            $this->email->setSubject($task['subject'] ?? 'Slip Gaji');
            
            $msg = "Yth. {$karyawan['nama']},<br><br>Terlampir slip gaji bulan {$karyawan['bulan']}.<br><br>Terima kasih.<br><br>Hormat kami,<br>Payroll PT Bagong Dekaka Makmur";
            $this->email->setMessage($msg);
            $this->email->attach($filepath);

            if ($this->email->send()) {
                // === MULAI PERUBAHAN DI SINI (TRANSAKSI DATABASE) ===
                // Menggunakan transaksi agar kedua tabel terupdate bersamaan
                $db->transStart();

                // 1. Update status di tabel Karyawan
                $this->karyawanModel->update($task['karyawan_id'], [
                    'status_kirim' => 'sent',
                    'tanggal_kirim' => date('Y-m-d H:i:s')
                ]);
                
                // 2. Update status di tabel Queue
                $queue->where('id', $taskId)->update([
                    'status' => 'sent',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                // Selesaikan transaksi
                $db->transComplete();
                // === SELESAI PERUBAHAN ===

                if (file_exists($filepath)) @unlink($filepath);
                return true;
            } else {
                // Failed - retry logic
                $attempts = $task['attempts'] + 1;
                $nextAvailable = date('Y-m-d H:i:s', time() + ($backoffSeconds * $attempts));
                
                // Ambil pesan error debugger
                $debugMsg = $this->email->printDebugger(['headers']); 

                if ($attempts >= $maxAttempts) {
                    $queue->update($taskId, [
                        'status' => 'failed',
                        'attempts' => $attempts,
                        'last_error' => substr($debugMsg, 0, 1000), // Simpan error log email
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                } else {
                    $queue->update($taskId, [
                        'status' => 'pending',
                        'attempts' => $attempts,
                        'available_at' => $nextAvailable,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }

                if (file_exists($filepath)) @unlink($filepath);
                return false;
            }

        } catch (\Exception $e) {
            $attempts = $task['attempts'] + 1;
            $nextAvailable = date('Y-m-d H:i:s', time() + ($backoffSeconds * $attempts));

            $queue->update($taskId, [
                'status' => ($attempts >= $maxAttempts) ? 'failed' : 'pending',
                'attempts' => $attempts,
                'last_error' => substr($e->getMessage(), 0, 1000),
                'available_at' => $nextAvailable,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            log_message('error', 'processEmailTask error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $this->karyawanModel->delete($id);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => 'Data berhasil dihapus', 'id' => $id]);
            }

            return redirect()->to('/slip-gaji')->with('success', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            log_message('error', 'Delete error id ' . $id . ': ' . $e->getMessage());

            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(500)
                                     ->setJSON(['success' => false, 'message' => 'Gagal hapus: ' . $e->getMessage()]);
            }

            return redirect()->back()->with('error', 'Gagal hapus: ' . $e->getMessage());
        }
    }
}