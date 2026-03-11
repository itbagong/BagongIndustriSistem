<?php

namespace App\Controllers\Attendance;

use App\Controllers\BaseController;
use App\Models\AttendanceModel;
use App\Models\EmployeeModel;   // model PostgreSQL kamu

class Attendanceadmincontroller extends BaseController
{
    protected AttendanceModel $attendanceModel;
    protected EmployeeModel   $employeeModel;

    public function __construct()
    {
        $this->attendanceModel = new AttendanceModel();
        $this->employeeModel   = new EmployeeModel();
    }

    /**
     * Ambil map [employee_id => employee_row] dari PostgreSQL
     * berdasarkan array employee_id dari tabel users (MySQL)
     */
    private function getEmpMap(array $employeeIds): array
    {
        if (empty($employeeIds)) return [];
        $empRows = $this->employeeModel
            ->whereIn('id', $employeeIds)
            ->findAll();
        $map = [];
        foreach ($empRows as $e) {
            $map[$e['id']] = $e;
        }
        return $map;
    }

    // ─────────────────────────────────────────────────────────────
    // GET /attendance/admin  →  dashboard
    // ─────────────────────────────────────────────────────────────
    public function dashboard()
    {
        $db    = \Config\Database::connect(); // MySQL
        $today = date('Y-m-d');
        $month = date('Y-m');
        $week7 = date('Y-m-d', strtotime('-6 days'));

        // [1] Total karyawan dari PostgreSQL
        $totalKaryawan = $this->employeeModel->countAllResults();

        // [2] Semua stat hari ini + bulan ini — 1 query MySQL
        $s = $db->query(
            "SELECT
                SUM(type='masuk'  AND DATE(created_at)=?)                                        AS hadir_hari_ini,
                SUM(type='masuk'  AND DATE(created_at)=? AND HOUR(created_at)>=8)                AS telat_hari_ini,
                SUM(type='pulang' AND DATE(created_at)=?)                                        AS sudah_pulang,
                SUM(DATE_FORMAT(created_at,'%Y-%m')=?)                                           AS total_bulan_ini,
                SUM(type='masuk'  AND DATE_FORMAT(created_at,'%Y-%m')=? AND HOUR(created_at)<8)  AS tepat_bulan_ini,
                SUM(type='masuk'  AND DATE_FORMAT(created_at,'%Y-%m')=? AND HOUR(created_at)>=8) AS telat_bulan_ini
             FROM attendances",
            [$today, $today, $today, $month, $month, $month]
        )->getRow();

        $hadirHariIni      = (int)($s->hadir_hari_ini  ?? 0);
        $telatHariIni      = (int)($s->telat_hari_ini  ?? 0);
        $sudahPulang       = (int)($s->sudah_pulang    ?? 0);
        $totalBulanIni     = (int)($s->total_bulan_ini ?? 0);
        $hariHadirBulanIni = (int)($s->tepat_bulan_ini ?? 0);
        $hariTelatBulanIni = (int)($s->telat_bulan_ini ?? 0);
        $belumAbsen        = max(0, $totalKaryawan - $hadirHariIni);

        // Hari kerja dihitung PHP
        $hariKerja = 0;
        $period    = new \DatePeriod(
            new \DateTime(date('Y-m-01')),
            new \DateInterval('P1D'),
            (new \DateTime($today))->modify('+1 day')
        );
        foreach ($period as $d) {
            if ($d->format('N') < 6) $hariKerja++;
        }
        $hariAbsenBulanIni = max(0, $hariKerja - $hariHadirBulanIni - $hariTelatBulanIni);

        // [3] Absen masuk hari ini — JOIN users untuk dapat employee_id
        $todayRecords = $db->query(
            "SELECT a.created_at, a.address, u.username AS user_name, u.employee_id
             FROM attendances a
             LEFT JOIN users u ON u.id = a.user_id
             WHERE a.type='masuk' AND DATE(a.created_at)=?
             ORDER BY a.created_at ASC LIMIT 10",
            [$today]
        )->getResultArray();

        // Enrich department dari PG via employee_id
        if (!empty($todayRecords)) {
            $empIds = array_filter(array_column($todayRecords, 'employee_id'));
            $empMap = $this->getEmpMap($empIds);
            foreach ($todayRecords as &$row) {
                $emp = $empMap[$row['employee_id']] ?? null;
                $row['department'] = $emp['department_id'] ?? '-';
            }
            unset($row);
        }

        // [4] Top 5 telat bulan ini
        $topLateRaw = $db->query(
            "SELECT u.username AS user_name, u.employee_id, COUNT(*) AS jumlah_telat
             FROM attendances a
             LEFT JOIN users u ON u.id = a.user_id
             WHERE a.type='masuk'
               AND DATE_FORMAT(a.created_at,'%Y-%m')=?
               AND HOUR(a.created_at)>=8
             GROUP BY a.user_id, u.username, u.employee_id
             ORDER BY jumlah_telat DESC LIMIT 5",
            [$month]
        )->getResultArray();

        if (!empty($topLateRaw)) {
            $empIds = array_filter(array_column($topLateRaw, 'employee_id'));
            $empMap = $this->getEmpMap($empIds);
            foreach ($topLateRaw as &$row) {
                $emp = $empMap[$row['employee_id']] ?? null;
                $row['department'] = $emp['department_id'] ?? '-';
            }
            unset($row);
        }
        $topLate = $topLateRaw;

        // [5] Chart 7 hari — 1 query GROUP BY
        $chartRows = $db->query(
            "SELECT
                DATE(created_at)           AS tgl,
                SUM(HOUR(created_at) < 8)  AS hadir,
                SUM(HOUR(created_at) >= 8) AS telat
             FROM attendances
             WHERE type='masuk' AND DATE(created_at) BETWEEN ? AND ?
             GROUP BY DATE(created_at)",
            [$week7, $today]
        )->getResultArray();

        $chartMap = [];
        foreach ($chartRows as $r) $chartMap[$r['tgl']] = $r;

        $weekChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $d           = date('Y-m-d', strtotime("-$i days"));
            $weekChart[] = [
                'label' => date('D d/m', strtotime($d)),
                'hadir' => (int)($chartMap[$d]['hadir'] ?? 0),
                'telat' => (int)($chartMap[$d]['telat'] ?? 0),
            ];
        }

