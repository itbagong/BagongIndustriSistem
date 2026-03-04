<?php

namespace App\Models\EmployeeMaster;

use CodeIgniter\Model;

class BloodTypeModel extends Model
{
    protected $DBGroup = 'pg';
    protected $table      = 'blood_types';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['id', 'name', 'is_deleted'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Generate next ID in format BT-BDM-XXXX
     */
    public function generateId(): string
    {
        $last = $this->orderBy('id', 'DESC')->first();

        if (!$last) {
            return 'BT-BDM-0001';
        }

        $num = (int) substr($last['id'], -4);
        return 'BT-BDM-' . str_pad($num + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get all active (non-deleted) blood types
     */
    public function getActive(): array
    {
        return $this->where('is_deleted', false)->findAll();
    }

    /**
     * Toggle is_deleted status
     */
    public function toggleStatus(string $id): bool
    {
        $record = $this->find($id);
        if (!$record) return false;

        // PostgreSQL returns 't'/'f' strings instead of true/false
        $currentlyDeleted = ($record['is_deleted'] === true || $record['is_deleted'] === 't' || $record['is_deleted'] === '1' || $record['is_deleted'] === 1);

        return $this->update($id, [
            'is_deleted' => !$currentlyDeleted,
        ]);
    }
}