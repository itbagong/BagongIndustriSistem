<?php
namespace App\Models;
use CodeIgniter\Model;

class RepairDocumentModel extends Model
{
    protected $DBGroup    = 'mysql';
    protected $table      = 'repair_documents';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'repair_id', 'file_path', 'file_name', 'file_size',
        'uploaded_by', 'uploaded_at', 'keterangan', 'is_latest'
    ];

    /**
     * Ambil semua dokumen milik 1 pengajuan, terbaru di atas
     */
    public function getByRepair(int $repairId): array
    {
        return $this->where('repair_id', $repairId)
                    ->orderBy('uploaded_at', 'DESC')
                    ->findAll();
    }

    /**
     * Ambil dokumen terbaru saja
     */
    public function getLatest(int $repairId): ?array
    {
        return $this->where('repair_id', $repairId)
                    ->where('is_latest', 1)
                    ->first();
    }

    /**
     * Set semua is_latest=0 dulu, lalu set id terbaru=1
     */
    public function setLatest(int $repairId, int $docId): void
    {
        $this->where('repair_id', $repairId)->set('is_latest', 0)->update();
        $this->update($docId, ['is_latest' => 1]);
    }

    /**
     * Setelah hapus, recalculate is_latest ke dokumen berikutnya
     */
    public function recalcLatest(int $repairId): void
    {
        $this->where('repair_id', $repairId)->set('is_latest', 0)->update();
        $latest = $this->where('repair_id', $repairId)
                       ->orderBy('uploaded_at', 'DESC')
                       ->first();
        if ($latest) {
            $this->update($latest['id'], ['is_latest' => 1]);
        }
    }
}