<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\IncomingRequest;

class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // belum login
        if (! session()->get('logged_in')) {
            return redirect()->to('/login')
                ->with('error', 'Silakan login terlebih dahulu');
        }

        // tidak ada permission didefinisikan
        if (empty($arguments)) {
            return;
        }

        $requiredPermission = $arguments[0];
        $permissions = session()->get('permissions') ?? [];

        if (! in_array($requiredPermission, $permissions)) {

            // ✅ AJAX SAFE CHECK
            $isAjax = $request instanceof IncomingRequest && $request->isAJAX();

            if ($isAjax) {
                return service('response')->setJSON([
                    'status'  => 'error',
                    'message' => 'Unauthorized access'
                ])->setStatusCode(403);
            }

            return redirect()->to('/dashboard')
                ->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // nothing
    }
}
