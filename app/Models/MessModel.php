<?php

namespace App\Models;

use CodeIgniter\Model;

class MessModel extends Model
{
    protected $DBGroup = 'mysql';
    protected $table      = 'mess_data';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'divisi_id',
        'site_id',
        'nama_karyawan',
        'nik',
        'luasan_mess',
        'jumlah_kamar_tidur',
        'jumlah_kamar_mandi',
        'akses_parkir',
        'luas_area_parkir',
        'fasilitas',
        'status_kepemilikan',
        'status_renovasi',
        'created_at',
        'updated_at',
        'is_deleted'
    ];
}