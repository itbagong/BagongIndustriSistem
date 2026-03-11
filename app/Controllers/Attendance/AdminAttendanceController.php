<?php

namespace App\Controllers\Attendance\admin;   // ← sesuai route kamu

use App\Controllers\BaseController;
use App\Models\AttendanceModel;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAttendanceController extends BaseController
{
    protected AttendanceModel $model;

    public function __construct()
    {
        $this->model = new AttendanceModel();
    }

    // ─────────────────────────────────────────────────────────────
    // GET /attendance/admin  →  dashboard
    // ─────────────────────────────────────────────────────────────
    public function dashboard()
    {
        $db    = \Config\Database::connect();
        $today = date('Y-m-d');
        $month = date('Y-m');

        $totalKaryawan = $db->table('users')->where('role !=', 'admin')->countAllResults();

        $hadirHariIni = $db->table('attendances')
            ->where('type', 'masuk')->where('DATE(created_at)', $today)->countAllResults();

        $telatHariIni = $db->table('attendances')
            ->where('type', 'masuk')->where('DATE(created_at)', $today)
            ->where('HOUR(created_at) >=', 8)->countAllResults();

        $sudahPulang = $db->table('attendances')
            ->where('type', 'pulang')->where('DATE(created_at)', $today)->countAllResults();

        $belumAbsen    = max(0, $totalKaryawan - $hadirHariIni);
        $totalBulanIni = $db->table('attendances')
            ->where("DATE_FORMAT(created_at,'%Y-%m')", $month)->countAllResults();

        // Hitung hari kerja bulan ini s/d hari ini
        $hariKerja = 0;
        $period    = new \DatePeriod(
            new \DateTime(date('Y-m-01')),
            new \DateInterval('P1D'),
            (new \DateTime($today))->modify('+1 day')
        );
        foreach ($period as $d) {
            if ($d->format('N') < 6) $hariKerja++;
        }

        $hariHadirBulanIni = (int) $db->query(
            "SELECT COUNT(DISTINCT DATE(created_at)) cnt FROM attendances
             WHERE type='masuk' AND DATE_FORMAT(created_at,'%Y-%m')=? AND HOUR(created_at)<8", [$month]
        )->getRow()->cnt;

        $hariTelatBulanIni = (int) $db->query(
            "SELECT COUNT(DISTINCT DATE(created_at)) cnt FROM attendances
             WHERE type='masuk' AND DATE_FORMAT(created_at,'%Y-%m')=? AND HOUR(created_at)>=8", [$month]
        )->getRow()->cnt;

        $hariAbsenBulanIni = max(0, $hariKerja - $hariHadirBulanIni - $hariTelatBulanIni);

        $todayRecords = $db->query(
            "SELECT a.*, u.name as user_name, e.department
             FROM attendances a
             LEFT JOIN users u ON u.id = a.user_id
             LEFT JOIN employees e ON e.user_id = a.user_id
             WHERE a.type='masuk' AND DATE(a.created_at)=?
             ORDER BY a.created_at ASC LIMIT 10", [$today]
        )->getResultArray();

        $topLate = $db->query(
            "SELECT u.name as user_name, e.department, COUNT(*) as jumlah_telat
             FROM attendances a
             LEFT JOIN users u ON u.id = a.user_id
             LEFT JOIN employees e ON e.user_id = a.user_id
             WHERE a.type='masuk' AND DATE_FORMAT(a.created_at,'%Y-%m')=? AND HOUR(a.created_at)>=8
             GROUP BY a.user_id ORDER BY jumlah_telat DESC LIMIT 5", [$month]
        )->getResultArray();

        $weekChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $weekChart[] = [
                'label' => date('D d/m', strtotime($d)),
                'hadir' => (int) $db->query("SELECT COUNT(*) c FROM attendances WHERE type='masuk' AND DATE(created_at)=? AND HOUR(created_at)<8",  [$d])->getRow()->c,
                'telat' => (int) $db->query("SELECT COUNT(*) c FROM attendances WHERE type='masuk' AND DATE(created_at)=? AND HOUR(created_at)>=8", [$d])->getRow()->c,
            ];
        }

        return view('attendance/admin/admin_dashboard', [
            'stats' => [
                'total_karyawan'       => $totalKaryawan,
                'hadir_hari_ini'       => $hadirHariIni,
                'telat_hari_ini'       => $telatHariIni,
                'belum_absen'          => $belumAbsen,
                'sudah_pulang'         => $sudahPulang,
                'total_bulan_ini'      => $totalBulanIni,
                'hari_kerja_bulan_ini' => $hariKerja,
                'hari_hadir_bulan_ini' => $hariHadirBulanIni,
                'hari_telat_bulan_ini' => $hariTelatBulanIni,
                'hari_absen_bulan_ini' => $hariAbsenBulanIni,
            ],
            'todayRecords' => $todayRecords,
            'topLate'      => $topLate,
            'weekChart'    => $weekChart,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // GET /attendance/admin/history  →  list + DataTable
    // ─────────────────────────────────────────────────────────────
    public function history()
    {
        $db = \Config\Database::connect();

        $departments = array_column(
            $db->query("SELECT DISTINCT department FROM employees WHERE department IS NOT NULL ORDER BY department")
               ->getResultArray(),
            'department'
        );

        return view('attendance/admin/admin_index', ['departments' => $departments]);
    }

    // ─────────────────────────────────────────────────────────────
    // POST /attendance/admin/data  →  DataTable server-side
    // ─────────────────────────────────────────────────────────────
    public function data()
    {
        $db  = \Config\Database::connect();
        $req = $this->request;

        $draw       = (int) $req->getPost('draw');
        $start      = (int) $req->getPost('start');
        $length     = (int) $req->getPost('length');
        $search     = $req->getPost('search')['value'] ?? '';
        $dateFrom   = $req->getPost('date_from')  ?: date('Y-m-01');
        $dateTo     = $req->getPost('date_to')    ?: date('Y-m-d');
        $type       = $req->getPost('type')       ?: '';
        $status     = $req->getPost('status')     ?: '';
        $department = $req->getPost('department') ?: '';

        $base   = "FROM attendances a
                   LEFT JOIN users u ON u.id = a.user_id
                   LEFT JOIN employees e ON e.user_id = a.user_id
                   WHERE DATE(a.created_at) BETWEEN ? AND ?";
        $params = [$dateFrom, $dateTo];

        if ($type)       { $base .= " AND a.type=?";        $params[] = $type; }
        if ($department) { $base .= " AND e.department=?";  $params[] = $department; }
        if ($status === 'telat') $base .= " AND a.type='masuk' AND HOUR(a.created_at)>=8";
        if ($status === 'tepat') $base .= " AND a.type='masuk' AND HOUR(a.created_at)<8";
        if ($search) {
            $base  .= " AND (u.name LIKE ? OR e.nik LIKE ? OR a.address LIKE ?)";
            $like   = "%$search%";
            $params = array_merge($params, [$like, $like, $like]);
        }

        $total = (int) $db->query("SELECT COUNT(*) c $base", $params)->getRow()->c;

        $colMap   = [1=>'DATE(a.created_at)',3=>'u.name',4=>'e.department',6=>'TIME(a.created_at)',7=>'a.type'];
        $orderCol = (int) ($req->getPost('order')[0]['column'] ?? 1);
        $orderDir = strtolower($req->getPost('order')[0]['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
        $orderSql = $colMap[$orderCol] ?? 'a.created_at';

        $rows = $db->query(
            "SELECT a.id,
                    DATE(a.created_at) as tanggal,
                    TIME(a.created_at) as jam,
                    e.nik, u.name as nama, e.department,
                    a.type,
                    CASE WHEN a.type='masuk' AND HOUR(a.created_at)>=8 THEN 1 ELSE 0 END as is_telat,
                    a.address,
                    CONCAT(ROUND(a.latitude,5),', ',ROUND(a.longitude,5)) as koordinat,
                    a.accuracy, a.photo, a.ip_address
             $base ORDER BY $orderSql $orderDir LIMIT $length OFFSET $start",
            $params
        )->getResultArray();

        $today = date('Y-m-d');
        $stats = [
            'hari_ini'  => $db->query("SELECT COUNT(*) c FROM attendances WHERE DATE(created_at)=?", [$today])->getRow()->c,
            'bulan_ini' => $db->query("SELECT COUNT(*) c FROM attendances WHERE DATE_FORMAT(created_at,'%Y-%m')=?", [date('Y-m')])->getRow()->c,
            'telat'     => $db->query("SELECT COUNT(*) c FROM attendances WHERE type='masuk' AND HOUR(created_at)>=8 AND DATE_FORMAT(created_at,'%Y-%m')=?", [date('Y-m')])->getRow()->c,
            'total'     => $db->query("SELECT COUNT(*) c FROM attendances")->getRow()->c,
        ];

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => $rows,
            'stats'           => $stats,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // GET /attendance/admin/export  →  CSV
    // ─────────────────────────────────────────────────────────────
    public function export()
    {
        $db  = \Config\Database::connect();
        $req = $this->request;

        $dateFrom   = $req->getGet('date_from')  ?: date('Y-m-01');
        $dateTo     = $req->getGet('date_to')    ?: date('Y-m-d');
        $type       = $req->getGet('type')       ?: '';
        $status     = $req->getGet('status')     ?: '';
        $department = $req->getGet('department') ?: '';
        $search     = $req->getGet('search')     ?: '';

        $base   = "FROM attendances a
                   LEFT JOIN users u ON u.id = a.user_id
                   LEFT JOIN employees e ON e.user_id = a.user_id
                   WHERE DATE(a.created_at) BETWEEN ? AND ?";
        $params = [$dateFrom, $dateTo];

        if ($type)       { $base .= " AND a.type=?";        $params[] = $type; }
        if ($department) { $base .= " AND e.department=?";  $params[] = $department; }
        if ($status === 'telat') $base .= " AND a.type='masuk' AND HOUR(a.created_at)>=8";
        if ($status === 'tepat') $base .= " AND a.type='masuk' AND HOUR(a.created_at)<8";
        if ($search) {
            $base  .= " AND (u.name LIKE ? OR e.nik LIKE ?)";
            $params = array_merge($params, ["%$search%", "%$search%"]);
        }

        $rows = $db->query(
            "SELECT
                DATE(a.created_at)                                                           as Tanggal,
                TIME(a.created_at)                                                           as Jam,
                e.nik                                                                        as NIK,
                u.name                                                                       as Nama,
                e.department                                                                 as Department,
                a.type                                                                       as Tipe,
                CASE WHEN a.type='masuk' AND HOUR(a.created_at)>=8 THEN 'Telat' ELSE 'Tepat' END as Status,
                a.address                                                                    as Lokasi,
                a.latitude                                                                   as Latitude,
                a.longitude                                                                  as Longitude,
                a.accuracy                                                                   as 'Akurasi(m)',
                a.ip_address                                                                 as IP
             $base ORDER BY a.created_at DESC",
            $params
        )->getResultArray();

        $filename = 'absensi_' . $dateFrom . '_sd_' . $dateTo . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8 agar Excel tidak rusak
        if (!empty($rows)) {
            fputcsv($out, array_keys($rows[0]));
            foreach ($rows as $row) fputcsv($out, $row);
        }
        fclose($out);
        exit;
    }

    // ─────────────────────────────────────────────────────────────
    // POST /attendance/admin/delete/(:num)
    // ─────────────────────────────────────────────────────────────
    public function delete(int $id)
    {
        $deleted = $this->model->delete($id);
        return $this->response->setJSON([
            'success' => (bool) $deleted,
            'message' => $deleted ? 'Data berhasil dihapus.' : 'Gagal menghapus data.',
        ]);
    }
}