<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="content-header mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-plus-circle mr-2 text-primary"></i>Form Pengajuan Perbaikan Baru</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('general-service/repair-request') ?>">Pengajuan Perbaikan</a></li>
                    <li class="breadcrumb-item active">Form Baru</li>
                </ol>
            </nav>
        </div>
        <a href="<?= base_url('general-service/repair-request') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<!-- Alert Messages -->
<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <h5><i class="fas fa-exclamation-triangle"></i> Validasi Error!</h5>
        <hr>
        <ul class="mb-0 pl-3">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Progress Steps -->
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="steps-progress">
            <div class="step active" data-step="1">
                <div class="step-number">1</div>
                <div class="step-label">Pilih Aset</div>
            </div>
            <div class="step-line"></div>
            <div class="step" data-step="2">
                <div class="step-number">2</div>
                <div class="step-label">Detail Kerusakan</div>
            </div>
            <div class="step-line"></div>
            <div class="step" data-step="3">
                <div class="step-number">3</div>
                <div class="step-label">Dokumentasi</div>
            </div>
        </div>
    </div>
</div>

<!-- Main Form -->
<form method="post" action="<?= base_url('general-service/repair-request/store') ?>" enctype="multipart/form-data" id="formPerbaikan">
    <?= csrf_field() ?>

    <!-- Step 1: Pilih Aset -->
    <div class="form-step active" id="step1">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-gradient-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-building"></i> Langkah 1: Pilih Aset yang Akan Diperbaiki
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">
                                Tipe Aset <span class="text-danger">*</span>
                            </label>
                            <select name="tipe_aset" id="tipeAset" class="form-control form-control-lg" required>
                                <option value="">-- Pilih Tipe Aset --</option>
                                <option value="Mess" <?= old('tipe_aset') == 'Mess' ? 'selected' : '' ?>>
                                    <i class="fas fa-home"></i> Mess (Tempat Tinggal)
                                </option>
                                <option value="Workshop" <?= old('tipe_aset') == 'Workshop' ? 'selected' : '' ?>>
                                    <i class="fas fa-tools"></i> Workshop (Bengkel)
                                </option>
                            </select>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Pilih tipe aset terlebih dahulu untuk melihat daftar aset
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">
                                Pilih Aset <span class="text-danger">*</span>
                            </label>
                            <select name="aset_id" id="asetId" class="form-control form-control-lg" required disabled>
                                <option value="">-- Pilih Tipe Aset Terlebih Dahulu --</option>
                            </select>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Pilih aset yang mengalami kerusakan
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Loading Indicator -->
                <div id="loadingAset" class="text-center py-3" style="display:none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="text-muted mt-2">Memuat data aset...</p>
                </div>

                <!-- Info Aset (akan muncul setelah memilih aset) -->
                <div id="asetInfo" class="alert alert-info border-info" style="display:none;">
                    <div class="d-flex justify-content-between align-items-start">
                        <h6 class="alert-heading mb-3">
                            <i class="fas fa-info-circle"></i> Informasi Aset Terpilih
                        </h6>
                        <span class="badge badge-primary badge-pill" id="badgeTipeAset"></span>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="text-primary"><i class="fas fa-user"></i> Data Penanggung Jawab</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td width="130"><strong>Nama</strong></td>
                                    <td id="infoNama">: -</td>
                                </tr>
                                <tr>
                                    <td><strong>NIK</strong></td>
                                    <td id="infoNik">: -</td>
                                </tr>
                                <tr>
                                    <td><strong>Divisi</strong></td>
                                    <td id="infoDivisi">: -</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-primary"><i class="fas fa-map-marker-alt"></i> Lokasi & Ukuran</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td width="130"><strong>Job Site</strong></td>
                                    <td id="infoSite">: -</td>
                                </tr>
                                <tr>
                                    <td><strong>Luas Bangunan</strong></td>
                                    <td id="infoLuas">: -</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-primary"><i class="fas fa-file-contract"></i> Status</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td width="130"><strong>Kepemilikan</strong></td>
                                    <td id="infoStatus">: -</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="text-right mt-4">
                    <button type="button" class="btn btn-primary btn-lg px-5" onclick="nextStep(2)" id="btnNext1">
                        Lanjutkan <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2: Detail Kerusakan -->
    <div class="form-step" id="step2" style="display:none;">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-gradient-warning text-dark">
                <h5 class="mb-0">
                    <i class="fas fa-tools"></i> Langkah 2: Detail Kerusakan
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="font-weight-bold">
                                Jenis Kerusakan <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="jenis_kerusakan" id="jenisKerusakan" class="form-control form-control-lg" 
                                   placeholder="Contoh: Atap bocor, Pintu rusak, Tembok retak, AC tidak dingin, dll" 
                                   value="<?= old('jenis_kerusakan') ?>" required>
                            <small class="form-text text-muted">
                                <i class="fas fa-lightbulb"></i> Sebutkan jenis kerusakan secara singkat dan jelas
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">
                                Tingkat Prioritas <span class="text-danger">*</span>
                            </label>
                            <select name="prioritas" id="prioritas" class="form-control form-control-lg" required>
                                <option value="">-- Pilih --</option>
                                <option value="Rendah" <?= old('prioritas') == 'Rendah' ? 'selected' : '' ?>>
                                    🟢 Rendah - Tidak mendesak
                                </option>
                                <option value="Sedang" <?= old('prioritas') == 'Sedang' ? 'selected' : 'selected' ?>>
                                    🟡 Sedang - 1-2 minggu
                                </option>
                                <option value="Tinggi" <?= old('prioritas') == 'Tinggi' ? 'selected' : '' ?>>
                                    🟠 Tinggi - Segera (&lt; 1 minggu)
                                </option>
                                <option value="Urgent" <?= old('prioritas') == 'Urgent' ? 'selected' : '' ?>>
                                    🔴 Urgent - Sangat mendesak (&lt; 3 hari)
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">
                        Deskripsi Kerusakan Detail <span class="text-danger">*</span>
                    </label>
                    <textarea name="deskripsi_kerusakan" id="deskripsiKerusakan" class="form-control" rows="6" 
                              placeholder="Jelaskan detail kerusakan secara lengkap:&#10;&#10;1. Lokasi kerusakan yang spesifik (misalnya: kamar tidur lantai 2, ruang bengkel bagian kiri)&#10;2. Kondisi kerusakan saat ini (seberapa parah)&#10;3. Kapan kerusakan mulai terjadi&#10;4. Dampak yang ditimbulkan (mengganggu aktivitas, bahaya, dll)&#10;5. Informasi tambahan lainnya" 
                              required><?= old('deskripsi_kerusakan') ?></textarea>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle"></i> Minimal 10 karakter. Semakin detail, semakin mudah proses perbaikan
                    </small>
                    <div class="mt-2">
                        <span class="badge badge-secondary">Karakter: <span id="charCount">0</span>/10 minimal</span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">
                                Estimasi Biaya Perbaikan
                                <span class="badge badge-info">Opsional</span>
                            </label>
                            <div class="input-group input-group-lg">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input type="text" name="estimasi_biaya" id="estimasiBiaya" class="form-control" 
                                       placeholder="0" value="<?= old('estimasi_biaya') ?>">
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-calculator"></i> Jika sudah ada perkiraan biaya, silakan diisi. Bisa dikosongkan jika belum tahu
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">
                                Estimasi Waktu Perbaikan
                                <span class="badge badge-info">Opsional</span>
                            </label>
                            <div class="input-group input-group-lg">
                                <input type="number" name="estimasi_waktu" id="estimasiWaktu" class="form-control" 
                                       placeholder="0" min="1" max="365">
                                <div class="input-group-append">
                                    <span class="input-group-text">Hari</span>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-clock"></i> Perkiraan berapa lama waktu yang dibutuhkan untuk perbaikan
                            </small>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-secondary btn-lg px-5" onclick="prevStep(1)">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </button>
                    <button type="button" class="btn btn-primary btn-lg px-5" onclick="nextStep(3)" id="btnNext2">
                        Lanjutkan <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 3: Dokumentasi -->
    <div class="form-step" id="step3" style="display:none;">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-gradient-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-images"></i> Langkah 3: Dokumentasi & Catatan
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info border-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Tips:</strong> Upload foto kerusakan untuk mempercepat proses perbaikan dan memudahkan pihak terkait memahami kondisi kerusakan.
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">
                        Upload Foto Kerusakan
                        <span class="badge badge-info">Opsional</span>
                    </label>
                    <div class="custom-file">
                        <input type="file" name="foto_kerusakan[]" class="custom-file-input" 
                               id="fotoKerusakan" multiple accept="image/jpeg,image/png,image/jpg,image/gif">
                        <label class="custom-file-label" for="fotoKerusakan">Pilih foto (maksimal 5 file)...</label>
                    </div>
                    <small class="form-text text-muted">
                        <i class="fas fa-camera"></i> Format: JPG, PNG, GIF | Maksimal 5 foto @ 2MB per file
                    </small>
                </div>

                <!-- Preview Foto -->
                <div id="previewFoto" class="row mt-3"></div>

                <div class="form-group mt-4">
                    <label class="font-weight-bold">
                        Catatan Tambahan
                        <span class="badge badge-info">Opsional</span>
                    </label>
                    <textarea name="catatan" id="catatan" class="form-control" rows="4" 
                              placeholder="Catatan tambahan, informasi kontak darurat, jadwal yang diinginkan, atau hal lain yang perlu diketahui oleh teknisi..."><?= old('catatan') ?></textarea>
                    <small class="form-text text-muted">
                        <i class="fas fa-sticky-note"></i> Informasi tambahan yang mungkin berguna untuk proses perbaikan
                    </small>
                </div>

                <!-- Review Summary -->
                <div class="card bg-light border mt-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-clipboard-check"></i> Review Pengajuan</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">Informasi Aset</h6>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td width="150"><strong>Tipe Aset</strong></td>
                                        <td id="reviewTipe">: -</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Lokasi</strong></td>
                                        <td id="reviewLokasi">: -</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Penanggung Jawab</strong></td>
                                        <td id="reviewPenanggungJawab">: -</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">Detail Kerusakan</h6>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td width="150"><strong>Jenis Kerusakan</strong></td>
                                        <td id="reviewJenis">: -</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Prioritas</strong></td>
                                        <td id="reviewPrioritas">: -</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Estimasi Biaya</strong></td>
                                        <td id="reviewBiaya">: Rp 0</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-secondary btn-lg px-5" onclick="prevStep(2)">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </button>
                    <button type="submit" class="btn btn-success btn-lg px-5" id="btnSubmit">
                        <i class="fas fa-paper-plane mr-2"></i> Ajukan Perbaikan
                    </button>
                </div>
            </div>
        </div>
    </div>

