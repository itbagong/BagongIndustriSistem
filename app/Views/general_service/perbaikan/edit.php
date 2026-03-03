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
            <button type="button" class="btn btn-success mr-2" onclick="showApproveModal()">
                <i class="fas fa-check-circle"></i> Setujui
            </button>
            <button type="button" class="btn btn-danger mr-2" onclick="showRejectModal()">
                <i class="fas fa-times-circle"></i> Tolak
            </button>
            <?php endif; ?>
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
                    if($repair['status'] == 'Completed')  $statusClass = 'success';
                    elseif($repair['status'] == 'Approved')   $statusClass = 'info';
                    elseif($repair['status'] == 'In Progress') $statusClass = 'primary';
                    elseif($repair['status'] == 'Rejected')   $statusClass = 'danger';
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

            <!-- ===== SECTION DOKUMEN TTD ===== -->
            <div class="card border-success mb-3" id="card-dokumen-ttd">
                <div class="card-header bg-success text-white py-2 d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-file-signature mr-2"></i>Dokumen Persetujuan</span>
                    <span class="badge badge-light text-success" id="badge-doc-count">
                        <?= count($dokumen_list ?? []) ?> dokumen
                    </span>
                </div>
                <div class="card-body">

                    <!-- FORM UPLOAD -->
                    <div class="border rounded p-3 mb-3 bg-light">
                        <div class="form-group mb-2">
                            <label class="font-weight-bold mb-1">
                                <i class="fas fa-upload mr-1"></i> Upload Dokumen Baru
                            </label>
                            <div class="custom-file mb-2">
                                <input type="file" id="input_dokumen_ttd" class="custom-file-input"
                                       accept="application/pdf">
                                <label class="custom-file-label" id="label_input_dokumen">Pilih file PDF...</label>
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <input type="text" id="input_keterangan" class="form-control form-control-sm"
                                   placeholder="Keterangan (opsional) — contoh: Revisi 1, Dokumen Final">
                        </div>
                        <button type="button" class="btn btn-success btn-sm" onclick="uploadDokumenTTD()">
                            <i class="fas fa-cloud-upload-alt mr-1"></i> Upload
                        </button>
                        <small class="text-muted ml-2">Format: PDF saja. Maks. 1MB.</small>
                        <div id="upload-progress" class="mt-2" style="display:none;">
                            <div class="progress" style="height:6px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                     style="width:100%"></div>
                            </div>
                            <small class="text-muted">Mengupload...</small>
                        </div>
                        <div id="upload-alert" class="mt-2" style="display:none;"></div>
                    </div>

                    <!-- LIST DOKUMEN -->
                    <div id="list-dokumen">
                        <?php if (empty($dokumen_list)): ?>
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                            Belum ada dokumen yang diupload.
                        </div>
                        <?php else: ?>
                            <?php foreach ($dokumen_list as $doc): ?>
                            <div class="d-flex align-items-center justify-content-between border rounded px-3 py-2 mb-2 bg-white"
                                 id="doc-item-<?= $doc['id'] ?>">
                                <div class="d-flex align-items-center" style="min-width:0;">
                                    <i class="fas fa-file-pdf text-danger mr-2" style="font-size:1.4rem; flex-shrink:0;"></i>
                                    <div style="min-width:0;">
                                        <div class="font-weight-bold text-truncate" style="max-width:300px;"
                                             title="<?= esc($doc['file_name']) ?>">
                                            <?= esc($doc['file_name']) ?>
                                        </div>
                                        <small class="text-muted">
                                            <?= date('d M Y H:i', strtotime($doc['uploaded_at'])) ?> WIB
                                            · <?= number_format($doc['file_size'] / 1024, 0) ?> KB
                                            <?php if (!empty($doc['keterangan'])): ?>
                                                · <em><?= esc($doc['keterangan']) ?></em>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center ml-2" style="flex-shrink:0;">
                                    <?php if ($doc['is_latest']): ?>
                                    <span class="badge badge-success mr-2">Terbaru</span>
                                    <?php endif; ?>
                                    <a href="<?= base_url($doc['file_path']) ?>" target="_blank"
                                       class="btn btn-xs btn-outline-primary mr-1" title="Preview">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= base_url($doc['file_path']) ?>" download
                                       class="btn btn-xs btn-outline-secondary mr-1" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button type="button" class="btn btn-xs btn-outline-danger"
                                            title="Hapus"
                                            onclick="hapusDokumen(<?= $doc['id'] ?>, '<?= esc($doc['file_name']) ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                </div>
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
// =============================================
// MODAL
// =============================================
function showApproveModal() {
    var modal = new bootstrap.Modal(document.getElementById('modalApprove'));
    modal.show();
}

