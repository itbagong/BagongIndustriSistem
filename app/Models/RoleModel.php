<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false; // roles biasanya tidak soft-delete
    protected $protectFields    = true;

    protected $allowedFields = [
        'name',
        'display_name',
        'description',
        'level',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // ── Validasi ──────────────────────────────────────────────
    protected $validationRules = [
        'name'         => 'required|min_length[3]|max_length[50]|alpha_dash|is_unique[roles.name,id,{id}]',
        'display_name' => 'required|min_length[3]|max_length[100]',
        'level'        => 'required|integer|greater_than[0]',
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Nama role wajib diisi.',
            'alpha_dash' => 'Nama role hanya boleh huruf, angka, underscore, dan dash. Contoh: admin_gudang.',
            'is_unique'  => 'Nama role sudah digunakan.',
        ],
        'display_name' => [
            'required' => 'Display name wajib diisi.',
        ],
        'level' => [
            'required'      => 'Level wajib diisi.',
            'integer'       => 'Level harus angka.',
            'greater_than'  => 'Level minimal 1.',
        ],
    ];

    // ── Helpers ───────────────────────────────────────────────

    /**
     * Semua role aktif, urutkan level asc
     */
    public function getActive(): array
    {
        return $this->where('is_active', 1)->orderBy('level', 'ASC')->findAll();
    }

    /**
     * List role dengan jumlah user per role
     */
    public function getListWithUserCount(array $filters = [], int $perPage = 10, int $page = 1): array
    {
        $builder = $this->buildQuery($filters);

        return $builder
            ->orderBy('r.level', 'ASC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();
    }

    public function countList(array $filters = []): int
    {
        return $this->buildQuery($filters)->countAllResults();
    }

    private function buildQuery(array $filters): \CodeIgniter\Database\BaseBuilder
    {
        $builder = $this->db->table('roles r')
            ->select('r.*, COUNT(u.id) AS user_count')
            ->join('users u', 'u.role_id = r.id AND u.deleted_at IS NULL', 'left')
            ->groupBy('r.id');

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('r.name',         $filters['search'])
                ->orLike('r.display_name', $filters['search'])
            ->groupEnd();
        }

        if ($filters['level'] ?? '' !== '') {
            $builder->where('r.level', $filters['level']);
        }

        if ($filters['is_active'] ?? '' !== '') {
            $builder->where('r.is_active', $filters['is_active']);
        }

        return $builder;
    }

    /**
     * Cek apakah role masih dipakai user
     */
    public function isUsed(int $id): bool
    {
        return $this->db->table('users')
            ->where('role_id', $id)
            ->where('deleted_at IS NULL')
            ->countAllResults() > 0;
    }
}