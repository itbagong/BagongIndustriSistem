<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollModel extends Model
{
    protected $table = 'slip_gaji';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'nomor_slip',
        'tanggal_slip',
        'nik',
        'nama',
        'jabatan',
        'status',
        'bulan',
        'site',
        'umk',
        'tunjangan_tidak_tetap',
        'insentif_lain',
        'kompensasi',
        'insentif_lembur',
        'insentif_makan',
        'uang_tunggu',
        'gaji_prorate',
        'total_pendapatan',
        'bpjs_kes',
        'bpjs_tk',
        'pot_pph21',
        'lainnya',
        'total_pot',
        'gaji_bersih',
        'email',
        'status_kirim',
        'tanggal_kirim',
        'created_at',
        'updated_at'
    ];

    public function getFiltered(array $filters, int $perPage = 25)
    {
        $q = $this;

        // TEXT FILTERS
        if (!empty($filters['nik'])) {
            $q->like('nik', $filters['nik']);
        }

        if (!empty($filters['nama'])) {
            $q->like('nama', $filters['nama']);
        }

        if (!empty($filters['jabatan'])) {
            $q->like('jabatan', $filters['jabatan']);
        }

        if (!empty($filters['site'])) {
            $q->like('site', $filters['site']);
        }

        if (!empty($filters['bulan'])) {
            $q->like('bulan', $filters['bulan']);
        }

        if (!empty($filters['email'])) {
            $q->like('email', $filters['email']);
        }

        // STATUS KIRIM
        if (!empty($filters['status_kirim'])) {
            $q->where('status_kirim', $filters['status_kirim']);
        }

        // GAJI BERSIH RANGE
        if (!empty($filters['gaji_min'])) {
            $min = (int) str_replace('.', '', $filters['gaji_min']);
            $q->where('gaji_bersih >=', $min);
        }

        if (!empty($filters['gaji_max'])) {
            $max = (int) str_replace('.', '', $filters['gaji_max']);
            $q->where('gaji_bersih <=', $max);
        }

        return $q
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage);
    }
    public function getDistinctValues(string $column)
    {
        return $this->select($column)
            ->groupBy($column)
            ->orderBy($column, 'ASC')
            ->findAll();
    }

}
