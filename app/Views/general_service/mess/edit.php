<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<style>
    /* Styling agar Tom Select mirip input Bootstrap standard */
    .ts-control {
        min-height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 6px 12px;
    }
    .ts-dropdown {
        z-index: 9999;
    }
    
    /* Perbaikan Card Animation */
    .perbaikan-card {
        animation: slideIn 0.3s ease-out;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* File Preview Styling */
    .file-preview-item {
        display: inline-block;
        margin: 5px;
        padding: 10px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        width: 100px;
        text-align: center;
    }
    
    /* Invalid feedback */
    .is-invalid {
        border-color: #dc3545 !important;
    }
    
    /* Badge custom */
    .badge-pending {
        background-color: #ffc107;
        color: #000;
    }
    
    .badge-approved {
        background-color: #28a745;
    }
    
    .badge-rejected {
        background-color: #dc3545;
    }
</style>

<div class="content-header mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-edit mr-2"></i> Edit Data Mess Karyawan</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-0 mt-2">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('general-service?tab=mess') ?>">Data Mess</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
        <a href="<?= base_url('general-service?tab=mess') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-warning text-dark">
        <h5 class="card-title mb-0"><i class="fas fa-home mr-2"></i> Form Edit Data Mess</h5>
    </div>
    <div class="card-body">
        
        <?php if(session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <h6><i class="fas fa-exclamation-triangle"></i> Validasi Error!</h6>
                <ul class="mb-0">
                <?php foreach(session()->getFlashdata('errors') as $err): ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach; ?>
                </ul>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?= esc(session()->getFlashdata('error')) ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?= esc(session()->getFlashdata('success')) ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- FORM EDIT MESS -->
        <form action="<?= base_url('general-service/mess/update/' . $mess['id']) ?>" method="post" id="messForm">
            <?= csrf_field() ?>
            <input type="hidden" name="_method" value="PUT">

            <!-- Info Box - Data Sebelumnya -->
            <div class="alert alert-info border-info">
                <h6 class="alert-heading"><i class="fas fa-info-circle"></i> Informasi Data Saat Ini</h6>
                <div class="row">
                    <div class="col-md-6">
                        <small><strong>Karyawan:</strong> <?= esc($mess['nama_karyawan']) ?></small><br>
                        <small><strong>NIK:</strong> <?= esc($mess['nik']) ?></small>
                    </div>
                    <div class="col-md-6">
                        <small><strong>Divisi:</strong> <?= esc($mess['divisi_name'] ?? '-') ?></small><br>
                        <small><strong>Job Site:</strong> <?= esc($mess['site_id']) ?></small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kode Mess <span class="text-danger">*</span></label>
                    <input type="text" name="mess_code" class="form-control" 
                           value="<?= esc($mess['mess_code']) ?>" readonly> 
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Divisi <span class="text-danger">*</span></label>
                    <select name="divisi" id="divisi" class="form-control" required>
                        <option value="">-- Pilih Divisi --</option>
                        <?php foreach($divisi_list as $divisi): ?>
                            <option value="<?= esc($divisi['id']) ?>" 
                                <?= (old('divisi', $mess['divisi_id']) == $divisi['id']) ? 'selected' : '' ?>>
                                <?= esc($divisi['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Job Site <span class="text-danger">*</span></label>
                    <select name="job_site" id="job_site" class="form-control" required>
                        <option value="<?= esc($mess['site_id']) ?>" selected>
                            <?= esc($mess['site_id']) ?>
                        </option>
                    </select>
                    <input type="hidden" name="site_id" id="site_id" value="<?= esc($mess['site_id']) ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Pilih Karyawan <span class="text-danger">*</span></label>
                    
                    <select name="employee_id" id="employee_select" required>
                        <!-- Pre-filled dengan data existing -->
                        <option value="<?= esc($mess['nik']) ?>" selected>
                            <?= esc($mess['nama_karyawan']) ?> (<?= esc($mess['nik']) ?>)
                        </option>
                    </select>
                    
                    <small class="text-muted d-block mt-1">
                        <i class="fas fa-info-circle"></i> Ketik minimal 2 karakter untuk mencari karyawan lain.
                    </small>
                    
                    <input type="hidden" name="nama_karyawan" id="nama_karyawan" value="<?= esc($mess['nama_karyawan']) ?>">
                    <input type="hidden" name="nik" id="nik_hidden" value="<?= esc($mess['nik']) ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">NIK Karyawan <span class="text-danger">*</span></label>
                    <input type="text" name="nik_display" id="nik_display" class="form-control" 
                           value="<?= esc($mess['nik']) ?>" readonly>
                    <small class="text-muted">NIK akan terisi otomatis setelah memilih karyawan</small>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Luasan Mess (m²) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="luasan_mess" class="form-control" 
                               placeholder="0" value="<?= old('luasan_mess', $mess['luasan_mess']) ?>" required>
                        <span class="input-group-text">m²</span>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jumlah Kamar Tidur <span class="text-danger">*</span></label>
                    <input type="number" name="jumlah_kamar_tidur" class="form-control" min="1" 
                           value="<?= old('jumlah_kamar_tidur', $mess['jumlah_kamar_tidur']) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jumlah Kamar Mandi <span class="text-danger">*</span></label>
                    <input type="number" name="jumlah_kamar_mandi" class="form-control" min="1" 
                           value="<?= old('jumlah_kamar_mandi', $mess['jumlah_kamar_mandi']) ?>" required>
                </div>
            </div>

            <div class="row bg-light p-2 rounded mb-3 mx-1">
                <div class="col-md-6 mb-3">
                    <label class="form-label d-block">Akses Parkir <span class="text-danger">*</span></label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="akses_parkir" id="parkirAda" 
                               value="Ada" <?= old('akses_parkir', $mess['akses_parkir']) === 'Ada' ? 'checked' : '' ?> required>
                        <label class="form-check-label" for="parkirAda">Ada</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="akses_parkir" id="parkirTidak" 
                               value="Tidak Ada" <?= old('akses_parkir', $mess['akses_parkir']) === 'Tidak Ada' ? 'checked' : '' ?> required>
                        <label class="form-check-label" for="parkirTidak">Tidak Ada</label>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Luas Area Parkir (m²) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="luas_area_parkir" id="luasParkir" class="form-control" 
                           value="<?= old('luas_area_parkir', $mess['luas_area_parkir']) ?>" required>
                    <small class="text-muted">Isi 0 jika tidak ada.</small>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label font-weight-bold">
                    Fasilitas (Centang yang tersedia) <span class="text-danger">*</span>
                </label>

                <?php
                $selected_fasilitas = [];

                if (!empty($mess['fasilitas'])) {
                    $decoded = json_decode($mess['fasilitas'], true);
                    if (is_array($decoded)) {
                        $selected_fasilitas = $decoded;
                    }
                }

                $selected_fasilitas = old('fasilitas', $selected_fasilitas);

                $fasilitas_items = [
                    'PDAM',
                    'Meter PLN',
                    'Kasur',
                    'Kipas Angin',
                    'Air Conditioner',
                    'Mesin Cuci',
                    'Lemari'
                ];
                ?>

                <div class="row">
                    <?php foreach ($fasilitas_items as $f): 
                        $fid = 'f_' . strtolower(str_replace(' ', '_', $f));
                    ?>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input fasilitas-checkbox"
                                type="checkbox"
                                name="fasilitas[]"
                                value="<?= esc($f) ?>"
                                id="<?= esc($fid) ?>"
                                <?= in_array($f, $selected_fasilitas, true) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="<?= esc($fid) ?>">
                                <?= esc($f) ?>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <small class="text-danger d-none" id="fasilitas-error">
                    Pilih minimal satu fasilitas
                </small>
            </div>


            <hr>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status Kepemilikan Lahan Mess <span class="text-danger">*</span></label>
                    <select name="status_kepemilikan" class="form-control" required>
                        <option value="">-- Pilih Status --</option>
                        <option value="Milik PT Bagong Dekaka Makmur" 
                            <?= old('status_kepemilikan', $mess['status_kepemilikan']) === 'Milik PT Bagong Dekaka Makmur' ? 'selected' : '' ?>>
                            Milik PT Bagong Dekaka Makmur
                        </option>
                        <option value="Sewa" 
                            <?= old('status_kepemilikan', $mess['status_kepemilikan']) === 'Sewa' ? 'selected' : '' ?>>
                            Sewa
                        </option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status Renovasi <span class="text-danger">*</span></label>
                    <div class="mt-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status_renovasi" id="renovPernah" 
                                   value="Pernah" <?= old('status_renovasi', $mess['status_renovasi']) === 'Pernah' ? 'checked' : '' ?> required>
                            <label class="form-check-label" for="renovPernah">Pernah</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status_renovasi" id="renovBelum" 
                                   value="Belum Pernah" <?= old('status_renovasi', $mess['status_renovasi']) === 'Belum Pernah' ? 'checked' : '' ?> required>
                            <label class="form-check-label" for="renovBelum">Belum Pernah</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Terakhir Diubah -->
            <?php if (!empty($mess['updated_at'])): ?>
            <div class="alert alert-light border">
                <small class="text-muted">
                    <i class="fas fa-clock"></i> Terakhir diubah: 
                    <strong><?= date('d/m/Y H:i', strtotime($mess['updated_at'])) ?></strong>
                    <?php if (!empty($mess['updated_by_name'])): ?>
                        oleh <strong><?= esc($mess['updated_by_name']) ?></strong>
                    <?php endif; ?>
                </small>
            </div>
            <?php endif; ?>

            <div class="form-group mt-4">
                <div class="d-flex justify-content-between">
                    <a href="<?= base_url('general-service?tab=mess') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-warning" id="submitBtn">
                        <i class="fas fa-save"></i> Update Data Mess
                    </button>
                </div>
            </div>

        </form>
        <!-- END FORM EDIT MESS -->

    </div>
</div>

<!-- ============================================ -->
<!-- SECTION PENGAJUAN PERBAIKAN (TERPISAH) -->
<!-- ============================================ -->

<div class="card shadow-sm mt-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="fas fa-tools mr-2"></i> Pengajuan Perbaikan Mess
        </h5>
    </div>
    <div class="card-body">
        
        <!-- Alert untuk notifikasi perbaikan -->
        <div id="perbaikanAlert" style="display: none;"></div>
        
        <!-- Tombol Tambah -->
        <div class="mb-3">
            <button type="button" class="btn btn-success" id="btnAddPerbaikan">
                <i class="fas fa-plus-circle"></i> Tambah Pengajuan Perbaikan
            </button>
        </div>

        <!-- Container List Perbaikan BARU (belum disimpan) -->
        <div id="perbaikanListContainer">
            <?php if (empty($existing_perbaikan)): ?>
            <div class="alert alert-info text-center" id="emptyPerbaikanMsg">
                <i class="fas fa-info-circle"></i> Belum ada pengajuan perbaikan baru. Klik tombol di atas untuk menambahkan.
            </div>
            <?php endif; ?>
        </div>

        <!-- Riwayat Perbaikan yang Sudah Ada -->
        <?php if (!empty($existing_perbaikan)): ?>
        <div class="mt-4">
            <h6 class="text-muted border-bottom pb-2 mb-3">
                <i class="fas fa-history"></i> Riwayat Perbaikan Sebelumnya
                <span class="badge badge-primary ml-2"><?= count($existing_perbaikan) ?> Pengajuan</span>
            </h6>
            
            <div class="table-responsive">
                <table class="table table-sm table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th width="3%">No</th>
                            <th width="12%">Kode</th>
                            <th width="10%">Kategori</th>
                            <th width="18%">Deskripsi</th>
                            <th width="10%">Status</th>
                            <th width="10%">Tgl Ajuan</th>
                            <th width="12%">Est. Biaya</th>
                            <th width="7%">File</th>
                            <th width="8%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($existing_perbaikan as $idx => $item): ?>
                        <tr>
                            <td><?= $idx + 1 ?></td>
                            <td>
                                <strong class="text-primary"><?= esc($item['kode_pengajuan']) ?></strong>
                            </td>
                            <td>
                                <?php
                                $kategori = $item['kategori_kerusakan'] ?? 'Lainnya';
                                $kategoryColor = [
                                    'Struktur' => 'secondary',
                                    'Elektrikal' => 'warning',
                                    'Plumbing' => 'info',
                                    'Interior' => 'success',
                                    'Eksterior' => 'primary',
                                    'Lainnya' => 'dark'
                                ];
                                $badgeColor = $kategoryColor[$kategori] ?? 'secondary';
                                ?>
                                <span class="badge badge-<?= $badgeColor ?>"><?= esc($kategori) ?></span>
                            </td>
                            <td>
                                <small><?= esc(substr($item['deskripsi_kerusakan'], 0, 50)) ?><?= strlen($item['deskripsi_kerusakan']) > 50 ? '...' : '' ?></small>
                            </td>
                            <td class="text-center">
                                <?php
                                $status = $item['status'] ?? 'Pending';
                                $statusColor = [
                                    'Pending' => 'warning',
                                    'Approved' => 'info',
                                    'In Progress' => 'primary',
                                    'Completed' => 'success',
                                    'Rejected' => 'danger',
                                    'Cancelled' => 'secondary'
                                ];
                                $sColor = $statusColor[$status] ?? 'secondary';
                                ?>
                                <span class="badge badge-<?= $sColor ?>"><?= esc($status) ?></span>
                            </td>
                            <td>
                                <small><?= date('d/m/Y', strtotime($item['tanggal_pengajuan'])) ?></small><br>
                                <small class="text-muted"><?= date('H:i', strtotime($item['tanggal_pengajuan'])) ?></small>
                            </td>
                            <td>
                                <?php if (!empty($item['estimasi_biaya']) && $item['estimasi_biaya'] > 0): ?>
                                    <small>Rp <?= number_format($item['estimasi_biaya'], 0, ',', '.') ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php 
                                $fotoCount = count($item['foto_kerusakan_parsed'] ?? []);
                                $lampiranCount = count($item['lampiran_parsed'] ?? []);
                                $totalFiles = $fotoCount + $lampiranCount;
                                ?>
                                <?php if ($totalFiles > 0): ?>
                                    <span class="badge badge-success">
                                        <i class="fas fa-file"></i> <?= $totalFiles ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-info" 
                                        onclick="viewDetailPerbaikan(<?= $item['id'] ?>)" 
                                        title="Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php endif; ?>

    </div>
</div>

<!-- Modal Detail Perbaikan -->
<div class="modal fade" id="modalDetailPerbaikan" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-clipboard-list mr-2"></i> 
                    Detail Pengajuan — <span id="modalKodePengajuan"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0" id="modalDetailContent">
                <div id="modalLoading" class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                    <p class="mt-3 text-muted">Memuat data...</p>
                </div>

                <div id="modalContent" style="display:none;">
                    
                    <!-- TIMELINE -->
                    <div class="px-4 pt-4 pb-3 border-bottom bg-light">
                        <h6 class="text-muted text-uppercase font-weight-bold mb-3" style="font-size:0.75rem; letter-spacing:0.05em;">
                            <i class="fas fa-route mr-1"></i> Progress Perbaikan
                        </h6>
                        <div id="timelineContainer"></div>
                        
                        <div id="progressBarContainer" style="display:none;" class="mt-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="font-weight-bold text-primary">Progress Pengerjaan</small>
                                <small id="progressPercent" class="font-weight-bold text-primary">0%</small>
                            </div>
                            <div class="progress" style="height: 10px; border-radius: 10px;">
                                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                     style="width: 0%; border-radius: 10px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- TABS -->
                    <ul class="nav nav-tabs px-3 pt-2 bg-white border-bottom">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#tabInfo">
                                <i class="fas fa-info-circle mr-1"></i> Info
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tabFoto">
                                <i class="fas fa-images mr-1"></i> File 
                                <span id="fotoCount" class="badge badge-secondary ml-1">0</span>
                            </a>
                        </li>
                        <li class="nav-item" id="tabRatingNav" style="display:none;">
                            <a class="nav-link" data-toggle="tab" href="#tabRating">
                                <i class="fas fa-star mr-1"></i> Rating
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-4">
                        <!-- TAB INFO -->
                        <div class="tab-pane fade show active" id="tabInfo">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless">
                                        <tr><td class="text-muted" width="45%">Tipe Aset</td><td><strong id="dTipeAset">-</strong></td></tr>
                                        <tr><td class="text-muted">Kategori</td><td><span id="dKategori" class="badge badge-info">-</span></td></tr>
                                        <tr><td class="text-muted">Prioritas</td><td><span id="dPrioritas" class="badge">-</span></td></tr>
                                        <tr><td class="text-muted">Tanggal Ajuan</td><td id="dTanggal">-</td></tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless">
                                        <tr><td class="text-muted" width="45%">Estimasi Biaya</td><td id="dEstimasi">-</td></tr>
                                        <tr><td class="text-muted">Biaya Aktual</td><td id="dBiayaAktual">-</td></tr>
                                        <tr><td class="text-muted">Penanggung Jawab</td><td id="dPIC">-</td></tr>
                                        <tr><td class="text-muted">Diproses Oleh</td><td id="dApprover">-</td></tr>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-2">
                                <p class="text-muted mb-1"><small class="font-weight-bold text-uppercase">Deskripsi Kerusakan</small></p>
                                <div id="dDeskripsi" class="p-3 bg-light rounded" style="font-size:0.9rem; line-height:1.6;"></div>
                            </div>

                            <div id="dCatatanBox" class="mt-3" style="display:none;">
                                <p class="text-muted mb-1"><small class="font-weight-bold text-uppercase" id="dCatatanLabel">Catatan</small></p>
                                <div id="dCatatan" class="p-3 rounded" style="font-size:0.9rem;"></div>
                            </div>
                        </div>

                        <!-- TAB FOTO -->
                        <div class="tab-pane fade" id="tabFoto">
                            <div id="fotoKerusakanSection">
                                <h6 class="text-muted text-uppercase mb-2" style="font-size:0.75rem;">File Kerusakan</h6>
                                <div id="fotoKerusakanList" class="d-flex flex-wrap mb-4"></div>
                            </div>
                            <div id="fotoProgressSection" style="display:none;">
                                <h6 class="text-muted text-uppercase mb-2" style="font-size:0.75rem;">File Progress</h6>
                                <div id="fotoProgressList" class="d-flex flex-wrap mb-4"></div>
                            </div>
                            <div id="fotoSelesaiSection" style="display:none;">
                                <h6 class="text-muted text-uppercase mb-2" style="font-size:0.75rem;">File Selesai</h6>
                                <div id="fotoSelesaiList" class="d-flex flex-wrap"></div>
                            </div>
                            <div id="noFotoMsg" class="text-center text-muted py-4" style="display:none;">
                                <i class="fas fa-image fa-2x mb-2"></i><br>Belum ada file
                            </div>
                        </div>

                        <!-- TAB RATING -->
                        <div class="tab-pane fade" id="tabRating">
                            <div id="ratingExisting" style="display:none;">
                                <div class="text-center py-3">
                                    <p class="text-muted mb-1">Rating yang diberikan</p>
                                    <div id="ratingStarsDisplay" class="mb-2" style="font-size:2rem; color:#f59e0b;"></div>
                                    <p id="ratingKomentar"></p>
                                </div>
                            </div>
                            <div id="ratingForm">
                                <p class="text-muted mb-3">Berikan penilaian untuk pekerjaan perbaikan ini:</p>
                                <div class="text-center mb-3">
                                    <div class="d-flex justify-content-center" style="gap:8px; font-size:2.5rem; cursor:pointer;">
                                        <?php for($s=1; $s<=5; $s++): ?>
                                        <i class="fas fa-star star-btn text-muted" data-value="<?= $s ?>"
                                           onmouseover="hoverStar(<?= $s ?>)"
                                           onmouseout="resetStars()"
                                           onclick="selectStar(<?= $s ?>)"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <small id="ratingLabel" class="text-muted mt-1 d-block">Pilih bintang</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Komentar (opsional)</label>
                                    <textarea id="ratingKomentarInput" class="form-control" rows="3" 
                                              placeholder="Bagaimana hasil perbaikannya?"></textarea>
                                </div>
                                <button type="button" class="btn btn-warning btn-block" onclick="submitRating()">
                                    <i class="fas fa-star mr-1"></i> Kirim Rating
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-danger" id="btnCancelPengajuan" 
                        style="display:none;" onclick="cancelPengajuan()">
                    <i class="fas fa-times-circle mr-1"></i> Batalkan Pengajuan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // ============================================
    // 1. INISIALISASI TOM SELECT
    // ============================================
    var employeeSelect = new TomSelect("#employee_select", {
        valueField: 'id',
        labelField: 'text',
        searchField: 'text',
        placeholder: "Ketik nama atau NIK...",
        maxOptions: 50,
        
        options: [{
            id: '<?= esc($mess['nik']) ?>',
            text: '<?= esc($mess['nama_karyawan']) ?> (<?= esc($mess['nik']) ?>)',
            nama_asli: '<?= esc($mess['nama_karyawan']) ?>',
            nik_asli: '<?= esc($mess['nik']) ?>'
        }],
        
        items: ['<?= esc($mess['nik']) ?>'],
        
        load: function(query, callback) {
            if (query.length < 2) return callback();

            var url = '<?= base_url('general-service/ajax/search-employees') ?>?search=' + encodeURIComponent(query);
            
            fetch(url)
                .then(response => response.json())
                .then(json => {
                    var results = json.map(function(item) {
                        return {
                            id: item.employee_number,
                            text: item.employee_name + ' (' + item.employee_number + ')',
                            nama_asli: item.employee_name,
                            nik_asli: item.employee_number
                        };
                    });
                    callback(results);
                })
                .catch(() => {
                    callback();
                });
        },
        
        render: {
            option: function(data, escape) {
                return '<div class="py-2 px-1">' +
                        '<div class="font-weight-bold text-dark">' + escape(data.nama_asli) + '</div>' +
                        '<div class="text-muted small">NIK: ' + escape(data.nik_asli) + '</div>' +
                    '</div>';
            },
            item: function(data, escape) {
                return '<div>' + escape(data.nama_asli) + ' (' + escape(data.nik_asli) + ')</div>';
            }
        },

        onChange: function(value) {
            var selectedData = this.options[value];

            if(selectedData) {
                document.getElementById('nama_karyawan').value = selectedData.nama_asli;
                document.getElementById('nik_hidden').value = selectedData.nik_asli;
                document.getElementById('nik_display').value = selectedData.nik_asli;
            } else {
                document.getElementById('nama_karyawan').value = '';
                document.getElementById('nik_hidden').value = '';
                document.getElementById('nik_display').value = '';
            }
        }
    });


    // ============================================
    // 2. LOGIKA DIVISI & JOB SITE
    // ============================================
    const divisiSelect = document.getElementById('divisi');
    const jobSiteSelect = document.getElementById('job_site');
    const siteIdInput = document.getElementById('site_id');

    if (divisiSelect.value) {
        loadJobSites(divisiSelect.value, '<?= esc($mess['site_id']) ?>');
    }

    divisiSelect.addEventListener('change', function() {
        const divisiId = this.value;
        if (divisiId) {
            loadJobSites(divisiId, null);
        } else {
            jobSiteSelect.innerHTML = '<option value="">-- Pilih Divisi Terlebih Dahulu --</option>';
            siteIdInput.value = '';
        }
    });

    function loadJobSites(divisiId, selectedSiteId = null) {
        jobSiteSelect.innerHTML = '<option value="">Loading...</option>';
        
        fetch('<?= base_url('general-service/ajax/get-site-by-divisi-code') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'divisi_id=' + encodeURIComponent(divisiId)
        })
        .then(response => response.json())
        .then(data => {
            let html = '<option value="">-- Pilih Job Site --</option>';
            data.forEach(item => {
                const isSelected = selectedSiteId && (item.name === selectedSiteId || item.id === selectedSiteId);
                html += `<option value="${item.name}" data-site-id="${item.id || item.name}" ${isSelected ? 'selected' : ''}>${item.name}</option>`;
            });
            jobSiteSelect.innerHTML = html;
            
            if (selectedSiteId) {
                const selectedOption = jobSiteSelect.options[jobSiteSelect.selectedIndex];
                if (selectedOption) {
                    siteIdInput.value = selectedOption.getAttribute('data-site-id') || selectedOption.value;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            jobSiteSelect.innerHTML = '<option value="">Error mengambil data</option>';
        });
    }

    jobSiteSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        siteIdInput.value = selectedOption.getAttribute('data-site-id') || this.value;
    });


    // ============================================
    // 3. LOGIKA PARKIR
    // ============================================
    const parkirAda = document.getElementById('parkirAda');
    const parkirTidak = document.getElementById('parkirTidak');
    const luasParkirInput = document.getElementById('luasParkir');

    function toggleParkir(isAda) {
        if (isAda) {
            luasParkirInput.readOnly = false;
        } else {
            luasParkirInput.value = 0;
            luasParkirInput.readOnly = true;
        }
    }

    if (parkirAda && parkirAda.checked) {
        luasParkirInput.readOnly = false;
    } else if (parkirTidak && parkirTidak.checked) {
        luasParkirInput.value = 0;
        luasParkirInput.readOnly = true;
    }

    if(parkirAda) parkirAda.addEventListener('change', function() { 
        if (this.checked) toggleParkir(true); 
    });
    if(parkirTidak) parkirTidak.addEventListener('change', function() { 
        if (this.checked) toggleParkir(false); 
    });


    // ============================================
    // 4. FORM VALIDATION MESS
    // ============================================
    const form = document.getElementById('messForm');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function(e) {
        const checkboxes = document.querySelectorAll('.fasilitas-checkbox:checked');
        const errorMsg = document.getElementById('fasilitas-error');
        const employeeSelectValue = document.getElementById('employee_select').value;
        
        if (checkboxes.length === 0) {
            e.preventDefault();
            errorMsg.classList.remove('d-none');
            alert('Pilih minimal satu fasilitas!');
            return false;
        } else {
            errorMsg.classList.add('d-none');
        }

        if (!employeeSelectValue) {
            e.preventDefault();
            alert('Silakan pilih karyawan terlebih dahulu!');
            return false;
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengupdate...';
        return true;
    });
});
</script>

<!-- ============================================ -->
<!-- JAVASCRIPT UNTUK PENGAJUAN PERBAIKAN -->
<!-- ============================================ -->
<script>
// ============================================
// DYNAMIC PERBAIKAN FORM HANDLER
// ============================================

let perbaikanCounter = 0;
const perbaikanList = [];

// Fungsi untuk membuat card perbaikan baru
function createPerbaikanCard(index) {
    return `
    <div class="card border-success mb-3 perbaikan-card" data-index="${index}" id="perbaikan-${index}">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-success">
                <i class="fas fa-wrench"></i> Pengajuan Perbaikan #${index + 1}
            </h6>
            <button type="button" class="btn btn-sm btn-danger" onclick="removePerbaikan(${index})">
                <i class="fas fa-trash"></i> Hapus
            </button>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Deskripsi Perbaikan -->
                <div class="col-md-8 mb-3">
                    <label class="form-label font-weight-bold">
                        Deskripsi Perbaikan <span class="text-danger">*</span>
                    </label>
                    <textarea id="deskripsi_${index}" 
                              class="form-control perbaikan-field" 
                              rows="3" 
                              placeholder="Jelaskan kerusakan/perbaikan yang dibutuhkan secara detail...
Contoh: Atap bocor di kamar tidur 1, perlu penggantian genteng"
                              required></textarea>
                </div>
                
                <!-- Kategori -->
                <div class="col-md-4 mb-3">
                    <label class="form-label font-weight-bold">
                        Kategori <span class="text-danger">*</span>
                    </label>
                    <select id="kategori_${index}" class="form-control perbaikan-field" required>
                        <option value="">-- Pilih --</option>
                        <option value="Ringan">🟢 Ringan</option>
                        <option value="Sedang">🟡 Sedang</option>
                        <option value="Berat">🟠 Berat</option>
                        <option value="Darurat">🔴 Darurat</option>
                    </select>
                    <small class="text-muted">Tingkat urgensi perbaikan</small>
                </div>
            </div>

            <div class="row">
                <!-- Upload File -->
                <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">
                        Upload Dokumen <span class="text-danger">*</span>
                    </label>
                    <input type="file" 
                           id="files_${index}"
                           class="form-control-file perbaikan-file" 
                           accept="image/*,.pdf,.doc,.docx"
                           multiple
                           onchange="previewFiles(this, ${index})"
                           required>
                    <small class="text-muted d-block mt-1">
                        <i class="fas fa-info-circle"></i> 
                        Format: JPG, PNG, PDF, DOC (Max 5 file @ 5MB)
                    </small>
                    <div id="preview-${index}" class="mt-2"></div>
                </div>
                
                <!-- Estimasi Biaya -->
                <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">
                        Estimasi Biaya (Opsional)
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Rp</span>
                        </div>
                        <input type="number" 
                               id="estimasi_biaya_${index}"
                               class="form-control" 
                               placeholder="0"
                               min="0"
                               step="1000">
                    </div>
                    <small class="text-muted">Perkiraan biaya perbaikan</small>
                </div>
            </div>

            <div class="row">
                
                
                <!-- Catatan Tambahan -->
                <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">
                        Catatan Tambahan
                    </label>
                    <textarea id="catatan_${index}" 
                              class="form-control" 
                              rows="2" 
                              placeholder="Informasi tambahan (opsional)"></textarea>
                </div>
            </div>

            <!-- Tombol Submit per card -->
            <div class="text-right mt-3">
                <button type="button" class="btn btn-primary" onclick="submitPerbaikan(${index})">
                    <i class="fas fa-paper-plane"></i> Kirim Pengajuan Ini
                </button>
            </div>
        </div>
    </div>
    `;
}

// Fungsi untuk menambah perbaikan
function addPerbaikan() {
    const container = document.getElementById('perbaikanListContainer');
    const emptyMsg = document.getElementById('emptyPerbaikanMsg');
    
    if (emptyMsg) {
        emptyMsg.style.display = 'none';
    }
    
    const cardHTML = createPerbaikanCard(perbaikanCounter);
    container.insertAdjacentHTML('beforeend', cardHTML);
    
    perbaikanList.push(perbaikanCounter);
    perbaikanCounter++;
    
    const newCard = document.getElementById(`perbaikan-${perbaikanCounter - 1}`);
    newCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    updatePerbaikanCount();
}

// Fungsi untuk menghapus perbaikan
function removePerbaikan(index) {
    if (confirm('Yakin ingin menghapus pengajuan perbaikan ini?')) {
        const card = document.getElementById(`perbaikan-${index}`);
        card.remove();
        
        const idx = perbaikanList.indexOf(index);
        if (idx > -1) {
            perbaikanList.splice(idx, 1);
        }
        
        if (perbaikanList.length === 0) {
            const emptyMsg = document.getElementById('emptyPerbaikanMsg');
            if (emptyMsg) {
                emptyMsg.style.display = 'block';
            }
        }
        
        updatePerbaikanCount();
    }
}

// Fungsi untuk preview file
function previewFiles(input, index) {
    const previewContainer = document.getElementById(`preview-${index}`);
    previewContainer.innerHTML = '';
    
    if (input.files && input.files.length > 0) {
        const fileList = Array.from(input.files);
        let html = '<div class="d-flex flex-wrap">';
        
        fileList.forEach((file, idx) => {
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            const isImage = file.type.startsWith('image/');
            const icon = isImage ? 'fa-image' : 'fa-file-alt';
            
            html += `
                <div class="file-preview-item">
                    <i class="fas ${icon} fa-2x text-primary"></i>
                    <div class="small mt-1">${file.name.substring(0, 10)}...</div>
                    <div class="text-muted" style="font-size: 10px;">${fileSize} MB</div>
                </div>
            `;
        });
        
        html += '</div>';
        previewContainer.innerHTML = html;
    }
}

// Update counter
function updatePerbaikanCount() {
    const btnAdd = document.getElementById('btnAddPerbaikan');
    const count = perbaikanList.length;
    
    if (count > 0) {
        btnAdd.innerHTML = `<i class="fas fa-plus-circle"></i> Tambah Pengajuan Perbaikan (${count} item)`;
    } else {
        btnAdd.innerHTML = '<i class="fas fa-plus-circle"></i> Tambah Pengajuan Perbaikan';
    }
}

// Submit perbaikan via AJAX
function submitPerbaikan(index) {
    const deskripsi = document.getElementById(`deskripsi_${index}`).value;
    const kategori = document.getElementById(`kategori_${index}`).value;
    const estimasi = document.getElementById(`estimasi_biaya_${index}`).value;
    const catatan = document.getElementById(`catatan_${index}`).value;
    const files = document.getElementById(`files_${index}`).files;

    if (!deskripsi || !kategori || !prioritas) {
        showAlert('error', 'Lengkapi semua field wajib!');
        return;
    }

    if (files.length === 0) {
        showAlert('error', 'Upload minimal 1 file!');
        return;
    }

    const formData = new FormData();

    // ===== FIELD SESUAI VALIDASI BACKEND =====
    formData.append('tipe_aset', 'Mess');
    formData.append('aset_id', '<?= $mess['id'] ?>');
    formData.append('aset_code', '<?= esc($mess['mess_code']) ?>');
    formData.append('kategori_kerusakan', kategori);
    formData.append('jenis_kerusakan', 'Perbaikan Bangunan Mess');
    formData.append('deskripsi_kerusakan', deskripsi);
    formData.append('estimasi_biaya', estimasi || 0);
    formData.append('catatan', catatan);

    // ===== FILE (WAJIB pakai NAMA INI) =====
    for (let i = 0; i < files.length; i++) {
        formData.append('foto_kerusakan[]', files[i]);
    }

    fetch('<?= base_url('general-service/repair-request/store') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        console.log(data); // ← lihat di F12 Console
        
        // Coba cek berbagai kemungkinan struktur
        const isSuccess = data.success === true 
            || data.status === 201 
            || (data.data && data.data.success === true);
        
        if (isSuccess) {
            showAlert('success', data.message || 'Berhasil');
            requestAnimationFrame(() => location.reload());
        } else {
            showAlert('error', data.message || 'Validasi gagal');
            console.error('Errors:', data.errors);
        }
    })
    .catch(() => {
        showAlert('error', 'Terjadi kesalahan server');
    });
}


