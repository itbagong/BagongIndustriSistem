<?php

namespace App\Controllers\Employee;

use App\Controllers\BaseApiController;
use App\Models\AuditLogModel;
use CodeIgniter\HTTP\ResponseInterface;

class EmployeeController extends BaseApiController
{
    protected $auditLogModel;

    public function __construct()
    {
        helper('cookie');
        $this->auditLogModel = new AuditLogModel();
    }

    public function index()
    {
        return view('employees/index', [
            'title' => 'Data Karyawan'
        ]);
    }

    public function getData(): ResponseInterface
    {
        // 1. Ambil Parameter
        $page       = (int) ($this->request->getGet('page') ?? 1);
        $perPage    = (int) ($this->request->getGet('per_page') ?? 10);
        
        // Parameter Filter (Normalisasi ke lowercase agar pencarian tidak case-sensitive)
        $search     = strtolower(trim($this->request->getGet('search') ?? ''));
        $department = $this->request->getGet('department') ?? '';
        $status     = $this->request->getGet('employee_status') ?? '';

        // Trik: Naikkan memory limit karena kita memanipulasi array besar
        ini_set('memory_limit', '512M'); 

        try {
            $cache = \Config\Services::cache();
            $cacheKey = 'master_data_employees_v1'; // Key unik untuk cache

            // 2. CEK CACHE (Agar tidak download terus menerus)
            if (! $allData = $cache->get($cacheKey)) {
                
                // Jika Cache Kosong, baru Request ke API (Hanya terjadi sesekali)
                $response = $this->api->get('employees'); // Ambil SEMUA data tanpa query params
                $result   = json_decode($response->getBody(), true);
                
                // Normalisasi struktur data
                if (isset($result['data']) && is_array($result['data'])) {
                    $allData = $result['data'];
                } elseif (is_array($result)) {
                    $allData = $result;
                } else {
                    $allData = [];
                }

                // Simpan ke cache selama 5 menit (300 detik)
                $cache->save($cacheKey, $allData, 300);
            }

            // 3. FILTERING DATA (Dilakukan di Memory PHP)
            // Ini bagian yang hilang di kode lama Anda.
            if ($search || $department || $status) {
                $allData = array_filter($allData, function ($item) use ($search, $department, $status) {
                    
                    // A. Filter Search (Nama atau NIK)
                    if ($search) {
                        $name = strtolower($item['employee_name'] ?? '');
                        $nik  = strtolower($item['employee_number'] ?? $item['bis_id'] ?? ''); // Sesuaikan key API
                        if (strpos($name, $search) === false && strpos($nik, $search) === false) {
                            return false; // Tidak cocok, buang
                        }
                    }

                    // B. Filter Department
                    if ($department) {
                        $dept = $item['department'] ?? '';
                        if ($dept !== $department) return false;
                    }

                    // C. Filter Status
                    if ($status) {
                        $st = $item['employee_status'] ?? '';
                        if ($st !== $status) return false;
                    }

                    return true; // Lolos semua filter
                });
                
                // Re-index array setelah filter (agar urutan mulai dari 0 lagi)
                $allData = array_values($allData);
            }

            // 4. PAGINATION LOGIC
            $totalData = count($allData);
            $offset    = ($page - 1) * $perPage;
            
            // Mencegah offset negatif atau melebihi jumlah data
            if ($offset < 0) $offset = 0;
            
            // Ambil potongan data
            $slicedData = array_slice($allData, $offset, $perPage);

            // 5. Response JSON
            return $this->response->setJSON([
                'status' => 'success',
                'data'   => $slicedData,
                'pagination' => [
                    'page'        => $page,
                    'per_page'    => $perPage,
                    'total'       => $totalData,
                    'total_pages' => ceil($totalData / ($perPage > 0 ? $perPage : 1))
                ]
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'EMPLOYEE ERROR: ' . $e->getMessage());

            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Gagal memuat data: ' . $e->getMessage(),
                'data'    => [] 
            ]);
        }
    }

    public function getStatistics()
    {
        // 1. Naikkan limit memory karena kita akan meloop 50.000 data
        ini_set('memory_limit', '512M');

        try {
            // 2. Ambil semua data dari API
            $response = $this->api->get('employees');
            $result   = json_decode($response->getBody(), true);
            
            // Deteksi lokasi data
            $allData = $result['data'] ?? ($result ?? []);
            
            // 3. Hitung Statistik Manual
            $total    = count($allData);
            $active   = 0;
            $inactive = 0;
            $newThisMonth = 0;

            $currentMonth = date('Y-m');

            foreach ($allData as $emp) {
                // Cek status (sesuaikan dengan key dari API, misal 'employee_status' atau 'status')
                $status = strtoupper($emp['employee_status'] ?? $emp['status'] ?? '');
                
                if (strpos($status, 'ACTIVE') !== false || strpos($status, 'AKTIF') !== false) {
                    $active++;
                } else {
                    $inactive++;
                }

                // Cek karyawan baru bulan ini
                $joinDate = $emp['join_date'] ?? $emp['joinDate'] ?? null;
                if ($joinDate && strpos($joinDate, $currentMonth) === 0) {
                    $newThisMonth++;
                }
            }

            // 4. Return JSON sesuai format yang diminta JS
            return $this->response->setJSON([
                'status' => 'success',
                'data'   => [
                    'total'          => $total,
                    'active'         => $active,
                    'inactive'       => $inactive,
                    'new_this_month' => $newThisMonth
                ]
            ]);

        } catch (\Throwable $e) {
            // Jika error, return 0 semua agar tampilan tidak rusak
            return $this->response->setJSON([
                'status' => 'error',
                'data'   => [
                    'total'    => 0,
                    'active'   => 0,
                    'inactive' => 0,
                    'new_this_month' => 0
                ]
            ]);
        }
    }
}