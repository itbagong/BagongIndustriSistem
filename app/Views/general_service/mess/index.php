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
        z-index: 9999; /* Agar dropdown tidak tertutup elemen lain */
    }
</style>

<div class="content-header mb-3">
    <h1>Input Data Mess Karyawan</h1>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-warning text-dark">
        <h5 class="card-title mb-0"><i class="fas fa-home mr-2"></i> Form Data Mess</h5>
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

        <form action="<?= base_url('general-service/mess/save') ?>" method="post" id="messForm">
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
                    <label class="form-label">Pilih Karyawan <span class="text-danger">*</span></label>
                    
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
                <div class="col-md-4 mb-3">
                    <label class="form-label">Luasan Mess (m²) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="luasan_mess" class="form-control" placeholder="0" value="<?= old('luasan_mess') ?>" required>
                        <span class="input-group-text">m²</span>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jumlah Kamar Tidur <span class="text-danger">*</span></label>
                    <input type="number" name="jumlah_kamar_tidur" class="form-control" min="1" value="<?= old('jumlah_kamar_tidur') ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jumlah Kamar Mandi <span class="text-danger">*</span></label>
                    <input type="number" name="jumlah_kamar_mandi" class="form-control" min="1" value="<?= old('jumlah_kamar_mandi') ?>" required>
                </div>
            </div>

            <div class="row bg-light p-2 rounded mb-3 mx-1">
                <div class="col-md-6 mb-3">
                    <label class="form-label d-block">Akses Parkir <span class="text-danger">*</span></label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="akses_parkir" id="parkirAda" value="Ada" <?= old('akses_parkir') === 'Ada' ? 'checked' : '' ?> required>
                        <label class="form-check-label" for="parkirAda">Ada</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="akses_parkir" id="parkirTidak" value="Tidak Ada" <?= old('akses_parkir') === 'Tidak Ada' ? 'checked' : '' ?> required>
                        <label class="form-check-label" for="parkirTidak">Tidak Ada</label>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Luas Area Parkir (m²) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="luas_area_parkir" id="luasParkir" class="form-control" value="<?= old('luas_area_parkir', '0') ?>" required>
                    <small class="text-muted">Isi 0 jika tidak ada.</small>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label font-weight-bold">Fasilitas (Centang yang tersedia) <span class="text-danger">*</span></label>
                <div class="row">
                    <?php 
                    $fasilitas_items = ['PDAM', 'Meter PLN', 'Kasur', 'Kipas Angin', 'Air Conditioner', 'Mesin Cuci', 'Lemari'];
                    foreach($fasilitas_items as $f): 
                        // Generate ID unik untuk label
                        $fid = 'f_' . strtolower(str_replace(' ', '_', $f));
                    ?>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input fasilitas-checkbox" type="checkbox" name="fasilitas[]" value="<?= $f ?>" id="<?= $fid ?>" <?= in_array($f, old('fasilitas') ?? []) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="<?= $fid ?>"><?= $f ?></label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <small class="text-danger d-none" id="fasilitas-error">Pilih minimal satu fasilitas</small>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status Kepemilikan Lahan Mess <span class="text-danger">*</span></label>
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
                    <label class="form-label">Status Renovasi <span class="text-danger">*</span></label>
                    <div class="mt-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status_renovasi" id="renovPernah" value="Pernah" <?= old('status_renovasi') === 'Pernah' ? 'checked' : '' ?> required>
                            <label class="form-check-label" for="renovPernah">Pernah</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status_renovasi" id="renovBelum" value="Belum Pernah" <?= old('status_renovasi') === 'Belum Pernah' ? 'checked' : '' ?> required>
                            <label class="form-check-label" for="renovBelum">Belum Pernah</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group mt-4 text-right">
                <a href="<?= base_url('general-service?tab=mess') ?>" class="btn btn-secondary">
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
    
    // --- 1. INISIALISASI TOM SELECT (PENGGANTI SELECT2) ---
    // Ini search box karyawan. Tidak butuh jQuery.
    
    new TomSelect("#employee_select", {
        valueField: 'id', // Value yang akan dikirim (employee_number)
        labelField: 'text', // Yang ditampilkan di dropdown
        searchField: 'text', // Field untuk pencarian
        placeholder: "Ketik nama atau NIK...",
        maxOptions: 50,
        
        // Fungsi Fetch data ke Server (AJAX)
        load: function(query, callback) {
            // Jika kurang dari 2 huruf, jangan cari
            if (query.length < 2) return callback(); 

            // URL Endpoint API
            var url = '<?= base_url('general-service/search-employees') ?>?search=' + encodeURIComponent(query);
            
            fetch(url)
                .then(response => response.json())
                .then(json => {
                    // Mapping data dari server ke format Tom Select
                    // Server return: [{employee_name: "Budi", employee_number: "123"}, ...]
                    var results = json.map(function(item) {
                        return {
                            id: item.employee_number, 
                            text: item.employee_name + ' (' + item.employee_number + ')',
                            // Simpan data tambahan untuk diakses saat dipilih
                            nama_asli: item.employee_name,
                            nik_asli: item.employee_number
                        };
                    });
                    callback(results);
                })
                .catch(() => {
                    callback(); // Kalau error, stop loading
                });
        },
        
        // Fungsi Render: Mengatur tampilan item di dropdown biar rapi
        render: {
            option: function(data, escape) {
                return '<div class="py-2 px-1">' +
                        '<div class="font-weight-bold text-dark">' + escape(data.nama_asli) + '</div>' +
                        '<div class="text-muted small">NIK: ' + escape(data.nik_asli) + '</div>' +
                    '</div>';
            },
            item: function(data, escape) {
                // Tampilan saat sudah dipilih
                return '<div>' + escape(data.nama_asli) + ' (' + escape(data.nik_asli) + ')</div>';
            }
        },

        // Event saat user memilih item
        onChange: function(value) {
            // 'this.options' berisi semua data yang sudah di-load
            var selectedData = this.options[value];

            if(selectedData) {
                // Isi inputan lain otomatis
                document.getElementById('nama_karyawan').value = selectedData.nama_asli;
                document.getElementById('nik_hidden').value = selectedData.nik_asli;
                document.getElementById('nik_display').value = selectedData.nik_asli;
            } else {
                // Jika user menghapus pilihan (clear)
                document.getElementById('nama_karyawan').value = '';
                document.getElementById('nik_hidden').value = '';
                document.getElementById('nik_display').value = '';
            }
        }
    });


    // --- 2. LOGIKA DIVISI & JOB SITE (Vanilla JS) ---
    const divisiSelect = document.getElementById('divisi');
    const jobSiteSelect = document.getElementById('job_site');
    const siteIdInput = document.getElementById('site_id');

    divisiSelect.addEventListener('change', function() {
        const divisiId = this.value;
        jobSiteSelect.innerHTML = '<option value="">Loading...</option>';
        siteIdInput.value = '';

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
                    html += `<option value="${item.name}" data-site-id="${item.id || item.name}">${item.name}</option>`;
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
        siteIdInput.value = selectedOption.getAttribute('data-site-id') || this.value;
    });


    // --- 3. LOGIKA PARKIR (Vanilla JS) ---
    const parkirAda = document.getElementById('parkirAda');
    const parkirTidak = document.getElementById('parkirTidak');
    const luasParkirInput = document.getElementById('luasParkir');

    function toggleParkir(isAda) {
        if (isAda) {
            luasParkirInput.readOnly = false;
            luasParkirInput.focus();
        } else {
            luasParkirInput.value = 0;
            luasParkirInput.readOnly = true;
        }
    }

    if(parkirAda) parkirAda.addEventListener('change', function() { if (this.checked) toggleParkir(true); });
    if(parkirTidak) parkirTidak.addEventListener('change', function() { if (this.checked) toggleParkir(false); });


    // --- 4. FORM VALIDATION ---
    const form = document.getElementById('messForm');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function(e) {
        const checkboxes = document.querySelectorAll('.fasilitas-checkbox:checked');
        const errorMsg = document.getElementById('fasilitas-error');
        const employeeSelect = document.getElementById('employee_select');
        
        // Validasi Fasilitas
        if (checkboxes.length === 0) {
            e.preventDefault();
            errorMsg.classList.remove('d-none');
            alert('Pilih minimal satu fasilitas!');
            return false;
        } else {
            errorMsg.classList.add('d-none');
        }

        // Validasi Employee
        if (!employeeSelect.value) {
            e.preventDefault();
            alert('Silakan pilih karyawan terlebih dahulu!');
            return false;
        }
        
        // Prevent Double Submit
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        return true;
    });
});
</script>

<?= $this->endSection() ?>