// Show alert
function showAlert(type, message) {
    const alertDiv = document.getElementById('perbaikanAlert');
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    alertDiv.className = `alert ${alertClass} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <i class="fas ${icon}"></i> ${message}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    `;
    alertDiv.style.display = 'block';
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        alertDiv.style.display = 'none';
    }, 5000);
    
    // Scroll to alert
    alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

let currentRepairId = null;
let selectedRating = 0;

function viewDetailPerbaikan(id) {
    currentRepairId = id;
    selectedRating = 0;

    document.getElementById('modalLoading').style.display = 'block';
    document.getElementById('modalContent').style.display = 'none';
    document.getElementById('btnCancelPengajuan').style.display = 'none';
    document.getElementById('modalKodePengajuan').textContent = '';

    $('#modalDetailPerbaikan').modal('show');

    fetch('<?= base_url('general-service/mess/detail/') ?>' + id + '?format=json', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('modalLoading').style.display = 'none';

        if (!data.success) {
            document.getElementById('modalContent').innerHTML = `
                <div class="alert alert-danger m-4">
                    <i class="fas fa-exclamation-triangle"></i> ${data.message}
                </div>`;
            document.getElementById('modalContent').style.display = 'block';
            return;
        }

        const item = data.data;
        document.getElementById('modalContent').style.display = 'block';
        document.getElementById('modalKodePengajuan').textContent = item.kode_pengajuan || '';

        renderTimeline(item);

        if (item.status === 'In Progress') {
            const pct = item.progress_percentage || 0;
            document.getElementById('progressBarContainer').style.display = 'block';
            document.getElementById('progressBar').style.width = pct + '%';
            document.getElementById('progressPercent').textContent = pct + '%';
        } else {
            document.getElementById('progressBarContainer').style.display = 'none';
        }

        document.getElementById('dTipeAset').textContent = item.tipe_aset || '-';
        document.getElementById('dKategori').textContent = item.kategori_kerusakan || '-';
        document.getElementById('dTanggal').textContent = formatDate(item.tanggal_pengajuan);
        document.getElementById('dDeskripsi').textContent = item.deskripsi_kerusakan || '-';
        document.getElementById('dPIC').textContent = item.penanggung_jawab || '-';
        document.getElementById('dApprover').textContent = item.disetujui_oleh_name || '-';
        document.getElementById('dEstimasi').textContent = item.estimasi_biaya > 0
            ? 'Rp ' + parseInt(item.estimasi_biaya).toLocaleString('id-ID') : '-';
        document.getElementById('dBiayaAktual').textContent = item.biaya_aktual > 0
            ? 'Rp ' + parseInt(item.biaya_aktual).toLocaleString('id-ID') : '-';

        if (item.status === 'Approved' && item.catatan_persetujuan) {
            document.getElementById('dCatatanBox').style.display = 'block';
            document.getElementById('dCatatanLabel').textContent = 'Catatan Persetujuan';
            document.getElementById('dCatatan').className = 'p-3 rounded bg-success text-white';
            document.getElementById('dCatatan').textContent = item.catatan_persetujuan;
        } else if (item.status === 'Rejected' && item.alasan_penolakan) {
            document.getElementById('dCatatanBox').style.display = 'block';
            document.getElementById('dCatatanLabel').textContent = 'Alasan Penolakan';
            document.getElementById('dCatatan').className = 'p-3 rounded bg-danger text-white';
            document.getElementById('dCatatan').textContent = item.alasan_penolakan;
        } else {
            document.getElementById('dCatatanBox').style.display = 'none';
        }

        renderFoto(item);

        const tabRatingNav = document.getElementById('tabRatingNav');
        if (item.status === 'Completed') {
            tabRatingNav.style.display = 'block';
            if (item.rating) {
                document.getElementById('ratingForm').style.display = 'none';
                document.getElementById('ratingExisting').style.display = 'block';
                document.getElementById('ratingStarsDisplay').innerHTML = '★'.repeat(item.rating) + '☆'.repeat(5 - item.rating);
                document.getElementById('ratingKomentar').textContent = item.komentar_rating || '-';
            } else {
                document.getElementById('ratingForm').style.display = 'block';
                document.getElementById('ratingExisting').style.display = 'none';
            }
        } else {
            tabRatingNav.style.display = 'none';
        }

        if (item.status === 'Pending') {
            document.getElementById('btnCancelPengajuan').style.display = 'inline-block';
        }
    })
    .catch(() => {
        document.getElementById('modalLoading').style.display = 'none';
        document.getElementById('modalContent').innerHTML = `
            <div class="alert alert-danger m-4">
                <i class="fas fa-exclamation-triangle"></i> Gagal memuat data
            </div>`;
        document.getElementById('modalContent').style.display = 'block';
    });
}

