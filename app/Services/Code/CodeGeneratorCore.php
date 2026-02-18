<?php

namespace App\Services\Code;

use CodeIgniter\Database\BaseConnection;

class CodeGeneratorCore
{
    protected BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    /**
     * =====================================================
     * CORE SEQUENCE GENERATOR (AMAN CONCURRENCY)
     * =====================================================
     * @param string $keyName  contoh: mess, workshop
     * @param string $prefix   contoh: MES-BDM-
     * @return string
     */
    public function generate(string $keyName, string $prefix): string
    {
        $period = 'GLOBAL'; 
        // $period = date('Ym'); // YYYYMM

        $this->db->transBegin();

        // LOCK sequence row
        $row = $this->db->query(
            "SELECT last_number
             FROM number_sequence
             WHERE key_name = ? AND period = ?
             FOR UPDATE",
            [$keyName, $period]
        )->getRow();

        if (!$row) {
            // pertama kali di periode ini
            $next = 1;

            $this->db->query(
                "INSERT INTO number_sequence (key_name, period, last_number)
                 VALUES (?, ?, ?)",
                [$keyName, $period, $next]
            );
        } else {
            $next = (int) $row->last_number + 1;

            $this->db->query(
                "UPDATE number_sequence
                 SET last_number = ?
                 WHERE key_name = ? AND period = ?",
                [$next, $keyName, $period]
            );
        }

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            throw new \RuntimeException('Gagal generate sequence');
        }

        $this->db->transCommit();

        return $this->format($prefix, $next);
    }

    /**
     * =====================================================
     * FORMAT OUTPUT
     * =====================================================
     */
    protected function format(string $prefix, int $number): string
    {
        //return $prefix . date('Ym') . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);

    }
}
