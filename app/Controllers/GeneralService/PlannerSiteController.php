<?php

namespace App\Controllers\GeneralService;

use App\Controllers\BaseController;
use App\Models\MessModel;
use App\Services\Code\CodeGeneratorService;
use App\Models\RepairRequestModel;
use App\Models\DivisionModel;
use App\Models\RepairDocumentModel;

class PlannerSiteController extends BaseController
{
    protected $messModel;
    protected $helpers = ['form', 'url', 'security'];
    protected $codeService;
    protected $repairRequestModel;
    protected $divisiModel;

    public function __construct()
    {
        $this->messModel         = new MessModel();
        $this->codeService       = new CodeGeneratorService();
        $this->repairRequestModel = new RepairRequestModel();
        $this->divisiModel       = new DivisionModel();
    }

    /* ==========================================================
     * INDEX — List Pekerjaan
     * ========================================================== */
    public function index()
    {
        $db      = \Config\Database::connect();
        $perPage = (int)($this->request->getGet('per_page') ?? 10);
        $page    = (int)($this->request->getGet('page') ?? 1);
        $offset  = ($page - 1) * $perPage;

        $filters = [
            'tipe_aset' => $this->request->getGet('tipe_aset'),
            'site'      => $this->request->getGet('site'),
            'status'    => $this->request->getGet('status'),
            'prioritas' => $this->request->getGet('prioritas'),
            'search'    => $this->request->getGet('search'),
        ];

        $builder = $db->table('repair_requests rr')
            ->select([
                'rr.*',
                'COALESCE(md.nama_karyawan, wd.name_karyawan) AS nama_karyawan',
                'COALESCE(md.nik, wd.nik) AS nik',
                'COALESCE(s.name, rr.lokasi_aset) AS site_name',
                'rd.file_path AS dokumen_ttd_path',
            ])
            ->join('mess_data md',     'md.id = rr.aset_id AND rr.tipe_aset = "Mess"',     'left')
            ->join('workshop wd',      'wd.id = rr.aset_id AND rr.tipe_aset = "Workshop"', 'left')
            ->join('sites s',          's.id = COALESCE(md.site_id, wd.site_id)',           'left')
            ->join('repair_documents rd', 'rd.repair_id = rr.id AND rd.is_latest = 1',     'left')
            ->whereIn('rr.status', ['Approved', 'In Progress', 'Completed']);

        // ── Filter ──────────────────────────────────────────────
        if (!empty($filters['tipe_aset'])) {
            $builder->where('rr.tipe_aset', $filters['tipe_aset']);
        }
        if (!empty($filters['site'])) {
            $builder->where('COALESCE(md.site_id, wd.site_id)', $filters['site']);
        }
        if (!empty($filters['status'])) {
            $builder->where('rr.status', $filters['status']);
        }
        if (!empty($filters['prioritas'])) {
            $builder->where('rr.prioritas', $filters['prioritas']);
        }
        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('rr.kode_pengajuan', $filters['search'])
                ->orLike('rr.jenis_kerusakan', $filters['search'])
                ->orLike('md.nama_karyawan', $filters['search'])
                ->orLike('wd.name_karyawan', $filters['search'])
            ->groupEnd();
        }

        // ── Total (countAllResults false = tidak reset builder) ──
        $total   = $builder->countAllResults(false);

        // ── Data ────────────────────────────────────────────────
        $repairs = $builder
            ->orderBy('rr.tanggal_disetujui', 'DESC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        // ── Stats ────────────────────────────────────────────────
        $stats = $db->query("
            SELECT
                SUM(status = 'Approved')    AS approved,
                SUM(status = 'In Progress') AS inprogress,
                SUM(status = 'Completed')   AS completed
            FROM repair_requests
            WHERE status IN ('Approved', 'In Progress', 'Completed')
        ")->getRowArray();

        // ── Pager ────────────────────────────────────────────────
        $pager = \Config\Services::pager();
        $pager->makeLinks($page, $perPage, $total);

        // ── View ─────────────────────────────────────────────────
        $this->data['repairs']         = $repairs;
        $this->data['pager']           = $pager;
        $this->data['stat_total']      = $total;
        $this->data['stat_approved']   = (int)($stats['approved']   ?? 0);
        $this->data['stat_inprogress'] = (int)($stats['inprogress'] ?? 0);
        $this->data['stat_completed']  = (int)($stats['completed']  ?? 0);
        $this->data['site_list']       = $db->table('sites')->get()->getResultArray();
        $this->data['title']           = 'List Pekerjaan Saya';

        return view('general_service/planner/index', $this->data);
    }

    /* ==========================================================
     * DETAIL — Halaman detail + aksi Planner Site
     * ========================================================== */
    public function detail(int $id)
    {
        $db = \Config\Database::connect();

        $repair = $db->table('repair_requests rr')
            ->select([
                'rr.*',
                'COALESCE(md.nama_karyawan, wd.name_karyawan) AS nama_karyawan',
                'COALESCE(md.nik, wd.nik) AS nik',
                'COALESCE(s.name, rr.lokasi_aset) AS site_name',
                'COALESCE(md.luasan_mess, wd.luasan) AS luas_aset',
            ])
            ->join('mess_data md',  'md.id = rr.aset_id AND rr.tipe_aset = "Mess"',     'left')
            ->join('workshop wd',   'wd.id = rr.aset_id AND rr.tipe_aset = "Workshop"', 'left')
            ->join('sites s',       's.id = COALESCE(md.site_id, wd.site_id)',           'left')
            ->where('rr.id', $id)
            ->get()->getRowArray();

        if (!$repair) {
            return redirect()->to(base_url('general-service/planner-site'))
                ->with('error', 'Data tidak ditemukan.');
        }

        // Decode JSON foto
        $repair['foto_kerusakan'] = !empty($repair['foto_kerusakan'])
            ? json_decode($repair['foto_kerusakan'], true) : [];

        // Dokumen TTD terbaru
        $docModel = new RepairDocumentModel();
        $dokumenLatest = $docModel->getLatest($id);

        // Foto bukti pekerjaan
        $fotoBukti = $db->table('repair_foto_bukti')
            ->where('repair_id', $id)
            ->orderBy('uploaded_at', 'DESC')
            ->get()->getResultArray();

        $this->data['repair']         = $repair;
        $this->data['dokumen_latest'] = $dokumenLatest;
        $this->data['foto_bukti']     = $fotoBukti;
        $this->data['title']          = 'Detail Pekerjaan — ' . $repair['kode_pengajuan'];

        return view('general_service/planner/detail', $this->data);
    }

    /* ==========================================================
     * MULAI PEKERJAAN — Approved → In Progress
     * ========================================================== */
    public function mulai(int $id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request.']);
        }

        $repair = $this->repairRequestModel->find($id);
        if (!$repair) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data tidak ditemukan.']);
        }
        if ($repair['status'] !== 'Approved') {
            return $this->response->setJSON(['success' => false, 'message' => 'Status bukan Approved.']);
        }

