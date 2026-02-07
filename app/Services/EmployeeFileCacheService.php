<?php
namespace App\Services;

/**
 * EmployeeFileCacheService
 * - File-based cache untuk employee master
 * - Safe write menggunakan temporary file + rename
 * - Simple locking agar tidak double-fetch
 */
class EmployeeFileCacheService
{
    protected string $cacheFile;
    protected int $ttl; // detik
    protected $api; // optional API client (mis. $this->api dari BaseApiController)

    public function __construct($apiClient = null, string $cacheFile = WRITEPATH . 'cache/employees_master_v1.json', int $ttl = 600)
    {
        $this->api = $apiClient; // bisa null, caller harus pastikan
        $this->cacheFile = $cacheFile;
        $this->ttl = $ttl;

        $this->ensureCacheDir();
    }

    protected function ensureCacheDir()
    {
        $dir = dirname($this->cacheFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Ambil semua employee (cache-first)
     * Jika cache expired/missing -> fetchFromApi() -> simpan file
     * Return array (list)
     */
    public function all(): array
    {
        // Jika file ada dan belum expired -> baca
        if (file_exists($this->cacheFile) && (filemtime($this->cacheFile) + $this->ttl) > time()) {
            $content = @file_get_contents($this->cacheFile);
            if ($content !== false) {
                $data = json_decode($content, true);
                if (is_array($data)) {
                    return $data;
                }
            }
            // kalau gagal decode -> lanjut ke fetch
        }

        // Lock file untuk mencegah N concurrent fetch
        $lockFile = $this->cacheFile . '.lock';
        $fpLock = @fopen($lockFile, 'w+');
        $gotLock = false;
        if ($fpLock) {
            $gotLock = flock($fpLock, LOCK_EX);
        }

        try {
            // Double-check: mungkin ada process lain yang sudah menulis cache
            if (file_exists($this->cacheFile) && (filemtime($this->cacheFile) + $this->ttl) > time()) {
                $content = @file_get_contents($this->cacheFile);
                if ($content !== false) {
                    $data = json_decode($content, true);
                    if (is_array($data)) {
                        return $data;
                    }
                }
            }

            // Fetch dari API (caller harus menyediakan $this->api atau override service)
            $data = $this->fetchFromApi();

            if (!is_array($data)) {
                $data = [];
            }

            // Simpan ke temp file -> rename (atomic)
            $tmp = $this->cacheFile . '.tmp';
            file_put_contents($tmp, json_encode($data, JSON_UNESCAPED_UNICODE));
            @chmod($tmp, 0644);
            rename($tmp, $this->cacheFile);

            return $data;
        } finally {
            if ($gotLock && $fpLock) {
                flock($fpLock, LOCK_UN);
            }
            if ($fpLock) {
                fclose($fpLock);
            }
            // optional: cleanup lock file
            @unlink($lockFile);
        }
    }

    /**
     * Simple fuzzy/search function
     * - prioritas contains (name / nik)
     * - fallback similar_text threshold
     */
    public function search(string $keyword, int $limit = 20): array
    {
        $keyword = strtolower(trim($keyword));
        if ($keyword === '') return [];

        $all = $this->all();
        $results = [];

        foreach ($all as $emp) {
            $name = strtolower($emp['employee_name'] ?? ($emp['name'] ?? ''));
            $nik  = strtolower($emp['employee_number'] ?? ($emp['nik'] ?? ''));

            // contains
            if ($name !== '' && strpos($name, $keyword) !== false) {
                $results[] = $emp;
                continue;
            }
            if ($nik !== '' && strpos($nik, $keyword) !== false) {
                $results[] = $emp;
                continue;
            }

            // fuzzy similarity (fallback)
            if ($name !== '') {
                similar_text($name, $keyword, $percent);
                if ($percent > 45) { // threshold, boleh disesuaikan
                    $results[] = $emp;
                    continue;
                }
            }
        }

        // reindex & limit
        $results = array_values($results);
        if (count($results) > $limit) {
            $results = array_slice($results, 0, $limit);
        }

        return $results;
    }

    /**
     * Force clear cache file (mis. dipanggil saat admin update)
     */
    public function clear(): bool
    {
        if (file_exists($this->cacheFile)) {
            return @unlink($this->cacheFile);
        }
        return true;
    }

    /**
     * Basic fetch function - gunakan $this->api jika ada
     * Caller dapat inject API client di constructor (mis. $this->api)
     *
     * Harap sesuaikan jika API memerlukan auth/header khusus.
     */
    protected function fetchFromApi(): array
    {
        // Jika ada API client (mis. BaseApiController punya $this->api)
        if ($this->api !== null) {
            try {
                // diasumsikan $this->api->get('employees') mengembalikan Response-like
                $response = $this->api->get('employees');
                $body = (string) $response->getBody();
                $result = json_decode($body, true);
                if (isset($result['data']) && is_array($result['data'])) {
                    return $result['data'];
                } elseif (is_array($result)) {
                    return $result;
                }
            } catch (\Throwable $e) {
                log_message('error', 'EmployeeFileCacheService fetchFromApi error: ' . $e->getMessage());
                return [];
            }
        }

        // Jika tidak ada API client, coba CurlRequest default (opsional)
        try {
            $client = \Config\Services::curlrequest();
            // Pastikan BASE_URI diset di config jika ingin memakai ini
            $resp = $client->get('employees'); // mungkin perlu full URL
            $result = json_decode((string)$resp->getBody(), true);
            if (isset($result['data']) && is_array($result['data'])) {
                return $result['data'];
            } elseif (is_array($result)) {
                return $result;
            }
        } catch (\Throwable $e) {
            log_message('error', 'EmployeeFileCacheService fetchFromApi (curl) error: ' . $e->getMessage());
        }

        return [];
    }
}
