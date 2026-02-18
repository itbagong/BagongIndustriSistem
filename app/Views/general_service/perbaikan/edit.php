<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="content-header mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-edit mr-2"></i> Edit Pengajuan Perbaikan</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-0 mt-2">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('general-service/repair-request') ?>">Pengajuan Perbaikan</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
        <div>
            <?php if($repair['status'] == 'Pending'): ?>
            <!-- Tombol Approve -->
            <button type="button" class="btn btn-success mr-2" onclick="showApproveModal()">
                <i class="fas fa-check-circle"></i> Setujui
            </button>
            <!-- Tombol Reject -->
            <button type="button" class="btn btn-danger mr-2" onclick="showRejectModal()">
                <i class="fas fa-times-circle"></i> Tolak
            </button>
            <?php endif; ?>
            
            <!-- Tombol Kembali -->
            <a href="<?= base_url('general-service/repair-request') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>

<?php if(session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <h6><i class="fas fa-exclamation-triangle"></i> Validasi Error!</h6>
        <ul class="mb-0">
        <?php foreach(session()->getFlashdata('errors') as $err): ?>
            <li><?= esc($err) ?></li>
        <?php endforeach; ?>
        </ul>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
<?php endif; ?>

<?php if(session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
<?php endif; ?>

<?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success') ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="fas fa-clipboard-check"></i> Form Edit Pengajuan</h5>
    </div>
    <div class="card-body">
        
        <!-- Info Box -->
        <div class="alert alert-info">
            <div class="row">
                <div class="col-md-6">
                    <strong>Kode:</strong> <?= esc($repair['kode_pengajuan']) ?><br>
                    <strong>Tipe Aset:</strong> <?= esc($repair['tipe_aset']) ?><br>
                    <strong>Status:</strong> 
                    <?php
                    $statusClass = 'secondary';
                    if($repair['status'] == 'Completed') $statusClass = 'success';
                    elseif($repair['status'] == 'Approved') $statusClass = 'info';
                    elseif($repair['status'] == 'In Progress') $statusClass = 'primary';
                    elseif($repair['status'] == 'Rejected') $statusClass = 'danger';
                    else $statusClass = 'warning';
                    ?>
                    <span class="badge badge-<?= $statusClass ?>"><?= esc($repair['status']) ?></span>
                </div>
                <div class="col-md-6">
                    <strong>PIC:</strong> <?= esc($repair['nama_karyawan']) ?> (<?= esc($repair['nik']) ?>)<br>
                    <strong>Lokasi:</strong> <?= esc($repair['site_id']) ?><br>
                    <strong>Divisi:</strong> <?= esc($repair['divisi_name'] ?? '-') ?>
                </div>
            </div>
        </div>

        <form action="<?= base_url('general-service/repair-request/update/' . $repair['id']) ?>" 
              method="post" 
              enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Kategori Kerusakan <span class="text-danger">*</span></label>
                    <select name="kategori_kerusakan" class="form-control" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach($kategori_list as $kat): ?>
                        <option value="<?= $kat ?>" <?= old('kategori_kerusakan', $repair['kategori_kerusakan']) == $kat ? 'selected' : '' ?>>
                            <?= $kat ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Prioritas <span class="text-danger">*</span></label>
                    <select name="prioritas" class="form-control" required>
                        <option value="">-- Pilih Prioritas --</option>
                        <option value="Rendah" <?= old('prioritas', $repair['prioritas']) == 'Rendah' ? 'selected' : '' ?>>⚪ Rendah</option>
                        <option value="Sedang" <?= old('prioritas', $repair['prioritas']) == 'Sedang' ? 'selected' : '' ?>>🟡 Sedang</option>
                        <option value="Tinggi" <?= old('prioritas', $repair['prioritas']) == 'Tinggi' ? 'selected' : '' ?>>🟠 Tinggi</option>
                        <option value="Urgent" <?= old('prioritas', $repair['prioritas']) == 'Urgent' ? 'selected' : '' ?>>🔴 Urgent</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label>Jenis Kerusakan <span class="text-danger">*</span></label>
                <input type="text" name="jenis_kerusakan" class="form-control" 
                       value="<?= old('jenis_kerusakan', $repair['jenis_kerusakan']) ?>" required>
            </div>

            <div class="mb-3">
                <label>Lokasi Spesifik</label>
                <input type="text" name="lokasi_spesifik" class="form-control" 
                       value="<?= old('lokasi_spesifik', $repair['lokasi_spesifik'] ?? '') ?>" 
                       placeholder="Contoh: Kamar tidur lantai 2">
            </div>

            <div class="mb-3">
                <label>Deskripsi Detail Kerusakan <span class="text-danger">*</span></label>
                <textarea name="deskripsi_kerusakan" class="form-control" rows="4" required><?= old('deskripsi_kerusakan', $repair['deskripsi_kerusakan']) ?></textarea>
                <small class="text-muted">Minimal 10 karakter</small>
            </div>

            <div class="mb-3">
                <label>Estimasi Biaya</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Rp</span>
                    </div>
                    <input type="number" name="estimasi_biaya" class="form-control" 
                           value="<?= old('estimasi_biaya', $repair['estimasi_biaya']) ?>" min="0" step="1000">
                </div>
            </div>

            <div class="mb-3">
                <label>Upload Foto Baru (Opsional)</label>
                <input type="file" name="foto_kerusakan[]" class="form-control-file" multiple accept="image/*">
                <small class="text-muted">Jika diisi, akan mengganti foto lama. Max 5 file @ 5MB</small>
            </div>

            <!-- Foto Existing -->
            <?php if (!empty($repair['foto_kerusakan'])): ?>
            <div class="mb-3">
                <label>Foto Saat Ini:</label>
                <div class="row">
                    <?php foreach($repair['foto_kerusakan'] as $foto): ?>
                    <div class="col-md-2 mb-2">
                        <a href="<?= base_url($foto['path']) ?>" target="_blank">
                            <img src="<?= base_url($foto['path']) ?>" class="img-thumbnail" style="width:100%; height:100px; object-fit:cover;">
                        </a>
                        <small class="d-block text-center text-truncate"><?= esc($foto['original_name']) ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="mb-3">
                <label>Catatan Tambahan</label>
                <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan tambahan (opsional)"><?= old('catatan', $repair['catatan'] ?? '') ?></textarea>
            </div>

            <hr>

            <div class="d-flex justify-content-between">
                <a href="<?= base_url('general-service/repair-request') ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Update Pengajuan
                </button>
            </div>
        </form>
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
                    <div class="alert alert-info">
                        <strong>Kode:</strong> <?= esc($repair['kode_pengajuan']) ?><br>
                        <strong>Jenis:</strong> <?= esc($repair['jenis_kerusakan']) ?>
                    </div>
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
                    <div class="alert alert-warning">
                        <strong>Kode:</strong> <?= esc($repair['kode_pengajuan']) ?><br>
                        <strong>Jenis:</strong> <?= esc($repair['jenis_kerusakan']) ?>
                    </div>
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

<script>
// VANILLA JS - TANPA JQUERY
function showApproveModal() {
    var modal = new bootstrap.Modal(document.getElementById('modalApprove'));
    modal.show();
}

function showRejectModal() {
    var modal = new bootstrap.Modal(document.getElementById('modalReject'));
    modal.show();
}

// Auto hide alerts
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});
</script>

<style>
.badge {
    font-size: 0.875rem;
    padding: 0.35em 0.65em;
}
</style>

<?= $this->endSection() ?>