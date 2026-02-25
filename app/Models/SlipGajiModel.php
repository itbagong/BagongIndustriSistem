<?php

namespace App\Models;

use CodeIgniter\Model;

class SlipGajiModel extends Model
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
        'kekurangan_gaji',
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
        'insentif_pulsa',
        'insentif_cuci_unit',
        'updated_at'
    ];
    protected $useTimestamps = true;
}