function renderTimeline(item) {
    const steps = [
        { label: 'Pengajuan Dibuat', date: item.tanggal_pengajuan, by: item.created_by_name, icon: 'fa-file-alt', color: 'primary', active: true },
        { label: item.status === 'Rejected' ? 'Ditolak' : 'Disetujui', date: item.tanggal_disetujui || item.tanggal_ditolak, by: item.disetujui_oleh_name, icon: item.status === 'Rejected' ? 'fa-times-circle' : 'fa-check-circle', color: item.status === 'Rejected' ? 'danger' : 'success', active: ['Approved','In Progress','Completed','Rejected'].includes(item.status) },
        { label: 'Dikerjakan', date: item.tanggal_mulai, by: item.penanggung_jawab, icon: 'fa-tools', color: 'info', active: ['In Progress','Completed'].includes(item.status) },
        { label: 'Selesai', date: item.tanggal_selesai, by: null, icon: 'fa-flag-checkered', color: 'success', active: item.status === 'Completed' },
    ];

    if (item.status === 'Cancelled') {
        steps[1] = { label: 'Dibatalkan', date: item.tanggal_dibatalkan, by: null, icon: 'fa-ban', color: 'secondary', active: true };
        steps.splice(2);
    }

    let html = '<div class="d-flex align-items-start justify-content-between" style="position:relative;">';
    html += `<div style="position:absolute; top:20px; left:20px; right:20px; height:2px; background:#e5e7eb; z-index:0;"></div>`;

    steps.forEach(step => {
        const opacity = step.active ? '1' : '0.35';
        html += `
        <div class="text-center" style="flex:1; position:relative; z-index:1; opacity:${opacity};">
            <div class="mx-auto d-flex align-items-center justify-content-center rounded-circle bg-${step.active ? step.color : 'light'}" 
                 style="width:40px; height:40px; border:2px solid ${step.active ? 'transparent' : '#dee2e6'}; margin-bottom:8px;">
                <i class="fas ${step.icon} text-${step.active ? 'white' : 'muted'}" style="font-size:0.9rem;"></i>
            </div>
            <div style="font-size:0.75rem; font-weight:600; color:${step.active ? '#111827' : '#9ca3af'};">${step.label}</div>
            <div style="font-size:0.7rem; color:#6b7280;">${step.date ? formatDate(step.date) : 'Menunggu'}</div>
            ${step.by ? `<div style="font-size:0.65rem; color:#9ca3af;">oleh: ${step.by}</div>` : ''}
        </div>`;
    });

    html += '</div>';
    document.getElementById('timelineContainer').innerHTML = html;
}

