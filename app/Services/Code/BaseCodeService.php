<?php

namespace App\Services\Code;

use CodeIgniter\Database\BaseConnection;

abstract class BaseCodeService
{
    protected BaseConnection $db;

    protected string $prefix;
    protected int $padLength = 4;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    /**
     * API yang dipakai Controller
     */
    public function generateFromInsertId(int $insertId): string
    {
        return $this->format($insertId);
    }

    /**
     * =========================
     * Helper
     * =========================
     */
    protected function format(int $number): string
    {
        return $this->prefix . str_pad($number, $this->padLength, '0', STR_PAD_LEFT);
    }

    /**
     * =========================
     * Template Future (SEQUENCE)
     * =========================
     */
    protected function generateFromSequence(string $keyName): string
    {
        $this->db->transStart();

        // LOCK ROW (MySQL / PostgreSQL)
        $row = $this->db->query(
            "SELECT last_number 
            FROM number_sequence 
            WHERE key_name = ? 
            FOR UPDATE",
            [$keyName]
        )->getRow();

        if (!$row) {
            throw new \RuntimeException("Sequence key '{$keyName}' tidak ditemukan");
        }

        $next = (int) $row->last_number + 1;

        $this->db->table('number_sequence')
            ->where('key_name', $keyName)
            ->update(['last_number' => $next]);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new \RuntimeException('Gagal generate sequence');
        }

        return $this->format($next);
    }

}
