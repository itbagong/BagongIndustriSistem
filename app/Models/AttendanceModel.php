<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table            = 'attendances';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'user_id',
        'type',        // masuk | pulang
        'photo',       // path file foto
        'latitude',
        'longitude',
        'accuracy',
        'address',
        'ip_address',
    ];

    // Validasi saat insert/update lewat model
    protected $validationRules = [
        'user_id'   => 'required|integer',
        'type'      => 'required|in_list[masuk,pulang]',
        'photo'     => 'required',
        'latitude'  => 'required|decimal',
        'longitude' => 'required|decimal',
    ];

    protected $validationMessages = [
        'user_id' => ['required' => 'User ID wajib diisi.'],
        'type'    => ['required' => 'Tipe absen wajib diisi.', 'in_list' => 'Tipe absen tidak valid.'],
    ];

    // ── Query helpers ──────────────────────────

    /**
     * Ambil absen hari ini milik user tertentu
     */
    public function getTodayAttendance(int $userId): array
    {
        return $this->where('user_id', $userId)
                    ->where('DATE(created_at)', date('Y-m-d'))
                    ->findAll();
    }

    /**
     * Cek apakah user sudah absen (masuk/pulang) hari ini
     */
    public function hasAttendedToday(int $userId, string $type): bool
    {
        return $this->where('user_id', $userId)
                    ->where('type', $type)
                    ->where('DATE(created_at)', date('Y-m-d'))
                    ->countAllResults() > 0;
    }

    /**
     * Rekap absen per bulan
     */
    public function getMonthlySummary(int $userId, string $yearMonth): array
    {
        return $this->select('DATE(created_at) as date, type, created_at, address, photo')
                    ->where('user_id', $userId)
                    ->where("DATE_FORMAT(created_at, '%Y-%m')", $yearMonth)
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }
}