</form>

<script>
$(document).ready(function() {
    // Character counter untuk deskripsi
    $('#deskripsiKerusakan').on('input', function() {
        const length = $(this).val().length;
        $('#charCount').text(length);
        
        if (length >= 10) {
            $('#charCount').parent().removeClass('badge-secondary').addClass('badge-success');
        } else {
            $('#charCount').parent().removeClass('badge-success').addClass('badge-secondary');
        }
    });

    // Format currency untuk estimasi biaya
    $('#estimasiBiaya').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        $(this).val(value);
    });

    // Handle Tipe Aset Change
    $('#tipeAset').on('change', function() {
        const tipeAset = $(this).val();
        const asetSelect = $('#asetId');
        
        // Reset
        asetSelect.html('<option value="">Loading...</option>').prop('disabled', true);
        $('#asetInfo').slideUp();
        $('#loadingAset').show();
        
        if (!tipeAset) {
            asetSelect.html('<option value="">-- Pilih Tipe Aset Terlebih Dahulu --</option>');
            $('#loadingAset').hide();
            return;
        }
        
        // Fetch data aset
        $.ajax({
            url: '<?= base_url('general-service/repair-request/get-aset') ?>',
            type: 'GET',
            data: { tipe: tipeAset },
            dataType: 'json',
            success: function(response) {
                $('#loadingAset').hide();
                
                if (response.success && response.data.length > 0) {
                    let options = '<option value="">-- Pilih Aset --</option>';
                    response.data.forEach(function(item) {
                        const displayText = `${item.nama} - ${item.site_id} (NIK: ${item.nik})`;
                        options += `<option value="${item.id}" data-info='${JSON.stringify(item)}'>${displayText}</option>`;
                    });
                    asetSelect.html(options).prop('disabled', false);
                } else {
                    asetSelect.html('<option value="">❌ Tidak ada data aset tersedia</option>');
                }
            },
            error: function(xhr, status, error) {
                $('#loadingAset').hide();
                console.error('Error:', error);
                asetSelect.html('<option value="">❌ Error loading data</option>');
                alert('Gagal memuat data aset. Silakan refresh halaman.');
            }
        });
    });

    // Handle Aset Selection
    $('#asetId').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        
        if (!selectedOption.val()) {
            $('#asetInfo').slideUp();
            return;
        }
        
        try {
            const info = JSON.parse(selectedOption.attr('data-info'));
            const tipeAset = $('#tipeAset').val();
            
            // Populate info
            $('#badgeTipeAset').text(tipeAset);
            $('#infoNama').html(': <strong>' + (info.nama || '-') + '</strong>');
            $('#infoNik').html(': <span class="badge badge-secondary">' + (info.nik || '-') + '</span>');
            $('#infoDivisi').text(': ' + (info.divisi_name || '-'));
            $('#infoSite').html(': <strong>' + (info.site_id || '-') + '</strong>');
            $('#infoLuas').html(': <strong>' + parseFloat(info.luas || 0).toLocaleString('id-ID') + '</strong> m²');
            
            let statusBadge = 'success';
            let statusText = info.status || '-';
            if (statusText.includes('Sewa')) {
                statusBadge = 'warning';
            }
            $('#infoStatus').html(': <span class="badge badge-' + statusBadge + '">' + statusText + '</span>');
            
            $('#asetInfo').slideDown();
        } catch (e) {
            console.error('Error parsing info:', e);
        }
    });

    // Preview foto yang diupload
    $('#fotoKerusakan').on('change', function() {
        const files = this.files;
        const previewContainer = $('#previewFoto');
        previewContainer.empty();
        
        if (files.length > 5) {
            alert('⚠️ Maksimal 5 foto!');
            this.value = '';
            $(this).next('.custom-file-label').text('Pilih foto (maksimal 5 file)...');
            return;
        }
        
        let validFiles = 0;
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            
            // Check file size (max 2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert(`⚠️ File "${file.name}" terlalu besar! Maksimal 2MB per file.`);
                continue;
            }
            
            // Check file type
            if (!file.type.match('image.*')) {
                alert(`⚠️ File "${file.name}" bukan gambar!`);
                continue;
            }
            
            validFiles++;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const sizeInKB = (file.size / 1024).toFixed(2);
                previewContainer.append(`
                    <div class="col-md-3 col-6 mb-3">
                        <div class="card shadow-sm">
                            <img src="${e.target.result}" class="card-img-top" style="height:150px; object-fit:cover;">
                            <div class="card-body p-2">
                                <small class="d-block text-truncate" title="${file.name}">${file.name}</small>
                                <small class="text-muted">${sizeInKB} KB</small>
                            </div>
                        </div>
                    </div>
                `);
            };
            reader.readAsDataURL(file);
        }
        
        // Update label
        const label = $(this).next('.custom-file-label');
        if (validFiles === 0) {
            label.text('Pilih foto (maksimal 5 file)...');
            this.value = '';
        } else if (validFiles === 1) {
            label.text(files[0].name);
        } else {
            label.text(`${validFiles} foto dipilih`);
        }
    });

    // Form validation before submit
    $('#formPerbaikan').on('submit', function(e) {
        const asetId = $('#asetId').val();
        const jenisKerusakan = $('#jenisKerusakan').val();
        const deskripsi = $('#deskripsiKerusakan').val();
        const prioritas = $('#prioritas').val();
        
        if (!asetId) {
            e.preventDefault();
            alert('⚠️ Silakan pilih aset terlebih dahulu!');
            goToStep(1);
            $('#asetId').focus();
            return false;
        }
        
        if (!jenisKerusakan || jenisKerusakan.length < 3) {
            e.preventDefault();
            alert('⚠️ Jenis kerusakan minimal 3 karakter!');
            goToStep(2);
            $('#jenisKerusakan').focus();
            return false;
        }
        
        if (!deskripsi || deskripsi.length < 10) {
            e.preventDefault();
            alert('⚠️ Deskripsi kerusakan minimal 10 karakter!');
            goToStep(2);
            $('#deskripsiKerusakan').focus();
            return false;
        }
        
        if (!prioritas) {
            e.preventDefault();
            alert('⚠️ Silakan pilih prioritas perbaikan!');
            goToStep(2);
            $('#prioritas').focus();
            return false;
        }
        
        // Show loading
        $('#btnSubmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Sedang mengajukan...');
    });
});

