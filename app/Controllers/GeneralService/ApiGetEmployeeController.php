<?php
namespace App\Controllers\GeneralService;
use App\Controllers\BaseApiController;

class ApiGetEmployeeController extends BaseApiController
{
    public function searchEmployees() {
        // ❌ COMMENT DULU UNTUK DEBUG
        // if ($this->request->isAJAX()) {
        
        $search = strtolower(trim($this->request->getPost('search') ?? ''));

        log_message('info', '=== EMPLOYEE SEARCH DEBUG ===');
        log_message('info', 'Search keyword: ' . $search);

        if (strlen($search) < 2) {
            log_message('info', 'Search too short');
            return $this->response->setJSON([]);
        }

        try {
            $cache = \Config\Services::cache();
            $cacheKey = 'employees_light_v1';

            // Ambil dari cache atau API
            if (!$allData = $cache->get($cacheKey)) {
                log_message('info', 'Cache MISS - Fetching from API...');
                
                $response = $this->api->get('employees');
                
                log_message('info', 'API Status: ' . $response->getStatusCode());
                
                $result = json_decode($response->getBody(), true);
                
                // LOG SAMPLE DATA
                log_message('info', 'Sample raw data: ' . json_encode(array_slice($result, 0, 2)));
                
                $allData = $result['data'] ?? $result ?? [];
                
                log_message('info', 'Total employees loaded: ' . count($allData));

                // Cache 5 menit
                $cache->save($cacheKey, $allData, 300);
            } else {
                log_message('info', 'Cache HIT - Total: ' . count($allData));
            }

            // Filter berdasarkan search keyword
            $filtered = array_filter($allData, function($emp) use ($search) {
                $name = strtolower($emp['employee_name'] ?? '');
                $nik = strtolower($emp['employee_number'] ?? '');

                return (strpos($name, $search) !== false || strpos($nik, $search) !== false);
            });

            // Limit hasil maksimal 20 untuk performa
            $filtered = array_slice(array_values($filtered), 0, 20);

            log_message('info', 'Filtered results: ' . count($filtered));
            log_message('info', 'Sample filtered: ' . json_encode(array_slice($filtered, 0, 2)));

            return $this->response->setJSON($filtered);

        } catch (\Throwable $e) {
            log_message('error', 'Employee Search Error: ' . $e->getMessage());
            log_message('error', 'Trace: ' . $e->getTraceAsString());
            
            return $this->response->setJSON([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
        
        // }

        // return $this->response->setJSON([]);
    }
}