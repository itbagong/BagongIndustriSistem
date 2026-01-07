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

        $this->emailConfig = config('Email'); // ambil config (di-override oleh .env)

    }



    public function index()

    {

        $data['karyawan'] = $this->karyawanModel->findAll();

        return view('Payroll/index', $data);

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



            array_shift($rows); // hapus header



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

        return view('Payroll/slip_gaji/preview', $data);

    }



    protected function generatePdfForEmployee(array $karyawan): string

    {

        // Buat HTML dari view

        $html = view('Payroll/slip_gaji/slip_pdf', ['karyawan' => $karyawan]);



        // Dompdf options (enable remote if ada CSS/gambar luar)

        $options = new Options();

        $options->set('isRemoteEnabled', true);

        $options->set('defaultFont', 'DejaVu Sans'); // jika butuh font unicode

        $dompdf = new Dompdf($options);



        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();



        $output = $dompdf->output();



        unset($dompdf);

        gc_collect_cycles();



        // Simpan file sementara dengan nama unik

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



        // direct stream PDF (no temp file)

        try {

            $html = view('Payroll/slip_gaji/slip_pdf', ['karyawan' => $karyawan]);

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



        // guard: jika sudah sent, batalkan kecuali ada parameter force

        $force = $this->request->getPost('force') ?? $this->request->getVar('force'); // accept POST atau query

        if ($karyawan['status_kirim'] === 'sent' && !$force) {

            return $this->response->setJSON(['success' => false, 'message' => 'Sudah terkirim sebelumnya. Gunakan opsi resend jika ingin kirim ulang.']);

        }



        $filepath = null;

        try {

            $filepath = $this->generatePdfForEmployee($karyawan);



            // set email defaults

            $fromEmail = $this->emailConfig->fromEmail ?: 'Payroll@bagongbis.com';

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

                // update status

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



    // kirim email tanpa queue, dengan batch & retry sederhana

    public function sendAllEmails()

    {

        // supaya proses tidak timeout cepat (bergantung config server/gateway)

        @set_time_limit(0);

        @ignore_user_abort(true);



        $karyawanList = $this->karyawanModel->where('email !=', '')->findAll();

        $success = 0;

        $failed = 0;



        // konfigurasi batch & retry

        $batchSize = 50; // ubah jika perlu

        $sleepPerEmail = 1; // detik

        $sleepPerBatch = 5; // detik antar batch

        $maxRetries = 2;



        $fromEmail = $this->emailConfig->fromEmail ?: 'Payroll@bagongbis.com';

        $fromName = $this->emailConfig->fromName ?: 'PT Bagong Dekaka Makmur';



        $total = count($karyawanList);

        $i = 0;



        try {

            foreach ($karyawanList as $k) {

                $i++;

                $attempt = 0;

                $sent = false;



                while ($attempt <= $maxRetries && ! $sent) {

                    $attempt++;

                    $filepath = null;

                    try {

                        $filepath = $this->generatePdfForEmployee($k);



                        $this->email->clear(true);

                        $this->email->setMailType('html');

                        $this->email->setFrom($fromEmail, $fromName);

                        $this->email->setTo($k['email']);

                        $this->email->setSubject('Slip Gaji - ' . $k['bulan']);



                        $message = "

                        Yth. Bapak/Ibu {$k['nama']},<br><br>

                        Terlampir slip gaji untuk bulan {$k['bulan']}.<br><br>

                        Terima kasih.<br><br>

                        Hormat kami,<br>

                        Payroll PT Bagong Dekaka Makmur

                        ";



                        $this->email->setMessage($message);

                        $this->email->attach($filepath);



                        if ($this->email->send()) {

                            $success++;

                            $this->karyawanModel->update($k['id'], ['status_kirim' => 'sent', 'tanggal_kirim' => date('Y-m-d H:i:s')]);

                            $sent = true;

                        } else {

                            $debug = $this->email->printDebugger(['headers', 'body']);

                            log_message('error', 'Attempt ' . $attempt . ' failed for ' . $k['email'] . ' debug: ' . print_r($debug, true));

                            // lanjut retry

                        }

                    } catch (\Exception $e) {

                        log_message('error', 'SendAllEmails exception for ' . $k['email'] . ' attempt ' . $attempt . ': ' . $e->getMessage());

                    } finally {

                        if (isset($filepath) && $filepath && file_exists($filepath)) {

                            @unlink($filepath);

                        }

                    }



                    if (! $sent) {

                        sleep(1); // jeda antar retry

                    }

                }



                if (! $sent) {

                    $failed++;

                }



                // throttle per email

                sleep($sleepPerEmail);



                // jeda antar batch

                if ($i % $batchSize === 0) {

                    sleep($sleepPerBatch);

                }

            }



            $message = "Email berhasil dikirim: $success, Gagal: $failed";



            // Jika dipanggil via AJAX (fetch), kembalikan JSON

            if ($this->request->isAJAX()) {

                return $this->response->setJSON([

                    'success' => true,

                    'message' => $message,

                    'total' => $total,

                    'sent' => $success,

                    'failed' => $failed

                ]);

            }



            // Jika non-AJAX (browser biasa), redirect seperti semula

            return redirect()->to('/slip-gaji')->with('success', $message);



        } catch (\Exception $e) {

            log_message('error', 'sendAllEmails fatal: ' . $e->getMessage());



            if ($this->request->isAJAX()) {

                return $this->response->setStatusCode(500)

                                     ->setJSON(['success' => false, 'message' => 'Exception: ' . $e->getMessage()]);

            }



            return redirect()->back()->with('error', 'Terjadi error: ' . $e->getMessage());

        }

    }



    // kirim email dengan queue (menambahkan ke tabel email_queue)

    // public function enqueueAllEmails()

    // {

    //     if (! $this->request->isAJAX()) {

    //         return $this->response->setStatusCode(400)->setJSON(['success'=>false,'message'=>'AJAX only']);

    //     }

    //     $db = \Config\Database::connect();

    //     $queue = $db->table('email_queue');


    //     $karyawanList = $this->karyawanModel->where('email !=', '')->findAll();

    //     $now = date('Y-m-d H:i:s');



    //     // ambil semua karyawan_id yang sudah ada di queue dengan status pending/processing

    //     $existing = $db->table('email_queue')

    //                 ->select('karyawan_id')

    //                 ->whereIn('status', ['pending','processing'])

    //                 ->get()->getResultArray();

    //     $existingIds = array_column($existing, 'karyawan_id');



    //     $toInsert = [];

    //     foreach ($karyawanList as $k) {

    //         // skip jika sudah terkirim di slip_gaji

    //         if (isset($k['status_kirim']) && $k['status_kirim'] === 'sent') continue;



    //         // skip jika sudah ada di queue pending/processing

    //         if (in_array($k['id'], $existingIds)) continue;



    //         $toInsert[] = [

    //             'karyawan_id' => $k['id'],

    //             'email' => $k['email'],

    //             'subject' => 'Slip Gaji - ' . $k['bulan'],

    //             'status' => 'pending',

    //             'created_at' => $now,

    //             'updated_at' => $now

    //         ];
    //     }

    //     $queue->insertBatch($toInsert);

    //     $total = count($toInsert);

    //     $message = "Email berhasil ditambahkan ke queue: $total";

    //     return $this->response->setJSON([
    //         'success' => true,
    //         'message' => $message,
    //         'total' => $total,
    //         'skipped_sent' => count($existingIds),
    //         'karyawan_total' => count($karyawanList)
    //     ]);

    // }

    // public function runWorker()
    // {
    //     $key = $this->request->getGet('key');
    //     if ($key !== 'Cvbagong.1994') {
    //         return $this->response->setStatusCode(403)
    //             ->setJSON(['success' => false, 'message' => 'Forbidden']);
    //     }

    //     $db = \Config\Database::connect();
    //     $queue = $db->table('email_queue');

    //     $batchSize = 20;

    //     $jobs = $queue
    //         ->where('status', 'pending')
    //         ->orderBy('id', 'ASC')
    //         ->limit($batchSize)
    //         ->get()
    //         ->getResultArray();

    //     if (!$jobs) {
    //         return $this->response->setJSON([
    //             'success' => true,
    //             'message' => 'No pending jobs'
    //         ]);
    //     }

    //     $sent = 0;
    //     $failed = 0;

    //     foreach ($jobs as $task) {

    //         $k = $this->karyawanModel->find($task['karyawan_id']);
    //         if (!$k) {
    //             $queue->update($task['id'], ['status' => 'failed']);
    //             $failed++;
    //             continue;
    //         }

    //         // skip jika sudah sent
    //         if ($k['status_kirim'] === 'sent') {
    //             $queue->update($task['id'], ['status' => 'sent']);
    //             continue;
    //         }

    //         try {
    //             $filepath = $this->generatePdfForEmployee($k);

    //             $this->email->clear(true);
    //             $this->email->setMailType('html');
    //             $this->email->setFrom('Payroll@bagongbis.com', 'PT Bagong Dekaka Makmur');
    //             $this->email->setTo($k['email']);
    //             $this->email->setSubject($task['subject']);

    //             $this->email->setMessage("
    //                 Yth {$k['nama']}<br>
    //                 Terlampir slip gaji bulan {$k['bulan']}
    //             ");

    //             $this->email->attach($filepath);

    //             if ($this->email->send()) {

    //                 $this->karyawanModel->update($k['id'], [
    //                     'status_kirim' => 'sent',
    //                     'tanggal_kirim' => date('Y-m-d H:i:s')
    //                 ]);

    //                 $queue->update($task['id'], ['status' => 'sent']);
    //                 $sent++;

    //             } else {

    //                 $queue->update($task['id'], [
    //                     'status' => 'failed'
    //                 ]);

    //                 $failed++;
    //             }

    //             unlink($filepath);

    //         } catch (\Exception $e) {

    //             $queue->update($task['id'], [
    //                 'status' => 'failed',
    //                 'last_error' => $e->getMessage()
    //             ]);

    //             $failed++;
    //         }
    //     }

    //     return $this->response->setJSON([
    //         'success' => true,
    //         'processed' => count($jobs),
    //         'sent' => $sent,
    //         'failed' => $failed
    //     ]);
    // }


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