// Step Navigation Functions
function nextStep(step) {
    // Validate current step
    if (step === 2) {
        const asetId = $('#asetId').val();
        if (!asetId) {
            alert('⚠️ Silakan pilih aset terlebih dahulu!');
            $('#asetId').focus();
            return;
        }
    }
    
    if (step === 3) {
        const jenisKerusakan = $('#jenisKerusakan').val();
        const deskripsi = $('#deskripsiKerusakan').val();
        const prioritas = $('#prioritas').val();
        
        if (!jenisKerusakan || jenisKerusakan.length < 3) {
            alert('⚠️ Jenis kerusakan minimal 3 karakter!');
            $('#jenisKerusakan').focus();
            return;
        }
        
        if (!deskripsi || deskripsi.length < 10) {
            alert('⚠️ Deskripsi kerusakan minimal 10 karakter!');
            $('#deskripsiKerusakan').focus();
            return;
        }
        
        if (!prioritas) {
            alert('⚠️ Silakan pilih prioritas perbaikan!');
            $('#prioritas').focus();
            return;
        }
        
        // Update review summary
        updateReviewSummary();
    }
    
    goToStep(step);
}

function prevStep(step) {
    goToStep(step);
}

function goToStep(step) {
    // Hide all steps
    $('.form-step').hide();
    
    // Show target step
    $('#step' + step).fadeIn();
    
    // Update progress
    $('.step').removeClass('active completed');
    for (let i = 1; i <= step; i++) {
        if (i < step) {
            $(`.step[data-step="${i}"]`).addClass('completed');
        } else {
            $(`.step[data-step="${i}"]`).addClass('active');
        }
    }
    
    // Scroll to top
    $('html, body').animate({ scrollTop: 0 }, 300);
}

