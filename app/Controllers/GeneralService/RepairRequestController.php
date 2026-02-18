<?php

namespace App\Controllers\GeneralService;
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if headers already sent
if (headers_sent($file, $line)) {
    die("Headers already sent in $file on line $line");
}
use App\Controllers\BaseController;
use App\Models\RepairRequestModel;
use App\Models\MessModel;
use App\Models\WorkshopModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\Code\CodeGeneratorService;
use App\Utils\ApiResponse;
use App\Utils\ApiResponseParams;
use App\Models\DivisionModel;


class RepairRequestController extends BaseController
{
    protected $repairModel;
    protected $messModel;
    protected $workshopModel;
    protected $codeService;
    protected $userModel;
    protected $divisiModel;
    protected $db;
    protected $data = [];

    public function __construct()
    {
        $this->repairModel = new RepairRequestModel();
        $this->messModel = new MessModel();
        $this->workshopModel = new WorkshopModel();
        $this->codeService = new CodeGeneratorService();
        $this->userModel = new \App\Models\UserModel();
        $this->divisiModel = new DivisionModel();
        $this->db = \Config\Database::connect();
    }

    // ===============================================
    // STORE REPAIR REQUEST (MESS & WORKSHOP)
    // ===============================================
    public function store()
    {
        $rules = [
            'tipe_aset'            => 'required|in_list[Mess,Workshop]',
            'aset_id'              => 'required|numeric',
            'jenis_kerusakan'      => 'required|min_length[5]|max_length[255]',
            'deskripsi_kerusakan'  => 'required',
            'estimasi_biaya'       => 'permit_empty|decimal',
            'catatan'              => 'permit_empty|string',
            'lokasi_spesifik'      => 'permit_empty|max_length[255]',
        ];

        // ================= VALIDATION =================
        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return api_response(
                    $this->response,
                    ResponseInterface::HTTP_UNPROCESSABLE_ENTITY,
                    'Validasi gagal',
                    null,
                    $this->validator->getErrors()
                );
            }

            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }


        $this->db->transBegin();

        try {
            // ================= GENERATE CODE =================
            $kodePengajuan = $this->codeService->generateRepair();
            if (!$kodePengajuan) {
                throw new \Exception('Gagal generate kode pengajuan');
            }

            // ================= ASET =================
            $tipeAset = $this->request->getPost('tipe_aset');
            $asetCode = $this->request->getPost('aset_code');
            $asetId   = (int) $this->request->getPost('aset_id');

            $asetDetails = $this->getAsetDetails($tipeAset, $asetId, $asetCode);
            if (!$asetDetails) {
                return ApiResponse::SetApiResponse(
                    new ApiResponseParams([
                        'status'  => ResponseInterface::HTTP_NOT_FOUND, // 404
                        'message' => 'Aset tidak ditemukan',
                        'data'    => null,
                    ])
                );
            }

            // ================= FILE UPLOAD =================
            $fotoKerusakan = $this->handleFileUploads('foto_kerusakan');
            $lampiran      = $this->handleFileUploads('lampiran');

            // ================= PREPARE DATA =================
            $data = [
                'kode_pengajuan'       => $kodePengajuan,
                'tipe_aset'            => $tipeAset,
                'aset_id'              => $asetId,
                'nama_aset'            => $asetDetails['nama'],
                'lokasi_aset'          => $asetDetails['lokasi'],
                'aset_code'            => $this->request->getPost('aset_code'),
                'kategori_kerusakan'   => $this->request->getPost('kategori_kerusakan'),
                'jenis_kerusakan'      => $this->request->getPost('jenis_kerusakan'),
                'deskripsi_kerusakan'  => $this->request->getPost('deskripsi_kerusakan'),
                'prioritas'            => $this->request->getPost('prioritas'),
                'tingkat_urgensi'      => $this->request->getPost('prioritas') === 'Segera' ? 1 : 0,
                'estimasi_biaya'       => $this->request->getPost('estimasi_biaya') ?? 0,
                'catatan'              => $this->request->getPost('catatan'),
                'foto_kerusakan'       => !empty($fotoKerusakan) ? json_encode($fotoKerusakan) : null,
                'lampiran'             => !empty($lampiran) ? json_encode($lampiran) : null,
                'status'               => 'Pending',
                'tanggal_pengajuan'    => date('Y-m-d H:i:s'),
                'created_by'           => session()->get('user_id'),
                'created_at'           => date('Y-m-d H:i:s'),
            ];

            // ================= INSERT =================
            $insertId = $this->repairModel->insert($data);

            if (!$insertId) {
                return ApiResponse::SetApiResponse(
                    new ApiResponseParams([
                        'status'  => ResponseInterface::HTTP_UNPROCESSABLE_ENTITY, // 422
                        'message' => 'Gagal menyimpan data',
                        'data'    => null,
                        'errors'  => $this->repairModel->errors(),
                    ])
                );
            }

            $this->db->transCommit();

            // ================= SUCCESS =================
            return ApiResponse::SetApiResponse(
                new ApiResponseParams([
                    'status'  => 201, // 201
                    'success' => true,
                    'message' => 'Pengajuan perbaikan berhasil dibuat',
                    'data'    => [
                        'id'             => $insertId,
                        'kode_pengajuan' => $kodePengajuan,
                    ],
                ])
            );

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', $e->getMessage());

            return ApiResponse::SetApiResponse(
                new ApiResponseParams([
                    'status'  => ResponseInterface::HTTP_INTERNAL_SERVER_ERROR, // 500
                    'message' => 'Terjadi kesalahan server',
                    'data'    => null,
                ])
            );
        }
    }


    // ===============================================
    // GET ASET DETAILS (MESS OR WORKSHOP)
    // ===============================================
    private function getAsetDetails(string $tipeAset, int $asetId): ?array
    {
        try {
            if ($tipeAset === 'Mess') {
                $mess = $this->messModel->find($asetId);
                if (!$mess) return null;

                return [
                    'nama' => $mess['nama_karyawan'] . ' - ' . $mess['nik'],
                    'lokasi' => $mess['site_id'] ?? 'N/A'
                ];
            } 
            elseif ($tipeAset === 'Workshop') {
                $workshop = $this->workshopModel->find($asetId);
                if (!$workshop) return null;

                return [
                    'nama' => $workshop['name_karyawan'] . ' - ' . $workshop['nik'],
                    'lokasi' => $workshop['site_id'] ?? 'N/A'
                ];
            }

            return null;
        } catch (\Exception $e) {
            log_message('error', 'Get aset details failed: ' . $e->getMessage());
            return null;
        }
    }

    // ===============================================
    // HANDLE FILE UPLOADS
    // ===============================================
    private function handleFileUploads(string $fieldName): array
    {
        $uploadedFiles = [];
        
        if ($files = $this->request->getFiles()) {
            if (isset($files[$fieldName])) {
                $fileList = $files[$fieldName];
                
                // Handle multiple files
                foreach ($fileList as $file) {
                    if ($file->isValid() && !$file->hasMoved()) {
                        // Validate file
                        $validated = $this->validateFile($file);
                        if (!$validated['valid']) {
                            log_message('error', 'File validation failed: ' . $validated['error']);
                            continue;
                        }

                        // Generate unique filename
                        $newName = $file->getRandomName();
                        
                        // Determine upload path based on field
                        $uploadPath = WRITEPATH . 'uploads/repair_requests/';
                        
                        if (!is_dir($uploadPath)) {
                            mkdir($uploadPath, 0755, true);
                        }

                        // Move file
                        if ($file->move($uploadPath, $newName)) {
                            $uploadedFiles[] = [
                                'filename' => $newName,
                                'original_name' => $file->getClientName(),
                                'path' => 'writable/uploads/repair_requests/' . $newName,
                                'size' => $file->getSize(),
                                'type' => $file->getClientMimeType(),
                                'uploaded_at' => date('Y-m-d H:i:s')
                            ];
                        }
                    }
                }
            }
        }

        return $uploadedFiles;
    }

    // ===============================================
    // VALIDATE FILE
    // ===============================================
    private function validateFile($file): array
    {
        $maxSize = 5 * 1024 * 1024; // 5MB
        $allowedTypes = [
            'image/jpeg', 
            'image/jpg', 
            'image/png', 
            'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        if ($file->getSize() > $maxSize) {
            return [
                'valid' => false,
                'error' => 'File size exceeds 5MB limit'
            ];
        }

        if (!in_array($file->getClientMimeType(), $allowedTypes)) {
            return [
                'valid' => false,
                'error' => 'File type not allowed'
            ];
        }

        return ['valid' => true];
    }

    // ===============================================
    // GET REPAIR DETAIL
    // ===============================================
    public function detail($id)
    {
        try {
            // Query dengan JOIN untuk ambil semua data
            $repair = $this->repairModel
                ->select('
                    repair_requests.*,
                    creator.username as created_by_name,
                    approver.username as disetujui_oleh_name,
                    CASE 
                        WHEN repair_requests.tipe_aset = "Mess" THEN mess_data.nama_karyawan
                        WHEN repair_requests.tipe_aset = "Workshop" THEN workshop.name_karyawan
                        ELSE NULL
                    END as nama_karyawan,
                    CASE 
                        WHEN repair_requests.tipe_aset = "Mess" THEN mess_data.nik
                        WHEN repair_requests.tipe_aset = "Workshop" THEN workshop.nik
                        ELSE NULL
                    END as nik,
                    CASE 
                        WHEN repair_requests.tipe_aset = "Mess" THEN mess_data.site_id
                        WHEN repair_requests.tipe_aset = "Workshop" THEN workshop.site_id
                        ELSE NULL
                    END as site_id,
                    CASE 
                        WHEN repair_requests.tipe_aset = "Mess" THEN mess_divisi.name
                        WHEN repair_requests.tipe_aset = "Workshop" THEN workshop_divisi.name
                        ELSE NULL
                    END as divisi_name,
                    CASE 
                        WHEN repair_requests.tipe_aset = "Mess" THEN mess_data.luasan_mess
                        WHEN repair_requests.tipe_aset = "Workshop" THEN workshop.luasan
                        ELSE NULL
                    END as luas,
                    CASE 
                        WHEN repair_requests.tipe_aset = "Mess" THEN mess_data.mess_code
                        WHEN repair_requests.tipe_aset = "Workshop" THEN workshop.workshop_code
                        ELSE NULL
                    END as aset_code
                ')
                ->join('users as creator', 'creator.id = repair_requests.created_by', 'left')
                ->join('users as approver', 'approver.id = repair_requests.disetujui_oleh', 'left')
                ->join('mess_data', 'mess_data.id = repair_requests.aset_id AND repair_requests.tipe_aset = "Mess"', 'left')
                ->join('divisions as mess_divisi', 'mess_divisi.id = mess_data.divisi_id', 'left')
                ->join('workshop', 'workshop.id = repair_requests.aset_id AND repair_requests.tipe_aset = "Workshop"', 'left')
                ->join('divisions as workshop_divisi', 'workshop_divisi.id = workshop.divisi_id', 'left')
                ->where('repair_requests.id', $id)
                ->first();
            
            if (!$repair) {
                return redirect()->to(base_url('general-service/repair-request'))
                    ->with('error', 'Data tidak ditemukan');
            }

            // Parse JSON fields
            $repair['foto_kerusakan'] = !empty($repair['foto_kerusakan']) ? json_decode($repair['foto_kerusakan'], true) : [];
            $repair['foto_progress'] = !empty($repair['foto_progress']) ? json_decode($repair['foto_progress'], true) : [];
            $repair['foto_selesai'] = !empty($repair['foto_selesai']) ? json_decode($repair['foto_selesai'], true) : [];
            $repair['lampiran'] = !empty($repair['lampiran']) ? json_decode($repair['lampiran'], true) : [];

            // Get PIC list
            $userModel = new \App\Models\UserModel();
            $pic_list = $userModel
                ->where('is_deleted', 0)
                ->where('is_active', 1)
                ->findAll();

            return view('general_service/repair_request/detail', [
                'repair' => $repair,
                'pic_list' => $pic_list,
                'title' => 'Detail Pengajuan Perbaikan'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Get repair detail failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat detail: ' . $e->getMessage());
        }
    }

    // ========================================
    // EDIT METHOD (untuk form edit)
    // ========================================
    public function edit($id)
    {
        try {
            $db = \Config\Database::connect();
            
            $repair = $db->table('repair_requests')
                ->select('
                    repair_requests.*,
                    CASE 
                        WHEN repair_requests.tipe_aset = "Mess" THEN mess_data.nama_karyawan
                        WHEN repair_requests.tipe_aset = "Workshop" THEN workshop.name_karyawan
                        ELSE NULL
                    END as nama_karyawan,
                    CASE 
                        WHEN repair_requests.tipe_aset = "Mess" THEN mess_data.nik
                        WHEN repair_requests.tipe_aset = "Workshop" THEN workshop.nik
                        ELSE NULL
                    END as nik,
                    CASE 
                        WHEN repair_requests.tipe_aset = "Mess" THEN mess_data.site_id
                        WHEN repair_requests.tipe_aset = "Workshop" THEN workshop.site_id
                        ELSE NULL
                    END as site_id,
                    CASE 
                        WHEN repair_requests.tipe_aset = "Mess" THEN mess_divisi.name
                        WHEN repair_requests.tipe_aset = "Workshop" THEN workshop_divisi.name
                        ELSE NULL
                    END as divisi_name
                ', false)
                ->join('mess_data', 'mess_data.id = repair_requests.aset_id AND repair_requests.tipe_aset = "Mess"', 'left')
                ->join('divisions as mess_divisi', 'mess_divisi.id = mess_data.divisi_id', 'left')
                ->join('workshop', 'workshop.id = repair_requests.aset_id AND repair_requests.tipe_aset = "Workshop"', 'left')
                ->join('divisions as workshop_divisi', 'workshop_divisi.id = workshop.divisi_id', 'left')
                ->where('repair_requests.id', $id)
                ->get()
                ->getRowArray();
            
            if (!$repair) {
                return redirect()->to(base_url('general-service/repair-request'))
                    ->with('error', 'Data tidak ditemukan');
            }

            // Cek status
            if ($repair['status'] != 'Pending') {
                return redirect()->to(base_url('general-service/repair-request/detail/' . $id))
                    ->with('error', 'Hanya pengajuan dengan status Pending yang bisa diedit');
            }

            // Parse JSON
            $repair['foto_kerusakan'] = !empty($repair['foto_kerusakan']) ? json_decode($repair['foto_kerusakan'], true) : [];
            $repair['lampiran'] = !empty($repair['lampiran']) ? json_decode($repair['lampiran'], true) : [];

            // ✅ SESUAIKAN dengan ENUM di database
            $kategori_list = [
                'Ringan',
                'Sedang', 
                'Berat',
                'Darurat'
            ];

            $prioritas_list = [
                'Rendah',
                'Normal',
                'Segera'
            ];

            // Assign to $this->data
            $this->data['title'] = 'Edit Pengajuan Perbaikan';
            $this->data['repair'] = $repair;
            $this->data['kategori_list'] = $kategori_list;
            $this->data['prioritas_list'] = $prioritas_list; // ✅ TAMBAHKAN INI

            return view('general_service/perbaikan/edit', $this->data);

        } catch (\Exception $e) {
            log_message('error', 'Get repair edit failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat form edit: ' . $e->getMessage());
        }
    }

    // ========================================
    // UPDATE METHOD
    // ========================================
    public function update($id)
    {
        try {
            $repair = $this->repairModel->find($id);
            
            if (!$repair) {
                return redirect()->back()->with('error', 'Data tidak ditemukan');
            }

            // Cek status - hanya Pending yang bisa diupdate
            if ($repair['status'] != 'Pending') {
                return redirect()->back()->with('error', 'Hanya pengajuan Pending yang bisa diupdate');
            }

            // Validation rules
            $rules = [
                'kategori_kerusakan' => 'required|in_list[Ringan,Sedang,Berat,Darurat]',
                'jenis_kerusakan' => 'required|min_length[5]',
                'deskripsi_kerusakan' => 'required|min_length[10]',
                'prioritas' => 'required|in_list[Rendah,Normal,Segera]',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()
                    ->withInput()
                    ->with('errors', $this->validator->getErrors());
            }

            // Get current user ID
            $userId = session()->get('user')['id'] ?? null;

            // Prepare data
            $data = [
                'kategori_kerusakan' => $this->request->getPost('kategori_kerusakan'),
                'jenis_kerusakan' => $this->request->getPost('jenis_kerusakan'),
                'deskripsi_kerusakan' => $this->request->getPost('deskripsi_kerusakan'),
                'prioritas' => $this->request->getPost('prioritas'),
                'estimasi_biaya' => $this->request->getPost('estimasi_biaya') ?? 0,
                'catatan' => $this->request->getPost('catatan'),
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $userId,
            ];

            // Handle file upload jika ada file baru
            $files = $this->request->getFiles();
            if (!empty($files['foto_kerusakan'])) {
                $uploadedFiles = [];
                foreach ($files['foto_kerusakan'] as $file) {
                    if ($file->isValid() && !$file->hasMoved()) {
                        // Validasi file
                        if ($file->getSize() > 5 * 1024 * 1024) { // Max 5MB
                            continue;
                        }
                        
                        $newName = $file->getRandomName();
                        $file->move(WRITEPATH . 'uploads/repair_requests', $newName);
                        
                        $uploadedFiles[] = [
                            'filename' => $newName,
                            'original_name' => $file->getClientName(),
                            'path' => 'writable/uploads/repair_requests/' . $newName,
                            'size' => $file->getSize(),
                            'type' => $file->getClientMimeType(),
                            'uploaded_at' => date('Y-m-d H:i:s')
                        ];
                    }
                }
                
                if (!empty($uploadedFiles)) {
                    $data['foto_kerusakan'] = json_encode($uploadedFiles);
                }
            }

            // Update
            if ($this->repairModel->update($id, $data)) {
                return redirect()->to(base_url('general-service/repair-request'))
                    ->with('success', 'Pengajuan perbaikan berhasil diupdate');
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengupdate pengajuan');

        } catch (\Exception $e) {
            log_message('error', 'Update repair failed: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ===============================================
    // LIST REPAIR REQUESTS (WITH FILTER)
    // ===============================================
    public function index()
    {
        // 1. Ambil Parameter Filter
        $tipeAset  = $this->request->getGet('tipe_aset');
        $status    = $this->request->getGet('status');
        $siteId    = $this->request->getGet('site');
        $prioritas = $this->request->getGet('prioritas');
        $divisiId  = $this->request->getGet('divisi');
        $search    = $this->request->getGet('search');
        
        // Pagination params
        $perPage = 10;
        $page = (int) ($this->request->getGet('page') ?? 1);
        $offset = ($page - 1) * $perPage;

        // ========================================
        // BUILD QUERY dengan SITES (CORRECT)
        // ========================================
        $builder = $this->db->table('repair_requests');
        
        $builder->select('
            repair_requests.*,
            COALESCE(mess_data.nama_karyawan, workshop.name_karyawan, "-") as nama_karyawan,
            COALESCE(mess_data.nik, workshop.nik, "-") as nik,
            COALESCE(mess_divisi.name, workshop_divisi.name, "-") as divisi_name,
            COALESCE(
                site_from_repair.name,
                site_from_mess.name, 
                site_from_workshop.name, 
                "-"
            ) as site_name,
            COALESCE(
                repair_requests.lokasi_aset,
                mess_data.site_id, 
                workshop.site_id
            ) as site_id
        ', false);
        
        // ✅ JOIN MESS
        $builder->join('mess_data', 'mess_data.id = repair_requests.aset_id AND repair_requests.tipe_aset = "Mess"', 'left');
        $builder->join('divisions as mess_divisi', 'mess_divisi.id = mess_data.divisi_id', 'left');
        $builder->join('sites as site_from_mess', 'site_from_mess.id = mess_data.site_id', 'left');
        
        // ✅ JOIN WORKSHOP
        $builder->join('workshop', 'workshop.id = repair_requests.aset_id AND repair_requests.tipe_aset = "Workshop"', 'left');
        $builder->join('divisions as workshop_divisi', 'workshop_divisi.id = workshop.divisi_id', 'left');
        $builder->join('sites as site_from_workshop', 'site_from_workshop.id = workshop.site_id', 'left');
        
        // ✅ JOIN SITE dari repair_requests.lokasi_aset (jika ada)
        $builder->join('sites as site_from_repair', 'site_from_repair.id = repair_requests.lokasi_aset', 'left');

        // Base Filter
        $builder->where('repair_requests.deleted_at', null);

        // Apply Filters
        if ($tipeAset) {
            $builder->where('repair_requests.tipe_aset', $tipeAset);
        }
        
        if ($status) {
            $builder->where('repair_requests.status', $status);
        }
        
        if ($prioritas) {
            $builder->where('repair_requests.prioritas', $prioritas);
        }
        
        // ✅ FILTER BY SITE (gunakan ID karena semua kolom berisi ID)
        if ($siteId) {
            $builder->groupStart()
                ->where('repair_requests.lokasi_aset', $siteId)
                ->orWhere('mess_data.site_id', $siteId)
                ->orWhere('workshop.site_id', $siteId)
            ->groupEnd();
        }
        
        // FILTER BY DIVISI
        if ($divisiId) {
            $builder->groupStart()
                ->where('mess_data.divisi_id', $divisiId)
                ->orWhere('workshop.divisi_id', $divisiId)
            ->groupEnd();
        }
        
        // FILTER SEARCH
        if ($search) {
            $builder->groupStart()
                ->like('repair_requests.kode_pengajuan', $search)
                ->orLike('repair_requests.jenis_kerusakan', $search)
                ->orLike('mess_data.nama_karyawan', $search)
                ->orLike('workshop.name_karyawan', $search)
                ->orLike('mess_data.nik', $search)
                ->orLike('workshop.nik', $search)
                ->orLike('site_from_repair.name', $search) // Search by site name
                ->orLike('site_from_mess.name', $search)
                ->orLike('site_from_workshop.name', $search)
            ->groupEnd();
        }

        $builder->orderBy('repair_requests.created_at', 'DESC');

        // PAGINATION
        $total_pengajuan = $builder->countAllResults(false);
        $repairs = $builder->limit($perPage, $offset)->get()->getResultArray();
        
        $pager = \Config\Services::pager();
        $pager->store('default', $page, $perPage, $total_pengajuan);

        // Statistik
        $stats = $this->db->table('repair_requests')
            ->select('
                SUM(CASE WHEN status = "Pending" THEN 1 ELSE 0 END) as total_pending,
                SUM(CASE WHEN status = "Approved" THEN 1 ELSE 0 END) as total_approved,
                SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as total_completed,
                SUM(CASE WHEN status = "Rejected" THEN 1 ELSE 0 END) as total_rejected
            ')
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        // ✅ GET SITES LIST untuk Dropdown Filter
        $site_list = $this->db->table('sites')
            ->select('id, name')
            ->where('is_active', true)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();

        // GET DIVISI LIST
        $divisi_list = $this->divisiModel->where('is_deleted', false)->findAll();

        // Pass to view
        $this->data['title']            = 'Daftar Pengajuan Perbaikan';
        $this->data['repairs']          = $repairs;
        $this->data['pager']            = $pager;
        $this->data['currentPage']      = $page;
        $this->data['perPage']          = $perPage;
        $this->data['total_pengajuan']  = $total_pengajuan;
        $this->data['total_pending']    = $stats['total_pending'] ?? 0;
        $this->data['total_approved']   = $stats['total_approved'] ?? 0;
        $this->data['total_completed']  = $stats['total_completed'] ?? 0;
        $this->data['total_rejected']   = $stats['total_rejected'] ?? 0;
        $this->data['site_list']        = $site_list;
        $this->data['divisi_list']      = $divisi_list;

        return view('general_service/perbaikan/index', $this->data);
    }


    // ===============================================
    // UPDATE STATUS (APPROVE/REJECT/ETC)
    // ===============================================
    public function updateStatus($id)
    {
        $newStatus = $this->request->getPost('status');
        $catatan = $this->request->getPost('catatan');

        $validStatuses = ['Pending', 'Approved', 'In Progress', 'Completed', 'Rejected', 'Cancelled'];
        
        if (!in_array($newStatus, $validStatuses)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Status tidak valid'
            ]);
        }

        $this->db->transStart();

        try {
            $updateData = [
                'status' => $newStatus,
                'updated_by' => session()->get('user_id'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Set specific fields based on status
            switch ($newStatus) {
                case 'Approved':
                    $updateData['tanggal_disetujui'] = date('Y-m-d H:i:s');
                    $updateData['disetujui_oleh'] = session()->get('user_id');
                    $updateData['catatan_persetujuan'] = $catatan;
                    break;
                    
                case 'Rejected':
                    $updateData['tanggal_ditolak'] = date('Y-m-d H:i:s');
                    $updateData['ditolak_oleh'] = session()->get('user_id');
                    $updateData['alasan_penolakan'] = $catatan;
                    break;
                    
                case 'In Progress':
                    if (!$this->repairModel->find($id)['tanggal_mulai']) {
                        $updateData['tanggal_mulai'] = date('Y-m-d H:i:s');
                    }
                    break;
                    
                case 'Completed':
                    $updateData['tanggal_selesai'] = date('Y-m-d H:i:s');
                    $updateData['catatan_selesai'] = $catatan;
                    $updateData['progress_percentage'] = 100;
                    $updateData['biaya_aktual'] = $this->request->getPost('biaya_aktual');
                    break;
                    
                case 'Cancelled':
                    $updateData['tanggal_dibatalkan'] = date('Y-m-d H:i:s');
                    $updateData['dibatalkan_oleh'] = session()->get('user_id');
                    break;
            }

            $this->repairModel->update($id, $updateData);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            // Send notification
            $this->sendNotification($id, 'status_changed', $newStatus);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Status berhasil diupdate menjadi: ' . $newStatus
            ]);

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Update status failed: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal update status: ' . $e->getMessage()
            ]);
        }
    }

    // ===============================================
    // SEND NOTIFICATION (OPTIONAL)
    // ===============================================
    private function sendNotification($repairId, $eventType, $additionalData = null)
    {
        // Implement notification logic here
        // Email, SMS, Push notification, etc.
        
        log_message('info', "Notification sent for repair #{$repairId}, event: {$eventType}");
    }

    // ===============================================
    // DELETE (SOFT DELETE)
    // ===============================================
    public function delete($id)
    {
        try {
            $this->repairModel->update($id, [
                'deleted_at' => date('Y-m-d H:i:s'),
                'updated_by' => session()->get('user_id')
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Data berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Delete repair failed: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menghapus data'
            ]);
        }
    }

    // ========================================
    // APPROVE - Setujui pengajuan
    // ========================================
    public function approve($id)
    {
        try {
            $repair = $this->repairModel->find($id);
            
            if (!$repair) {
                return redirect()->back()->with('error', 'Data tidak ditemukan');
            }

            if ($repair['status'] != 'Pending') {
                return redirect()->back()->with('error', 'Hanya pengajuan dengan status Pending yang bisa disetujui');
            }

            $userId = session()->get('user')['id'] ?? null;
            if (!$userId) {
                return redirect()->back()->with('error', 'Session expired. Silakan login kembali.');
            }

            $data = [
                'status' => 'Approved',
                'tanggal_disetujui' => date('Y-m-d H:i:s'),
                'disetujui_oleh' => $userId,
                'catatan_persetujuan' => $this->request->getPost('catatan_persetujuan'),
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $userId, // ✅ TAMBAHKAN INI
            ];

            if ($this->repairModel->update($id, $data)) {
                log_message('info', "Repair request #{$id} approved by user #{$userId}");
                
                return redirect()->to(base_url('general-service/repair-request'))
                    ->with('success', 'Pengajuan perbaikan berhasil disetujui');
            }

            return redirect()->back()->with('error', 'Gagal menyetujui pengajuan');

        } catch (\Exception $e) {
            log_message('error', 'Approve repair failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ========================================
    // REJECT - Tolak pengajuan
    // ========================================
    public function reject($id)
{
    try {
        $repair = $this->repairModel->find($id);
        
        if (!$repair) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }

        if ($repair['status'] != 'Pending') {
            return redirect()->back()->with('error', 'Hanya pengajuan dengan status Pending yang bisa ditolak');
        }

        $alasanPenolakan = $this->request->getPost('alasan_penolakan');
        if (empty($alasanPenolakan)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Alasan penolakan harus diisi');
        }

        $userId = session()->get('user')['id'] ?? null;
        if (!$userId) {
            return redirect()->back()->with('error', 'Session expired. Silakan login kembali.');
        }

        $data = [
            'status' => 'Rejected',
            'tanggal_ditolak' => date('Y-m-d H:i:s'),
            'ditolak_oleh' => $userId,
            'alasan_penolakan' => $alasanPenolakan,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $userId, // ✅ TAMBAHKAN INI
        ];

        if ($this->repairModel->update($id, $data)) {
            log_message('info', "Repair request #{$id} rejected by user #{$userId}");
            
            return redirect()->to(base_url('general-service/repair-request'))
                ->with('success', 'Pengajuan perbaikan telah ditolak');
        }

        return redirect()->back()->with('error', 'Gagal menolak pengajuan');

    } catch (\Exception $e) {
        log_message('error', 'Reject repair failed: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

    // ========================================
    // START - Mulai perbaikan
    // ========================================
    public function start($id)
    {
        try {
            $repair = $this->repairModel->find($id);
            
            if (!$repair) {
                return redirect()->back()->with('error', 'Data tidak ditemukan');
            }

            // Cek status - hanya Approved yang bisa distart
            if ($repair['status'] != 'Approved') {
                return redirect()->back()->with('error', 'Hanya pengajuan yang sudah disetujui yang bisa dimulai');
            }

            // Prepare data
            $data = [
                'status' => 'In Progress',
                'tanggal_mulai' => date('Y-m-d H:i:s'),
                'penanggung_jawab' => $this->request->getPost('penanggung_jawab'),
                'progress_percentage' => 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            // Update
            if ($this->repairModel->update($id, $data)) {
                log_message('info', "Repair request #{$id} started");
                
                return redirect()->to(base_url('general-service/repair-request/detail/' . $id))
                    ->with('success', 'Perbaikan telah dimulai');
            }

            return redirect()->back()->with('error', 'Gagal memulai perbaikan');

        } catch (\Exception $e) {
            log_message('error', 'Start repair failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ========================================
    // COMPLETE - Selesaikan perbaikan
    // ========================================
    public function complete($id)
    {
        try {
            $repair = $this->repairModel->find($id);
            
            if (!$repair) {
                return redirect()->back()->with('error', 'Data tidak ditemukan');
            }

            // Cek status - hanya In Progress yang bisa dicomplete
            if ($repair['status'] != 'In Progress') {
                return redirect()->back()->with('error', 'Hanya perbaikan yang sedang berjalan yang bisa diselesaikan');
            }

            // Validation
            $rules = [
                'biaya_aktual' => 'required|numeric|greater_than_equal_to[0]',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()
                    ->withInput()
                    ->with('errors', $this->validator->getErrors());
            }

            // Handle file upload foto selesai
            $fotoSelesai = [];
            $files = $this->request->getFiles();
            if (!empty($files['foto_selesai'])) {
                foreach ($files['foto_selesai'] as $file) {
                    if ($file->isValid() && !$file->hasMoved()) {
                        $newName = $file->getRandomName();
                        $file->move(WRITEPATH . 'uploads/repair_requests', $newName);
                        
                        $fotoSelesai[] = [
                            'filename' => $newName,
                            'original_name' => $file->getClientName(),
                            'path' => 'writable/uploads/repair_requests/' . $newName,
                            'size' => $file->getSize(),
                            'type' => $file->getClientMimeType(),
                            'uploaded_at' => date('Y-m-d H:i:s')
                        ];
                    }
                }
            }

            // Prepare data
            $data = [
                'status' => 'Completed',
                'tanggal_selesai' => date('Y-m-d H:i:s'),
                'biaya_aktual' => $this->request->getPost('biaya_aktual'),
                'catatan_selesai' => $this->request->getPost('catatan_selesai'),
                'progress_percentage' => 100,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if (!empty($fotoSelesai)) {
                $data['foto_selesai'] = json_encode($fotoSelesai);
            }

            // Update
            if ($this->repairModel->update($id, $data)) {
                log_message('info', "Repair request #{$id} completed");
                
                return redirect()->to(base_url('general-service/repair-request/detail/' . $id))
                    ->with('success', 'Perbaikan telah selesai');
            }

            return redirect()->back()->with('error', 'Gagal menyelesaikan perbaikan');

        } catch (\Exception $e) {
            log_message('error', 'Complete repair failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ========================================
    // CANCEL - Batalkan pengajuan
    // ========================================
    public function cancel($id)
    {
        try {
            $repair = $this->repairModel->find($id);
            
            if (!$repair) {
                return redirect()->back()->with('error', 'Data tidak ditemukan');
            }

            // Cek status - tidak bisa cancel jika sudah Completed atau Rejected
            if (in_array($repair['status'], ['Completed', 'Rejected'])) {
                return redirect()->back()->with('error', 'Pengajuan yang sudah selesai atau ditolak tidak bisa dibatalkan');
            }

            $userId = session()->get('user')['id'] ?? null;

            // Prepare data
            $data = [
                'status' => 'Cancelled',
                'tanggal_dibatalkan' => date('Y-m-d H:i:s'),
                'dibatalkan_oleh' => $userId,
                'catatan' => ($repair['catatan'] ?? '') . "\n[Dibatalkan pada " . date('d/m/Y H:i') . "]",
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            // Update
            if ($this->repairModel->update($id, $data)) {
                log_message('info', "Repair request #{$id} cancelled by user #{$userId}");
                
                return redirect()->to(base_url('general-service/repair-request'))
                    ->with('success', 'Pengajuan perbaikan telah dibatalkan');
            }

            return redirect()->back()->with('error', 'Gagal membatalkan pengajuan');

        } catch (\Exception $e) {
            log_message('error', 'Cancel repair failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ========================================
    // UPDATE PROGRESS - Update progress percentage
    // ========================================
    public function updateProgress($id)
    {
        try {
            $repair = $this->repairModel->find($id);
            
            if (!$repair || $repair['status'] != 'In Progress') {
                return redirect()->back()->with('error', 'Hanya perbaikan yang sedang berjalan yang bisa diupdate progressnya');
            }

            $progress = $this->request->getPost('progress_percentage');
            
            if ($progress < 0 || $progress > 100) {
                return redirect()->back()->with('error', 'Progress harus antara 0-100');
            }

            $data = [
                'progress_percentage' => $progress,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if ($this->repairModel->update($id, $data)) {
                return redirect()->back()->with('success', "Progress diupdate menjadi {$progress}%");
            }

            return redirect()->back()->with('error', 'Gagal mengupdate progress');

        } catch (\Exception $e) {
            log_message('error', 'Update progress failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}