        return view('attendance/admin/admin_dashboard', array_merge($this->data, [
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
        ]));
    }

    // ─────────────────────────────────────────────────────────────
    // GET /attendance/admin/history
    // ─────────────────────────────────────────────────────────────
    public function history()
    {
        // Ambil daftar department_id unik dari PG
        $departments = array_unique(array_filter(array_column(
            $this->employeeModel->select('department_id')->findAll(),
            'department_id'
        )));
        sort($departments);
        return view('attendance/admin_index', array_merge($this->data, ['departments' => $departments]));
    }

    // ─────────────────────────────────────────────────────────────
    // POST /attendance/admin/data  (DataTable server-side)
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

        // Jika filter department → cari employee_id dari PG → cari user_id di MySQL
        $userIdFilter = [];
        if ($department !== '') {
            $empIds = array_column(
                $this->employeeModel->select('id')->where('department_id', $department)->findAll(),
                'id'
            );
            if (empty($empIds)) {
                return $this->response->setJSON([
                    'draw'=>$draw,'recordsTotal'=>0,'recordsFiltered'=>0,'data'=>[],'stats'=>[],
                ]);
            }
            $placeholders = implode(',', array_fill(0, count($empIds), '?'));
            $userRows     = $db->query(
                "SELECT id FROM users WHERE employee_id IN ($placeholders)", $empIds
            )->getResultArray();
            $userIdFilter = array_column($userRows, 'id');
            if (empty($userIdFilter)) {
                return $this->response->setJSON([
                    'draw'=>$draw,'recordsTotal'=>0,'recordsFiltered'=>0,'data'=>[],'stats'=>[],
                ]);
            }
        }

        $base   = "FROM attendances a
                   LEFT JOIN users u ON u.id = a.user_id
                   WHERE DATE(a.created_at) BETWEEN ? AND ?";
        $params = [$dateFrom, $dateTo];

        if ($type)   { $base .= " AND a.type=?"; $params[] = $type; }
        if ($status === 'telat') $base .= " AND a.type='masuk' AND HOUR(a.created_at)>=8";
        if ($status === 'tepat') $base .= " AND a.type='masuk' AND HOUR(a.created_at)<8";
        if (!empty($userIdFilter)) {
            $ids   = implode(',', array_map('intval', $userIdFilter));
            $base .= " AND a.user_id IN ($ids)";
        }
        if ($search) {
            $base  .= " AND u.username LIKE ?";
            $params = array_merge($params, ["%$search%"]);
        }

        $total = (int) $db->query("SELECT COUNT(*) c $base", $params)->getRow()->c;

        $colMap   = [1=>'DATE(a.created_at)', 3=>'u.username', 6=>'TIME(a.created_at)', 7=>'a.type'];
        $orderCol = (int)($req->getPost('order')[0]['column'] ?? 1);
        $orderDir = strtolower($req->getPost('order')[0]['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
        $orderSql = $colMap[$orderCol] ?? 'a.created_at';

        $rows = $db->query(
            "SELECT a.id,
                    DATE(a.created_at)  AS tanggal,
                    TIME(a.created_at)  AS jam,
                    u.username          AS nama,
                    u.employee_id,
                    a.type,
                    CASE WHEN a.type='masuk' AND HOUR(a.created_at)>=8 THEN 1 ELSE 0 END AS is_telat,
                    a.address,
                    CONCAT(ROUND(a.latitude,5),', ',ROUND(a.longitude,5)) AS koordinat,
                    a.accuracy, a.photo, a.ip_address
             $base ORDER BY $orderSql $orderDir LIMIT $length OFFSET $start",
            $params
        )->getResultArray();

        // Enrich NIK + department dari PG
        if (!empty($rows)) {
            $empIds = array_filter(array_column($rows, 'employee_id'));
            $empMap = $this->getEmpMap($empIds);
            foreach ($rows as &$row) {
                $emp = $empMap[$row['employee_id']] ?? null;
                $row['nik']        = $emp['nik']           ?? '-';
                $row['department'] = $emp['department_id'] ?? '-';
                unset($row['employee_id']);
            }
            unset($row);
        }

        // Stats 4 kartu — 1 query
        $today = date('Y-m-d');
        $sv = $db->query(
            "SELECT
                SUM(DATE(created_at)=?)                                                         AS hari_ini,
                SUM(DATE_FORMAT(created_at,'%Y-%m')=?)                                          AS bulan_ini,
                SUM(type='masuk' AND HOUR(created_at)>=8 AND DATE_FORMAT(created_at,'%Y-%m')=?) AS telat,
                COUNT(*)                                                                        AS total
             FROM attendances",
            [$today, date('Y-m'), date('Y-m')]
        )->getRow();

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => $rows,
            'stats'           => [
                'hari_ini'  => (int)($sv->hari_ini  ?? 0),
                'bulan_ini' => (int)($sv->bulan_ini ?? 0),
                'telat'     => (int)($sv->telat     ?? 0),
                'total'     => (int)($sv->total     ?? 0),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // GET /attendance/admin/export
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

        $userIdFilter = [];
        if ($department !== '') {
            $empIds = array_column(
                $this->employeeModel->select('id')->where('department_id', $department)->findAll(),
                'id'
            );
            if (!empty($empIds)) {
                $placeholders = implode(',', array_fill(0, count($empIds), '?'));
                $userIdFilter = array_column(
                    $db->query("SELECT id FROM users WHERE employee_id IN ($placeholders)", $empIds)->getResultArray(),
                    'id'
                );
            }
            if (empty($userIdFilter)) { exit; }
        }

        $base   = "FROM attendances a
                   LEFT JOIN users u ON u.id = a.user_id
                   WHERE DATE(a.created_at) BETWEEN ? AND ?";
        $params = [$dateFrom, $dateTo];

        if ($type)   { $base .= " AND a.type=?"; $params[] = $type; }
        if ($status === 'telat') $base .= " AND a.type='masuk' AND HOUR(a.created_at)>=8";
        if ($status === 'tepat') $base .= " AND a.type='masuk' AND HOUR(a.created_at)<8";
        if (!empty($userIdFilter)) {
            $ids   = implode(',', array_map('intval', $userIdFilter));
            $base .= " AND a.user_id IN ($ids)";
        }
        if ($search) {
            $base  .= " AND u.username LIKE ?";
            $params = array_merge($params, ["%$search%"]);
        }

        $rows = $db->query(
            "SELECT u.employee_id,
                DATE(a.created_at)  AS Tanggal,
                TIME(a.created_at)  AS Jam,
                u.username          AS Nama,
                a.type              AS Tipe,
                CASE WHEN a.type='masuk' AND HOUR(a.created_at)>=8 THEN 'Telat' ELSE 'Tepat' END AS Status,
                a.address           AS Lokasi,
                a.latitude          AS Latitude,
                a.longitude         AS Longitude,
                a.accuracy          AS Akurasi_m,
                a.ip_address        AS IP
             $base ORDER BY a.created_at DESC",
            $params
        )->getResultArray();

        // Enrich NIK + department dari PG
        if (!empty($rows)) {
            $empIds = array_filter(array_column($rows, 'employee_id'));
            $empMap = $this->getEmpMap($empIds);
            foreach ($rows as &$row) {
                $emp = $empMap[$row['employee_id']] ?? null;
                $row['NIK']        = $emp['nik']           ?? '';
                $row['Department'] = $emp['department_id'] ?? '';
                unset($row['employee_id']);
            }
            unset($row);
        }

        $filename = 'absensi_' . $dateFrom . '_sd_' . $dateTo . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
        if (!empty($rows)) {
            fputcsv($out, array_keys($rows[0]));
            foreach ($rows as $row) fputcsv($out, $row);
        }
        fclose($out);
        exit;
    }

    // ─────────────────────────────────────────────────────────────
    // GET /attendance/admin/present
    // ─────────────────────────────────────────────────────────────
    public function present()
    {
        $db   = \Config\Database::connect();
        $date = $this->request->getGet('date') ?: date('Y-m-d');

        // Semua karyawan yang sudah absen masuk pada tanggal tsb
        $rows = $db->query(
            "SELECT
                a.id,
                a.created_at,
                TIME(a.created_at)  AS jam_masuk,
                CASE WHEN HOUR(a.created_at)>=8 THEN 1 ELSE 0 END AS is_telat,
                a.address,
                a.accuracy,
                a.photo,
                u.username          AS nama,
                u.employee_id,
                p.type              AS sudah_pulang,
                p.created_at        AS jam_pulang_raw
             FROM attendances a
             LEFT JOIN users u ON u.id = a.user_id
             LEFT JOIN (
                 SELECT user_id, type, created_at
                 FROM attendances
                 WHERE type='pulang' AND DATE(created_at)=?
             ) p ON p.user_id = a.user_id
             WHERE a.type='masuk' AND DATE(a.created_at)=?
             ORDER BY a.created_at ASC",
            [$date, $date]
        )->getResultArray();

        // Enrich dari PG
        if (!empty($rows)) {
            $empIds = array_filter(array_column($rows, 'employee_id'));
            $empMap = $this->getEmpMap($empIds);
            foreach ($rows as &$row) {
                $emp = $empMap[$row['employee_id']] ?? null;
                $row['nik']        = $emp['nik']           ?? '-';
                $row['department'] = $emp['department_id'] ?? '-';
                $row['jam_pulang'] = $row['jam_pulang_raw']
                    ? date('H:i', strtotime($row['jam_pulang_raw']))
                    : null;
                unset($row['employee_id'], $row['jam_pulang_raw']);
            }
            unset($row);
        }

        // Summary stat
        $total   = count($rows);
        $tepat   = count(array_filter($rows, fn($r) => !$r['is_telat']));
        $telat   = $total - $tepat;
        $pulang  = count(array_filter($rows, fn($r) => $r['sudah_pulang']));
        $blmPlg  = $total - $pulang;

        return view('attendance/admin/admin_present', array_merge($this->data, [
            'rows'    => $rows,
            'date'    => $date,
            'summary' => compact('total', 'tepat', 'telat', 'pulang', 'blmPlg'),
        ]));
    }

    // ─────────────────────────────────────────────────────────────
    // GET /attendance/admin/present/export
    // ─────────────────────────────────────────────────────────────
    public function presentExport()
    {
        $db   = \Config\Database::connect();
        $date = $this->request->getGet('date') ?: date('Y-m-d');

        $rows = $db->query(
            "SELECT
                TIME(a.created_at)  AS Jam_Masuk,
                CASE WHEN HOUR(a.created_at)>=8 THEN 'Telat' ELSE 'Tepat' END AS Status,
                TIME(p.created_at)  AS Jam_Pulang,
                u.username          AS Nama,
                u.employee_id,
                a.address           AS Lokasi,
                a.accuracy          AS Akurasi_m,
                a.ip_address        AS IP
             FROM attendances a
             LEFT JOIN users u ON u.id = a.user_id
             LEFT JOIN (
                 SELECT user_id, created_at FROM attendances
                 WHERE type='pulang' AND DATE(created_at)=?
             ) p ON p.user_id = a.user_id
             WHERE a.type='masuk' AND DATE(a.created_at)=?
             ORDER BY a.created_at ASC",
            [$date, $date]
        )->getResultArray();

        if (!empty($rows)) {
            $empIds = array_filter(array_column($rows, 'employee_id'));
            $empMap = $this->getEmpMap($empIds);
            foreach ($rows as &$row) {
                $emp = $empMap[$row['employee_id']] ?? null;
                $row['NIK']        = $emp['nik']           ?? '';
                $row['Department'] = $emp['department_id'] ?? '';
                unset($row['employee_id']);
            }
            unset($row);
        }

        $filename = 'hadir_' . $date . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
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
        $deleted = $this->attendanceModel->delete($id);
        return $this->response->setJSON([
            'success' => (bool) $deleted,
            'message' => $deleted ? 'Data berhasil dihapus.' : 'Gagal menghapus data.',
        ]);
    }
}