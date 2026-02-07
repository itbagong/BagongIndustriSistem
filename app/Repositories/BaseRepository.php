<?php

namespace App\Repositories;

use App\Traits\Cacheable;
use CodeIgniter\Model;

abstract class BaseRepository
{
    use Cacheable;

    protected Model $model;
    protected string $cachePrefix;
    protected int $cacheTTL = 300;

    public function __construct()
    {
        $this->model = $this->getModel();
        $this->cachePrefix = $this->getCachePrefix();
    }

    /**
     * Must be implemented by child class
     */
    abstract protected function getModel(): Model;

    /**
     * Get cache prefix (default: model table name)
     */
    protected function getCachePrefix(): string
    {
        return $this->model->table ?? 'unknown';
    }

    /**
     * Find by ID (cached)
     */
    public function find(int $id): ?array
    {
        return $this->remember("find:{$id}", function() use ($id) {
            return $this->model->find($id);
        });
    }

    /**
     * Find all (cached)
     */
    public function all(int $limit = null, int $offset = 0): array
    {
        $key = "all:{$limit}:{$offset}";
        
        return $this->remember($key, function() use ($limit, $offset) {
            if ($limit) {
                return $this->model->findAll($limit, $offset);
            }
            return $this->model->findAll();
        });
    }

    /**
     * Insert and invalidate cache
     */
    public function insert(array $data): int
    {
        $id = $this->model->insert($data);
        $this->flushCache();
        return $id;
    }

    /**
     * Update and invalidate cache
     */
    public function update(int $id, array $data): bool
    {
        $result = $this->model->update($id, $data);
        
        if ($result) {
            $this->forget("find:{$id}");
            $this->flushCache();
        }

        return $result;
    }

    /**
     * Delete and invalidate cache
     */
    public function delete(int $id): bool
    {
        $result = $this->model->delete($id);
        
        if ($result) {
            $this->flushCache();
        }

        return $result;
    }
}