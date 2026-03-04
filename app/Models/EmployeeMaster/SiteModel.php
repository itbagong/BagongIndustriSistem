<?php

namespace App\Models\EmployeeMaster;

use CodeIgniter\Model;

class SiteModel extends Model
{
    protected $DBGroup    = 'pg';
    protected $table      = 'sites';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['id', 'name', 'address', 'description', 'is_deleted'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Generate next ID in format SITE-XXXX
     */
    public function generateId(): string
    {
        $last = $this->orderBy('id', 'DESC')->first();

        if (!$last) {
            return 'SITE001';
        }

        $num = (int) substr($last['id'], 4);
        return 'SITE' . str_pad($num + 1, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Toggle is_deleted status (handles PostgreSQL 't'/'f' strings)
     */
    public function toggleStatus(string $id): bool
    {
        $record = $this->find($id);
        if (!$record) return false;

        $currentlyDeleted = (
            $record['is_deleted'] === true ||
            $record['is_deleted'] === 't'  ||
            $record['is_deleted'] === '1'  ||
            $record['is_deleted'] === 1
        );

        return $this->update($id, ['is_deleted' => !$currentlyDeleted]);
    }
}