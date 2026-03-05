<?php

namespace App\Models\EmployeeMaster;

use CodeIgniter\Model;

class DivisionModel extends Model
{
    protected $DBGroup = 'pg';
    protected $table      = 'divisions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['id', 'name', 'code', 'description', 'is_deleted'];

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
     * Generate next ID in format BU-BDM-XXXX
     */
    public function generateId(): string
    {
        $last = $this->orderBy('id', 'DESC')->first();

        if (!$last) {
            return 'BU-BDM-0001';
        }

        $num = (int) substr($last['id'], -4);
        return 'BU-BDM-' . str_pad($num + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Toggle is_deleted status (handles PostgreSQL 't'/'f' strings)
     */
    public function toggleStatus(string $id): bool
    {
        $record = $this->find($id);
        if (!$record) return false;

        $currentlyDeleted = (
            $record['is_deleted'] === true  ||
            $record['is_deleted'] === 't'   ||
            $record['is_deleted'] === '1'   ||
            $record['is_deleted'] === 1
        );

        return $this->update($id, ['is_deleted' => !$currentlyDeleted]);
    }

    // -------------------------------------------------------
    // INSERT with aliases
    // -------------------------------------------------------
    public function insertDiv(array $data): void
    {
        $aliases = $this->toPostgresArray($data['aliases'] ?? []);

        $this->db->query(
            'INSERT INTO divisions (id, name, code, description, aliases, is_deleted)
             VALUES (?, ?, ?, ?, ?, false)',
            [$data['id'], $data['name'], $data['code'], $data['description'], $aliases]
        );
    }

    // -------------------------------------------------------
    // UPDATE — handles optional ID rename with FK cascade
    // -------------------------------------------------------
    public function updateDiv(string $oldId, array $data): void
    {
        $newId   = trim($data['id'] ?? $oldId);
        $aliases = $this->toPostgresArray($data['aliases'] ?? []);

        $this->db->transStart();

        if ($newId !== $oldId) {
            // 1. Insert new row copying is_deleted from the old one
            $this->db->query(
                'INSERT INTO divisions (id, name, code, description, aliases, is_deleted)
                 SELECT ?, ?, ?, ?, ?, is_deleted FROM divisions WHERE id = ?',
                [$newId, $data['name'], $data['code'], $data['description'], $aliases, $oldId]
            );

            // 2. Re-point every employee that referenced the old site_id
            $this->db->query(
                'UPDATE employees SET division_id = ? WHERE division_id = ?',
                [$newId, $oldId]
            );

            // 3. Remove the old row
            $this->db->query('DELETE FROM divisions WHERE id = ?', [$oldId]);

        } else {
            $this->db->query(
                'UPDATE divisions SET name=?, code=?, description=?, aliases=? WHERE id=?',
                [$data['name'], $data['code'], $data['description'], $aliases, $oldId]
            );
        }

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            throw new \RuntimeException('Employee status update transaction failed.');
        }
    }
}