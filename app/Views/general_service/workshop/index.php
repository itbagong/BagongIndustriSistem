<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="content-header mb-3">
    <h1>Input Data Workshop</h1>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-warning text-dark">
        <h5 class="card-title mb-0"><i class="fas fa-home mr-2"></i> Form Data Workshop</h5>
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

        <?php if(session()->getFlashdata('errors') && is_array(session()->getFlashdata('errors'))): ?>
        <?php endif; ?>
        <form action="<?= base_url('workshop/save') ?>" method="post">
            <?= csrf_field() ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Divisi <span class="text-danger">*</span></label>
                    <select name="divisi" id="divisi" class="form-control" required>
                        <option value="">-- Pilih Divisi --</option>
                        <?php foreach($divisi_list as $divisi): ?>
                            <option value="<?= esc($divisi['id']) ?>"><?= esc($divisi['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Job Site <span class="text-danger">*</span></label>
                    <select name="job_site" id="job_site" class="form-control" required>
                        <option value="">-- Pilih Divisi Terlebih Dahulu --</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Cari Nama Karyawan <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        id="search_employee"
                        class="form-control mb-2" 
                        placeholder="Ketik minimal 2 karakter untuk mencari..."
                        autocomplete="off">
                    <small class="text-muted mb-2 d-block">Ketik nama atau NIK karyawan</small>
                    
                    <label class="form-label">Pilih Karyawan <span class="text-danger">*</span></label>
                    <select name="employee_id" id="employee_select" class="form-control" required>
                        <option value="">-- Ketik untuk mencari karyawan --</option>
                    </select>
                    <input type="hidden" name="nama_karyawan" id="nama_karyawan">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">NIK Karyawan <span class="text-danger">*</span></label>
                    <input type="text" name="nik" id="nik" class="form-control" readonly required>
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
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="kompartemen[]" value="Gutter Oil" id="k_gutter" <?= in_array('Gutter Oil', old('kompartemen') ?? []) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="k_gutter">Gutter Oil</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="kompartemen[]" value="Oil Trap" id="k_oiltrap" <?= in_array('Oil Trap', old('kompartemen') ?? []) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="k_oiltrap">Oil Trap</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="kompartemen[]" value="Gudang Alat" id="k_gdg_alat" <?= in_array('Gudang Alat', old('kompartemen') ?? []) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="k_gdg_alat">Gudang Alat</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="kompartemen[]" value="Gudang Oli" id="k_gdg_oli" <?= in_array('Gudang Oli', old('kompartemen') ?? []) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="k_gdg_oli">Gudang Oli</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="kompartemen[]" value="Gudang Sparepart" id="k_gdg_spare" <?= in_array('Gudang Sparepart', old('kompartemen') ?? []) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="k_gdg_spare">Gudang Sparepart</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="kompartemen[]" value="Demarkasi" id="k_demarkasi" <?= in_array('Demarkasi', old('kompartemen') ?? []) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="k_demarkasi">Demarkasi</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="kompartemen[]" value="Panel Listrik" id="k_panel" <?= in_array('Panel Listrik', old('kompartemen') ?? []) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="k_panel">Panel Listrik</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="kompartemen[]" value="Gudang B3 Cair" id="k_b3_cair" <?= in_array('Gudang B3 Cair', old('kompartemen') ?? []) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="k_b3_cair">Gudang B3 Cair</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="kompartemen[]" value="Gudang B3 Padat" id="k_b3_padat" <?= in_array('Gudang B3 Padat', old('kompartemen') ?? []) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="k_b3_padat">Gudang B3 Padat</label>
                        </div>
                    </div>
                </div>
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
                <a href="<?= base_url('workshop') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Data
                </button>
            </div>

        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    
    const divisiSelect = document.getElementById('divisi');
    const jobSiteSelect = document.getElementById('job_site');
    const searchInput = document.getElementById('search_employee');
    const employeeSelect = document.getElementById('employee_select');
    const nikInput = document.getElementById('nik');
    const namaInput = document.getElementById('nama_karyawan');
    
    let searchTimeout = null;

    // ==================== DIVISI & JOB SITE ====================
    divisiSelect.addEventListener('change', function() {
        const divisiId = this.value;
        jobSiteSelect.innerHTML = '<option value="">Loading...</option>';

        if (divisiId) {
            fetch('<?= base_url('general-service/get-site-by-divisi-code') ?>', {
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
                    html += `<option value="${item.name}">${item.name}</option>`;
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

    // ==================== EMPLOYEE SEARCH ====================
    searchInput.addEventListener('input', function() {
        const keyword = this.value.trim();
        
        // Clear timeout sebelumnya
        clearTimeout(searchTimeout);
        
        // Jika kurang dari 2 karakter, reset dropdown
        if (keyword.length < 2) {
            employeeSelect.innerHTML = '<option value="">-- Ketik untuk mencari karyawan --</option>';
            nikInput.value = '';
            namaInput.value = '';
            return;
        }

        // Show loading
        employeeSelect.innerHTML = '<option value="">Mencari...</option>';

        // Debounce: tunggu 300ms setelah user berhenti mengetik
        searchTimeout = setTimeout(() => {
            searchEmployees(keyword);
        }, 300);
    });

    // Fungsi untuk search employees
    function searchEmployees(keyword) {
        console.log('Searching for:', keyword);
        
        fetch('<?= base_url('general-service/search-employees') ?>?search=' + encodeURIComponent(keyword), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Results:', data);
            
            if (data.error) {
                console.error('Error:', data.message);
                employeeSelect.innerHTML = '<option value="">Error: ' + data.message + '</option>';
                return;
            }
            
            // Update dropdown dengan hasil
            if (data.length === 0) {
                employeeSelect.innerHTML = '<option value="">Tidak ada hasil ditemukan</option>';
            } else {
                let html = '<option value="">-- Pilih Karyawan --</option>';
                data.forEach(emp => {
                    html += `<option value="${emp.employee_number}" data-name="${emp.employee_name}">${emp.employee_name} (${emp.employee_number})</option>`;
                });
                employeeSelect.innerHTML = html;
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            employeeSelect.innerHTML = '<option value="">Error mengambil data</option>';
        });
    }

    // Event ketika user memilih dari dropdown
    employeeSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            nikInput.value = this.value;
            namaInput.value = selectedOption.getAttribute('data-name') || '';
            console.log('Selected:', {
                nik: this.value,
                name: selectedOption.getAttribute('data-name')
            });
        } else {
            nikInput.value = '';
            namaInput.value = '';
        }
    });

    // ==================== PARKIR TOGGLE ====================
    function toggleParkir(isAda) {
        const inputLuas = document.getElementById('luasParkir');
        if (isAda) {
            inputLuas.readOnly = false;
            inputLuas.focus();
        } else {
            inputLuas.value = 0;
            inputLuas.readOnly = true;
        }
    }

    // Event listener untuk radio parkir
    document.getElementById('parkirAda')?.addEventListener('change', function() {
        toggleParkir(true);
    });
    
    document.getElementById('parkirTidak')?.addEventListener('change', function() {
        toggleParkir(false);
    });
});
</script>

<?= $this->endSection() ?>