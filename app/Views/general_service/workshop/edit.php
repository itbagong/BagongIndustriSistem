<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<style>
    .ts-control {
        min-height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 6px 12px;
    }
    .ts-dropdown { z-index: 9999; }
    
    .perbaikan-card {
        animation: slideIn 0.3s ease-out;
    }
    
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .file-preview-item {
        display: inline-block;
        margin: 5px;
        padding: 10px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        width: 100px;
        text-align: center;
    }
    
    .is-invalid { border-color: #dc3545 !important; }
</style>

<div class="content-header mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-edit mr-2"></i> Edit Data Workshop</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-0 mt-2">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('general-service?tab=workshop') ?>">Data Workshop</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
        <a href="<?= base_url('general-service?tab=workshop') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<!-- ALERTS -->
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
        <i class="fas fa-exclamation-circle"></i> <?= esc(session()->getFlashdata('error')) ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
<?php endif; ?>

<?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i> <?= esc(session()->getFlashdata('success')) ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
<?php endif; ?>

<!-- FORM EDIT WORKSHOP -->
<div class="card shadow-sm">
    <div class="card-header bg-warning text-dark">
        <h5 class="card-title mb-0"><i class="fas fa-tools mr-2"></i> Form Edit Data Workshop</h5>
    </div>
    <div class="card-body">
        <form action="<?= base_url('general-service/workshop/update/' . $workshop['id']) ?>" method="post" id="workshopForm">
            <?= csrf_field() ?>

            <!-- Info Box -->
            <div class="alert alert-info border-info">
                <h6 class="alert-heading"><i class="fas fa-info-circle"></i> Informasi Data Saat Ini</h6>
                <div class="row">
                    <div class="col-md-6">
                        <small><strong>Karyawan:</strong> <?= esc($workshop['name_karyawan']) ?></small><br>
                        <small><strong>NIK:</strong> <?= esc($workshop['nik']) ?></small>
                    </div>
                    <div class="col-md-6">
                        <small><strong>Divisi:</strong> <?= esc($workshop['divisi_name'] ?? '-') ?></small><br>
                        <small><strong>Job Site:</strong> <?= esc($workshop['site_id']) ?></small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kode Workshop <span class="text-danger">*</span></label>
                    <input type="text" name="workshop_code" class="form-control" 
                           value="<?= esc($workshop['workshop_code']) ?>" readonly> 
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Divisi <span class="text-danger">*</span></label>
                    <select name="divisi_id" id="divisi" class="form-control" required>
                        <option value="">-- Pilih Divisi --</option>
                        <?php foreach($divisi_list as $divisi): ?>
                            <option value="<?= esc($divisi['id']) ?>" 
                                <?= (old('divisi_id', $workshop['divisi_id']) == $divisi['id']) ? 'selected' : '' ?>>
                                <?= esc($divisi['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Job Site <span class="text-danger">*</span></label>
                    <select name="job_site" id="job_site" class="form-control" required>
                        <option value="<?= esc($workshop['site_id']) ?>" selected>
                            <?= esc($workshop['site_id']) ?>
                        </option>
                    </select>
                    <input type="hidden" name="site_id" id="site_id" value="<?= esc($workshop['site_id']) ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Pilih Karyawan <span class="text-danger">*</span></label>
                    <select name="employee_id" id="employee_select" required>
                        <option value="<?= esc($workshop['nik']) ?>" selected>
                            <?= esc($workshop['name_karyawan']) ?> (<?= esc($workshop['nik']) ?>)
                        </option>
                    </select>
                    <small class="text-muted d-block mt-1">
                        <i class="fas fa-info-circle"></i> Ketik minimal 2 karakter untuk mencari karyawan lain.
                    </small>
                    <input type="hidden" name="name_karyawan" id="nama_karyawan" value="<?= esc($workshop['name_karyawan']) ?>">
                    <input type="hidden" name="nik" id="nik_hidden" value="<?= esc($workshop['nik']) ?>">
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Luasan Workshop (m²) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="luasan" class="form-control" 
                               value="<?= old('luasan', $workshop['luasan']) ?>" required>
                        <span class="input-group-text">m²</span>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jumlah Bays <span class="text-danger">*</span></label>
                    <input type="number" name="bays" class="form-control" min="1" 
                           value="<?= old('bays', $workshop['bays']) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status workshop <span class="text-danger">*</span></label>
                        
                            <select name="status_workshop" class="form-control" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="<?= old('status_workshop', $workshop['status_workshop']) ?>" 
                                    <?= old('status_workshop', $workshop['status_workshop']) === 'Eksisting (Sudah ada sejak awal/dibangun pemilik)' ? 'selected' : '' ?>>
                                    Eksisting (Sudah ada sejak awal/dibangun pemilik)
                                </option>
                                <option value="Dibangun Sendiri (PT Bagong Dekaka Makmur)" <?= old('status_workshop', $workshop['status_workshop']) === 'Dibangun Sendiri (PT Bagong Dekaka Makmur)' ? 'selected' : '' ?>>
                                    Dibangun Sendiri (PT Bagong Dekaka Makmur)
                                </option>
                            </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label font-weight-bold">
                    Kompartemen (Centang yang tersedia) <span class="text-danger">*</span>
                </label>

                <?php
                $selected_kompartemen = [];
                if (!empty($workshop['kompartemen'])) {
                    $decoded = json_decode($workshop['kompartemen'], true);
                    if (is_array($decoded)) {
                        $selected_kompartemen = $decoded;
                    }
                }
                $selected_kompartemen = old('kompartemen', $selected_kompartemen);

                $kompartemen_items = [
                        'Gutter Oil', 'Oil Trap', 'Gudang Alat', 'Gudang Oli', 
                        'Gudang Sparepart', 'Demarkasi', 'Panel Listrik', 
                        'Gudang B3 Cair', 'Gudang B3 Padat'
                    ];
                ?>

                <div class="row">
                    <?php foreach ($kompartemen_items as $k): 
                        $kid = 'k_' . strtolower(str_replace(' ', '_', $k));
                    ?>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input kompartemen-checkbox"
                                type="checkbox"
                                name="kompartemen[]"
                                value="<?= esc($k) ?>"
                                id="<?= esc($kid) ?>"
                                <?= in_array($k, $selected_kompartemen, true) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="<?= esc($kid) ?>">
                                <?= esc($k) ?>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <small class="text-danger d-none" id="kompartemen-error">
                    Pilih minimal satu kompartemen
                </small>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status Kepemilikan Lahan <span class="text-danger">*</span></label>
                    <select name="status_lahan" class="form-control" required>
                        <option value="">-- Pilih Status --</option>
                        <option value="Milik PT Bagong Dekaka Makmur" 
                            <?= old('status_lahan', $workshop['status_lahan']) === 'Milik PT Bagong Dekaka Makmur' ? 'selected' : '' ?>>
                            Milik PT Bagong Dekaka Makmur
                        </option>
                        <option value="Sewa" 
                            <?= old('status_lahan', $workshop['status_lahan']) === 'Sewa' ? 'selected' : '' ?>>
                            Sewa
                        </option>
                    </select>
                </div>
            </div>

            <?php if (!empty($workshop['updated_at'])): ?>
            <div class="alert alert-light border">
                <small class="text-muted">
                    <i class="fas fa-clock"></i> Terakhir diubah: 
                    <strong><?= date('d/m/Y H:i', strtotime($workshop['updated_at'])) ?></strong>
                </small>
            </div>
            <?php endif; ?>

            <div class="form-group mt-4">
                <div class="d-flex justify-content-between">
                    <a href="<?= base_url('general-service?tab=workshop') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-warning" id="submitBtn">
                        <i class="fas fa-save"></i> Update Data Workshop
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- SECTION PENGAJUAN PERBAIKAN -->
<div class="card shadow-sm mt-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="fas fa-tools mr-2"></i> Pengajuan Perbaikan Workshop
        </h5>
    </div>
    <div class="card-body">
        
        <div id="perbaikanAlert" style="display: none;"></div>
        
        <div class="mb-3">
            <button type="button" class="btn btn-success" id="btnAddPerbaikan">
                <i class="fas fa-plus-circle"></i> Tambah Pengajuan Perbaikan
            </button>
        </div>

        <div id="perbaikanListContainer">
            <?php if (empty($existing_perbaikan)): ?>
            <div class="alert alert-info text-center" id="emptyPerbaikanMsg">
                <i class="fas fa-info-circle"></i> Belum ada pengajuan perbaikan baru. Klik tombol di atas untuk menambahkan.
            </div>
            <?php endif; ?>
        </div>

        <!-- Riwayat Perbaikan -->
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
                            <th width="10%">Prioritas</th>
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
                            <td><strong class="text-primary"><?= esc($item['kode_pengajuan']) ?></strong></td>
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
                                $prioritas = $item['prioritas'] ?? 'Sedang';
                                $prioritasColor = [
                                    'Rendah' => 'secondary',
                                    'Sedang' => 'info',
                                    'Tinggi' => 'warning',
                                    'Urgent' => 'danger'
                                ];
                                $pColor = $prioritasColor[$prioritas] ?? 'secondary';
                                ?>
                                <span class="badge badge-<?= $pColor ?>"><?= esc($prioritas) ?></span>
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

<!-- Modal Detail -->
<div class="modal fade" id="modalDetailPerbaikan" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-info-circle"></i> Detail Pengajuan Perbaikan</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="modalDetailContent">
                <div class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                    <p class="mt-3">Memuat data...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // TOM SELECT
    var employeeSelect = new TomSelect("#employee_select", {
        valueField: 'id',
        labelField: 'text',
        searchField: 'text',
        placeholder: "Ketik nama atau NIK...",
        maxOptions: 50,
        
        options: [{
            id: '<?= esc($workshop['nik']) ?>',
            text: '<?= esc($workshop['name_karyawan']) ?> (<?= esc($workshop['nik']) ?>)',
            nama_asli: '<?= esc($workshop['name_karyawan']) ?>',
            nik_asli: '<?= esc($workshop['nik']) ?>'
        }],
        
        items: ['<?= esc($workshop['nik']) ?>'],
        
        load: function(query, callback) {
            if (query.length < 2) return callback();
            fetch('<?= base_url('general-service/ajax/search-employees') ?>?search=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(json => {
                    callback(json.map(item => ({
                        id: item.employee_number,
                        text: item.employee_name + ' (' + item.employee_number + ')',
                        nama_asli: item.employee_name,
                        nik_asli: item.employee_number
                    })));
                })
                .catch(() => callback());
        },
        
        onChange: function(value) {
            var selectedData = this.options[value];
            if(selectedData) {
                document.getElementById('nama_karyawan').value = selectedData.nama_asli;
                document.getElementById('nik_hidden').value = selectedData.nik_asli;
            }
        }
    });

    // FORM VALIDATION
    const form = document.getElementById('workshopForm');
    form.addEventListener('submit', function(e) {
        const checkboxes = document.querySelectorAll('.kompartemen-checkbox:checked');
        const errorMsg = document.getElementById('kompartemen-error');
        
        if (checkboxes.length === 0) {
            e.preventDefault();
            errorMsg.classList.remove('d-none');
            alert('Pilih minimal satu kompartemen!');
            return false;
        } else {
            errorMsg.classList.add('d-none');
        }
        
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengupdate...';
    });
});
    // ============================================
    // 2. LOGIKA DIVISI & JOB SITE
    // ============================================
    const divisiSelect = document.getElementById('divisi');
    const jobSiteSelect = document.getElementById('job_site');
    const siteIdInput = document.getElementById('site_id');

    if (divisiSelect.value) {
        loadJobSites(divisiSelect.value, '<?= esc($workshop['site_id']) ?>');
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
</script>

<!-- JAVASCRIPT PERBAIKAN -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let perbaikanCounter = 0;
const perbaikanList = [];

function createPerbaikanCard(index) {
    return `
    <div class="card border-success mb-3 perbaikan-card" id="perbaikan-${index}">
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
                <div class="col-md-8 mb-3">
                    <label class="form-label font-weight-bold">Deskripsi Perbaikan <span class="text-danger">*</span></label>
                    <textarea id="deskripsi_${index}" class="form-control" rows="3" placeholder="Jelaskan kerusakan..." required></textarea>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label font-weight-bold">Kategori <span class="text-danger">*</span></label>
                    <select id="kategori_${index}" class="form-control perbaikan-field" required>
                        <option value="">-- Pilih --</option>
                        <option value="Ringan">🟢 Ringan</option>
                        <option value="Sedang">🟡 Sedang</option>
                        <option value="Berat">🟠 Berat</option>
                        <option value="Darurat">🔴 Darurat</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">Upload Foto <span class="text-danger">*</span></label>
                    <input type="file" id="files_${index}" class="form-control-file" accept="image/*,.pdf" multiple required onchange="previewFiles(this, ${index})">
                    <small class="text-muted">Max 5 file @ 5MB</small>
                    <div id="preview-${index}" class="mt-2"></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">Estimasi Biaya (Opsional)</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                        <input type="number" id="estimasi_biaya_${index}" class="form-control" placeholder="0" min="0" step="1000">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">Prioritas <span class="text-danger">*</span></label>
                    <select id="prioritas_${index}" class="form-control perbaikan-field" required>
                        <option value="">-- Pilih --</option>
                        <option value="Segera">Segera (< 3 hari)</option>
                        <option value="Normal">Normal (< 1 minggu)</option>
                        <option value="Rendah">Rendah (> 1 minggu)</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">Catatan</label>
                    <textarea id="catatan_${index}" class="form-control" rows="2" placeholder="Opsional"></textarea>
                </div>
            </div>
            <div class="text-right mt-3">
                <button type="button" class="btn btn-primary" onclick="submitPerbaikan(${index})">
                    <i class="fas fa-paper-plane"></i> Kirim Pengajuan Ini
                </button>
            </div>
        </div>
    </div>
    `;
}

function addPerbaikan() {
    const container = document.getElementById('perbaikanListContainer');
    const emptyMsg = document.getElementById('emptyPerbaikanMsg');
    if (emptyMsg) emptyMsg.style.display = 'none';
    
    container.insertAdjacentHTML('beforeend', createPerbaikanCard(perbaikanCounter));
    perbaikanList.push(perbaikanCounter);
    perbaikanCounter++;
    updatePerbaikanCount();
}

function removePerbaikan(index) {
    if (confirm('Yakin hapus?')) {
        document.getElementById(`perbaikan-${index}`).remove();
        const idx = perbaikanList.indexOf(index);
        if (idx > -1) perbaikanList.splice(idx, 1);
        
        if (perbaikanList.length === 0) {
            const emptyMsg = document.getElementById('emptyPerbaikanMsg');
            if (emptyMsg) emptyMsg.style.display = 'block';
        }
        updatePerbaikanCount();
    }
}

function previewFiles(input, index) {
    const preview = document.getElementById(`preview-${index}`);
    preview.innerHTML = '';
    if (input.files.length > 0) {
        let html = '<div class="d-flex flex-wrap">';
        Array.from(input.files).forEach(file => {
            const size = (file.size / 1024 / 1024).toFixed(2);
            const icon = file.type.startsWith('image/') ? 'fa-image' : 'fa-file-alt';
            html += `
                <div class="file-preview-item">
                    <i class="fas ${icon} fa-2x text-primary"></i>
                    <div class="small mt-1">${file.name.substring(0,10)}...</div>
                    <div class="text-muted" style="font-size:10px;">${size} MB</div>
                </div>
            `;
        });
        preview.innerHTML = html + '</div>';
    }
}

function updatePerbaikanCount() {
    const btn = document.getElementById('btnAddPerbaikan');
    btn.innerHTML = perbaikanList.length > 0 
        ? `<i class="fas fa-plus-circle"></i> Tambah Pengajuan Perbaikan (${perbaikanList.length} item)`
        : '<i class="fas fa-plus-circle"></i> Tambah Pengajuan Perbaikan';
}

// Submit perbaikan via AJAX
function submitPerbaikan(index) {
    const deskripsi = document.getElementById(`deskripsi_${index}`).value;
    const kategori = document.getElementById(`kategori_${index}`).value;
    const prioritas = document.getElementById(`prioritas_${index}`).value;
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
    formData.append('tipe_aset', 'Workshop');
    formData.append('aset_id', '<?= $workshop['id'] ?>');
    formData.append('aset_code', '<?= esc($workshop['workshop_code']) ?>');
    formData.append('kategori_kerusakan', kategori);
    formData.append('jenis_kerusakan', 'Perbaikan Bangunan Workshop');
    formData.append('deskripsi_kerusakan', deskripsi);
    formData.append('prioritas', prioritas);
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
        if (data.success) {
            showAlert('success', data.message);
            removePerbaikan(index);
            location.reload();
        } else {
            console.error(data.errors);
            showAlert('error', data.message || 'Validasi gagal');
        }
    })
    .catch(() => {
        showAlert('error', 'Terjadi kesalahan server');
    });
}