function showRejectModal() {
    var modal = new bootstrap.Modal(document.getElementById('modalReject'));
    modal.show();
}

// Auto hide alerts setelah 5 detik
document.addEventListener('DOMContentLoaded', function () {
    setTimeout(function () {
        document.querySelectorAll('.alert').forEach(function (alert) {
            try { new bootstrap.Alert(alert).close(); } catch(e) {}
        });
    }, 5000);
});

// =============================================
// FILE INPUT — update label saat file dipilih
// =============================================
document.getElementById('input_dokumen_ttd')?.addEventListener('change', function () {
    const label = document.getElementById('label_input_dokumen');
    label.textContent = this.files[0]?.name || 'Pilih file PDF...';
});

// =============================================
// UPLOAD DOKUMEN TTD
// =============================================
async function uploadDokumenTTD() {
    const fileInput  = document.getElementById('input_dokumen_ttd');
    const keterangan = document.getElementById('input_keterangan').value;
    const alertBox   = document.getElementById('upload-alert');
    const progress   = document.getElementById('upload-progress');

    alertBox.style.display = 'none';

    // Validasi: file harus dipilih dulu
    if (!fileInput.files || !fileInput.files[0]) {
        showUploadAlert('warning', 'Pilih file PDF terlebih dahulu.');
        return;
    }

    // Validasi: harus PDF
    if (fileInput.files[0].type !== 'application/pdf') {
        showUploadAlert('danger', 'File harus berformat PDF.');
        return;
    }

    // Validasi: maksimal 1MB
    const maxSize = 1 * 1024 * 1024; // 1MB
    if (fileInput.files[0].size > maxSize) {
        showUploadAlert('danger',
            'File terlalu besar! Maksimal 1MB. ' +
            'Ukuran file Anda: ' + (fileInput.files[0].size / 1024 / 1024).toFixed(2) + 'MB'
        );
        return;
    }

    const formData = new FormData();
    formData.append('dokumen_ttd', fileInput.files[0]);
    formData.append('keterangan', keterangan);
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    progress.style.display = 'block';

    try {
        const res = await fetch('<?= base_url('general-service/repair-request/upload-dokumen/' . $repair['id']) ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });

        // Tangkap raw response dulu untuk debug jika bukan JSON
        const rawText = await res.text();
        let data;
        try {
            data = JSON.parse(rawText);
        } catch (parseErr) {
            progress.style.display = 'none';
            console.error('Server response bukan JSON:', rawText);
            showUploadAlert('danger', 'Server error. Cek console untuk detail.');
            return;
        }

        progress.style.display = 'none';

        if (data.success) {
            showUploadAlert('success', data.message);
            renderDokumenList(data.docs);
            fileInput.value = '';
            document.getElementById('label_input_dokumen').textContent = 'Pilih file PDF...';
            document.getElementById('input_keterangan').value = '';
        } else {
            showUploadAlert('danger', data.message || 'Upload gagal.');
        }

    } catch (err) {
        progress.style.display = 'none';
        showUploadAlert('danger', 'Error koneksi: ' + err.message);
    }
}

// =============================================
// HAPUS DOKUMEN
// =============================================
async function hapusDokumen(docId, fileName) {
    if (!confirm(`Hapus dokumen "${fileName}"?`)) return;

    try {
        const params = new URLSearchParams();
        params.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        const res  = await fetch(`<?= base_url('general-service/repair-request/delete-dokumen/') ?>${docId}`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: params
        });

        const rawText = await res.text();
        let data;
        try {
            data = JSON.parse(rawText);
        } catch (e) {
            console.error('Server response bukan JSON:', rawText);
            alert('Server error. Cek console untuk detail.');
            return;
        }

        if (data.success) {
            document.getElementById(`doc-item-${docId}`)?.remove();
            if (document.querySelectorAll('[id^="doc-item-"]').length === 0) {
                document.getElementById('list-dokumen').innerHTML = `
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                        Belum ada dokumen yang diupload.
                    </div>`;
            }
            updateBadgeCount();
        } else {
            alert(data.message || 'Gagal menghapus dokumen.');
        }
    } catch (err) {
        alert('Error koneksi: ' + err.message);
    }
}

