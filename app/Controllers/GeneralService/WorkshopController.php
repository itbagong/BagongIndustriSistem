<?php

namespace App\Controllers\GeneralService;

use App\Controllers\BaseController;
use App\Models\WorkshopModel;

class WorkshopController extends BaseController
{
    protected $workshopModel;
    protected $helpers = ['form', 'url', 'security'];

    public function __construct()
    {
        $this->workshopModel = new WorkshopModel();
    }

    /* =========================
     * VIEW
     * ========================= */
    public function index()
    {
        return view('general_service/workshop/index', $this->data);
    }

    /* =========================
     * SAVE DATA
     * ========================= */
    public function save()
    {
        // PERBAIKAN: Validasi yang lebih ketat
        $rules = [
            'divisi'              => 'required',
            'job_site'            => 'required',
            'site_id'             => 'permit_empty', // Bisa kosong, akan diisi dari job_site jika kosong
            'employee_id'         => 'required',
            'nik'                 => 'required', // PERBAIKAN: Tambahkan validasi untuk NIK
            'nama_karyawan'       => 'required',
            'luasan_workshop'     => 'required|numeric',
            'jumlah_bays'         => 'required|integer|greater_than[0]',
            'kompartemen'         => 'required',
            'status_kepemilikan'  => 'required',
            'status_pembangunan'  => 'required',
        ];

        $messages = [
            'divisi' => [
                'required' => 'Divisi harus dipilih'
            ],
            'job_site' => [
                'required' => 'Job Site harus dipilih'
            ],
            'employee_id' => [
                'required' => 'Karyawan harus dipilih'
            ],
            'nik' => [
                'required' => 'NIK karyawan harus terisi'
            ],
            'nama_karyawan' => [
                'required' => 'Nama karyawan harus terisi'
            ],
            'luasan_workshop' => [
                'required' => 'Luasan workshop harus diisi',
                'numeric' => 'Luasan workshop harus berupa angka'
            ],
            'jumlah_bays' => [
                'required' => 'Jumlah bays harus diisi',
                'integer' => 'Jumlah bays harus berupa angka bulat',
                'greater_than' => 'Jumlah bays minimal 1'
            ],
            'kompartemen' => [
                'required' => 'Minimal pilih satu kompartemen'
            ],
            'status_kepemilikan' => [
                'required' => 'Status kepemilikan harus dipilih'
            ],
            'status_pembangunan' => [
                'required' => 'Status pembangunan harus dipilih'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // ================= INPUT =================
        $divisiId        = $this->request->getPost('divisi');
        $jobSite         = $this->request->getPost('job_site');
        $siteId          = $this->request->getPost('site_id') ?: $jobSite; // PERBAIKAN: Fallback ke job_site
        $nik             = $this->request->getPost('nik'); // PERBAIKAN: Ambil dari field NIK
        $namaKaryawan    = $this->request->getPost('nama_karyawan');
        $luasan          = $this->request->getPost('luasan_workshop');
        $bays            = $this->request->getPost('jumlah_bays');
        $kompartemenArr  = $this->request->getPost('kompartemen');
        $statusLahan     = $this->request->getPost('status_kepemilikan');
        $statusWorkshop  = $this->request->getPost('status_pembangunan');
        $linkMap         = $this->request->getPost('link_map') ?: null;

        // ================= VALIDASI KOMPARTEMEN =================
        if (!is_array($kompartemenArr) || empty($kompartemenArr)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['kompartemen' => 'Minimal pilih satu kompartemen']);
        }

        $kompartemenJson = json_encode($kompartemenArr, JSON_UNESCAPED_UNICODE);

        $now = date('Y-m-d H:i:s');

        // ================= INSERT =================
        $dataInsert = [
            'divisi_id'      => $divisiId,
            'site_id'        => $siteId,
            'name_karyawan'  => $namaKaryawan,
            'nik'            => $nik,
            'luasan'         => $luasan,
            'bays'           => $bays,
            'kompartemen'    => $kompartemenJson,
            'status_workshop'=> $statusWorkshop,
            'status_lahan'   => $statusLahan,
            'link_map'       => $linkMap,
            'created_at'     => $now,
            'updated_at'     => $now,
            'is_deleted'     => 0,
        ];

        try {
            // PERBAIKAN: Debug log
            log_message('info', 'Attempting to save workshop data: ' . json_encode($dataInsert));
            
            $insertID = $this->workshopModel->insert($dataInsert);
            
            if ($insertID) {
                log_message('info', 'Workshop ID ' . $insertID . ' berhasil disimpan');
                session()->setFlashdata('success', 'Data workshop berhasil disimpan');
                return redirect()->to(base_url('workshop'));
            } else {
                // PERBAIKAN: Handle kasus insert gagal
                $errors = $this->workshopModel->errors();
                log_message('error', 'Gagal menyimpan data workshop. Errors: ' . json_encode($errors));
                return redirect()->back()
                    ->withInput()
                    ->with('errors', $errors ?: ['general' => 'Gagal menyimpan data']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Gagal menyimpan data workshop. Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('errors', ['general' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
}