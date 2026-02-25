<?php

namespace App\Controllers\GeneralService;
use App\Controllers\BaseController;
use App\Models\FasilityModel;
use App\Models\EmployeeRecruitmentModel;
use App\Controllers\BaseApiController;
use App\Models\MessModel;
use App\Models\WorkshopModel;
use App\Models\DivisionModel;
use App\Models\SiteModel;

class FasilityController extends BaseApiController {
    protected $data = [];
    protected $messModel;
    protected $workshopModel;
    protected $divisiModel;

    public function __construct()
    {
        $this->messModel     = new MessModel();
        $this->workshopModel = new WorkshopModel();
        $this->divisiModel   = new DivisionModel();
    }
    public function index()
{
    $db = \Config\Database::connect();

    // ── 1. TOTAL ASET ──────────────────────────────────────────────────────
    $total_mess     = $db->table('mess_data')->where('is_deleted', 0)->countAllResults();
    $total_workshop = $db->table('workshop')->where('is_deleted', 0)->countAllResults();

    $total_luas_mess     = $db->table('mess_data')->selectSum('luasan_mess')->where('is_deleted', 0)->get()->getRow()->luasan_mess ?? 0;
    $total_luas_workshop = $db->table('workshop')->selectSum('luasan')->where('is_deleted', 0)->get()->getRow()->luasan ?? 0;

    // ── 2. STATUS KEPEMILIKAN ──────────────────────────────────────────────
    $total_milik_mess = $db->table('mess_data')
        ->where('is_deleted', 0)
        ->where('status_kepemilikan', 'Milik PT Bagong Dekaka Makmur')
        ->countAllResults();

    $total_sewa_mess = $db->table('mess_data')
        ->where('is_deleted', 0)
        ->where('status_kepemilikan', 'Sewa')
        ->countAllResults();

    $total_milik_workshop = $db->table('workshop')
        ->where('is_deleted', 0)
        ->where('status_workshop', 'Milik PT Bagong Dekaka Makmur')
        ->countAllResults();

    $total_sewa_workshop = $db->table('workshop')
        ->where('is_deleted', 0)
        ->where('status_workshop', 'Sewa')
        ->countAllResults();

    // ── 3. REPAIR STATS ────────────────────────────────────────────────────
    $repairStats = $db->table('repair_requests')
        ->select("
            SUM(CASE WHEN status = 'Pending'     THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'Approved'    THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as inprogress,
            SUM(CASE WHEN status = 'Completed'   THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'Rejected'    THEN 1 ELSE 0 END) as rejected,
            SUM(CASE WHEN status = 'Cancelled'   THEN 1 ELSE 0 END) as cancelled
        ")
        ->where('is_deleted', 0)
        ->get()->getRowArray();

    // ── 4. REPAIR PER KATEGORI ─────────────────────────────────────────────
    $repair_by_kategori = $db->table('repair_requests')
        ->select('kategori_kerusakan as kategori, COUNT(*) as total')
        ->where('is_deleted', 0)
        ->whereIn('kategori_kerusakan', ['Ringan','Sedang','Berat','Darurat'])
        ->groupBy('kategori_kerusakan')
        ->orderBy("FIELD(kategori_kerusakan,'Ringan','Sedang','Berat','Darurat')")
        ->get()->getResultArray();

    // Pastikan semua kategori ada (isi 0 jika tidak ada data)
    $kategoriMap = ['Ringan'=>0,'Sedang'=>0,'Berat'=>0,'Darurat'=>0];
    foreach ($repair_by_kategori as $r) {
        $kategoriMap[$r['kategori']] = (int)$r['total'];
    }
    $repair_by_kategori = array_map(fn($k,$v) => ['kategori'=>$k,'total'=>$v], array_keys($kategoriMap), $kategoriMap);

    // ── 5. SITE BREAKDOWN ──────────────────────────────────────────────────
    // Wrap dalam subquery agar HAVING & ORDER BY bisa referensi alias aggregate
    $site_breakdown_raw = $db->query("
        SELECT * FROM (
            SELECT
                s.name AS site_name,
                COALESCE(SUM(CASE WHEN combined.src = 'mess'     THEN 1 ELSE 0 END), 0) AS total_mess,
                COALESCE(SUM(CASE WHEN combined.src = 'workshop' THEN 1 ELSE 0 END), 0) AS total_workshop,
                COALESCE(SUM(CASE WHEN combined.src = 'repair'   THEN 1 ELSE 0 END), 0) AS total_repair
            FROM sites s
            LEFT JOIN (
                SELECT site_id AS site_ref, 'mess' AS src
                    FROM mess_data WHERE is_deleted = 0
                UNION ALL
                SELECT site_id AS site_ref, 'workshop' AS src
                    FROM workshop WHERE is_deleted = 0
                UNION ALL
                SELECT lokasi_aset AS site_ref, 'repair' AS src
                    FROM repair_requests WHERE is_deleted = 0
            ) combined ON combined.site_ref = s.id
            WHERE s.is_active = 1
            GROUP BY s.id, s.name
        ) sub
        WHERE (sub.total_mess + sub.total_workshop) > 0
        ORDER BY (sub.total_mess + sub.total_workshop) DESC
        LIMIT 10
    ")->getResultArray();

    // ── 6. TREND BULANAN (6 bulan terakhir) ───────────────────────────────
    // Gunakan mon_start (bukan alias bulan) sebagai GROUP BY key
    $trend_monthly = $db->query("
        SELECT
            DATE_FORMAT(months.mon_start, '%b')  AS bulan,
            DATE_FORMAT(months.mon_start, '%Y-%m') AS bulan_sort,
            COUNT(DISTINCT m.id) AS total_mess,
            COUNT(DISTINCT w.id) AS total_ws,
            COUNT(DISTINCT r.id) AS total_repair
        FROM (
            SELECT DATE_FORMAT(NOW() - INTERVAL n MONTH, '%Y-%m-01') AS mon_start
            FROM (
                SELECT 0 AS n UNION SELECT 1 UNION SELECT 2
                UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
            ) nums
        ) months
        LEFT JOIN mess_data m
            ON DATE_FORMAT(m.created_at, '%Y-%m') = DATE_FORMAT(months.mon_start, '%Y-%m')
            AND m.is_deleted = 0
        LEFT JOIN workshop w
            ON DATE_FORMAT(w.created_at, '%Y-%m') = DATE_FORMAT(months.mon_start, '%Y-%m')
            AND w.is_deleted = 0
        LEFT JOIN repair_requests r
            ON DATE_FORMAT(r.created_at, '%Y-%m') = DATE_FORMAT(months.mon_start, '%Y-%m')
            AND r.is_deleted = 0
        GROUP BY months.mon_start
        ORDER BY months.mon_start ASC
    ")->getResultArray();

    // ── PASS TO VIEW ───────────────────────────────────────────────────────
    return view('general_service/index', [
        'title'                => 'Dashboard General Service',

        // Totals
        'total_mess'           => $total_mess,
        'total_workshop'       => $total_workshop,
        'total_luas_mess'      => $total_luas_mess,
        'total_luas_workshop'  => $total_luas_workshop,

        // Kepemilikan
        'total_milik_mess'     => $total_milik_mess,
        'total_sewa_mess'      => $total_sewa_mess,
        'total_milik_workshop' => $total_milik_workshop,
        'total_sewa_workshop'  => $total_sewa_workshop,

        // Repair stats
        'total_repair_pending'    => (int)($repairStats['pending']    ?? 0),
        'total_repair_approved'   => (int)($repairStats['approved']   ?? 0),
        'total_repair_inprogress' => (int)($repairStats['inprogress'] ?? 0),
        'total_repair_completed'  => (int)($repairStats['completed']  ?? 0),
        'total_repair_rejected'   => (int)($repairStats['rejected']   ?? 0),
        'total_repair_cancelled'  => (int)($repairStats['cancelled']  ?? 0),

        // Charts data
        'repair_by_kategori'   => $repair_by_kategori,
        'site_breakdown'       => $site_breakdown_raw,
        'trend_monthly'        => $trend_monthly,
    ]);
}

    public function asset()
    {
        // ================= DIVISI =================
        $this->data['divisi_list'] = $this->divisiModel->findAll();

        // ================= MESS =================
        $messBuilder = $this->messModel
            ->select('mess_data.*, divisions.name AS divisi_name, sites.name AS site_name')
            ->join('divisions', 'divisions.id = mess_data.divisi_id', 'left')
            ->join('sites', 'sites.id = mess_data.site_id', 'left')
            ->where('mess_data.is_deleted', false);

        if ($this->request->getGet('mess_divisi')) {
            $messBuilder->where('mess_data.divisi_id', $this->request->getGet('mess_divisi'));
        }

        if ($this->request->getGet('mess_job_site')) {
            $messBuilder->where('mess_data.site_id', $this->request->getGet('mess_job_site'));
        }

        if ($this->request->getGet('mess_status')) {
            $messBuilder->where('mess_data.status_kepemilikan', $this->request->getGet('mess_status'));
        }

        if ($this->request->getGet('mess_search')) {
            $search = $this->request->getGet('mess_search');
            $messBuilder->groupStart()
                ->like('mess_data.nama_karyawan', $search)
                ->orLike('mess_data.nik', $search)
                ->groupEnd();
        }

        // Total DULU (pakai countAllResults sebelum paginate)
        $this->data['total_mess'] = $messBuilder->countAllResults(false); // false = jangan reset builder

        // Baru paginate
        $this->data['mess_data']  = $messBuilder->paginate(10, 'mess');
        $this->data['pager_mess'] = $this->messModel->pager;


        // ================= WORKSHOP =================
        $workshopBuilder = $this->workshopModel
            ->select('workshop.*, divisions.name AS divisi_name, sites.name AS site_name')
            ->join('divisions', 'divisions.id = workshop.divisi_id', 'left')
            ->join('sites', 'sites.id = workshop.site_id', 'left')
            ->where('workshop.is_deleted', false);

        if ($this->request->getGet('workshop_divisi')) {
            $workshopBuilder->where('workshop.divisi_id', $this->request->getGet('workshop_divisi'));
        }

        if ($this->request->getGet('workshop_job_site')) {
            $workshopBuilder->where('workshop.site_id', $this->request->getGet('workshop_job_site'));
        }

        if ($this->request->getGet('workshop_status')) {
            $workshopBuilder->where('workshop.status_workshop', $this->request->getGet('workshop_status'));
        }

        if ($this->request->getGet('workshop_search')) {
            $search = $this->request->getGet('workshop_search');
            $workshopBuilder->groupStart()
                ->like('workshop.name_karyawan', $search)
                ->orLike('workshop.nik', $search)
                ->groupEnd();
        }

        // Total DULU
        $this->data['total_workshop'] = $workshopBuilder->countAllResults(false);

        // Baru paginate
        $this->data['workshop_data']  = $workshopBuilder->paginate(10, 'workshop');
        $this->data['pager_workshop'] = $this->workshopModel->pager;

        return view('general_service/asset/index', $this->data);
    }

    public function mess() {
        $this->data['divisi_list'] = $this->divisiModel->findAll();
        return view('general_service/mess/index', $this->data);
    }

    public function workshop() {
        $this->data['divisi_list'] = $this->divisiModel->findAll();
        return view('general_service/workshop/index', $this->data);
    }
    
    public function getSiteByDivisiCode() {
        if ($this->request->isAJAX()) {
            $divisiId = $this->request->getPost('divisi_id');
            $siteModel = new SiteModel();
            
            $sites = $siteModel->where('business_unit_id', $divisiId)
                               ->where('is_active', true)
                               ->orderBy('name', 'ASC')
                               ->findAll();

            return $this->response->setJSON($sites);
        }
    }
    public function searchEmployees() {
        $search = strtolower(trim(
            $this->request->getGet('search') ?? 
            $this->request->getPost('search') ?? 
            ''
        ));

        if (strlen($search) < 2) {
            return $this->response->setJSON([]);
        }

        try {
            // ✅ Langsung pakai query builder
            $db = \Config\Database::connect('pg');
            
            $builder = $db->table('employees_recruitment');
            $builder->select('employee_name, employee_number');
            $builder->groupStart()
                ->like('LOWER(employee_name)', $search)
                ->orLike('LOWER(employee_number)', $search)
            ->groupEnd();
            $builder->limit(20);
            
            $results = $builder->get()->getResultArray();

            return $this->response->setJSON($results);

        } catch (\Throwable $e) {
            log_message('error', 'Employee Search Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }
    
}