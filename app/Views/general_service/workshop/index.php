<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<style>
    /* Styling agar Tom Select konsisten dengan Bootstrap */
    .ts-control {
        min-height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 6px 12px;
    }
    .ts-dropdown {
        z-index: 9999;
    }
</style>

<div class="content-header mb-3">
    <h1>Input Data Workshop</h1>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-warning text-dark">
        <h5 class="card-title mb-0"><i class="fas fa-tools mr-2"></i> Form Data Workshop</h5>
    </div>
    <div class="card-body">
        
        <?php if(session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                <?php foreach(session()->getFlashdata('errors') as $err): ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('general-service/workshop/save') ?>" method="post" id="workshopForm">
            <?= csrf_field() ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Divisi <span class="text-danger">*</span></label>
                    <select name="divisi" id="divisi" class="form-control" required>
                        <option value="">-- Pilih Divisi --</option>
                        <?php foreach($divisi_list as $divisi): ?>
                            <option value="<?= esc($divisi['id']) ?>" <?= old('divisi') == $divisi['id'] ? 'selected' : '' ?>>
                                <?= esc($divisi['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Job Site <span class="text-danger">*</span></label>
                    <select name="job_site" id="job_site" class="form-control" required>
                        <option value="">-- Pilih Divisi Terlebih Dahulu --</option>
                    </select>
                    <input type="hidden" name="site_id" id="site_id">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Pilih Karyawan (PIC) <span class="text-danger">*</span></label>
                    
                    <select name="employee_id" id="employee_select" placeholder="Ketik nama atau NIK karyawan..." required>
                        <option value="">Ketik nama atau NIK karyawan...</option>
                    </select>
                    
                    <small class="text-muted d-block mt-1">
                        <i class="fas fa-info-circle"></i> Ketik minimal 2 karakter untuk mencari.
                    </small>
                    
                    <input type="hidden" name="nama_karyawan" id="nama_karyawan">
                    <input type="hidden" name="nik" id="nik_hidden">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">NIK Karyawan <span class="text-danger">*</span></label>
                    <input type="text" name="nik_display" id="nik_display" class="form-control" readonly>
                    <small class="text-muted">NIK akan terisi otomatis setelah memilih karyawan</small>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Luasan Workshop (m²) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="luasan_workshop" class="form-control" placeholder="0" value="<?= old('luasan_workshop') ?>" required>
                        <span class="input-group-text">m²</span>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jumlah Bays <span class="text-danger">*</span></label>
                    <input type="number" name="jumlah_bays" class="form-control" min="1" value="<?= old('jumlah_bays') ?>" required>
                    <small class="text-muted">Bays = area kerja untuk kendaraan</small>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label font-weight-bold">Kompartemen Wajib (Centang yang tersedia) <span class="text-danger">*</span></label>
                <div class="row">
                    <?php 
                    // Array daftar kompartemen agar kode lebih rapi
                    $kompartemen_items = [
                        'Gutter Oil', 'Oil Trap', 'Gudang Alat', 'Gudang Oli', 
                        'Gudang Sparepart', 'Demarkasi', 'Panel Listrik', 
                        'Gudang B3 Cair', 'Gudang B3 Padat'
                    ];
                    
                    foreach($kompartemen_items as $item): 
                        // Generate ID unik
                        $kid = 'k_' . strtolower(str_replace(' ', '_', $item));
                    ?>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input kompartemen-checkbox" type="checkbox" name="kompartemen[]" value="<?= $item ?>" id="<?= $kid ?>" <?= in_array($item, old('kompartemen') ?? []) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="<?= $kid ?>"><?= $item ?></label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <small class="text-danger d-none" id="kompartemen-error">Pilih minimal satu kompartemen</small>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status Kepemilikan Lahan Workshop <span class="text-danger">*</span></label>
                    <select name="status_kepemilikan" class="form-control" required>
                        <option value="">-- Pilih Status --</option>
                        <option value="Milik PT Bagong Dekaka Makmur" <?= old('status_kepemilikan') === 'Milik PT Bagong Dekaka Makmur' ? 'selected' : '' ?>>
                            Milik PT Bagong Dekaka Makmur
                        </option>
                        <option value="Sewa" <?= old('status_kepemilikan') === 'Sewa' ? 'selected' : '' ?>>
                            Sewa
                        </option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status Pembangunan Workshop <span class="text-danger">*</span></label>
                    <select name="status_pembangunan" class="form-control" required>
                        <option value="">-- Pilih Status --</option>
                        <option value="Eksisting (Sudah ada sejak awal/dibangun pemilik)" <?= old('status_pembangunan') === 'Eksisting (Sudah ada sejak awal/dibangun pemilik)' ? 'selected' : '' ?>>
                            Eksisting (Sudah ada sejak awal/dibangun pemilik)
                        </option>
                        <option value="Dibangun Sendiri (PT Bagong Dekaka Makmur)" <?= old('status_pembangunan') === 'Dibangun Sendiri (PT Bagong Dekaka Makmur)' ? 'selected' : '' ?>>
                            Dibangun Sendiri (PT Bagong Dekaka Makmur)
                        </option>
                    </select>
                </div>
            </div>

            <div class="form-group mt-4 text-right">
                <a href="<?= base_url('general-service?tab=workshop') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i> Simpan Data
                </button>
            </div>

        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // --- 1. INISIALISASI TOM SELECT (SEARCH KARYAWAN) ---
    new TomSelect("#employee_select", {
        valueField: 'id',
        labelField: 'text',
        searchField: 'text',
        placeholder: "Ketik nama atau NIK...",
        maxOptions: 50,
        
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
                .catch(() => { callback(); });
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

    // --- 2. LOGIKA DIVISI & JOB SITE ---
    const divisiSelect = document.getElementById('divisi');
    const jobSiteSelect = document.getElementById('job_site');
    const siteIdInput = document.getElementById('site_id');

    divisiSelect.addEventListener('change', function() {
        const divisiId = this.value;
        jobSiteSelect.innerHTML = '<option value="">Loading...</option>';
        if(siteIdInput) siteIdInput.value = '';

        if (divisiId) {
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
                    html += `<option value="${item.id}" data-site-id="${item.id || item.name}">${item.name}</option>`;
                });
                jobSiteSelect.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                jobSiteSelect.innerHTML = '<option value="">Error mengambil data</option>';
            });
        } else {
            jobSiteSelect.innerHTML = '<option value="">-- Pilih Divisi Terlebih Dahulu --</option>';
        }
    });

    jobSiteSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        // Jika ada element site_id (hidden), isi nilainya
        if(siteIdInput) {
            siteIdInput.value = selectedOption.getAttribute('data-site-id') || this.value;
        }
    });

    // --- 3. FORM VALIDATION ---
    const form = document.getElementById('workshopForm');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function(e) {
        // Validasi Checkbox Kompartemen (Optional: jika wajib pilih minimal 1)
        const checkboxes = document.querySelectorAll('.kompartemen-checkbox:checked');
        const errorMsg = document.getElementById('kompartemen-error');
        
        /* Uncomment jika ingin mewajibkan minimal 1 checkbox
        if (checkboxes.length === 0) {
            e.preventDefault();
            errorMsg.classList.remove('d-none');
            // Scroll ke error message
            errorMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        } else {
            errorMsg.classList.add('d-none');
        }
        */

        // Validasi Karyawan
        const employeeSelect = document.getElementById('employee_select');
        if (!employeeSelect.value) {
            e.preventDefault();
            alert('Silakan pilih karyawan (PIC) terlebih dahulu!');
            return false;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    });
});
</script>

<?= $this->endSection() ?>