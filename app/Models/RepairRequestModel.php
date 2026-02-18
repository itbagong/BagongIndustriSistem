<?php

namespace App\Models;
use CodeIgniter\Model;

class RepairRequestModel extends Model
{
    protected $DBGroup = 'mysql';
    protected $table = 'repair_requests';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true; // UBAH ke true untuk soft delete
    protected $protectFields = true;
    
    protected $allowedFields = [
        'kode_pengajuan',
        'tipe_aset',
        'aset_id',
        'nama_aset',                    // TAMBAHAN
        'lokasi_aset',                  // TAMBAHAN
        'kategori_kerusakan',           // TAMBAHAN
        'jenis_kerusakan',
        'deskripsi_kerusakan',
        'aset_code',              // TAMBAHAN
        'prioritas',
        'tingkat_urgensi',              // TAMBAHAN
        'estimasi_biaya',
        'biaya_aktual',
        'catatan',
        'foto_kerusakan',
        'foto_progress',                // TAMBAHAN
        'foto_selesai',                 // TAMBAHAN
        'lampiran',
        'catatan_persetujuan',          // TAMBAHAN
        'catatan_selesai',
        'alasan_penolakan',
        'status',
        'progress_percentage',          // TAMBAHAN
        'vendor_id',                    // TAMBAHAN
        'nama_vendor',                  // TAMBAHAN
        'nomor_kontrak',                // TAMBAHAN
        'target_selesai',               // TAMBAHAN
        'durasi_estimasi_hari',         // TAMBAHAN
        'tanggal_pengajuan',
        'tanggal_disetujui',
        'tanggal_mulai',
        'tanggal_selesai',
        'tanggal_ditolak',
        'tanggal_dibatalkan',           // TAMBAHAN
        'disetujui_oleh',
        'ditolak_oleh',                 // TAMBAHAN
        'dibatalkan_oleh',              // TAMBAHAN
        'penanggung_jawab',             // TAMBAHAN
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at',                   // TAMBAHAN
        'rating_perbaikan',             // TAMBAHAN
        'feedback'                      // TAMBAHAN
    ];

