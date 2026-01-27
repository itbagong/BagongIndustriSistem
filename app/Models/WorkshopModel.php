<?php namespace App\Models;

use CodeIgniter\Model;

class WorkshopModel extends Model
{
    protected $DBGroup = 'mysql';
    protected $table      = 'workshop';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'divisi_id', 'site_id', 'name_karyawan', 'nik',
        'luasan', 'bays', 'kompartemen',
        'status_workshop', 'status_lahan', 'link_map',
        'created_at', 'updated_at', 'is_deleted'
    ];

    // Jika ingin fitur timestamps otomatis, bisa aktifkan:
    // protected $useTimestamps = true;
    // protected $createdField  = 'created_at';
    // protected $updatedField  = 'updated_at';
}
