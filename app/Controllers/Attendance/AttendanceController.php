<?php

namespace App\Controllers\Attendance;

use App\Controllers\BaseController;
use App\Models\AttendanceModel;
use CodeIgniter\HTTP\ResponseInterface;

class AttendanceController extends BaseController
{
    protected AttendanceModel $attendanceModel;

    public function __construct()
    {
        $this->attendanceModel = new AttendanceModel();
    }

    // GET /attendance
    public function index()
    {

        $userId = session()->get('user_id');
        $today  = date('Y-m-d');

        // Cek absen hari ini berdasarkan data DB (bukan jam)
        $data['todayMasuk'] = $this->attendanceModel
            ->where('user_id', $userId)
            ->where('type', 'masuk')
            ->where('DATE(created_at)', $today)
            ->first();

        $data['todayPulang'] = $this->attendanceModel
            ->where('user_id', $userId)
            ->where('type', 'pulang')
            ->where('DATE(created_at)', $today)
            ->first();

        // 10 riwayat terakhir untuk ditampilkan di bawah form
        $data['records'] = $this->attendanceModel
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->find();

        return view('attendance/index', $data);
    }

    // POST /attendance/store
    public function store()
    {
        // FIX #2: isAJAX() dihapus — Android WebView tidak selalu kirim
        // header X-Requested-With sehingga isAJAX() selalu false → redirect terus


        $userId = session()->get('user_id');

        $rules = [
            'photo'     => 'uploaded[photo]|is_image[photo]|mime_in[photo,image/jpeg,image/png]|max_size[photo,2048]',
            'latitude'  => 'required|decimal',
            'longitude' => 'required|decimal',
            'type'      => 'required|in_list[masuk,pulang]',
        ];

        if (!$this->validate($rules)) {
            return $this->json([
                'success' => false,
                'message' => implode(' ', $this->validator->getErrors()),
            ], 422);
        }

        $type  = $this->request->getPost('type');
        $today = date('Y-m-d');

        // Cek duplikat absen hari ini
        $existing = $this->attendanceModel
            ->where('user_id', $userId)
            ->where('type', $type)
            ->where('DATE(created_at)', $today)
            ->first();

        if ($existing) {
            return $this->json([
                'success' => false,
                'message' => 'Kamu sudah absen ' . $type . ' hari ini.',
            ], 409);
        }

        // Upload foto
        $photo     = $this->request->getFile('photo');
        $uploadDir = FCPATH . 'uploads/attendance/' . date('Y/m/');

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName  = 'absen_' . $userId . '_' . time() . '.' . $photo->getExtension();
        $photo->move($uploadDir, $fileName);
        $photoPath = 'uploads/attendance/' . date('Y/m/') . $fileName;

        // Simpan ke DB
        $data = [
            'user_id'    => $userId,
            'type'       => $type,
            'photo'      => $photoPath,
            'latitude'   => $this->request->getPost('latitude'),
            'longitude'  => $this->request->getPost('longitude'),
            'accuracy'   => $this->request->getPost('accuracy'),
            'address'    => $this->request->getPost('address'),
            'ip_address' => $this->request->getIPAddress(),
        ];

        $inserted = $this->attendanceModel->insert($data);

        if (!$inserted) {
            return $this->json(['success' => false, 'message' => 'Gagal menyimpan data.'], 500);
        }

        return $this->json([
            'success'      => true,
            'message'      => 'Absen ' . ($type === 'masuk' ? 'Masuk' : 'Pulang') . ' berhasil dicatat.',
            'data'         => ['id' => $inserted, 'type' => $type, 'time' => date('H:i')],
            'redirect_url' => base_url('dashboard'),   // ← ganti 'dashboard' sesuai route kamu
        ]);
    }

    // GET /attendance/history
    public function history()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');

        // Filter bulan — default bulan ini
        $month = $this->request->getGet('month') ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }

        $data['records'] = $this->attendanceModel
            ->where('user_id', $userId)
            ->where("DATE_FORMAT(created_at, '%Y-%m')", $month)
            ->orderBy('created_at', 'DESC')
            ->paginate(20);

        $data['pager']       = $this->attendanceModel->pager;
        $data['activeMonth'] = $month;

        return view('attendance/history', $data);
    }

    private function json(array $data, int $status = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setJSON($data);
    }
}