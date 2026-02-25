<?php

namespace App\Controllers\GeneralService;

use App\Controllers\BaseController;
use App\Models\MessModel;
use App\Services\Code\CodeGeneratorService;
use App\Models\RepairRequestModel;
use App\Models\DivisionModel;

class MessController extends BaseController
{
    protected $messModel;
    protected $helpers = ['form', 'url', 'security'];
    protected $codeService;
    protected $repairRequestModel;
    protected $divisiModel;
    
    public function __construct()
    {
        $this->messModel = new MessModel();
        $this->codeService = new CodeGeneratorService();
        $this->repairRequestModel = new RepairRequestModel();
        $this->divisiModel = new DivisionModel();
    }

    /* =========================
     * VIEW
     * ========================= */
    public function index()
    {
        return view('general_service/mess/index', $this->data);
    }

    /* =========================
     * DETAIL - Get single mess data
     * ========================= */
    public function detail($id)
    {
        try {
            $sql = "
                SELECT 
                    rr.*,
                    creator.username as created_by_name,
                    approver.username as disetujui_oleh_name,
                    CASE 
                        WHEN rr.tipe_aset = 'Mess' THEN m.nama_karyawan
                        WHEN rr.tipe_aset = 'Workshop' THEN w.name_karyawan
                        ELSE NULL
                    END as nama_karyawan,
                    CASE 
                        WHEN rr.tipe_aset = 'Mess' THEN m.nik
                        WHEN rr.tipe_aset = 'Workshop' THEN w.nik
                        ELSE NULL
                    END as nik,
                    CASE 
                        WHEN rr.tipe_aset = 'Mess' THEN m.site_id
                        WHEN rr.tipe_aset = 'Workshop' THEN w.site_id
                        ELSE NULL
                    END as site_id,
                    CASE 
                        WHEN rr.tipe_aset = 'Mess' THEN md.name
                        WHEN rr.tipe_aset = 'Workshop' THEN wd.name
                        ELSE NULL
                    END as divisi_name,
                    CASE 
                        WHEN rr.tipe_aset = 'Mess' THEN m.luasan_mess
                        WHEN rr.tipe_aset = 'Workshop' THEN w.luasan
                        ELSE NULL
                    END as luas,
                    CASE 
                        WHEN rr.tipe_aset = 'Mess' THEN m.mess_code
                        WHEN rr.tipe_aset = 'Workshop' THEN w.workshop_code
                        ELSE NULL
                    END as aset_code
                FROM repair_requests rr
                LEFT JOIN users as creator ON creator.id = rr.created_by
                LEFT JOIN users as approver ON approver.id = rr.disetujui_oleh
                LEFT JOIN mess_data m ON m.id = rr.aset_id AND rr.tipe_aset = 'Mess'
                LEFT JOIN divisions md ON md.id = m.divisi_id
                LEFT JOIN workshop w ON w.id = rr.aset_id AND rr.tipe_aset = 'Workshop'
                LEFT JOIN divisions wd ON wd.id = w.divisi_id
                WHERE rr.id = ?
                LIMIT 1
            ";

            $repair = $this->repairRequestModel->query($sql, [$id])->getRowArray();

        if (!$repair) {
            if ($this->request->isAJAX() || $this->request->getGet('format') === 'json') {
                return $this->response->setJSON(['success' => false, 'message' => 'Data tidak ditemukan']);
            }
            return redirect()->to(base_url('general-service/w'))->with('error', 'Data tidak ditemukan');
        }

        // Parse JSON fields
        $repair['foto_kerusakan'] = !empty($repair['foto_kerusakan']) ? json_decode($repair['foto_kerusakan'], true) : [];
        $repair['foto_progress']  = !empty($repair['foto_progress'])  ? json_decode($repair['foto_progress'], true)  : [];
        $repair['foto_selesai']   = !empty($repair['foto_selesai'])   ? json_decode($repair['foto_selesai'], true)   : [];
        $repair['lampiran']       = !empty($repair['lampiran'])       ? json_decode($repair['lampiran'], true)       : [];

        // ← PAKAI CEK FORMAT JUGA, bukan cuma isAJAX()
        if ($this->request->isAJAX() || $this->request->getGet('format') === 'json') {
            return $this->response->setJSON(['success' => true, 'data' => $repair]);
        }

        $pic_list = $this->repairRequestModel->where('is_deleted', 0)->where('is_active', 1)->findAll();

        return view('general_service/perbaikan/detail', [
            'repair'   => $repair,
            'pic_list' => $pic_list,
            'title'    => 'Detail Pengajuan Perbaikan'
        ]);

    } catch (\Exception $e) {
        log_message('error', 'Get repair detail failed: ' . $e->getMessage());
        if ($this->request->isAJAX() || $this->request->getGet('format') === 'json') {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
        return redirect()->back()->with('error', 'Gagal memuat detail: ' . $e->getMessage());
    }
}

    /* =========================
     * SAVE DATA
     * ========================= */
    public function save()
    {
        // PERBAIKAN: Validasi yang lebih ketat
        $rules = [
            'divisi'              => 'required',
            'job_site'            => 'required',
            'site_id'             => 'permit_empty',
            'nik'                 => 'required',
            'nama_karyawan'       => 'required',
            'luasan_mess'         => 'required|numeric|greater_than[0]',
            'jumlah_kamar_tidur'  => 'required|integer|greater_than[0]',
            'jumlah_kamar_mandi'  => 'required|integer|greater_than[0]',
            'akses_parkir'        => 'required|in_list[Ada,Tidak Ada]',
            'luas_area_parkir'    => 'required|numeric',
            'fasilitas'           => 'required',
            'status_kepemilikan'  => 'required',
            'status_renovasi'     => 'required|in_list[Pernah,Belum Pernah]',
        ];

        $messages = [
            'divisi' => [
                'required' => 'Divisi harus dipilih'
            ],
            'job_site' => [
                'required' => 'Job Site harus dipilih'
            ],
            'nik' => [
                'required' => 'NIK karyawan harus terisi'
            ],
            'nama_karyawan' => [
                'required' => 'Nama karyawan harus terisi'
            ],
            'luasan_mess' => [
                'required' => 'Luasan mess harus diisi',
                'numeric' => 'Luasan mess harus berupa angka',
                'greater_than' => 'Luasan mess harus lebih dari 0'
            ],
            'jumlah_kamar_tidur' => [
                'required' => 'Jumlah kamar tidur harus diisi',
                'integer' => 'Jumlah kamar tidur harus berupa angka bulat',
                'greater_than' => 'Jumlah kamar tidur minimal 1'
            ],
            'jumlah_kamar_mandi' => [
                'required' => 'Jumlah kamar mandi harus diisi',
                'integer' => 'Jumlah kamar mandi harus berupa angka bulat',
                'greater_than' => 'Jumlah kamar mandi minimal 1'
            ],
            'akses_parkir' => [
                'required' => 'Akses parkir harus dipilih',
                'in_list' => 'Pilihan akses parkir tidak valid'
            ],
            'luas_area_parkir' => [
                'required' => 'Luas area parkir harus diisi',
                'numeric' => 'Luas area parkir harus berupa angka'
            ],
            'fasilitas' => [
                'required' => 'Minimal pilih satu fasilitas'
            ],
            'status_kepemilikan' => [
                'required' => 'Status kepemilikan harus dipilih'
            ],
            'status_renovasi' => [
                'required' => 'Status renovasi harus dipilih',
                'in_list' => 'Pilihan status renovasi tidak valid'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // ================= INPUT =================
        $divisiId           = $this->request->getPost('divisi');
        $jobSite            = $this->request->getPost('job_site');
        $siteId             = $this->request->getPost('site_id') ?: $jobSite;
        $nik                = $this->request->getPost('nik');
        $namaKaryawan       = $this->request->getPost('nama_karyawan');
        $luasanMess         = $this->request->getPost('luasan_mess');
        $jumlahKamarTidur   = $this->request->getPost('jumlah_kamar_tidur');
        $jumlahKamarMandi   = $this->request->getPost('jumlah_kamar_mandi');
        $aksesParkir        = $this->request->getPost('akses_parkir');
        $luasAreaParkir     = $this->request->getPost('luas_area_parkir');
        $fasilitasArr       = $this->request->getPost('fasilitas');
        $statusKepemilikan  = $this->request->getPost('status_kepemilikan');
        $statusRenovasi     = $this->request->getPost('status_renovasi');

        // ================= VALIDASI FASILITAS =================
        if (!is_array($fasilitasArr) || empty($fasilitasArr)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['fasilitas' => 'Minimal pilih satu fasilitas']);
        }

        $fasilitasJson = json_encode($fasilitasArr, JSON_UNESCAPED_UNICODE);

        $now = date('Y-m-d H:i:s');

        // ================= GENERATE CODE =================
        try {
            $messCode = $this->codeService->generateMess();
        } catch (\Exception $e) {
            log_message('error', 'Generate mess code failed: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('errors', ['general' => 'Gagal membuat kode mess. Hubungi admin.']);
        }


        // ================= DATA INSERT =================
        $dataInsert = [
            'divisi_id'          => $divisiId,
            'site_id'            => $siteId,
            'nik'                => $nik,
            'nama_karyawan'      => $namaKaryawan,
            'luasan_mess'        => $luasanMess,
            'jumlah_kamar_tidur' => $jumlahKamarTidur,
            'jumlah_kamar_mandi' => $jumlahKamarMandi,
            'akses_parkir'       => $aksesParkir,
            'luas_area_parkir'   => $luasAreaParkir,
            'fasilitas'          => $fasilitasJson,
            'status_kepemilikan' => $statusKepemilikan,
            'status_renovasi'    => $statusRenovasi,
            'created_at'         => $now,
            'updated_at'         => $now,
            'is_deleted'         => 0,
            'mess_code'          => $messCode,
        ];

        try {
            log_message('info', 'Attempting to save mess data: ' . json_encode($dataInsert));
            
            $insertID = $this->messModel->insert($dataInsert);
            
            if ($insertID) {
                log_message('info', 'Mess ID ' . $insertID . ' berhasil disimpan');
                session()->setFlashdata('success', 'Data mess berhasil disimpan');
                return redirect()->to(base_url('general-service?tab=mess'));
            } else {
                $errors = $this->messModel->errors();
                log_message('error', 'Gagal menyimpan data mess. Errors: ' . json_encode($errors));
                return redirect()->back()
                    ->withInput()
                    ->with('errors', $errors ?: ['general' => 'Gagal menyimpan data']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Exception saat menyimpan data mess: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('errors', ['general' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /* =========================
     * EDIT - Show edit form
     * ========================= */
    public function edit($id)
    {
        $divisiModel = new \App\Models\DivisionModel(); // atau sesuaikan nama model Anda
        $repairModel = new \App\Models\RepairRequestModel(); // TAMBAHKAN INI

        $mess = $this->messModel
            ->select('mess_data.*, divisions.name AS divisi_name')
            ->join('divisions', 'divisions.id = mess_data.divisi_id', 'left')
            ->where('mess_data.id', $id)
            ->where('mess_data.is_deleted', 0)
            ->first();

        if (!$mess) {
            return redirect()->to('/general-service?tab=mess')
                ->with('error', 'Data mess tidak ditemukan');
        }

        // ========================================
        // AMBIL RIWAYAT PERBAIKAN UNTUK MESS INI
        // ========================================
        $existing_perbaikan = $repairModel
            ->select('repair_requests.*, users.username as created_by_name')
            ->join('users', 'users.id = repair_requests.created_by', 'left')
            ->where('repair_requests.tipe_aset', 'Mess')
            ->where('repair_requests.aset_id', $id)
            ->where('repair_requests.deleted_at IS NULL', null, false) // Exclude soft deleted
            ->orderBy('repair_requests.created_at', 'DESC')
            ->findAll();

        // Parse JSON fields untuk setiap item
        if (!empty($existing_perbaikan)) {
            foreach ($existing_perbaikan as &$item) {
                // Parse foto_kerusakan
                if (!empty($item['foto_kerusakan'])) {
                    $decoded = json_decode($item['foto_kerusakan'], true);
                    $item['foto_kerusakan_parsed'] = is_array($decoded) ? $decoded : [];
                } else {
                    $item['foto_kerusakan_parsed'] = [];
                }

                // Parse lampiran
                if (!empty($item['lampiran'])) {
                    $decoded = json_decode($item['lampiran'], true);
                    $item['lampiran_parsed'] = is_array($decoded) ? $decoded : [];
                } else {
                    $item['lampiran_parsed'] = [];
                }

                // Parse foto_progress (jika ada)
                if (!empty($item['foto_progress'])) {
                    $decoded = json_decode($item['foto_progress'], true);
                    $item['foto_progress_parsed'] = is_array($decoded) ? $decoded : [];
                } else {
                    $item['foto_progress_parsed'] = [];
                }

                // Parse foto_selesai (jika ada)
                if (!empty($item['foto_selesai'])) {
                    $decoded = json_decode($item['foto_selesai'], true);
                    $item['foto_selesai_parsed'] = is_array($decoded) ? $decoded : [];
                } else {
                    $item['foto_selesai_parsed'] = [];
                }
            }
        }

        // Data untuk form
        $this->data['title']       = 'Edit Data Mess';
        $this->data['mess']        = $mess;
        $this->data['divisi_list'] = $divisiModel->where('is_deleted', false)->findAll();
        
        // Job sites distinct untuk dropdown
        $this->data['site_list'] = $this->messModel
            ->select('site_id')
            ->where('site_id IS NOT NULL')
            ->where('site_id !=', '')
            ->distinct()
            ->findAll();

        // TAMBAHKAN RIWAYAT PERBAIKAN KE DATA
        $this->data['existing_perbaikan'] = $existing_perbaikan;

        return view('general_service/mess/edit', $this->data);
    }

    /* =========================
     * UPDATE - Process edit form
     * ========================= */
    public function update($id)
    {
        $mess = $this->messModel->find($id);
        if (!$mess || $mess['is_deleted'] == 1) {
            return redirect()->to('/general-service?tab=mess')
                ->with('error', 'Data mess tidak ditemukan');
        }

        // Validation rules
        $rules = [
            'divisi_id'       => 'required',
            'site_id'         => 'required',
            'nama_karyawan'   => 'required|min_length[3]',
            'nik'             => 'required',
            'luasan_mess'     => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $fasilitasPost = $this->request->getPost('fasilitas');
        $fasilitasJson = null;
        if (is_array($fasilitasPost) && !empty($fasilitasPost)) {
            $fasilitasJson = json_encode($fasilitasPost, JSON_UNESCAPED_UNICODE);
        }

        $data = [
            'divisi_id'           => $this->request->getPost('divisi_id'),
            'site_id'             => $this->request->getPost('site_id'),
            'nama_karyawan'       => trim($this->request->getPost('nama_karyawan')),
            'nik'                 => trim($this->request->getPost('nik')),
            'luasan_mess'         => (float) $this->request->getPost('luasan_mess'),
            'jumlah_kamar_tidur'  => (int) ($this->request->getPost('jumlah_kamar_tidur') ?? 0),
            'jumlah_kamar_mandi'  => (int) ($this->request->getPost('jumlah_kamar_mandi') ?? 0),
            'akses_parkir'        => $this->request->getPost('akses_parkir'),
            'luas_area_parkir'    => (float) ($this->request->getPost('luas_area_parkir') ?? 0),
            'fasilitas'           => $fasilitasJson,
            'status_kepemilikan'  => $this->request->getPost('status_kepemilikan'),
            'status_renovasi'     => $this->request->getPost('status_renovasi'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ];

        try {
            if ($this->messModel->update($id, $data)) {
                session()->setFlashdata('success', 'Data mess berhasil diperbarui');
                return redirect()->to('/general-service?tab=mess');
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data mess');

        } catch (\Exception $e) {
            log_message('error', 'Mess update error: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /* =========================
     * DELETE - Soft delete mess data
     * ========================= */
    public function delete($id)
    {
        // Hanya terima AJAX request
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ])->setStatusCode(400);
        }

        try {
            $mess = $this->messModel->find($id);
            
            if (!$mess || $mess['is_deleted'] == 1) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Data mess tidak ditemukan'
                ])->setStatusCode(404);
            }

            // Soft delete
            $result = $this->messModel->update($id, [
                'is_deleted' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if ($result) {
                log_message('info', 'Mess ID ' . $id . ' berhasil dihapus');
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Data mess berhasil dihapus'
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menghapus data mess'
            ])->setStatusCode(500);

        } catch (\Exception $e) {
            log_message('error', 'Mess delete error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}