        $this->repairRequestModel->update($id, [
            'status'        => 'In Progress',
            'tanggal_mulai' => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['success' => true, 'message' => 'Pekerjaan dimulai.']);
    }

    /* ==========================================================
     * SELESAI — In Progress → Completed
     * ========================================================== */
    public function selesai(int $id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request.']);
        }

        $repair = $this->repairRequestModel->find($id);
        if (!$repair) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data tidak ditemukan.']);
        }
        if ($repair['status'] !== 'In Progress') {
            return $this->response->setJSON(['success' => false, 'message' => 'Status bukan In Progress.']);
        }

        $biaya   = $this->request->getPost('biaya_aktual');
        $catatan = $this->request->getPost('catatan_selesai') ?? '';

        if (empty($biaya) || (int)$biaya <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Biaya aktual wajib diisi.']);
        }

        $this->repairRequestModel->update($id, [
            'status'           => 'Completed',
            'biaya_aktual'     => (int)$biaya,
            'catatan_selesai'  => $catatan,
            'tanggal_selesai'  => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['success' => true, 'message' => 'Pekerjaan selesai.']);
    }

    /* ==========================================================
     * UPLOAD FOTO BUKTI
     * ========================================================== */
    public function uploadFotoBukti(int $repairId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request.']);
        }

        $files = $this->request->getFileMultiple('foto_bukti');
        if (empty($files) || !$files[0]->isValid()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada file yang dikirim.']);
        }
        if (count($files) > 5) {
            return $this->response->setJSON(['success' => false, 'message' => 'Maksimal 5 foto sekaligus.']);
        }

        $uploadDir = FCPATH . 'uploads/foto_bukti/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

        $db    = \Config\Database::connect();
        $saved = [];

        foreach ($files as $file) {
            if (!$file->isValid() || $file->hasMoved()) continue;

            if ($file->getSize() > 2 * 1024 * 1024) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "File \"{$file->getClientName()}\" melebihi batas 2MB.",
                ]);
            }
            if (!str_starts_with($file->getMimeType(), 'image/')) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "File \"{$file->getClientName()}\" bukan gambar.",
                ]);
            }

            $namaFile = 'BUKTI_' . $repairId . '_' . time() . '_' . rand(100, 999)
                      . '.' . $file->getExtension();
            $file->move($uploadDir, $namaFile);

            $db->table('repair_foto_bukti')->insert([
                'repair_id'   => $repairId,
                'file_path'   => 'uploads/foto_bukti/' . $namaFile,
                'file_name'   => $file->getClientName(),
                'file_size'   => $file->getSize(),
                'uploaded_by' => user_id(),
                'uploaded_at' => date('Y-m-d H:i:s'),
            ]);

            $saved[] = [
                'id'        => $db->insertID(),
                'file_path' => 'uploads/foto_bukti/' . $namaFile,
                'file_name' => $file->getClientName(),
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => count($saved) . ' foto berhasil diupload.',
            'fotos'   => $saved,
        ]);
    }

    /* ==========================================================
     * HAPUS FOTO BUKTI
     * ========================================================== */
    public function hapusFotoBukti(int $fotoId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request.']);
        }

        $db   = \Config\Database::connect();
        $foto = $db->table('repair_foto_bukti')->where('id', $fotoId)->get()->getRowArray();

        if (!$foto) {
            return $this->response->setJSON(['success' => false, 'message' => 'File tidak ditemukan.']);
        }

        // Hapus file fisik
        $filePath = FCPATH . $foto['file_path'];
        if (file_exists($filePath)) unlink($filePath);

        $db->table('repair_foto_bukti')->delete(['id' => $fotoId]);

        return $this->response->setJSON(['success' => true, 'message' => 'Foto dihapus.']);
    }
}