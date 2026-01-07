<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseApiController;
use App\Models\AuditLogModel;
use App\Models\UserSessionModel;
use CodeIgniter\HTTP\RedirectResponse;

class LoginController extends BaseApiController
{
    protected $auditLogModel;
    protected $sessionModel;

    public function __construct()
    {
        helper('cookie');
        $this->auditLogModel = new AuditLogModel();
        $this->sessionModel  = new UserSessionModel();
        // api client sudah di-init di BaseApiController (initController)
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
        $usernameInput = trim($this->request->getPost('username') ?: $this->request->getPost('email'));
        $password = $this->request->getPost('password');
        $remember = $this->request->getPost('remember');

        if (!$usernameInput || !$password) {
            return redirect()->back()->withInput()->with('error', 'Username / email dan password harus diisi');
        }

        try {
            // gunakan service('apiClient') atau $this->api jika pakai BaseApiController
            $apiClient = $this->api;
            $response = $apiClient->post('login', [
                'json' => [
                    'email' => $usernameInput,
                    'password' => $password
                ]
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'API login error: ' . $e->getMessage());
            $this->logAudit(null, 'login_failed', null, null, 'API connection error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal terhubung ke server otentikasi.');
        }

        $status = $response->getStatusCode();
        $data = json_decode((string)$response->getBody(), true) ?? [];

        if ($status !== 200) {
            $msg = $data['message'] ?? ($data['error'] ?? 'Login gagal');
            $this->logAudit(null, 'login_failed', null, null, 'API responded ' . $status . ': ' . $msg);
            return redirect()->back()->withInput()->with('error', $msg);
        }

        $accessToken = $data['accessToken'] ?? $data['token'] ?? ($data['data']['accessToken'] ?? null);
        $userProfile = $data['user'] ?? ($data['data']['user'] ?? ($data['data'] ?? null));

        if (!$accessToken) {
            $this->logAudit(null, 'login_failed', null, null, 'No access token in API response');
            return redirect()->back()->withInput()->with('error', 'Server otentikasi tidak mengembalikan token.');
        }

        if (!is_array($userProfile)) {
            $userProfile = ['username' => $usernameInput, 'email' => $usernameInput];
        }

        //
        // 1) Ambil role dari DB lokal (user_roles -> roles)
        // 2) Jika tidak ada, beri fallback dev role
        // 3) Generate permissions sederhana berdasarkan role.level
        //
        $permissions = [];
        $userRole = null;

        try {
            $roleModel = new \App\Models\RoleModel();
            $userRole = $roleModel->getUserRole($userProfile['id'] ?? null);
        } catch (\Throwable $e) {
            log_message('error', 'RoleModel error: ' . $e->getMessage());
            // jangan gagalkan login hanya karena role lookup gagal
            $userRole = null;
        }

        // fallback jika user belum diassign role (DEV mode)
        if (!$userRole) {
            $userRole = [
                'id'    => null,
                'name'  => 'guest',
                'level' => 99
            ];
        }

        // Jika API juga mengirim permissions, gabungkan (prioritas = union)
        $apiPermissions = $data['permissions']
            ?? ($data['data']['permissions'] ?? [])
            ?? ($userProfile['permissions'] ?? []);

        if (!is_array($apiPermissions)) {
            $apiPermissions = $apiPermissions ? explode(',', $apiPermissions) : [];
        }

        // Generate default permissions berdasarkan level (sederhana untuk dev)
        switch ((int)($userRole['level'] ?? 99)) {
            case 1: // super_admin
                $generated = [
                    'employee.view',
                    'employee.manage',
                    'employee.export',
                    'system.manage'
                ];
                break;
            case 2: // admin
                $generated = [
                    'employee.view',
                    'employee.manage',
                    'employee.export'
                ];
                break;
            case 3: // manager
                $generated = [
                    'employee.view',
                    'employee.export'
                ];
                break;
            default: // staff / guest
                $generated = [
                    'employee.view'
                ];
        }

        // union apiPermissions + generated (unique)
        $permissions = array_values(array_unique(array_merge($apiPermissions, $generated)));

        // DEV helper: selalu pastikan employee.view ada supaya halaman tidak terkunci
        if (!in_array('employee.view', $permissions)) {
            $permissions[] = 'employee.view';
        }

        // Simpan session lengkap (role & permissions)
        session()->set([
            'logged_in'     => true,
            'access_token'  => $accessToken,

            'user_id'       => $userProfile['id'] ?? null,
            'username'      => $userProfile['username'] ?? $userProfile['name'] ?? null,
            'email'         => $userProfile['email'] ?? null,

            'role_id'       => $userRole['id'],
            'role_name'     => $userRole['name'],
            'role_level'    => $userRole['level'],

            'permissions'   => $permissions,

            'user'          => $userProfile,
            'login_time'    => time()
        ]);

        // store hashed token locally for session tracking
        try {
            $hashed = hash('sha256', $accessToken);
            $now = date('Y-m-d H:i:s');

            $this->sessionModel->insert([
                'user_id'       => $userProfile['id'] ?? null,
                'session_token' => $hashed,
                'ip_address'    => $this->request->getIPAddress(),
                'user_agent'    => $this->request->getUserAgent()->getAgentString(),
                'last_activity' => $now,
                'created_at'    => $now,
            ]);

            session()->set('session_token', $accessToken);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to register session model: ' . $e->getMessage());
        }

        if ($remember) {
            set_cookie([
                'name'     => 'access_token',
                'value'    => $accessToken,
                'expire'   => 30 * 24 * 60 * 60,
                'secure'   => true,
                'httponly' => true,
                'samesite' => 'Lax',
                'path'     => '/'
            ]);
        }

        $this->logAudit($userProfile['id'] ?? null, 'login', null, null, 'Login via API successful');

        return redirect()->to('/dashboard')->with('success', 'Login berhasil! Selamat datang, ' . ($userProfile['name'] ?? $userProfile['username'] ?? ''));
    }


    public function checkRememberMe(): RedirectResponse
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        $token = get_cookie('access_token');
        if (! $token) {
            return redirect()->to('/login');
        }

        try {
            $response = $this->api->get('me', ['headers' => ['Authorization' => 'Bearer ' . $token]]);
        } catch (\Throwable $e) {
            log_message('error', 'Auth me error: ' . $e->getMessage());
            $this->removeRememberMeCookie();
            return redirect()->to('/login');
        }

        $status = $response->getStatusCode();
        $data = json_decode((string)$response->getBody(), true) ?? [];

        if ($status !== 200) {
            $this->removeRememberMeCookie();
            return redirect()->to('/login')->with('error', $data['message'] ?? 'Session tidak valid. Silakan login ulang.');
        }

        $userProfile = $data['user'] ?? ($data['data']['user'] ?? ($data['data'] ?? null));
        if (!is_array($userProfile)) {
            $userProfile = ['username' => $data['email'] ?? 'unknown'];
        }

        session()->set([
            'logged_in'    => true,
            'access_token' => $token,
            'user'         => $userProfile,
            'login_time'   => time()
        ]);

        // upsert sessionModel
        try {
            $hashed = hash('sha256', $token);
            $now = date('Y-m-d H:i:s');

            $db = \Config\Database::connect();
            $exists = $db->table('user_sessions')->where('session_token', $hashed)->get()->getRowArray();

            if ($exists) {
                $db->table('user_sessions')->where('session_token', $hashed)->update(['last_activity' => $now]);
            } else {
                $this->sessionModel->insert([
                    'user_id'       => $userProfile['id'] ?? null,
                    'session_token' => $hashed,
                    'ip_address'    => $this->request->getIPAddress(),
                    'user_agent'    => $this->request->getUserAgent()->getAgentString(),
                    'last_activity' => $now,
                    'created_at'    => $now
                ]);
            }

            session()->set('session_token', $token);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to upsert sessionModel: ' . $e->getMessage());
        }

        $this->logAudit($userProfile['id'] ?? null, 'login_via_cookie', null, null, 'Session restored from cookie');

        return redirect()->to('/dashboard');
    }

    public function logout(): RedirectResponse
    {
        $user = session()->get('user');
        $userId = $user['id'] ?? session()->get('user_id') ?? null;
        $token  = session()->get('session_token') ?? get_cookie('access_token');

        if ($token) {
            $hashed = hash('sha256', $token);
            try {
                $db = \Config\Database::connect();
                $db->table('user_sessions')->where('session_token', $hashed)->delete();
            } catch (\Throwable $e) {
                log_message('error', 'Failed to delete user_session: ' . $e->getMessage());
            }
        }

        if ($userId) {
            $this->logAudit($userId, 'logout', null, null, 'User logged out');
        }

        $this->removeRememberMeCookie();
        session()->destroy();

        return redirect()->to('/login')->with('success', 'Anda telah berhasil logout');
    }

    private function removeRememberMeCookie(): void
    {
        $response = service('response');
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
