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

    // Cek DB lokal
    $localUser = $this->userModel
        ->where('username', $usernameInput)
        ->orWhere('email', $usernameInput)
        ->first();

    if (! $localUser) {
        return redirect()->back()->withInput()->with('error', 'Username/email atau password salah.');
    }

    if (empty($localUser['is_active'])) {
        return redirect()->back()->with('error', 'Akun Anda tidak aktif.');
    }

    if (! password_verify($password, $localUser['password'])) {
        return redirect()->back()->withInput()->with('error', 'Username/email atau password salah.');
    }

    // Ambil role & permissions
    $roleId      = $localUser['role_id'] ?? null;
    $role        = null;
    $permissions = [];

    try {
        if ($roleId) {
            $role        = $this->roleModel->find($roleId);
            $permissions = $this->permissionModel->getPermissionsByUserId($localUser['id']);
        }
    } catch (\Throwable $e) {
        log_message('error', 'Role/Permission lookup error: ' . $e->getMessage());
    }

    if (! in_array('Public', $permissions, true)) {
        $permissions[] = 'Public';
    }

    $this->userModel->update($localUser['id'], ['last_login' => date('Y-m-d H:i:s')]);

    session()->set([
        'logged_in'   => true,
        'user_id'     => $localUser['id'],
        'employee_id' => $localUser['employee_id'] ?? null,
        'username'    => $localUser['username'],
        'email'       => $localUser['email'] ?? null,
        'role_id'     => $role['id']    ?? $roleId,
        'role_name'   => $role['name']  ?? null,
        'role_level'  => $role['level'] ?? null,
        'permissions' => $permissions,
        'user'        => $localUser,
        'login_time'  => time(),
    ]);

    if ($remember) {
        // remember me tanpa token API, pakai session ID saja
        set_cookie([
            'name'     => 'remember_user',
            'value'    => $localUser['id'],
            'expire'   => 30 * 24 * 60 * 60,
            'httponly' => true,
            'samesite' => 'Lax',
            'path'     => '/'
        ]);
    }

    $this->logAudit($localUser['id'], 'login', null, null, 'Login via local DB');

    return redirect()->to('/dashboard')->with('success', 'Login berhasil! Selamat datang, ' . $localUser['username']);
}

    // public function process(): RedirectResponse
    // {
    //     $usernameInput = trim((string) ($this->request->getPost('username') ?: $this->request->getPost('email')));
    //     $password      = (string) $this->request->getPost('password');
    //     $remember      = (bool) $this->request->getPost('remember');

    //     if (! $usernameInput || ! $password) {
    //         return redirect()->back()->withInput()->with('error', 'Username/email dan password harus diisi');
    //     }

    //     // ══════════════════════════════════════════════════════════════
    //     // 1) COBA LOGIN VIA API EKSTERNAL DULU
    //     // ══════════════════════════════════════════════════════════════
    //     $apiSuccess  = false;
    //     $accessToken = null;
    //     $refreshToken= null;
    //     $apiUser     = null;

    //     try {
    //         $response = $this->api->post('login', [
    //             'json'        => [
    //                 'email'    => $usernameInput,
    //                 'password' => $password
    //             ],
    //             'http_errors' => false
    //         ]);

    //         $status = $response->getStatusCode();
    //         $body   = json_decode((string) $response->getBody(), true) ?? [];

    //         if ($status === 200) {
    //             $accessToken  = $body['data']['accessToken']  ?? $body['accessToken'] ?? $body['token'] ?? null;
    //             $refreshToken = $body['data']['refreshToken'] ?? $body['refreshToken'] ?? null;

    //             if ($accessToken) {
    //                 // Ambil profile dari API
    //                 $resUser    = $this->api->get('user', [
    //                     'headers'     => ['Authorization' => 'Bearer ' . $accessToken],
    //                     'http_errors' => false
    //                 ]);
    //                 $statusUser = $resUser->getStatusCode();
    //                 $payload    = json_decode((string) $resUser->getBody(), true) ?? [];

    //                 if ($statusUser === 200) {
    //                     $apiData = $payload['data'] ?? $payload;

    //                     if (is_array($apiData)) {
    //                         $isList = array_keys($apiData) === range(0, count($apiData) - 1);

    //                         if ($isList) {
    //                             $search = strtolower($usernameInput);
    //                             foreach ($apiData as $u) {
    //                                 if (!is_array($u)) continue;
    //                                 if (!empty($u['email'])    && strtolower($u['email'])    === $search) { $apiUser = $u; break; }
    //                                 if (!empty($u['nickName']) && strtolower($u['nickName']) === $search) { $apiUser = $u; break; }
    //                                 if (!empty($u['username']) && strtolower($u['username']) === $search) { $apiUser = $u; break; }
    //                             }
    //                             if (! $apiUser && isset($apiData[0]) && is_array($apiData[0])) {
    //                                 $apiUser = $apiData[0];
    //                             }
    //                         } else {
    //                             $apiUser = $apiData;
    //                         }
    //                     }

    //                     if (is_array($apiUser) && !empty($apiUser['id'])) {
    //                         $apiSuccess = true;
    //                     }
    //                 }
    //             }
    //         }

    //     } catch (\Throwable $e) {
    //         log_message('error', 'API login error: ' . $e->getMessage());
    //         // API tidak bisa dihubungi — lanjut ke fallback lokal
    //     }

    //     // ══════════════════════════════════════════════════════════════
    //     // 2) FALLBACK — cek DB lokal jika API gagal / user tidak ada
    //     // ══════════════════════════════════════════════════════════════
    //     if (! $apiSuccess) {
    //         $localUser = $this->userModel
    //             ->where('username', $usernameInput)
    //             ->orWhere('email', $usernameInput)
    //             ->first();

    //         // User tidak ditemukan di lokal juga
    //         if (! $localUser) {
    //             $this->logAudit(null, 'login_failed', null, null, 'User not found in API or local DB');
    //             return redirect()->back()->withInput()->with('error', 'Username/email atau password salah.');
    //         }

    //         // Cek is_active
    //         if (empty($localUser['is_active'])) {
    //             $this->logAudit($localUser['id'], 'login_failed', null, null, 'Local user inactive');
    //             return redirect()->back()->with('error', 'Akun Anda tidak aktif.');
    //         }

    //         // Verifikasi password
    //         if (! password_verify($password, $localUser['password'])) {
    //             $this->logAudit($localUser['id'], 'login_failed', null, null, 'Wrong password (local)');
    //             return redirect()->back()->withInput()->with('error', 'Username/email atau password salah.');
    //         }

    //         // ── Login lokal berhasil — bangun session langsung ──────────
    //         $roleId = $localUser['role_id'] ?? null;
    //         $role   = null;
    //         $permissions = [];

    //         try {
    //             if ($roleId) {
    //                 $role        = $this->roleModel->find($roleId);
    //                 $permissions = $this->permissionModel->getPermissionsByUserId($localUser['id']);
    //             }
    //         } catch (\Throwable $e) {
    //             log_message('error', 'Role/Permission lookup error (local): ' . $e->getMessage());
    //         }

    //         if (! in_array('Public', $permissions, true)) {
    //             $permissions[] = 'Public';
    //         }

    //         // Update last_login
    //         $this->userModel->update($localUser['id'], ['last_login' => date('Y-m-d H:i:s')]);

    //         session()->set([
    //             'logged_in'   => true,
    //             'access_token'=> null,
    //             'user_id'     => $localUser['id'],
    //             'employee_id' => $localUser['employee_id'] ?? null,
    //             'username'    => $localUser['username'],
    //             'email'       => $localUser['email'] ?? null,
    //             'role_id'     => $role['id']    ?? $roleId,
    //             'role_name'   => $role['name']  ?? null,
    //             'role_level'  => $role['level'] ?? null,
    //             'permissions' => $permissions,
    //             'user'        => $localUser,
    //             'login_time'  => time(),
    //             'auth_source' => 'local', // penanda login dari DB lokal
    //         ]);

    //         $this->logAudit($localUser['id'], 'login', null, null, 'Login via local DB');

    //         return redirect()->to('/dashboard')->with('success', 'Login berhasil! Selamat datang, ' . ($localUser['username'] ?? ''));
    //     }

    //     // ══════════════════════════════════════════════════════════════
    //     // 3) API LOGIN BERHASIL — lanjut proses seperti semula
    //     // ══════════════════════════════════════════════════════════════

    //     // Cek active status
    //     if (isset($apiUser['activeStatus']) && ! $apiUser['activeStatus']) {
    //         $this->logAudit($apiUser['id'] ?? null, 'login_failed', null, null, 'User inactive in auth server');
    //         return redirect()->back()->with('error', 'Akun Anda tidak aktif.');
    //     }

    //     // Mapping role dari API
    //     $mappedRoleId = 3;
    //     if (! empty($apiUser['employeeNumber']) && str_starts_with((string) $apiUser['employeeNumber'], '24')) {
    //         $mappedRoleId = 2;
    //     }
    //     if (! empty($apiUser['email']) && strtolower($apiUser['email']) === 'ebri@bagongbis.com') {
    //         $mappedRoleId = 1;
    //     }

    //     // Sync user ke DB lokal
    //     $localUserId = null;
    //     $roleId      = $mappedRoleId;

    //     try {
    //         $existing = $this->userModel->where('api_user_id', $apiUser['id'])->first();

    //         $data = [
    //             'api_user_id' => $apiUser['id'],
    //             'username'    => $apiUser['nickName'] ?? ($apiUser['email'] ?? null),
    //             'email'       => $apiUser['email'] ?? null,
    //             'is_active'   => ! empty($apiUser['activeStatus']) ? 1 : 0,
    //             'last_login'  => date('Y-m-d H:i:s'),
    //             'updated_at'  => date('Y-m-d H:i:s'),
    //         ];

    //         if ($existing) {
    //             $data['role_id'] = $existing['role_id'] ?? $mappedRoleId;
    //             $this->userModel->update($existing['id'], $data);
    //             $localUserId = $existing['id'];
    //             $roleId      = $data['role_id'];
    //         } else {
    //             $data['role_id']    = $mappedRoleId;
    //             $data['created_at'] = date('Y-m-d H:i:s');
    //             $this->userModel->insert($data);
    //             $localUserId = $this->userModel->getInsertID();
    //         }
    //     } catch (\Throwable $e) {
    //         log_message('error', 'User sync error: ' . $e->getMessage());
    //     }

    //     // Ambil role & permissions
    //     $role        = null;
    //     $permissions = [];

    //     try {
    //         if ($localUserId && $roleId) {
    //             $role        = $this->roleModel->find($roleId);
    //             $permissions = $this->permissionModel->getPermissionsByUserId($localUserId);
    //         } else {
    //             $role        = ['id' => null, 'name' => 'guest', 'level' => 99];
    //             $permissions = ['Public'];
    //         }
    //     } catch (\Throwable $e) {
    //         log_message('error', 'Role/Permission lookup error: ' . $e->getMessage());
    //         $permissions = ['Public'];
    //     }

    //     if (! in_array('Public', $permissions, true)) {
    //         $permissions[] = 'Public';
    //     }

    //     // Simpan session
    //     session()->set([
    //         'logged_in'     => true,
    //         'access_token'  => $accessToken,
    //         'refresh_token' => $refreshToken,
    //         'employee_id'   => $apiUser['id'],
    //         'user_id'       => $localUserId,
    //         'username'      => $apiUser['nickName'] ?? $apiUser['email'] ?? null,
    //         'email'         => $apiUser['email'] ?? null,
    //         'role_id'       => $role['id'] ?? $roleId,
    //         'role_name'     => $role['name'] ?? null,
    //         'role_level'    => $role['level'] ?? null,
    //         'permissions'   => $permissions,
    //         'user'          => $apiUser,
    //         'login_time'    => time(),
    //         'auth_source'   => 'api', // penanda login dari API
    //     ]);

    //     // Simpan session token ke DB
    //     try {
    //         $hashed = hash('sha256', $accessToken);
    //         $now    = date('Y-m-d H:i:s');

    //         $this->sessionModel->insert([
    //             'user_id'       => $localUserId ?? $apiUser['id'],
    //             'session_token' => $hashed,
    //             'ip_address'    => $this->request->getIPAddress(),
    //             'user_agent'    => $this->request->getUserAgent()->getAgentString(),
    //             'last_activity' => $now,
    //             'created_at'    => $now,
    //         ]);

    //         session()->set('session_token_hash', $hashed);
    //     } catch (\Throwable $e) {
    //         log_message('error', 'Failed to register session model: ' . $e->getMessage());
    //     }

    //     // Remember me cookie
    //     if ($remember) {
    //         $secureFlag = $this->request->isSecure();
    //         set_cookie([
    //             'name'     => 'access_token',
    //             'value'    => $accessToken,
    //             'expire'   => 30 * 24 * 60 * 60,
    //             'secure'   => $secureFlag,
    //             'httponly' => true,
    //             'samesite' => 'Lax',
    //             'path'     => '/'
    //         ]);
    //     }

    //     $this->logAudit($apiUser['id'] ?? null, 'login', null, null, 'Login via API successful');

    //     return redirect()->to('/dashboard')->with('success', 'Login berhasil! Selamat datang, ' . ($apiUser['nickName'] ?? $apiUser['email'] ?? ''));
    // }

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
