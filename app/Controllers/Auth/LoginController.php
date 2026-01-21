<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseApiController;
use App\Models\AuditLogModel;
use App\Models\UserSessionModel;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\PermissionModel;
use CodeIgniter\HTTP\RedirectResponse;
use Config\Services;

class LoginController extends BaseApiController
{
    protected $auditLogModel;
    protected $sessionModel;
    protected $userModel;
    protected $roleModel;
    protected $permissionModel;

    public function __construct()
    {
        helper('cookie');

        $this->auditLogModel   = new AuditLogModel();
        $this->sessionModel    = new UserSessionModel();
        $this->userModel       = new UserModel();
        $this->roleModel       = new RoleModel();
        $this->permissionModel = new PermissionModel();

        // $this->api di-init di BaseApiController::initController()
    }

    public function index()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function process(): RedirectResponse
    {
        $usernameInput = trim((string) ($this->request->getPost('username') ?: $this->request->getPost('email')));
        $password      = (string) $this->request->getPost('password');
        $remember      = (bool) $this->request->getPost('remember');

        if (! $usernameInput || ! $password) {
            return redirect()->back()->withInput()->with('error', 'Username/email dan password harus diisi');
        }

        // 1) panggil API login
        try {
            $response = $this->api->post('login', [
                'json'        => [
                    'email'    => $usernameInput,
                    'password' => $password
                ],
                'http_errors' => false
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'API login error: ' . $e->getMessage());
            $this->logAudit(null, 'login_failed', null, null, 'API connection error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal terhubung ke server otentikasi.');
        }

        $status = $response->getStatusCode();
        $body   = json_decode((string) $response->getBody(), true) ?? [];

        if ($status !== 200) {
            $msg = $body['message'] ?? $body['error'] ?? 'Login gagal';
            $this->logAudit(null, 'login_failed', null, null, 'API responded ' . $status . ': ' . $msg);
            return redirect()->back()->withInput()->with('error', $msg);
        }

        // 2) ambil token dari response (beberapa variasi field ditangani)
        $accessToken  = $body['data']['accessToken']  ?? $body['accessToken'] ?? $body['token'] ?? null;
        $refreshToken = $body['data']['refreshToken'] ?? $body['refreshToken'] ?? null;

        if (! $accessToken) {
            $this->logAudit(null, 'login_failed', null, null, 'No access token in API response');
            return redirect()->back()->withInput()->with('error', 'Server otentikasi tidak mengembalikan token.');
        }

        // simpan token sementara di session (dipakai untuk request /user dan request selanjutnya)
        session()->set([
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken
        ]);

        // 3) ambil profile /user menggunakan token
        try {
            $resUser = $this->api->get('user', [
                'headers'     => ['Authorization' => 'Bearer ' . $accessToken],
                'http_errors' => false
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'API /user error: ' . $e->getMessage());
            $this->logAudit(null, 'login_failed', null, null, 'Failed to fetch /user: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal mengambil data user dari auth server.');
        }

        $statusUser = $resUser->getStatusCode();
        $payload    = json_decode((string) $resUser->getBody(), true) ?? [];

        if ($statusUser !== 200) {
            $this->logAudit(null, 'login_failed', null, null, 'API /user responded ' . $statusUser);
            return redirect()->back()->withInput()->with('error', 'Gagal mengambil data user dari auth server.');
        }

        // ---------- Normalisasi dan pemilihan user dari payload ----------
        // payload['data'] bisa:
        // - object user
        // - array numerik [user1, user2, ...]
        // - associative array with keys
        $apiData = $payload['data'] ?? $payload;

        $apiUser = null;

        if (is_array($apiData)) {
            // cek apakah list numerik
            $isList = array_keys($apiData) === range(0, count($apiData) - 1);

            if ($isList) {
                // cari user yang cocok dengan email/nickname login yang diberikan
                $search = strtolower($usernameInput);
                foreach ($apiData as $u) {
                    if (!is_array($u)) continue;
                    if (!empty($u['email']) && strtolower($u['email']) === $search) {
                        $apiUser = $u;
                        break;
                    }
                    if (!empty($u['nickName']) && strtolower($u['nickName']) === $search) {
                        $apiUser = $u;
                        break;
                    }
                    // kadang API return 'username' atau 'name'
                    if (!empty($u['username']) && strtolower($u['username']) === $search) {
                        $apiUser = $u;
                        break;
                    }
                }
                // fallback: jika tidak ditemukan, ambil index 0 jika ada
                if (! $apiUser && isset($apiData[0]) && is_array($apiData[0])) {
                    $apiUser = $apiData[0];
                }
            } else {
                // associative array => anggap ini object user
                $apiUser = $apiData;
            }
        } else {
            // bukan array: langsung ambil apa adanya
            $apiUser = $apiData;
        }

        // validasi minimal
        if (! is_array($apiUser) || empty($apiUser['id'])) {
            $this->logAudit(null, 'login_failed', null, null, 'Profile incomplete or not found in /user payload: ' . print_r($payload, true));
            return redirect()->back()->withInput()->with('error', 'Profile user tidak lengkap dari auth server.');
        }

        // cek active status
        if (isset($apiUser['activeStatus']) && ! $apiUser['activeStatus']) {
            $this->logAudit($apiUser['id'] ?? null, 'login_failed', null, null, 'User inactive in auth server');
            return redirect()->back()->with('error', 'Akun Anda tidak aktif.');
        }

        // ===========================
        // Mapping role dari API (DI SINI)
        // ===========================
        // mappedRoleId = default staff (ubah sesuai kebutuhan)
        $mappedRoleId = 3;

        // contoh mapping sederhana:
        if (! empty($apiUser['employeeNumber']) && str_starts_with((string) $apiUser['employeeNumber'], '24')) {
            $mappedRoleId = 2;
        }

        if (! empty($apiUser['email']) && strtolower($apiUser['email']) === 'ebri@bagongbis.com') {
            $mappedRoleId = 1; // superadmin
        }

        // 4) sync user ke DB lokal (users.api_user_id = apiUser.id)
        try {
            $existing = $this->userModel->where('api_user_id', $apiUser['id'])->first();

            $data = [
                'api_user_id' => $apiUser['id'],
                'username'    => $apiUser['nickName'] ?? ($apiUser['email'] ?? null),
                'email'       => $apiUser['email'] ?? null,
                'is_active'   => ! empty($apiUser['activeStatus']) ? 1 : 0,
                'last_login'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ];

            if ($existing) {
                // jika role sudah di-set manual/admin, jangan timpa
                $data['role_id'] = $existing['role_id'] ?? $mappedRoleId;

                $this->userModel->update($existing['id'], $data);
                $localUserId = $existing['id'];
                $roleId      = $data['role_id'];
            } else {
                // user baru: beri mapped role sebagai default
                $data['role_id']    = $mappedRoleId;
                $data['created_at'] = date('Y-m-d H:i:s');

                $this->userModel->insert($data);
                $localUserId = $this->userModel->getInsertID();
                $roleId      = $mappedRoleId;
            }
        } catch (\Throwable $e) {
            log_message('error', 'User sync error: ' . $e->getMessage());
            // jangan gagalkan login hanya karena sync fail — tetap set session minimal
            $localUserId = null;
            $roleId      = null;
        }

        // 5) ambil role & permissions lokal (jika ada)
        $role        = null;
        $permissions = [];

        try {
            if ($localUserId && $roleId) {
                $role = $this->roleModel->find($roleId);
                // PermissionModel harus mengembalikan array permission string (permissions.name)
                $permissions = $this->permissionModel->getPermissionsByUserId($localUserId);
            } else {
                // fallback: permission dasar
                $role        = $role ?? ['id' => null, 'name' => 'guest', 'level' => 99];
                $permissions = ['employee.view'];
            }
        } catch (\Throwable $e) {
            log_message('error', 'Role/Permission lookup error: ' . $e->getMessage());
            $permissions = ['employee.view'];
        }

        // pastikan minimal permission tidak hilang
        if (! in_array('employee.view', $permissions, true)) {
            $permissions[] = 'employee.view';
        }

        // 6) simpan session & session_model (hashed token)
        session()->set([
            'logged_in'    => true,
            'access_token' => $accessToken,
            'refresh_token'=> $refreshToken,

            // external id + local id
            'employee_id'  => $apiUser['id'],
            'user_id'      => $localUserId,
            'username'     => $apiUser['nickName'] ?? $apiUser['email'] ?? null,
            'email'        => $apiUser['email'] ?? null,

            'role_id'      => $role['id'] ?? $roleId,
            'role_name'    => $role['name'] ?? null,
            'role_level'   => $role['level'] ?? null,

            'permissions'  => $permissions,
            'user'         => $apiUser,
            'login_time'   => time()
        ]);

        // store hashed token in user_sessions table for tracking (jangan simpan raw token ke DB)
        try {
            $hashed = hash('sha256', $accessToken);
            $now    = date('Y-m-d H:i:s');

            $this->sessionModel->insert([
                // simpan local user id kalau ada, fallback ke employee id
                'user_id'       => $localUserId ?? $apiUser['id'],
                'session_token' => $hashed,
                'ip_address'    => $this->request->getIPAddress(),
                'user_agent'    => $this->request->getUserAgent()->getAgentString(),
                'last_activity' => $now,
                'created_at'    => $now,
            ]);

            // Simpan hash token di session untuk mempermudah logout
            session()->set('session_token_hash', $hashed);
            // jangan simpan raw token di DB; raw tetap ada di 'access_token' session untuk penggunaan API
        } catch (\Throwable $e) {
            log_message('error', 'Failed to register session model: ' . $e->getMessage());
        }

        // remember me cookie (set secure hanya di HTTPS)
        if ($remember) {
            $secureFlag = $this->request->isSecure();
            set_cookie([
                'name'     => 'access_token',
                'value'    => $accessToken,
                'expire'   => 30 * 24 * 60 * 60,
                'secure'   => $secureFlag,
                'httponly' => true,
                'samesite' => 'Lax',
                'path'     => '/'
            ]);
        }

        $this->logAudit($apiUser['id'] ?? null, 'login', null, null, 'Login via API successful');

        return redirect()->to('/dashboard')->with('success', 'Login berhasil! Selamat datang, ' . ($apiUser['nickName'] ?? $apiUser['email'] ?? ''));
    }

    public function logout(): RedirectResponse
    {
        $user = session()->get('user') ?? [];
        $employeeId = session()->get('employee_id') ?? ($user['id'] ?? null);

        // Prefer using stored hash in session; jika tidak ada, coba ambil dari cookie raw dan hash-nya
        $tokenHash = session()->get('session_token_hash') ?? null;

        if (! $tokenHash) {
            $cookieToken = get_cookie('access_token');
            if ($cookieToken) {
                $tokenHash = hash('sha256', $cookieToken);
            }
        }

        if ($tokenHash) {
            try {
                $db = \Config\Database::connect();
                $db->table('user_sessions')->where('session_token', $tokenHash)->delete();
            } catch (\Throwable $e) {
                log_message('error', 'Failed to delete user_session: ' . $e->getMessage());
            }
        }

        if ($employeeId) {
            $this->logAudit($employeeId, 'logout', null, null, 'User logged out');
        }

        $this->removeRememberMeCookie();
        session()->destroy();

        return redirect()->to('/login')->with('success', 'Anda telah berhasil logout');
    }

    private function removeRememberMeCookie(): void
    {
        $response = service('response');
        // Hapus cookie access_token (jika ada)
        $response->deleteCookie('access_token');
    }

    private function logAudit($userId, $action, $table = null, $record = null, $desc = null): void
    {
        try {
            $this->auditLogModel->insert([
                'user_id'    => $userId,
                'action'     => $action,
                'table_name' => $table,
                'record_id'  => $record,
                'new_values' => $desc,
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Audit log failed: ' . $e->getMessage());
        }
    }
}