function renderFoto(item) {
    const baseUrl = '<?= base_url() ?>';
    let totalFoto = 0;

    function buildFotoHtml(arr) {
        if (!arr || arr.length === 0) return '';
        return arr.map(f => `
            <a href="${baseUrl}${f.path}" target="_blank" 
               style="display:inline-block; margin:4px; border-radius:8px; overflow:hidden; border:1px solid #e5e7eb; width:80px; height:80px;">
                ${f.type && f.type.startsWith('image/')
                    ? `<img src="${baseUrl}${f.path}" style="width:100%; height:100%; object-fit:cover;">`
                    : `<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#f3f4f6;">
                         <i class="fas fa-file-alt fa-2x text-muted"></i></div>`}
            </a>`).join('');
    }

    document.getElementById('fotoKerusakanList').innerHTML = buildFotoHtml(item.foto_kerusakan) || '<small class="text-muted">Tidak ada foto</small>';
    totalFoto += (item.foto_kerusakan || []).length;

    if (item.foto_progress && item.foto_progress.length > 0) {
        document.getElementById('fotoProgressSection').style.display = 'block';
        document.getElementById('fotoProgressList').innerHTML = buildFotoHtml(item.foto_progress);
        totalFoto += item.foto_progress.length;
    } else {
        document.getElementById('fotoProgressSection').style.display = 'none';
    }

    if (item.foto_selesai && item.foto_selesai.length > 0) {
        document.getElementById('fotoSelesaiSection').style.display = 'block';
        document.getElementById('fotoSelesaiList').innerHTML = buildFotoHtml(item.foto_selesai);
        totalFoto += item.foto_selesai.length;
    } else {
        document.getElementById('fotoSelesaiSection').style.display = 'none';
    }

    document.getElementById('fotoCount').textContent = totalFoto;
    document.getElementById('noFotoMsg').style.display = totalFoto === 0 ? 'block' : 'none';
}

