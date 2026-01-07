<?php

namespace App\Models;

use CodeIgniter\Model;

class SlipGajiModel extends Model
{
    protected $table = 'slip_gaji';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'tanggal_slip', 'nik', 'nama', 'jabatan', 'status', 'bulan', 'site',
        'umk', 'insentif_lain', 'insentif_pulsa', 'kompensasi_cuti',
        'insentif_lembur', 'insentif_makan', 'uang_tunggu', 'gaji_prorate',
        'total_pendapatan', 'bpjs_kes', 'bpjs_tk', 'pot_pph21', 'lainnya',
        'total_pot', 'gaji_bersih', 'email', 'status_kirim', 'tanggal_kirim'
    ];
    protected $useTimestamps = true;
}