function showAlert(type, message) {
    const alertDiv = document.getElementById('perbaikanAlert');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle"></i> ${message}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    `;
    alertDiv.style.display = 'block';
    setTimeout(() => alertDiv.style.display = 'none', 5000);
    alertDiv.scrollIntoView({behavior: 'smooth', block: 'center'});
}

function viewDetailPerbaikan(id) {
    $('#modalDetailPerbaikan').modal('show');
    
    fetch('<?= base_url('general-service/repair-request/detail/') ?>' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = data.data;
                document.getElementById('modalDetailContent').innerHTML = `
                    <table class="table table-sm">
                        <tr><th width="30%">Kode</th><td>${item.kode_pengajuan || '-'}</td></tr>
                        <tr><th>Deskripsi</th><td>${item.deskripsi_kerusakan || '-'}</td></tr>
                        <tr><th>Kategori</th><td><span class="badge badge-info">${item.kategori_kerusakan || '-'}</span></td></tr>
                        <tr><th>Prioritas</th><td><span class="badge badge-warning">${item.prioritas || '-'}</span></td></tr>
                        <tr><th>Status</th><td><span class="badge badge-primary">${item.status || '-'}</span></td></tr>
                        <tr><th>Estimasi Biaya</th><td>Rp ${parseInt(item.estimasi_biaya || 0).toLocaleString('id-ID')}</td></tr>
                        <tr><th>Tanggal</th><td>${new Date(item.tanggal_pengajuan).toLocaleString('id-ID')}</td></tr>
                        <tr><th>Catatan</th><td>${item.catatan || '-'}</td></tr>
                    </table>
                `;
            } else {
                document.getElementById('modalDetailContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> ${data.message || 'Data tidak ditemukan'}
                    </div>
                `;
            }
        })
        .catch(() => {
            document.getElementById('modalDetailContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Error memuat data
                </div>
            `;
        });
}

document.getElementById('btnAddPerbaikan').addEventListener('click', addPerbaikan);
</script>

<?= $this->endSection() ?>