function cancelPengajuan() {
    if (!confirm('Yakin ingin membatalkan pengajuan ini?')) return;

    fetch('<?= base_url('general-service/repair-request/cancel/') ?>' + currentRepairId, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            $('#modalDetailPerbaikan').modal('hide');
            showAlert('success', 'Pengajuan berhasil dibatalkan');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('error', data.message || 'Gagal membatalkan');
        }
    })
    .catch(() => showAlert('error', 'Terjadi kesalahan'));
}

function hoverStar(val) {
    document.querySelectorAll('.star-btn').forEach((s, i) => {
        s.style.color = i < val ? '#f59e0b' : '#9ca3af';
    });
}
function resetStars() {
    document.querySelectorAll('.star-btn').forEach((s, i) => {
        s.style.color = i < selectedRating ? '#f59e0b' : '#9ca3af';
    });
}
function selectStar(val) {
    selectedRating = val;
    const labels = ['', 'Sangat Buruk', 'Buruk', 'Cukup', 'Baik', 'Sangat Baik'];
    document.getElementById('ratingLabel').textContent = labels[val];
    resetStars();
}
function submitRating() {
    if (selectedRating === 0) { alert('Pilih bintang terlebih dahulu!'); return; }

    const formData = new FormData();
    formData.append('rating', selectedRating);
    formData.append('komentar_rating', document.getElementById('ratingKomentarInput').value);

    fetch('<?= base_url('general-service/repair-request/rate/') ?>' + currentRepairId, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Rating berhasil dikirim!');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('error', data.message || 'Gagal mengirim rating');
        }
    })
    .catch(() => showAlert('error', 'Terjadi kesalahan'));
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' })
           + ' ' + d.toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' });
}

// Event listener untuk tombol tambah
document.getElementById('btnAddPerbaikan').addEventListener('click', addPerbaikan);
</script>

<?= $this->endSection() ?>