function updateReviewSummary() {
    const tipeAset = $('#tipeAset').val();
    const selectedAset = $('#asetId option:selected');
    const jenisKerusakan = $('#jenisKerusakan').val();
    const prioritas = $('#prioritas').val();
    const estimasiBiaya = $('#estimasiBiaya').val() || '0';
    
    try {
        const info = JSON.parse(selectedAset.attr('data-info'));
        
        $('#reviewTipe').html(': <span class="badge badge-' + (tipeAset === 'Mess' ? 'info' : 'success') + '">' + tipeAset + '</span>');
        $('#reviewLokasi').html(': <strong>' + (info.site_id || '-') + '</strong>');
        $('#reviewPenanggungJawab').text(': ' + (info.nama || '-') + ' (' + (info.nik || '-') + ')');
        $('#reviewJenis').html(': <strong>' + jenisKerusakan + '</strong>');
        
        let prioritasBadge = 'secondary';
        if (prioritas === 'Urgent') prioritasBadge = 'danger';
        else if (prioritas === 'Tinggi') prioritasBadge = 'warning';
        else if (prioritas === 'Sedang') prioritasBadge = 'info';
        
        $('#reviewPrioritas').html(': <span class="badge badge-' + prioritasBadge + '">' + prioritas + '</span>');
        $('#reviewBiaya').html(': <strong>Rp ' + parseInt(estimasiBiaya).toLocaleString('id-ID') + '</strong>');
    } catch (e) {
        console.error('Error updating review:', e);
    }
}
</script>