    // Dates
    protected $useTimestamps = true; // UBAH ke true
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'kode_pengajuan' => 'required|is_unique[repair_requests.kode_pengajuan,id,{id}]',
        'tipe_aset' => 'required|in_list[Mess,Workshop]',
        'aset_id' => 'required|numeric',
        'kategori_kerusakan' => 'required|in_list[Ringan,Sedang,Berat,Darurat]', // TAMBAHAN
        'jenis_kerusakan' => 'required|min_length[5]|max_length[255]', // UPDATE min_length
        'deskripsi_kerusakan' => 'required',
        'prioritas' => 'required|in_list[Segera,Normal,Rendah]',
        'status' => 'permit_empty|in_list[Pending,Approved,In Progress,Completed,Rejected,Cancelled]', // TAMBAH Cancelled
    ];

    protected $validationMessages = [
        'kode_pengajuan' => [
            'required' => 'Kode pengajuan harus diisi',
            'is_unique' => 'Kode pengajuan sudah digunakan'
        ],
        'tipe_aset' => [
            'required' => 'Tipe aset harus dipilih',
            'in_list' => 'Tipe aset tidak valid'
        ],
        'aset_id' => [
            'required' => 'Aset harus dipilih',
            'numeric' => 'ID aset tidak valid'
        ],
        'kategori_kerusakan' => [
            'required' => 'Kategori kerusakan harus dipilih',
            'in_list' => 'Kategori kerusakan tidak valid'
        ],
        'jenis_kerusakan' => [
            'required' => 'Jenis kerusakan harus diisi',
            'min_length' => 'Jenis kerusakan minimal 5 karakter'
        ],
        'deskripsi_kerusakan' => [
            'required' => 'Deskripsi kerusakan harus diisi',
            'min_length' => 'Deskripsi kerusakan minimal 10 karakter'
        ],
        'prioritas' => [
            'required' => 'Prioritas harus dipilih',
            'in_list' => 'Prioritas tidak valid'
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['setDefaults'];
    protected $afterInsert = [];
    protected $beforeUpdate = ['updateTimestamp'];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = ['parseJsonFields'];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    // ===============================================
    // CALLBACKS
    // ===============================================
    
    /**
     * Set default values before insert
     */
    protected function setDefaults(array $data)
    {
        if (!isset($data['data']['status'])) {
            $data['data']['status'] = 'Pending';
        }
        
        if (!isset($data['data']['progress_percentage'])) {
            $data['data']['progress_percentage'] = 0;
        }
        
        if (!isset($data['data']['tanggal_pengajuan'])) {
            $data['data']['tanggal_pengajuan'] = date('Y-m-d H:i:s');
        }

        return $data;
    }

    /**
     * Update timestamp before update
     */
    protected function updateTimestamp(array $data)
    {
        if (isset($data['data']) && !isset($data['data']['updated_at'])) {
            $data['data']['updated_at'] = date('Y-m-d H:i:s');
        }

        return $data;
    }

    /**
     * Parse JSON fields after find
     */
    protected function parseJsonFields(array $data)
    {
        if (isset($data['data'])) {
            $jsonFields = ['foto_kerusakan', 'foto_progress', 'foto_selesai', 'lampiran'];
            
            foreach ($jsonFields as $field) {
                if (isset($data['data'][$field]) && !empty($data['data'][$field])) {
                    $decoded = json_decode($data['data'][$field], true);
                    $data['data'][$field] = $decoded ?? [];
                }
            }
        }

        return $data;
    }

    // ===============================================
    // EXISTING METHODS (tetap ada)
    // ===============================================

    /**
     * Get repair requests with asset details
     */
    public function getWithDetails($filters = [])
    {
        $builder = $this->builder();
        
        $builder->select('repair_requests.*, 
                         CASE 
                            WHEN repair_requests.tipe_aset = "Mess" THEN mess.nama_karyawan
                            WHEN repair_requests.tipe_aset = "Workshop" THEN workshop.name_karyawan
                         END as nama_karyawan,
                         CASE 
                            WHEN repair_requests.tipe_aset = "Mess" THEN mess.nik
                            WHEN repair_requests.tipe_aset = "Workshop" THEN workshop.nik
                         END as nik,
                         CASE 
                            WHEN repair_requests.tipe_aset = "Mess" THEN mess.site_id
                            WHEN repair_requests.tipe_aset = "Workshop" THEN workshop.site_id
                         END as site_id,
                         CASE 
                            WHEN repair_requests.tipe_aset = "Mess" THEN mess.luasan_mess
                            WHEN repair_requests.tipe_aset = "Workshop" THEN workshop.luasan
                         END as luas,
                         divisi.name as divisi_name,
                         users.username as created_by_name');
        
        $builder->join('mess', 'mess.id = repair_requests.aset_id AND repair_requests.tipe_aset = "Mess"', 'left');
        $builder->join('workshop', 'workshop.id = repair_requests.aset_id AND repair_requests.tipe_aset = "Workshop"', 'left');
        $builder->join('divisi', 'divisi.id = COALESCE(mess.divisi_id, workshop.divisi_id)', 'left');
        $builder->join('users', 'users.id = repair_requests.created_by', 'left');

        // Apply filters
        if (isset($filters['tipe_aset']) && !empty($filters['tipe_aset'])) {
            $builder->where('repair_requests.tipe_aset', $filters['tipe_aset']);
        }

        if (isset($filters['status']) && !empty($filters['status'])) {
            $builder->where('repair_requests.status', $filters['status']);
        }

        if (isset($filters['prioritas']) && !empty($filters['prioritas'])) {
            $builder->where('repair_requests.prioritas', $filters['prioritas']);
        }

        if (isset($filters['divisi_id']) && !empty($filters['divisi_id'])) {
            $builder->where('divisi.id', $filters['divisi_id']);
        }

        if (isset($filters['search']) && !empty($filters['search'])) {
            $builder->groupStart()
                ->like('repair_requests.kode_pengajuan', $filters['search'])
                ->orLike('mess.nama_karyawan', $filters['search'])
                ->orLike('mess.nik', $filters['search'])
                ->orLike('workshop.name_karyawan', $filters['search'])
                ->orLike('workshop.nik', $filters['search'])
                ->orLike('repair_requests.jenis_kerusakan', $filters['search'])
                ->groupEnd();
        }

        if (isset($filters['date_from']) && !empty($filters['date_from'])) {
            $builder->where('repair_requests.tanggal_pengajuan >=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && !empty($filters['date_to'])) {
            $builder->where('repair_requests.tanggal_pengajuan <=', $filters['date_to']);
        }

        $builder->orderBy('repair_requests.created_at', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Get single repair request with details
     */
    public function getDetailById($id)
    {
        $builder = $this->builder();
        
        $builder->select('repair_requests.*, 
                         CASE 
                            WHEN repair_requests.tipe_aset = "Mess" THEN mess.nama_karyawan
                            WHEN repair_requests.tipe_aset = "Workshop" THEN workshop.name_karyawan
                         END as nama_karyawan,
                         CASE 
                            WHEN repair_requests.tipe_aset = "Mess" THEN mess.nik
                            WHEN repair_requests.tipe_aset = "Workshop" THEN workshop.nik
                         END as nik,
                         CASE 
                            WHEN repair_requests.tipe_aset = "Mess" THEN mess.site_id
                            WHEN repair_requests.tipe_aset = "Workshop" THEN workshop.site_id
                         END as site_id,
                         CASE 
                            WHEN repair_requests.tipe_aset = "Mess" THEN mess.luasan_mess
                            WHEN repair_requests.tipe_aset = "Workshop" THEN workshop.luasan
                         END as luas,
                         CASE 
                            WHEN repair_requests.tipe_aset = "Mess" THEN mess.status_kepemilikan
                            WHEN repair_requests.tipe_aset = "Workshop" THEN workshop.status_workshop
                         END as status_kepemilikan,
                         divisi.name as divisi_name,
                         creator.username as created_by_name,
                         approver.username as disetujui_oleh_name,
                         rejector.username as ditolak_oleh_name,
                         canceller.username as dibatalkan_oleh_name,
                         pic.username as penanggung_jawab_name');
        
        $builder->join('mess', 'mess.id = repair_requests.aset_id AND repair_requests.tipe_aset = "Mess"', 'left');
        $builder->join('workshop', 'workshop.id = repair_requests.aset_id AND repair_requests.tipe_aset = "Workshop"', 'left');
        $builder->join('divisi', 'divisi.id = COALESCE(mess.divisi_id, workshop.divisi_id)', 'left');
        $builder->join('users creator', 'creator.id = repair_requests.created_by', 'left');
        $builder->join('users approver', 'approver.id = repair_requests.disetujui_oleh', 'left');
        $builder->join('users rejector', 'rejector.id = repair_requests.ditolak_oleh', 'left'); // TAMBAHAN
        $builder->join('users canceller', 'canceller.id = repair_requests.dibatalkan_oleh', 'left'); // TAMBAHAN
        $builder->join('users pic', 'pic.id = repair_requests.penanggung_jawab', 'left'); // TAMBAHAN
        
        $builder->where('repair_requests.id', $id);

        return $builder->get()->getRowArray();
    }

    /**
     * Get statistics by status
     */
    public function getStatsByStatus()
    {
        $builder = $this->builder();
        $builder->select('status, COUNT(*) as total');
        $builder->groupBy('status');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get statistics by priority
     */
    public function getStatsByPriority()
    {
        $builder = $this->builder();
        $builder->select('prioritas, COUNT(*) as total');
        $builder->groupBy('prioritas');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get statistics by asset type
     */
    public function getStatsByAssetType()
    {
        $builder = $this->builder();
        $builder->select('tipe_aset, COUNT(*) as total, SUM(estimasi_biaya) as total_estimasi');
        $builder->groupBy('tipe_aset');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get pending requests count
     */
    public function getPendingCount()
    {
        return $this->where('status', 'Pending')->countAllResults();
    }

    /**
     * Get urgent requests
     */
    public function getUrgentRequests()
    {
        return $this->where('prioritas', 'Urgent')
                    ->whereIn('status', ['Pending', 'Approved', 'In Progress'])
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get requests by date range
     */
    public function getByDateRange($startDate, $endDate)
    {
        return $this->where('tanggal_pengajuan >=', $startDate)
                    ->where('tanggal_pengajuan <=', $endDate)
                    ->orderBy('tanggal_pengajuan', 'DESC')
                    ->findAll();
    }

    /**
     * Get total estimated cost
     */
    public function getTotalEstimatedCost($filters = [])
    {
        $builder = $this->builder();
        $builder->selectSum('estimasi_biaya', 'total');

        if (isset($filters['tipe_aset'])) {
            $builder->where('tipe_aset', $filters['tipe_aset']);
        }

        if (isset($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        $result = $builder->get()->getRowArray();
        return $result['total'] ?? 0;
    }

    /**
     * Get total actual cost
     */
    public function getTotalActualCost($filters = [])
    {
        $builder = $this->builder();
        $builder->selectSum('biaya_aktual', 'total');
        $builder->where('status', 'Completed');

        if (isset($filters['tipe_aset'])) {
            $builder->where('tipe_aset', $filters['tipe_aset']);
        }

        $result = $builder->get()->getRowArray();
        return $result['total'] ?? 0;
    }

    /**
     * Get monthly report
     */
    public function getMonthlyReport($year, $month)
    {
        $builder = $this->builder();
        
        $builder->select('
            COUNT(*) as total_pengajuan,
            SUM(CASE WHEN status = "Pending" THEN 1 ELSE 0 END) as total_pending,
            SUM(CASE WHEN status = "Approved" THEN 1 ELSE 0 END) as total_approved,
            SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as total_completed,
            SUM(CASE WHEN status = "Rejected" THEN 1 ELSE 0 END) as total_rejected,
            SUM(CASE WHEN status = "Cancelled" THEN 1 ELSE 0 END) as total_cancelled,
            SUM(estimasi_biaya) as total_estimasi,
            SUM(biaya_aktual) as total_aktual
        ');
        
        $builder->where('YEAR(tanggal_pengajuan)', $year);
        $builder->where('MONTH(tanggal_pengajuan)', $month);

        return $builder->get()->getRowArray();
    }

    // ===============================================
    // NEW METHODS - TAMBAHAN
    // ===============================================

    /**
     * Get requests by specific aset
     */
    public function getByAset($tipeAset, $asetId)
    {
        return $this->where('tipe_aset', $tipeAset)
                    ->where('aset_id', $asetId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get completion rate
     */
    public function getCompletionRate($filters = [])
    {
        $builder = $this->builder();
        
        $builder->select('
            COUNT(*) as total,
            SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as completed,
            ROUND((SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as completion_rate
        ');

        if (isset($filters['tipe_aset'])) {
            $builder->where('tipe_aset', $filters['tipe_aset']);
        }

        if (isset($filters['date_from'])) {
            $builder->where('tanggal_pengajuan >=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $builder->where('tanggal_pengajuan <=', $filters['date_to']);
        }

        return $builder->get()->getRowArray();
    }

    /**
     * Get average rating
     */
    public function getAverageRating($filters = [])
    {
        $builder = $this->builder();
        $builder->selectAvg('rating_perbaikan', 'avg_rating');
        $builder->where('rating_perbaikan IS NOT NULL');
        $builder->where('status', 'Completed');

        if (isset($filters['tipe_aset'])) {
            $builder->where('tipe_aset', $filters['tipe_aset']);
        }

        $result = $builder->get()->getRowArray();
        return round($result['avg_rating'] ?? 0, 2);
    }

    /**
     * Get overdue repairs (melewati target_selesai)
     */
    public function getOverdueRepairs()
    {
        return $this->where('target_selesai <', date('Y-m-d'))
                    ->whereIn('status', ['Pending', 'Approved', 'In Progress'])
                    ->orderBy('target_selesai', 'ASC')
                    ->findAll();
    }

    /**
     * Get repairs by kategori kerusakan
     */
    public function getByKategoriKerusakan($kategori = null)
    {
        $builder = $this->builder();
        $builder->select('kategori_kerusakan, COUNT(*) as total');
        
        if ($kategori) {
            $builder->where('kategori_kerusakan', $kategori);
        }
        
        $builder->groupBy('kategori_kerusakan');
        return $builder->get()->getResultArray();
    }

    /**
     * Update progress percentage
     */
    public function updateProgress($id, $percentage)
    {
        return $this->update($id, [
            'progress_percentage' => $percentage,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus(array $ids, string $status)
    {
        return $this->whereIn('id', $ids)
                    ->set([
                        'status' => $status,
                        'updated_at' => date('Y-m-d H:i:s')
                    ])
                    ->update();
    }
}