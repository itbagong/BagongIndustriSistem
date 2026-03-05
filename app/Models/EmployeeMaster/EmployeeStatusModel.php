<?php

namespace App\Models\EmployeeMaster;

use CodeIgniter\Model;

class EmployeeStatusModel extends Model
{
    protected $DBGroup    = 'pg';
    protected $table      = 'employee_statuses';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['id', 'name', 'description', 'is_deleted'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // -------------------------------------------------------
    // Postgres text[] helpers
    // -------------------------------------------------------

    /** PHP array → Postgres literal  e.g. ["a","b"] → {"a","b"} */
    private function toPostgresArray(array $arr): string
    {
        if (empty($arr)) return '{}';
        $escaped = array_map(fn($v) => '"' . str_replace('"', '\\"', $v) . '"', $arr);
        return '{' . implode(',', $escaped) . '}';
    }

    /** Postgres literal → PHP array  e.g. {"a","b"} → ["a","b"] */
    public function fromPostgresArray(?string $str): array
    {
        if (!$str || $str === '{}') return [];
        $str = trim($str, '{}');
        preg_match_all('/"((?:[^"\\\\]|\\\\.)*)"|([^,]+)/', $str, $m);
        return array_map(
            fn($q, $u) => $q !== '' ? stripslashes($q) : trim($u),
            $m[1], $m[2]
        );
    }

    private function decodeRow(array $row): array
    {
        if (isset($row['aliases']) && is_string($row['aliases'])) {
            $row['aliases'] = $this->fromPostgresArray($row['aliases']);
        }
        return $row;
    }

    /**
     * Generate next ID in format EMST-BDM-XXXX
     */
    public function generateId(): string
    {
        $last = $this->orderBy('id', 'DESC')->first();

        if (!$last) {
            return 'EMST-BDM-0001';
        }

        $num = (int) substr($last['id'], -4);
        return 'EMST-BDM-' . str_pad($num + 1, 4, '0', STR_PAD_LEFT);
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

    // -------------------------------------------------------
    // INSERT with aliases
    // -------------------------------------------------------
    public function insertEs(array $data): void
    {
        $aliases = $this->toPostgresArray($data['aliases'] ?? []);

        $this->db->query(
            'INSERT INTO employment_statuses (id, name, description, aliases, is_deleted)
             VALUES (?, ?, ?, ?, false)',
            [$data['id'], $data['name'], $data['description'], $aliases]
        );
    }

    // -------------------------------------------------------
    // UPDATE — handles optional ID rename with FK cascade
    // -------------------------------------------------------
    public function updateEs(string $oldId, array $data): void
    {
        $newId   = trim($data['id'] ?? $oldId);
        $aliases = $this->toPostgresArray($data['aliases'] ?? []);

        $this->db->transStart();

        if ($newId !== $oldId) {
            // 1. Insert new row copying is_deleted from the old one
            $this->db->query(
                'INSERT INTO employee_statuses (id, name, description, aliases, is_deleted)
                 SELECT ?, ?, ?, ?, is_deleted FROM employee_statuses WHERE id = ?',
                [$newId, $data['name'], $data['description'], $aliases, $oldId]
            );

            // 2. Re-point every employee that referenced the old site_id
            $this->db->query(
                'UPDATE employees SET employee_status_id = ? WHERE employee_status_id = ?',
                [$newId, $oldId]
            );

            // 3. Remove the old row
            $this->db->query('DELETE FROM employee_statuses WHERE id = ?', [$oldId]);

        } else {
            $this->db->query(
                'UPDATE employee_statuses SET name=?, description=?, aliases=? WHERE id=?',
                [$data['name'], $data['description'], $aliases, $oldId]
            );
        }

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            throw new \RuntimeException('Employee status update transaction failed.');
        }
    }
}