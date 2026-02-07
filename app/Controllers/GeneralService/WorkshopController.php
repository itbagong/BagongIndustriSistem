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
     * DETAIL - Get single workshop data
     * ========================= */
    public function detail($id) 
    {
        try {
            $workshopData = $this->workshopModel
                ->select('workshop.*, divisions.name AS divisi_name')
                ->join('divisions', 'divisions.id = workshop.divisi_id', 'left')
                ->where('workshop.id', $id)
                ->where('workshop.is_deleted', 0)
                ->first();
            
            if (!$workshopData) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "Data workshop dengan ID {$id} tidak ditemukan."
                ])->setStatusCode(404);   
            }
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $workshopData
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Workshop detail error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data'
            ])->setStatusCode(500);
        }
    }

    /* =========================
     * SAVE DATA
     * ========================= */
    public function save()
    {
        $rules = [
            'divisi'              => 'required',
            'job_site'            => 'required',
            'site_id'             => 'permit_empty',
            'employee_id'         => 'required',
            'nik'                 => 'required',
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
        $siteId          = $this->request->getPost('site_id') ?: $jobSite;
        $nik             = $this->request->getPost('nik');
        $namaKaryawan    = $this->request->getPost('nama_karyawan');
        $luasan          = $this->request->getPost('luasan_workshop');
        $bays            = $this->request->getPost('jumlah_bays');
        $kompartemenArr  = $this->request->getPost('kompartemen');
        $statusLahan     = $this->request->getPost('status_kepemilikan');
        $statusWorkshop  = $this->request->getPost('status_pembangunan');

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
            'created_at'     => $now,
            'updated_at'     => $now,
            'is_deleted'     => 0,
        ];

        try {
            log_message('info', 'Attempting to save workshop data: ' . json_encode($dataInsert));
            
            $insertID = $this->workshopModel->insert($dataInsert);
            
            if ($insertID) {
                log_message('info', 'Workshop ID ' . $insertID . ' berhasil disimpan');
                session()->setFlashdata('success', 'Data workshop berhasil disimpan');
                return redirect()->to(base_url('general-service?tab=workshop'));
            } else {
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

    /* =========================
     * EDIT - Show edit form
     * ========================= */
    public function edit($id)
    {
        $divisiModel = new \App\Models\DivisionModel();

        $workshop = $this->workshopModel
            ->select('workshop.*, divisions.name AS divisi_name')
            ->join('divisions', 'divisions.id = workshop.divisi_id', 'left')
            ->where('workshop.id', $id)
            ->where('workshop.is_deleted', 0)
            ->first();

        if (!$workshop) {
            return redirect()->to('/general-service?tab=workshop')
                ->with('error', 'Data workshop tidak ditemukan');
        }

        // Data untuk form
        $this->data['title']       = 'Edit Data Workshop';
        $this->data['workshop']    = $workshop;
        $this->data['divisi_list'] = $divisiModel->where('is_deleted', 0)->findAll();
        
        // Job sites distinct untuk dropdown
        $this->data['job_sites'] = $this->workshopModel
            ->select('site_id')
            ->where('site_id IS NOT NULL')
            ->where('site_id !=', '')
            ->distinct()
            ->findAll();

        return view('general_service/workshop_form', $this->data);
    }

    /* =========================
     * UPDATE - Process edit form
     * ========================= */
    public function update($id)
    {
        $workshop = $this->workshopModel->find($id);
        if (!$workshop || $workshop['is_deleted'] == 1) {
            return redirect()->to('/general-service?tab=workshop')
                ->with('error', 'Data workshop tidak ditemukan');
        }

        // Validation rules
        $rules = [
            'divisi_id'      => 'required',
            'site_id'        => 'required',
            'name_karyawan'  => 'required|min_length[3]',
            'nik'            => 'required',
            'luasan'         => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $kompartemenPost = $this->request->getPost('kompartemen');
        $kompartemenJson = null;
        if (is_array($kompartemenPost) && !empty($kompartemenPost)) {
            $kompartemenJson = json_encode($kompartemenPost, JSON_UNESCAPED_UNICODE);
        }

        $data = [
            'divisi_id'       => $this->request->getPost('divisi_id'),
            'site_id'         => trim($this->request->getPost('site_id')),
            'name_karyawan'   => trim($this->request->getPost('name_karyawan')),
            'nik'             => trim($this->request->getPost('nik')),
            'luasan'          => (float) $this->request->getPost('luasan'),
            'bays'            => (int) ($this->request->getPost('bays') ?? 0),
            'kompartemen'     => $kompartemenJson,
            'status_workshop' => $this->request->getPost('status_workshop'),
            'status_lahan'    => $this->request->getPost('status_lahan'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        try {
            if ($this->workshopModel->update($id, $data)) {
                session()->setFlashdata('success', 'Data workshop berhasil diperbarui');
                return redirect()->to('/general-service?tab=workshop');
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data workshop');

        } catch (\Exception $e) {
            log_message('error', 'Workshop update error: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /* =========================
     * DELETE - Soft delete workshop data
     * ========================= */
    public function delete($id)
    {
        // Hanya terima AJAX request
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ])->setStatusCode(400);
        }

        try {
            $workshop = $this->workshopModel->find($id);
            
            if (!$workshop || $workshop['is_deleted'] == 1) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Data workshop tidak ditemukan'
                ])->setStatusCode(404);
            }

            // Soft delete
            $result = $this->workshopModel->update($id, [
                'is_deleted' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if ($result) {
                log_message('info', 'Workshop ID ' . $id . ' berhasil dihapus');
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Data workshop berhasil dihapus'
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menghapus data workshop'
            ])->setStatusCode(500);

        } catch (\Exception $e) {
            log_message('error', 'Workshop delete error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}