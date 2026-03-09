<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeImportJobModel extends Model
{
    protected $DBGroup          = 'pg';
    protected $table            = 'employee_import_jobs';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'file_name', 'status', 'processed', 'total',
        'inserted', 'updated', 'skipped', 'logs', 'message',
    ];

    // ── Lock detection ───────────────────────────────────────────────────

    /**
     * Returns the first running job whose updated_at heartbeat is recent.
     * Jobs older than $staleMinutes are considered dead (process crashed).
     */
    public function getRunningJob(int $staleMinutes = 10): ?array
    {
        return $this->where('status', 'running')
                    ->where('updated_at >=', date('Y-m-d H:i:s', strtotime("-{$staleMinutes} minutes")))
                    ->orderBy('id', 'DESC')
                    ->first();
    }

    // ── Atomic claim ─────────────────────────────────────────────────────

    /**
     * Transitions a job from 'pending' → 'running' in a single UPDATE.
     * Returns true only if this process won the race.
     */
    public function claimJob(int $id): bool
    {
        $this->db->table('employee_import_jobs')
                 ->where('id', $id)
                 ->where('status', 'pending')
                 ->update(['status' => 'running', 'updated_at' => date('Y-m-d H:i:s')]);

        return $this->db->affectedRows() > 0;
    }

    // ── Incremental log flush ────────────────────────────────────────────

    /**
     * Append log-entry objects to the JSONB logs column.
     * Each entry: ['level' => 'success|update|warn|error|info', 'message' => '…']
     */
    public function appendLogs(int $id, array $entries): void
    {
        if (empty($entries)) {
            return;
        }

        $this->db->query(
            "UPDATE employee_import_jobs
             SET    logs       = logs || ?::jsonb,
                    updated_at = NOW()
             WHERE  id = ?",
            [json_encode($entries), $id]
        );
    }

    // ── Progress heartbeat ───────────────────────────────────────────────

    public function bumpProgress(int $id, int $processed, int $inserted, int $updated, int $skipped): void
    {
        $this->db->query(
            "UPDATE employee_import_jobs
             SET processed  = ?,
                 inserted   = ?,
                 updated    = ?,
                 skipped    = ?,
                 updated_at = NOW()
             WHERE id = ?",
            [$processed, $inserted, $updated, $skipped, $id]
        );
    }

    // ── Terminal states ──────────────────────────────────────────────────

    public function markFailed(int $id, string $message): void
    {
        $this->update($id, ['status' => 'failed', 'message' => $message]);
    }

    public function markDone(int $id, int $processed, int $inserted, int $updated, int $skipped): void
    {
        $this->update($id, [
            'status'    => 'done',
            'processed' => $processed,
            'inserted'  => $inserted,
            'updated'   => $updated,
            'skipped'   => $skipped,
            'message'   => "Import complete: {$inserted} inserted, {$updated} updated, {$skipped} skipped.",
        ]);
    }

    // ── Restart ──────────────────────────────────────────────────────────

    /**
     * Resets a done/failed job back to pending.
     * Returns false if the job is currently running.
     */
    public function resetJob(int $id): bool
    {
        $job = $this->find($id);
        if (! $job || $job['status'] === 'running') {
            return false;
        }

        $this->db->query(
            "UPDATE employee_import_jobs
             SET status     = 'pending',
                 processed  = 0,
                 inserted   = 0,
                 updated    = 0,
                 skipped    = 0,
                 logs       = '[]'::jsonb,
                 message    = NULL,
                 updated_at = NOW()
             WHERE id = ?",
            [$id]
        );

        return $this->db->affectedRows() > 0;
    }

    // ── Delete ───────────────────────────────────────────────────────────

    public function deleteJob(int $id): bool
    {
        return (bool) $this->delete($id);
    }

    // ── List ─────────────────────────────────────────────────────────────

    public function getAllJobs(int $limit = 100, int $offset = 0): array
    {
        $jobs = $this->orderBy('id', 'DESC')->findAll($limit, $offset);

        return array_map(function (array $job): array {
            if (is_string($job['logs'])) {
                $job['logs'] = json_decode($job['logs'], true) ?? [];
            }
            return $job;
        }, $jobs);
    }

    public function countJobs(): int
    {
        return $this->countAllResults();
    }

    // ── Single job ───────────────────────────────────────────────────────

    public function getJobStatus(int $id): ?array
    {
        $job = $this->find($id);
        if (! $job) {
            return null;
        }

        if (is_string($job['logs'])) {
            $job['logs'] = json_decode($job['logs'], true) ?? [];
        }

        return $job;
    }
}