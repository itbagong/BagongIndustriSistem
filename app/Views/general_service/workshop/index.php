<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="content-header mb-3">
    <h1>Input Data Workshop</h1>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-warning text-dark">
        <h5 class="card-title mb-0"><i class="fas fa-wrench mr-2"></i> Form Data Workshop</h5>
    </div>
    <div class="card-body">
        
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
                    <label class="form-label">Nama Karyawan (Penanggung Jawab) <span class="text-danger">*</span></label>
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
                    <label class="form-label">Luasan Workshop <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="luas_workshop" class="form-control" placeholder="0" required>
                        <div class="input-group-append">
                            <span class="input-group-text">m²</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jumlah Bays <span class="text-danger">*</span></label>
                    <input type="number" name="jumlah_bays" class="form-control" placeholder="0" min="0" required>
                    <small class="text-muted">Total area kerja (bay) kendaraan.</small>
                </div>
            </div>

            <div class="card bg-light border-0 mb-4">
                <div class="card-body">
                    <label class="form-label font-weight-bold mb-3">Kompartemen Wajib (Centang yang tersedia) <span class="text-danger">*</span></label>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="kompartemen[]" value="Gutter Oil" id="k_gutter">
                                <label class="form-check-label" for="k_gutter">Gutter Oil</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="kompartemen[]" value="Oil Trap" id="k_oiltrap">
                                <label class="form-check-label" for="k_oiltrap">Oil Trap</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="kompartemen[]" value="Gudang Alat" id="k_alat">
                                <label class="form-check-label" for="k_alat">Gudang Alat</label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="kompartemen[]" value="Gudang Oli" id="k_oli">
                                <label class="form-check-label" for="k_oli">Gudang Oli</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="kompartemen[]" value="Gudang Sparepart" id="k_sparepart">
                                <label class="form-check-label" for="k_sparepart">Gudang Sparepart</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="kompartemen[]" value="Demarkasi" id="k_demarkasi">
                                <label class="form-check-label" for="k_demarkasi">Demarkasi</label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="kompartemen[]" value="Panel Listrik" id="k_panel">
                                <label class="form-check-label" for="k_panel">Panel Listrik</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="kompartemen[]" value="Gudang B3 Cair" id="k_b3cair">
                                <label class="form-check-label" for="k_b3cair">Gudang B3 Cair</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="kompartemen[]" value="Gudang B3 Padat" id="k_b3padat">
                                <label class="form-check-label" for="k_b3padat">Gudang B3 Padat</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label d-block font-weight-bold">Status Kepemilikan Lahan <span class="text-danger">*</span></label>
                    <div class="mt-2">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="status_kepemilikan" id="milikSendiri" value="Milik PT Bagong Dekaka Makmur" required>
                            <label class="form-check-label" for="milikSendiri">Milik PT Bagong Dekaka Makmur</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status_kepemilikan" id="sewa" value="Sewa" required>
                            <label class="form-check-label" for="sewa">Sewa</label>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label d-block font-weight-bold">Status Pembangunan Workshop <span class="text-danger">*</span></label>
                    <div class="mt-2">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="status_pembangunan" id="eksisting" value="Eksisting" required>
                            <label class="form-check-label" for="eksisting">Eksisting (Sudah ada sejak awal)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status_pembangunan" id="bangunSendiri" value="Dibangun Sendiri" required>
                            <label class="form-check-label" for="bangunSendiri">Dibangun Sendiri (PT Bagong)</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group mt-4 text-right">
                <a href="<?= base_url('workshop') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-warning text-dark font-weight-bold">
                    <i class="fas fa-save"></i> Simpan Data Workshop
                </button>
            </div>

        </form>
    </div>
</div>
<!-- jQuery (WAJIB SEBELUM SELECT2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    
    const divisiSelect = document.getElementById('divisi');
    const jobSiteSelect = document.getElementById('job_site');
    const nikInput = document.getElementById('nik');
    const namaInput = document.getElementById('nama_karyawan');

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

    // ==================== EMPLOYEE SELECT2 AUTOCOMPLETE ====================
    // ✅ PASTIKAN JQUERY SUDAH READY
    $(document).ready(function() {
        $('#employee_select').select2({
            placeholder: 'Ketik nama atau NIK karyawan (min 2 karakter)',
            allowClear: true,
            width: '100%', // ✅ TAMBAHKAN WIDTH
            minimumInputLength: 2,
            language: {
                inputTooShort: function() {
                    return 'Ketik minimal 2 karakter untuk mencari...';
                },
                searching: function() {
                    return 'Mencari...';
                },
                noResults: function() {
                    return 'Tidak ada hasil ditemukan';
                }
            },
            ajax: {
                url: '<?= base_url('general-service/search-employees') ?>',
                type: 'POST',
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    console.log('Searching for:', params.term); // ✅ DEBUG
                    return {
                        search: params.term
                    };
                },
                processResults: function(data) {
                    console.log('Results:', data); // ✅ DEBUG
                    
                    if (!data || data.length === 0) {
                        return { results: [] };
                    }
                    
                    return {
                        results: data.map(function(employee) {
                            return {
                                id: employee.employee_number,
                                text: employee.employee_name + ' (' + employee.employee_number + ')',
                                name: employee.employee_name,
                                nik: employee.employee_number
                            };
                        })
                    };
                },
                cache: true
            }
        });

        // Event ketika user memilih karyawan
        $('#employee_select').on('select2:select', function(e) {
            const data = e.params.data;
            
            nikInput.value = data.nik;
            namaInput.value = data.name;
            
            console.log('Selected:', data);
        });

        // Event ketika user clear selection
        $('#employee_select').on('select2:clear', function() {
            nikInput.value = '';
            namaInput.value = '';
        });
    });
});
</script>

<?= $this->endSection() ?>