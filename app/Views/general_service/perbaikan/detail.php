<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="content-header mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-clipboard-check mr-2"></i> Review Pengajuan Perbaikan</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-0 mt-2">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('general-service/repair-request') ?>">Pengajuan Perbaikan</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </nav>
        </div>
        <a href="<?= base_url('general-service/repair-request') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success') ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
<?php endif; ?>

<?php if(session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
<?php endif; ?>

<!-- Summary Cards -->
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h6 class="mb-2">Kode Pengajuan</h6>
                <h4 class="mb-0"><?= esc($repair['kode_pengajuan']) ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-2">Status Saat Ini</h6>
                <?php
                $statusClass = 'secondary';
                if($repair['status'] == 'Completed') $statusClass = 'success';
                elseif($repair['status'] == 'Approved') $statusClass = 'info';
                elseif($repair['status'] == 'In Progress') $statusClass = 'primary';
                elseif($repair['status'] == 'Rejected') $statusClass = 'danger';
                else $statusClass = 'warning';
                ?>
                <h4 class="mb-0">
                    <span class="badge badge-<?= $statusClass ?>"><?= esc($repair['status']) ?></span>
                </h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-2">Prioritas</h6>
                <?php
                $prioritasClass = 'secondary';
                if($repair['prioritas'] == 'Urgent') $prioritasClass = 'danger';
                elseif($repair['prioritas'] == 'Tinggi') $prioritasClass = 'warning';
                elseif($repair['prioritas'] == 'Sedang') $prioritasClass = 'info';
                ?>
                <h4 class="mb-0">
                    <span class="badge badge-<?= $prioritasClass ?>"><?= esc($repair['prioritas']) ?></span>
                </h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-2">Estimasi Biaya</h6>
                <h4 class="mb-0">Rp <?= number_format($repair['estimasi_biaya'] ?? 0, 0, ',', '.') ?></h4>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column - Detail Pengajuan -->
    <div class="col-md-8">
        
        <!-- Informasi Aset -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-building"></i> Informasi Aset</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><td width="150"><strong>Tipe Aset</strong></td><td>: <?= esc($repair['tipe_aset']) ?></td></tr>
                            <tr><td><strong>Kode Aset</strong></td><td>: <?= esc($repair['aset_code'] ?? '-') ?></td></tr>
                            <tr><td><strong>Penanggung Jawab</strong></td><td>: <?= esc($repair['nama_karyawan']) ?></td></tr>
                            <tr><td><strong>NIK</strong></td><td>: <?= esc($repair['nik']) ?></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><td width="150"><strong>Divisi</strong></td><td>: <?= esc($repair['divisi_name'] ?? '-') ?></td></tr>
                            <tr><td><strong>Lokasi/Site</strong></td><td>: <?= esc($repair['site_id']) ?></td></tr>
                            <tr><td><strong>Luas Aset</strong></td><td>: <?= number_format($repair['luas'] ?? 0, 2) ?> m²</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Kerusakan -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Detail Kerusakan</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Kategori:</strong> 
                        <span class="badge badge-secondary"><?= esc($repair['kategori_kerusakan'] ?? 'Lainnya') ?></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Lokasi Spesifik:</strong> <?= esc($repair['lokasi_spesifik'] ?? '-') ?>
                    </div>
                </div>

                <h6><strong>Jenis Kerusakan:</strong></h6>
                <p><?= esc($repair['jenis_kerusakan']) ?></p>

                <h6><strong>Deskripsi Detail:</strong></h6>
                <p><?= esc($repair['deskripsi_detail']) ?></p>

                <?php if(!empty($repair['catatan'])): ?>
                <h6><strong>Catatan Tambahan:</strong></h6>
                <p class="text-muted"><?= esc($repair['catatan']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Dokumentasi -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-images"></i> Dokumentasi Kerusakan</h5>
            </div>
            <div class="card-body">
                <?php 
                $fotoKerusakan = !empty($repair['foto_kerusakan']) ? json_decode($repair['foto_kerusakan'], true) : [];
                if (!empty($fotoKerusakan) && is_array($fotoKerusakan)): 
                ?>
                <div class="row">
                    <?php foreach($fotoKerusakan as $foto): ?>
                    <div class="col-md-4 mb-3">
                        <a href="<?= base_url($foto['path']) ?>" target="_blank" data-lightbox="kerusakan">
                            <?php if(strpos($foto['type'], 'image') !== false): ?>
                                <img src="<?= base_url($foto['path']) ?>" class="img-thumbnail" style="width:100%; height:200px; object-fit:cover;">
                            <?php else: ?>
                                <div class="border p-3 text-center" style="height:200px; display:flex; align-items:center; justify-content:center;">
                                    <div>
                                        <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                        <p class="mt-2 mb-0 small"><?= esc($foto['original_name']) ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </a>
                        <small class="d-block text-center mt-1"><?= esc($foto['original_name']) ?></small>
                        <small class="d-block text-center text-muted"><?= number_format($foto['size']/1024, 2) ?> KB</small>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted text-center mb-0">Tidak ada dokumentasi</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-history"></i> Timeline</h5>
            </div>
            <div class="card-body">
                <ul class="timeline">
                    <li class="timeline-item">
                        <i class="fas fa-plus-circle text-primary"></i>
                        <strong>Pengajuan Dibuat</strong>
                        <span class="float-right text-muted"><?= date('d/m/Y H:i', strtotime($repair['tanggal_pengajuan'])) ?></span>
                        <?php if(!empty($repair['created_by_name'])): ?>
                        <p class="text-muted mb-0">oleh <?= esc($repair['created_by_name']) ?></p>
                        <?php endif; ?>
                    </li>

                    <?php if(!empty($repair['tanggal_disetujui'])): ?>
                    <li class="timeline-item">
                        <i class="fas fa-check-circle text-success"></i>
                        <strong>Disetujui</strong>
                        <span class="float-right text-muted"><?= date('d/m/Y H:i', strtotime($repair['tanggal_disetujui'])) ?></span>
                        <?php if(!empty($repair['disetujui_oleh_name'])): ?>
                        <p class="text-muted mb-0">oleh <?= esc($repair['disetujui_oleh_name']) ?></p>
                        <?php endif; ?>
                        <?php if(!empty($repair['catatan_persetujuan'])): ?>
                        <p class="text-muted mb-0"><em>"<?= esc($repair['catatan_persetujuan']) ?>"</em></p>
                        <?php endif; ?>
                    </li>
                    <?php endif; ?>

                    <?php if(!empty($repair['tanggal_mulai'])): ?>
                    <li class="timeline-item">
                        <i class="fas fa-play-circle text-info"></i>
                        <strong>Perbaikan Dimulai</strong>
                        <span class="float-right text-muted"><?= date('d/m/Y H:i', strtotime($repair['tanggal_mulai'])) ?></span>
                    </li>
                    <?php endif; ?>

                    <?php if(!empty($repair['tanggal_selesai'])): ?>
                    <li class="timeline-item">
                        <i class="fas fa-check-double text-success"></i>
                        <strong>Perbaikan Selesai</strong>
                        <span class="float-right text-muted"><?= date('d/m/Y H:i', strtotime($repair['tanggal_selesai'])) ?></span>
                        <?php if(!empty($repair['catatan_selesai'])): ?>
                        <p class="text-muted mb-0"><em>"<?= esc($repair['catatan_selesai']) ?>"</em></p>
                        <?php endif; ?>
                    </li>
                    <?php endif; ?>

                    <?php if(!empty($repair['tanggal_ditolak'])): ?>
                    <li class="timeline-item">
                        <i class="fas fa-times-circle text-danger"></i>
                        <strong>Ditolak</strong>
                        <span class="float-right text-muted"><?= date('d/m/Y H:i', strtotime($repair['tanggal_ditolak'])) ?></span>
                        <?php if(!empty($repair['alasan_penolakan'])): ?>
                        <p class="text-danger mb-0"><strong>Alasan:</strong> <?= esc($repair['alasan_penolakan']) ?></p>
                        <?php endif; ?>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

    </div>

    <!-- Right Column - Actions -->
    <div class="col-md-4">
        
        <!-- Action Panel -->
        <div class="card shadow-sm mb-3 sticky-top" style="top: 20px;">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-cogs"></i> Panel Aksi</h5>
            </div>
            <div class="card-body">
                
                <?php if($repair['status'] == 'Pending'): ?>
                <!-- Approve -->
                <button type="button" class="btn btn-success btn-block mb-2" onclick="showApproveModal()">
                    <i class="fas fa-check-circle"></i> Setujui Pengajuan
                </button>
                
                <!-- Reject -->
                <button type="button" class="btn btn-danger btn-block mb-3" onclick="showRejectModal()">
                    <i class="fas fa-times-circle"></i> Tolak Pengajuan
                </button>
                <?php endif; ?>

                <?php if($repair['status'] == 'Approved'): ?>
                <!-- Start Progress -->
                <button type="button" class="btn btn-primary btn-block mb-2" onclick="showStartModal()">
                    <i class="fas fa-play-circle"></i> Mulai Perbaikan
                </button>
                <?php endif; ?>

                <?php if($repair['status'] == 'In Progress'): ?>
                <!-- Update Progress -->
                <button type="button" class="btn btn-info btn-block mb-2" onclick="showProgressModal()">
                    <i class="fas fa-tasks"></i> Update Progress
                </button>

                <!-- Complete -->
                <button type="button" class="btn btn-success btn-block mb-2" onclick="showCompleteModal()">
                    <i class="fas fa-check-double"></i> Selesaikan Perbaikan
                </button>
                <?php endif; ?>

                <hr>

                <!-- Print -->
                <button type="button" class="btn btn-outline-secondary btn-block mb-2" onclick="window.print()">
                    <i class="fas fa-print"></i> Cetak Detail
                </button>

                <!-- Export PDF -->
                <a href="<?= base_url('general-service/repair-request/export-pdf/' . $repair['id']) ?>" 
                   class="btn btn-outline-danger btn-block" target="_blank">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>

            </div>
        </div>

    </div>
</div>

<!-- Modal Approve -->
<div class="modal fade" id="modalApprove" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check-circle"></i> Setujui Pengajuan</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="<?= base_url('general-service/repair-request/approve/' . $repair['id']) ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menyetujui pengajuan perbaikan ini?</p>
                    <div class="form-group">
                        <label>Catatan Persetujuan</label>
                        <textarea name="catatan_persetujuan" class="form-control" rows="3" 
                                  placeholder="Catatan untuk persetujuan (opsional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Ya, Setujui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Reject -->
<div class="modal fade" id="modalReject" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-times-circle"></i> Tolak Pengajuan</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="<?= base_url('general-service/repair-request/reject/' . $repair['id']) ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <p class="text-danger"><strong>Pengajuan akan ditolak!</strong></p>
                    <div class="form-group">
                        <label>Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="alasan_penolakan" class="form-control" rows="4" 
                                  placeholder="Jelaskan alasan penolakan..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Ya, Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Start Progress -->
<div class="modal fade" id="modalStart" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-play-circle"></i> Mulai Perbaikan</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="<?= base_url('general-service/repair-request/start/' . $repair['id']) ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <p>Tandai perbaikan ini sudah dimulai?</p>
                    <div class="form-group">
                        <label>Penanggung Jawab Perbaikan</label>
                        <select name="penanggung_jawab" class="form-control">
                            <option value="">Pilih PIC</option>
                            <?php foreach($pic_list ?? [] as $pic): ?>
                            <option value="<?= $pic['id'] ?>"><?= esc($pic['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-play"></i> Mulai
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Complete -->
<div class="modal fade" id="modalComplete" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check-double"></i> Selesaikan Perbaikan</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="<?= base_url('general-service/repair-request/complete/' . $repair['id']) ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Biaya Aktual Perbaikan</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                            <input type="number" name="biaya_aktual" class="form-control" 
                                   value="<?= $repair['estimasi_biaya'] ?? 0 ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Upload Foto Setelah Perbaikan</label>
                        <input type="file" name="foto_selesai[]" class="form-control-file" multiple accept="image/*">
                    </div>
                    <div class="form-group">
                        <label>Catatan Penyelesaian</label>
                        <textarea name="catatan_selesai" class="form-control" rows="3" 
                                  placeholder="Catatan hasil perbaikan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Selesai
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// TANPA JQUERY - VANILLA JS
function showApproveModal() {
    var modal = new bootstrap.Modal(document.getElementById('modalApprove'));
    modal.show();
}

function showRejectModal() {
    var modal = new bootstrap.Modal(document.getElementById('modalReject'));
    modal.show();
}

function showStartModal() {
    var modal = new bootstrap.Modal(document.getElementById('modalStart'));
    modal.show();
}

function showCompleteModal() {
    var modal = new bootstrap.Modal(document.getElementById('modalComplete'));
    modal.show();
}

function showProgressModal() {
    var modal = new bootstrap.Modal(document.getElementById('modalProgress'));
    modal.show();
}
</script>

<style>
.timeline {
    list-style: none;
    padding: 20px 0;
    position: relative;
}
.timeline:before {
    top: 0;
    bottom: 0;
    position: absolute;
    content: " ";
    width: 3px;
    background-color: #eeeeee;
    left: 15px;
    margin-left: -1.5px;
}
.timeline-item {
    margin-bottom: 20px;
    position: relative;
    padding-left: 40px;
}
.timeline-item i {
    position: absolute;
    left: 0;
    top: 0;
    background: #fff;
    padding: 5px;
    border-radius: 50%;
    border: 3px solid #eeeeee;
}
.sticky-top {
    position: -webkit-sticky;
    position: sticky;
}
@media print {
    .card-header.bg-success, .btn, .modal { display: none !important; }
}
</style>

<?= $this->endSection() ?>