<style>
/* Steps Progress Styling */
.steps-progress {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 600px;
    margin: 0 auto;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    flex: 1;
}

.step-number {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.2rem;
    transition: all 0.3s ease;
    border: 3px solid #e9ecef;
}

.step-label {
    margin-top: 10px;
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 500;
    text-align: center;
}

.step.active .step-number {
    background: #007bff;
    color: white;
    border-color: #007bff;
    box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.2);
}

.step.active .step-label {
    color: #007bff;
    font-weight: 600;
}

.step.completed .step-number {
    background: #28a745;
    color: white;
    border-color: #28a745;
}

.step.completed .step-number::before {
    content: '✓';
    font-size: 1.5rem;
}

.step.completed .step-label {
    color: #28a745;
}

.step-line {
    height: 3px;
    background: #e9ecef;
    flex: 1;
    margin: 0 10px;
    margin-bottom: 40px;
    transition: all 0.3s ease;
}

.step.completed + .step-line {
    background: #28a745;
}

/* Card Gradient Headers */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

/* Custom File Input */
.custom-file-label::after {
    content: "Browse";
    background: #007bff;
    color: white;
}

/* Hover effects */
.btn-lg:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
}

/* Alert animations */
.alert {
    animation: slideInDown 0.3s ease;
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Loading spinner */
.spinner-border {
    width: 3rem;
    height: 3rem;
}

/* Responsive */
@media (max-width: 768px) {
    .steps-progress {
        padding: 0 10px;
    }
    
    .step-label {
        font-size: 0.75rem;
    }
    
    .step-number {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
}
</style>

<?= $this->endSection() ?>