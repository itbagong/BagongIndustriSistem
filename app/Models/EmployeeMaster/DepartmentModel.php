<?php

namespace App\Models\EmployeeMaster;

use CodeIgniter\Model;

class DepartmentModel extends Model
{
    protected $DBGroup = 'pg';
    protected $table      = 'departments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['id', 'name', 'description', 'is_deleted'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Generate next ID in format DEPT-BDM-XXXX
     */
    public function generateId(): string
    {
        $last = $this->orderBy('id', 'DESC')->first();

        if (!$last) {
            return 'DEPT-BDM-0001';
        }

        $num = (int) substr($last['id'], -4);
        return 'DEPT-BDM-' . str_pad($num + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Toggle is_deleted status
     */
    public function toggleStatus(string $id): bool
    {
        $record = $this->find($id);
        if (!$record) return false;

        $currentlyDeleted = ($record['is_deleted'] === true || $record['is_deleted'] === 't' || $record['is_deleted'] === '1' || $record['is_deleted'] === 1);

        return $this->update($id, [
            'is_deleted' => !$currentlyDeleted,
        ]);
    }
}