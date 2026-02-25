<?php

namespace App\Controllers\GeneralService;

use App\Controllers\BaseController;
use App\Models\WorkshopModel;
use App\Services\Code\CodeGeneratorService;
use App\Services\PermissionService;
use App\Models\RepairRequestModel;

class WorkshopController extends BaseController
{
    protected $workshopModel;
    protected $helpers = ['form', 'url', 'security'];
    protected $codeService;
    protected $repairRequestModel;

    public function __construct()
    {
        $this->workshopModel = new WorkshopModel();
        $this->codeService = new CodeGeneratorService();
        $this->repairRequestModel = new RepairRequestModel();
    }

    /* =========================
     * VIEW
     * ========================= */
    public function index()
    {
        return view('general_service/workshop/index', $this->data);
    }

    /* =========================
     * DETAIL - Get single workshop data
     * ========================= */
    public function getRepairDetail($id)
{
    try {
        $db = \Config\Database::connect();
        
        $sql = "
            SELECT rr.*, 
                creator.username as created_by_name,
                approver.username as disetujui_oleh_name,
                w.name_karyawan, w.nik
            FROM repair_requests rr
            LEFT JOIN users as creator ON creator.id = rr.created_by
            LEFT JOIN users as approver ON approver.id = rr.disetujui_oleh
            LEFT JOIN workshop w ON w.id = rr.aset_id AND rr.tipe_aset = 'Workshop'
            WHERE rr.id = ?
            LIMIT 1
        ";

        $repair = $db->query($sql, [$id])->getRowArray();

        if (!$repair) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data tidak ditemukan']);
        }

        $repair['foto_kerusakan'] = !empty($repair['foto_kerusakan']) ? json_decode($repair['foto_kerusakan'], true) : [];
        $repair['foto_progress']  = !empty($repair['foto_progress'])  ? json_decode($repair['foto_progress'], true)  : [];
        $repair['foto_selesai']   = !empty($repair['foto_selesai'])   ? json_decode($repair['foto_selesai'], true)   : [];
        $repair['lampiran']       = !empty($repair['lampiran'])       ? json_decode($repair['lampiran'], true)       : [];

        return $this->response->setJSON(['success' => true, 'data' => $repair]);

    } catch (\Exception $e) {
        return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
    }
}
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
        $rules = [
            'divisi'              => 'required',
            'job_site'            => 'required',
            'site_id'             => 'permit_empty',
            'employee_id'         => 'required',
            'nik'                 => 'required',
            'nama_karyawan'       => 'required',
            'luasan_workshop'     => 'required|numeric',
            'jumlah_bays'         => 'required|integer|greater_than[0]',
            'kompartemen'         => 'required',
            'status_kepemilikan'  => 'required',
            'status_pembangunan'  => 'required',
        ];

        $messages = [
            'divisi' => [
                'required' => 'Divisi harus dipilih'
            ],
            'job_site' => [
                'required' => 'Job Site harus dipilih'
            ],
            'employee_id' => [
                'required' => 'Karyawan harus dipilih'
            ],
            'nik' => [
                'required' => 'NIK karyawan harus terisi'
            ],
            'nama_karyawan' => [
                'required' => 'Nama karyawan harus terisi'
            ],
            'luasan_workshop' => [
                'required' => 'Luasan workshop harus diisi',
                'numeric' => 'Luasan workshop harus berupa angka'
            ],
            'jumlah_bays' => [
                'required' => 'Jumlah bays harus diisi',
                'integer' => 'Jumlah bays harus berupa angka bulat',
                'greater_than' => 'Jumlah bays minimal 1'
            ],
            'kompartemen' => [
                'required' => 'Minimal pilih satu kompartemen'
            ],
            'status_kepemilikan' => [
                'required' => 'Status kepemilikan harus dipilih'
            ],
            'status_pembangunan' => [
                'required' => 'Status pembangunan harus dipilih'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // ================= INPUT =================
        $divisiId        = $this->request->getPost('divisi');
        $jobSite         = $this->request->getPost('job_site');
        $siteId          = $this->request->getPost('site_id') ?: $jobSite;
        $nik             = $this->request->getPost('nik');
        $namaKaryawan    = $this->request->getPost('nama_karyawan');
        $luasan          = $this->request->getPost('luasan_workshop');
        $bays            = $this->request->getPost('jumlah_bays');
        $kompartemenArr  = $this->request->getPost('kompartemen');
        $statusLahan     = $this->request->getPost('status_kepemilikan');
        $statusWorkshop  = $this->request->getPost('status_pembangunan');

        // ================= VALIDASI KOMPARTEMEN =================
        if (!is_array($kompartemenArr) || empty($kompartemenArr)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['kompartemen' => 'Minimal pilih satu kompartemen']);
        }

        $kompartemenJson = json_encode($kompartemenArr, JSON_UNESCAPED_UNICODE);

        // generate code workshop
        try {
            $workshopCode = $this->codeService->generateWorkshop();
        } catch (\Exception $e) {
            log_message('error', 'Generate workshop code failed: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('errors', ['general' => 'Gagal membuat kode workshop. Hubungi admin.']);
        }

        $now = date('Y-m-d H:i:s');

        // ================= INSERT =================
        $dataInsert = [
            'workshop_code'  => $workshopCode,
            'divisi_id'      => $divisiId,
            'site_id'        => $siteId,
            'name_karyawan'  => $namaKaryawan,
            'nik'            => $nik,
            'luasan'         => $luasan,
            'bays'           => $bays,
            'kompartemen'    => $kompartemenJson,
            'status_workshop'=> $statusWorkshop,
            'status_lahan'   => $statusLahan,
            'created_at'     => $now,
            'updated_at'     => $now,
            'is_deleted'     => 0
        ];

        try {
            log_message('info', 'Attempting to save workshop data: ' . json_encode($dataInsert));
            
            $insertID = $this->workshopModel->insert($dataInsert);
            
            if ($insertID) {
                log_message('info', 'Workshop ID ' . $insertID . ' berhasil disimpan');
                session()->setFlashdata('success', 'Data workshop berhasil disimpan');
                return redirect()->to(base_url('general-service?tab=workshop'));
            } else {
                $errors = $this->workshopModel->errors();
                log_message('error', 'Gagal menyimpan data workshop. Errors: ' . json_encode($errors));
                return redirect()->back()
                    ->withInput()
                    ->with('errors', $errors ?: ['general' => 'Gagal menyimpan data']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Gagal menyimpan data workshop. Error: ' . $e->getMessage());
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
        $divisiModel = new \App\Models\DivisionModel();
        $repairModel = new \App\Models\RepairRequestModel(); // TAMBAHKAN INI

        $workshop = $this->workshopModel
            ->select('workshop.*, divisions.name AS divisi_name')
            ->join('divisions', 'divisions.id = workshop.divisi_id', 'left')
            ->where('workshop.id', $id)
            ->where('workshop.is_deleted', false)
            ->first();

        if (!$workshop) {
            return redirect()->to('/general-service?tab=workshop')
                ->with('error', 'Data workshop tidak ditemukan');
        }

        // ========================================
        // GET RIWAYAT PERBAIKAN UNTUK WORKSHOP INI
        // ========================================
        $existing_perbaikan = $repairModel
            ->select('repair_requests.*, users.username as created_by_name')
            ->join('users', 'users.id = repair_requests.created_by', 'left')
            ->where('repair_requests.tipe_aset', 'Workshop')
            ->where('repair_requests.aset_id', $id)
            ->where('repair_requests.deleted_at', null)
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

                // Parse foto_progress
                if (!empty($item['foto_progress'])) {
                    $decoded = json_decode($item['foto_progress'], true);
                    $item['foto_progress_parsed'] = is_array($decoded) ? $decoded : [];
                } else {
                    $item['foto_progress_parsed'] = [];
                }

                // Parse foto_selesai
                if (!empty($item['foto_selesai'])) {
                    $decoded = json_decode($item['foto_selesai'], true);
                    $item['foto_selesai_parsed'] = is_array($decoded) ? $decoded : [];
                } else {
                    $item['foto_selesai_parsed'] = [];
                }
            }
        }

        // Data untuk form
        $this->data['title']       = 'Edit Data Workshop';
        $this->data['workshop']    = $workshop;
        $this->data['divisi_list'] = $divisiModel->where('is_deleted', false)->findAll();
        
        // Job sites distinct untuk dropdown
        $this->data['job_sites'] = $this->workshopModel
            ->select('site_id')
            ->where('site_id IS NOT NULL')
            ->where('site_id !=', '')
            ->distinct()
            ->findAll();

        // TAMBAHKAN RIWAYAT PERBAIKAN
        $this->data['existing_perbaikan'] = $existing_perbaikan;

        return view('general_service/workshop/edit', $this->data);
    }

    /* =========================
     * UPDATE - Process edit form
     * ========================= */
    public function update($id)
    {
        $workshop = $this->workshopModel->find($id);
        if (!$workshop || $workshop['is_deleted'] == 1) {
            return redirect()->to('/general-service?tab=workshop')
                ->with('error', 'Data workshop tidak ditemukan');
        }

        // Validation rules
        $rules = [
            'divisi_id'      => 'required',
            'site_id'        => 'required',
            'name_karyawan'  => 'required|min_length[3]',
            'nik'            => 'required',
            'luasan'         => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $kompartemenPost = $this->request->getPost('kompartemen');
        $kompartemenJson = null;
        if (is_array($kompartemenPost) && !empty($kompartemenPost)) {
            $kompartemenJson = json_encode($kompartemenPost, JSON_UNESCAPED_UNICODE);
        }

        $data = [
            'divisi_id'       => $this->request->getPost('divisi_id'),
            'site_id'         => trim($this->request->getPost('site_id')),
            'name_karyawan'   => trim($this->request->getPost('name_karyawan')),
            'nik'             => trim($this->request->getPost('nik')),
            'luasan'          => (float) $this->request->getPost('luasan'),
            'bays'            => (int) ($this->request->getPost('bays') ?? 0),
            'kompartemen'     => $kompartemenJson,
            'status_workshop' => $this->request->getPost('status_workshop'),
            'status_lahan'    => $this->request->getPost('status_lahan'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        try {
            if ($this->workshopModel->update($id, $data)) {
                session()->setFlashdata('success', 'Data workshop berhasil diperbarui');
                return redirect()->to('/general-service?tab=workshop');
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data workshop');

        } catch (\Exception $e) {
            log_message('error', 'Workshop update error: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /* =========================
     * DELETE - Soft delete workshop data
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
            $workshop = $this->workshopModel->find($id);
            
            if (!$workshop || $workshop['is_deleted'] == 1) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Data workshop tidak ditemukan'
                ])->setStatusCode(404);
            }

            // Soft delete
            $result = $this->workshopModel->update($id, [
                'is_deleted' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if ($result) {
                log_message('info', 'Workshop ID ' . $id . ' berhasil dihapus');
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Data workshop berhasil dihapus'
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menghapus data workshop'
            ])->setStatusCode(500);

        } catch (\Exception $e) {
            log_message('error', 'Workshop delete error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}