// =============================================
// RENDER ULANG LIST DOKUMEN SETELAH UPLOAD
// =============================================
function renderDokumenList(docs) {
    const list = document.getElementById('list-dokumen');
    if (!list) return;

    if (!docs || docs.length === 0) {
        list.innerHTML = `
            <div class="text-center text-muted py-3">
                <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                Belum ada dokumen yang diupload.
            </div>`;
        updateBadgeCount();
        return;
    }

    const baseUrl = '<?= base_url() ?>';
    list.innerHTML = docs.map(doc => `
        <div class="d-flex align-items-center justify-content-between border rounded px-3 py-2 mb-2 bg-white"
             id="doc-item-${doc.id}">
            <div class="d-flex align-items-center" style="min-width:0;">
                <i class="fas fa-file-pdf text-danger mr-2" style="font-size:1.4rem; flex-shrink:0;"></i>
                <div style="min-width:0;">
                    <div class="font-weight-bold text-truncate" style="max-width:300px;" title="${escHtml(doc.file_name)}">
                        ${escHtml(doc.file_name)}
                    </div>
                    <small class="text-muted">
                        ${formatTanggal(doc.uploaded_at)} WIB
                        &middot; ${Math.round(doc.file_size / 1024)} KB
                        ${doc.keterangan ? '&middot; <em>' + escHtml(doc.keterangan) + '</em>' : ''}
                    </small>
                </div>
            </div>
            <div class="d-flex align-items-center ml-2" style="flex-shrink:0;">
                ${parseInt(doc.is_latest) === 1 ? '<span class="badge badge-success mr-2">Terbaru</span>' : ''}
                <a href="${baseUrl}${doc.file_path}" target="_blank"
                   class="btn btn-xs btn-outline-primary mr-1" title="Preview">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="${baseUrl}${doc.file_path}" download
                   class="btn btn-xs btn-outline-secondary mr-1" title="Download">
                    <i class="fas fa-download"></i>
                </a>
                <button type="button" class="btn btn-xs btn-outline-danger" title="Hapus"
                        onclick="hapusDokumen(${doc.id}, '${doc.file_name.replace(/\\/g, '\\\\').replace(/'/g, "\\'")}')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `).join('');

    updateBadgeCount();
}

// =============================================
// HELPERS
// =============================================
function updateBadgeCount() {
    const count = document.querySelectorAll('[id^="doc-item-"]').length;
    const badge = document.getElementById('badge-doc-count');
    if (badge) badge.textContent = count + ' dokumen';
}

function showUploadAlert(type, msg) {
    const el = document.getElementById('upload-alert');
    el.className = `alert alert-${type} py-2 mt-2`;
    el.textContent = msg;
    el.style.display = 'block';
}

function formatTanggal(dateStr) {
    const d   = new Date(dateStr);
    const bln = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    return String(d.getDate()).padStart(2, '0') + ' ' +
           bln[d.getMonth()] + ' ' +
           d.getFullYear() + ' ' +
           String(d.getHours()).padStart(2, '0') + ':' +
           String(d.getMinutes()).padStart(2, '0');
}

function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
</script>

<style>
.badge {
    font-size: 0.875rem;
    padding: 0.35em 0.65em;
}
</style>

<?= $this->endSection() ?>