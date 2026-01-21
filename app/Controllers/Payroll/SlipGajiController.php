<?php

namespace App\Controllers\Payroll;

use App\Models\SlipGajiModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Controllers\BaseController;
use CodeIgniter\Controller;
use Faker\Provider\Base;

class SlipGajiController extends BaseController
{
    protected $karyawanModel;
    protected $email;
    protected $emailConfig;

    public function __construct()
    {
        $this->karyawanModel = new SlipGajiModel();
        $this->email = \Config\Services::email();
        $this->emailConfig = config('Email');
        helper(['number']);
    }

    public function index()
    {
        $this->data['karyawan'] = $this->karyawanModel->findAll();
        return view('payroll/index', $this->data);
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
            $rows = $worksheet->toArray(null, true, true, true);

            $header = array_shift($rows);
            $map = array_flip(array_map('trim', $header));

            $inserted = 0;
            foreach ($rows as $row) {
                $data = [
                    'nik'                   => $row[$map['NIK']] ?? '',
                    'nama'                  => $row[$map['Nama']] ?? '',
                    'jabatan'               => $row[$map['Jabatan']] ?? '',
                    'status'                => $row[$map['Status']] ?? '',
                    'bulan'                 => $row[$map['Bulan']] ?? '',
                    'site'                  => $row[$map['Site']] ?? '',
                    'umk'                   => normalize_number($row[$map['UMK']]) ?? 0,
                    'tunjangan_tidak_tetap' => normalize_number($row[$map['Tunjangan Tidak Tetap']]) ?? 0,
                    // 'insentif_lain'      => normalize_number($row[$map['Insentif Lain']]) ?? 0,
                    //  'insentif_lembur'   => normalize_number($row[$map['Insentif Lembur']]) ?? 0,
                    'kompenasasi'           => normalize_number($row[$map['Kompensasi']]) ?? 0,
                    // 'uang_tunggu'        => normalize_number($row[$map['Uang Tunggu']]) ?? 0,
                    'gaji_prorate'          => normalize_number($row[$map['Gaji Prorate']]) ?? 0,
                    'total_pendapatan'      => normalize_number($row[$map['Total Pendapatan']]) ?? 0,
                    'bpjs_kes'              => normalize_number($row[$map['BPJS Kes']]) ?? 0,
                    'bpjs_tk'               => normalize_number($row[$map['BPJS TK']]) ?? 0,
                    'pot_pph21'             => normalize_number($row[$map['Pot. PPh 21']]) ?? 0,
                    'lainnya'               => normalize_number($row[$map['Lainnya']]) ?? 0,
                    'total_pot'             => normalize_number($row[$map['Total Pot']]) ?? 0,
                    'gaji_bersih'           => normalize_number($row[$map['Gaji Bersih']]) ?? 0,
                    'email'                 => $row[$map['Email']] ?? '',
                ];

                if (!empty($data['nik']) && !empty($data['nama'])) {
                    $this->karyawanModel->insert($data);
                    $karyawanId = $this->karyawanModel->getInsertID();

                    $nomerSlip = "09.8.$karyawanId/HCGS-BDM/HO/SG/" . $data['bulan'] . "/" . date('Y');
                    $this->karyawanModel->update($karyawanId, ['nomor_slip' => $nomerSlip]);

                    $inserted++;
                }
            }

            @unlink($filePath);
            return redirect()->to('/slip-gaji')->with('success', "Berhasil upload $inserted data karyawan");

        } catch (\Exception $e) {
            log_message('error', 'Upload Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error saat proses upload: ' . $e->getMessage());
        }
    }

    public function preview($id)
    {
        $this->data['karyawan'] = $this->karyawanModel->find($id);
        if (!$this->data['karyawan']) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }
        return view('payroll/slip_gaji/preview', $this->data);
    }

    /**
     * Prepare and optimize images for PDF generation
     * Resize ke ukuran kecil untuk hemat memory
     */
    protected function prepareImagesForPdf()
    {
        $cacheDir = WRITEPATH . 'barcode/';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        // Optimize logo header
        $logoHeader = FCPATH . 'assets/img/logo_header.png';
        $logoHeaderOptimized = $cacheDir . 'logo_header.png';
        
        if (file_exists($logoHeader) && !file_exists($logoHeaderOptimized)) {
            $this->resizeImage($logoHeader, $logoHeaderOptimized, 400, 100);
        }

        // Optimize barcode
        $barcode = WRITEPATH . 'uploads/barcode_ttd.png';
        $barcodeOptimized = $cacheDir . 'barcode_ttd.png';
        
        if (file_exists($barcode) && !file_exists($barcodeOptimized)) {
            $this->resizeImage($barcode, $barcodeOptimized, 300, 150);
        }
    }

    /**
     * Resize image dan compress untuk PDF
     */
    protected function resizeImage($source, $destination, $maxWidth, $maxHeight)
    {
        if (!file_exists($source)) {
            return false;
        }

        $imageInfo = @getimagesize($source);
        if (!$imageInfo) {
            return false;
        }

        list($width, $height, $type) = $imageInfo;

        // Calculate new dimensions
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        
        // Skip resize jika sudah kecil
        if ($ratio >= 1) {
            copy($source, $destination);
            return true;
        }

        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);

        // Create source image
        switch ($type) {
            case IMAGETYPE_PNG:
                $srcImage = @imagecreatefrompng($source);
                break;
            case IMAGETYPE_JPEG:
                $srcImage = @imagecreatefromjpeg($source);
                break;
            case IMAGETYPE_GIF:
                $srcImage = @imagecreatefromgif($source);
                break;
            default:
                return false;
        }

        if (!$srcImage) {
            return false;
        }

        // Create new image
        $dstImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG
        if ($type == IMAGETYPE_PNG) {
            imagealphablending($dstImage, false);
            imagesavealpha($dstImage, true);
            $transparent = imagecolorallocatealpha($dstImage, 0, 0, 0, 127);
            imagefill($dstImage, 0, 0, $transparent);
        }

        // Resize
        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Save
        $result = false;
        switch ($type) {
            case IMAGETYPE_PNG:
                $result = imagepng($dstImage, $destination, 6); // Compression level 6
                break;
            case IMAGETYPE_JPEG:
                $result = imagejpeg($dstImage, $destination, 75); // Quality 75%
                break;
            case IMAGETYPE_GIF:
                $result = imagegif($dstImage, $destination);
                break;
        }

        imagedestroy($srcImage);
        imagedestroy($dstImage);

        return $result;
    }

    /**
     * Generate PDF and return path to temporary stored file (WRITEPATH/uploads)
     */
    protected function generatePdfForEmployee(array $karyawan): string
    {
        // Increase memory limit untuk generate PDF
        ini_set('memory_limit', '512M');
        
        // Optimize images before PDF generation
        $this->prepareImagesForPdf();
        
        $html = view('payroll/slip_gaji/slip_pdf', ['karyawan' => $karyawan]);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('chroot', ROOTPATH);
        $options->set('debugPng', false);
        $options->set('debugKeepTemp', false);

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
            // Increase memory limit
            ini_set('memory_limit', '512M');
            
            // Optimize images
            $this->prepareImagesForPdf();
            
            $html = view('payroll/slip_gaji/slip_pdf', ['karyawan' => $karyawan]);

            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);
            $options->set('defaultFont', 'DejaVu Sans');
            $options->set('chroot', ROOTPATH);
            $options->set('debugPng', false);
            $options->set('debugKeepTemp', false);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = 'SlipGaji_' . $karyawan['nama'] . '_' . $karyawan['bulan'] . '.pdf';
            $dompdf->stream($filename, ['Attachment' => true]);

            unset($dompdf);
            gc_collect_cycles();

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
                @unlink($filepath);
                return $this->response->setJSON(['success' => true, 'message' => 'Email berhasil dikirim ke ' . $karyawan['email']]);
            } else {
                $debug = $this->email->printDebugger(['headers']);
                log_message('error', 'Email send failed for ' . $karyawan['email'] . ' debugger: ' . print_r($debug, true));
                if ($filepath && file_exists($filepath)) @unlink($filepath);
                return $this->response->setJSON(['success' => false, 'message' => 'Gagal mengirim email. Cek log.']);
            }

        } catch (\Exception $e) {
            if ($filepath && file_exists($filepath)) @unlink($filepath);
            log_message('error', 'SendEmail Error for ID ' . $id . ': ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function enqueueAllEmails()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success'=>false,'message'=>'AJAX only']);
        }

        $db = \Config\Database::connect();
        $queue = $db->table('email_queue');

        $karyawanList = $this->karyawanModel->where('email !=', '')->findAll();
        $now = date('Y-m-d H:i:s');

        $existing = $db->table('email_queue')
                    ->select('karyawan_id')
                    ->whereIn('status', ['pending','processing'])
                    ->get()->getResultArray();
        $existingIds = array_column($existing, 'karyawan_id');

        $toInsert = [];
        foreach ($karyawanList as $k) {
            if (isset($k['status_kirim']) && $k['status_kirim'] === 'sent') continue;
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

        $this->triggerBackgroundWorker();

        return $this->response->setJSON([
            'success' => true,
            'message' => count($toInsert) . ' email ditambahkan ke queue dan worker dimulai',
            'queued' => count($toInsert)
        ]);
    }

    private function triggerBackgroundWorker()
    {
        $workerUrl = base_url('slip-gaji/run-worker?key=Cvbagong.1994&background=1');
        
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

    public function queueStatus()
    {
        $db = \Config\Database::connect();

        // Fix sinkronisasi
        $sqlSync = "UPDATE email_queue eq
                   JOIN slip_gaji sg ON eq.karyawan_id = sg.id
                   SET eq.status = 'sent', eq.updated_at = NOW()
                   WHERE eq.status = 'processing' AND sg.status_kirim = 'sent'";
        $db->query($sqlSync);

        // Fix stuck processing
        $sqlReset = "UPDATE email_queue 
                     SET status = 'pending', updated_at = NOW(), last_error = 'Reset otomatis (Stuck Detected)'
                     WHERE status = 'processing' 
                     AND updated_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
        $db->query($sqlReset);

        // Auto-restart worker
        $pendingCount = $db->table('email_queue')->where('status', 'pending')->countAllResults();
        $processingCount = $db->table('email_queue')->where('status', 'processing')->countAllResults();
        
        if ($pendingCount > 0 && $processingCount == 0) {
            $this->triggerBackgroundWorker();
        }

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

    public function runWorker()
    {
        $key = $this->request->getGet('key');
        if ($key !== 'Cvbagong.1994') {
            return $this->response->setStatusCode(403)
                ->setJSON(['success' => false, 'message' => 'Forbidden']);
        }

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

                $tasks = $db->table('email_queue')
                    ->where('status', 'pending')
                    ->where('available_at <=', $now)
                    ->orderBy('id', 'ASC')
                    ->limit($batchSize)
                    ->get()
                    ->getResultArray();

                if (empty($tasks)) {
                    break;
                }

                $ids = array_column($tasks, 'id');
                
                $db->transStart();
                
                $db->table('email_queue') 
                   ->whereIn('id', $ids)
                   ->update([
                       'status' => 'processing',
                       'updated_at' => $now
                   ]);
                   
                $db->transComplete();

                foreach ($tasks as $task) {
                    $this->processEmailTask($task, $maxAttempts, $backoffSeconds);
                    $totalProcessed++;
                    
                    usleep(500000); 
                }

                sleep(2);

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

            if (isset($karyawan['status_kirim']) && $karyawan['status_kirim'] === 'sent') {
                $queue->where('id', $taskId)->update([
                    'status' => 'sent',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                return true;
            }

            $filepath = $this->generatePdfForEmployee($karyawan);

            $this->email->clear(true);
            $this->email->setMailType('html');
            $this->email->setFrom('payroll@bagongbis.com', 'PT Bagong Dekaka Makmur');
            $this->email->setTo($task['email']);
            $this->email->setSubject($task['subject'] ?? 'Slip Gaji');
            
            $msg = "Yth. {$karyawan['nama']},<br><br>Terlampir slip gaji bulan {$karyawan['bulan']}.<br><br>Terima kasih.<br><br>Hormat kami,<br>Payroll PT Bagong Dekaka Makmur";
            $this->email->setMessage($msg);
            $this->email->attach($filepath);

            if ($this->email->send()) {
                $db->transStart();

                $this->karyawanModel->update($task['karyawan_id'], [
                    'status_kirim' => 'sent',
                    'tanggal_kirim' => date('Y-m-d H:i:s')
                ]);
                
                $queue->where('id', $taskId)->update([
                    'status' => 'sent',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                $db->transComplete();

                if (file_exists($filepath)) @unlink($filepath);
                return true;
            } else {
                $attempts = $task['attempts'] + 1;
                $nextAvailable = date('Y-m-d H:i:s', time() + ($backoffSeconds * $attempts));
                
                $debugMsg = $this->email->printDebugger(['headers']); 

                if ($attempts >= $maxAttempts) {
                    $queue->update($taskId, [
                        'status' => 'failed',
                        'attempts' => $attempts,
                        'last_error' => substr($debugMsg, 0, 1000),
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