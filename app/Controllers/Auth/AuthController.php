<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class Auth extends Controller
{
    protected $userModel;
    protected $session;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->session = \Config\Services::session();
        helper(['form', 'url']);
    }

    /**
     * Display login page
     */
    public function login()
    {
        // Redirect if already logged in
        if ($this->session->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        $data = [
            'title' => 'Login',
            'validation' => \Config\Services::validation()
        ];

        return view('auth/login', $data);
    }

    /**
     * Process login
     */
    public function attemptLogin()
    {
        $rules = [
            'username' => 'required|min_length[3]',
            'password' => 'required|min_length[6]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Username dan password harus diisi dengan benar!');
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $remember = $this->request->getPost('remember');

        // Get user
        $user = $this->userModel->getUserByUsernameOrEmail($username);

        // Check if user exists
        if (!$user) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Username atau email tidak ditemukan!');
        }

        // Check if account is locked
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $lockTime = date('H:i:s', strtotime($user['locked_until']));
            return redirect()->back()
                ->withInput()
                ->with('error', "Akun Anda terkunci sampai {$lockTime}. Terlalu banyak percobaan login gagal.");
        }

        // Check if account is active
        if (!$user['is_active']) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Akun Anda tidak aktif. Silakan hubungi administrator.');
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            // Increment login attempts
            $attempts = $user['login_attempts'] + 1;
            $updateData = ['login_attempts' => $attempts];

            // Lock account after 5 failed attempts
            if ($attempts >= 5) {
                $updateData['locked_until'] = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                $message = 'Terlalu banyak percobaan login gagal. Akun Anda dikunci selama 30 menit.';
            } else {
                $remaining = 5 - $attempts;
                $message = "Password salah! Sisa percobaan: {$remaining}";
            }

            $this->userModel->update($user['id'], $updateData);

            return redirect()->back()
                ->withInput()
                ->with('error', $message);
        }

        // Login successful - Reset login attempts
        $this->userModel->update($user['id'], [
            'login_attempts' => 0,
            'locked_until' => null,
            'last_login' => date('Y-m-d H:i:s')
        ]);

        // Get user role and permissions
        $roleData = $this->userModel->getUserRoleAndPermissions($user['id']);

        // Get employee data if linked
        $employeeData = null;
        if ($user['employee_id']) {
            $employeeData = $this->userModel->getEmployeeData($user['employee_id']);
        }

        // Set session data
        $sessionData = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role_id' => $user['role_id'],
            'role_name' => $roleData['role_name'] ?? 'user',
            'role_level' => $roleData['role_level'] ?? 5,
            'permissions' => $roleData['permissions'] ?? [],
            'employee_id' => $user['employee_id'],
            'employee_name' => $employeeData['nama'] ?? $user['username'],
            'department_name' => $employeeData['department_name'] ?? null,
            'position_name' => $employeeData['position_name'] ?? null,
            'logged_in' => true
        ];

        $this->session->set($sessionData);

        // Set remember me cookie
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $this->userModel->update($user['id'], ['remember_token' => $token]);
            
            // Set cookie for 30 days
            setcookie('remember_token', $token, time() + (86400 * 30), '/');
            setcookie('user_id', $user['id'], time() + (86400 * 30), '/');
        }

        // Log activity
        $this->logActivity($user['id'], 'login', 'auth', 'User logged in successfully');

        // Redirect based on role
        return redirect()->to('/dashboard')
            ->with('success', 'Selamat datang, ' . $sessionData['employee_name'] . '!');
    }

    /**
     * Logout
     */
    public function logout()
    {
        $userId = $this->session->get('user_id');

        // Log activity
        if ($userId) {
            $this->logActivity($userId, 'logout', 'auth', 'User logged out');
        }

        // Clear remember me cookies
        setcookie('remember_token', '', time() - 3600, '/');
        setcookie('user_id', '', time() - 3600, '/');

        // Destroy session
        $this->session->destroy();

        return redirect()->to('/login')
            ->with('success', 'Anda telah berhasil logout.');
    }

    /**
     * Check remember me token
     */
    public function checkRememberMe()
    {
        if ($this->session->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        if (isset($_COOKIE['remember_token']) && isset($_COOKIE['user_id'])) {
            $userId = $_COOKIE['user_id'];
            $token = $_COOKIE['remember_token'];

            $user = $this->userModel->find($userId);

            if ($user && $user['remember_token'] === $token && $user['is_active']) {
                // Auto login
                $roleData = $this->userModel->getUserRoleAndPermissions($user['id']);
                $employeeData = null;
                if ($user['employee_id']) {
                    $employeeData = $this->userModel->getEmployeeData($user['employee_id']);
                }

                $sessionData = [
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role_id' => $user['role_id'],
                    'role_name' => $roleData['role_name'] ?? 'user',
                    'role_level' => $roleData['role_level'] ?? 5,
                    'permissions' => $roleData['permissions'] ?? [],
                    'employee_id' => $user['employee_id'],
                    'employee_name' => $employeeData['nama'] ?? $user['username'],
                    'department_name' => $employeeData['department_name'] ?? null,
                    'position_name' => $employeeData['position_name'] ?? null,
                    'logged_in' => true
                ];

                $this->session->set($sessionData);

                return redirect()->to('/dashboard');
            }
        }

        return redirect()->to('/login');
    }

    /**
     * Log user activity
     */
    private function logActivity($userId, $action, $module, $description)
    {
        $db = \Config\Database::connect();
        
        $data = [
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString()
        ];

        $db->table('activity_logs')->insert($data);
    }

    /**
     * Change password (for first login)
     */
    public function changePassword()
    {
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/login');
        }

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'current_password' => 'required',
                'new_password' => 'required|min_length[8]',
                'confirm_password' => 'required|matches[new_password]'
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Password tidak valid!');
            }

            $userId = $this->session->get('user_id');
            $user = $this->userModel->find($userId);

            // Verify current password
            if (!password_verify($this->request->getPost('current_password'), $user['password'])) {
                return redirect()->back()
                    ->with('error', 'Password lama tidak sesuai!');
            }

            // Update password
            $this->userModel->update($userId, [
                'password' => $this->request->getPost('new_password')
            ]);

            $this->logActivity($userId, 'change_password', 'auth', 'User changed password');

            return redirect()->to('/dashboard')
                ->with('success', 'Password berhasil diubah!');
        }

        $data = ['title' => 'Ubah Password'];
        return view('auth/change_password', $data);
    }
}