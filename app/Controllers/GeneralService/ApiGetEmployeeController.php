<?php

namespace App\Controllers\GeneralService;

use App\Controllers\BaseApiController;

class ApiGetEmployeeController extends BaseApiController
{
    public function searchEmployees()
    {
        // ambil keyword (POST + fallback GET biar kebal)
        $keyword = trim(
            $this->request->getPost('search')
            ?? $this->request->getGet('search')
            ?? ''
        );

        if ($keyword === '') {
            return $this->response->setJSON([]);
        }

        // koneksi langsung ke PostgreSQL (default DB)
        $db = \Config\Database::connect();

        // 🔥 QUERY LANGSUNG POSTGRES
        $sql = "
            SELECT
                employee_name,
                employee_number
            FROM employees
            WHERE
                employee_name ILIKE ?
                OR employee_number ILIKE ?
            ORDER BY employee_name
            LIMIT 20
        ";

        $rows = $db->query($sql, [
            "%{$keyword}%",
            "%{$keyword}%"
        ])->getResultArray();

        // format khusus Select2
        $results = [];
        foreach ($rows as $row) {
            $results[] = [
                'id'   => $row['employee_number'],
                'text' => $row['employee_name'] . ' (' . $row['employee_number'] . ')',
                'name' => $row['employee_name'],
                'nik'  => $row['employee_number'],
            ];
        }

        return $this->response->setJSON($results);
    }
}
