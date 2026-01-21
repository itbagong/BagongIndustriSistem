<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="content-header mb-3">
    <h1>Input Data Mess Karyawan</h1>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0"><i class="fas fa-home mr-2"></i> Form Data Mess</h5>
    </div>
    <div class="card-body">
        
        <form action="<?= base_url('mess/save') ?>" method="post">
            <?= csrf_field() ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Divisi <span class="text-danger">*</span></label>
                    <select name="divisi" class="form-control" required>
                        <option value="">-- Pilih Divisi --</option>
                        <option value="Umum">Umum</option>
                        <option value="Mining">Mining</option>
                        <option value="AKDP">AKDP</option>
                        <option value="BTS">BTS</option>
                        <option value="Pariwisata">Pariwisata</option>
                        <option value="MTrans">MTrans</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Job Site <span class="text-danger">*</span></label>
                    <input type="text" name="job_site" class="form-control" placeholder="Contoh: Site Batu Hijau" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Karyawan (Lengkap) <span class="text-danger">*</span></label>
                    <input type="text" name="nama_karyawan" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">NIK Karyawan <span class="text-danger">*</span></label>
                    <input type="text" name="nik" class="form-control" required>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Luasan Mess (m²) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="luas_mess" class="form-control" placeholder="0" required>
                        <span class="input-group-text">m²</span>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jumlah Kamar Tidur <span class="text-danger">*</span></label>
                    <input type="number" name="jml_kamar_tidur" class="form-control" min="0" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jumlah Kamar Mandi <span class="text-danger">*</span></label>
                    <input type="number" name="jml_kamar_mandi" class="form-control" min="0" required>
                </div>
            </div>

            <div class="row bg-light p-2 rounded mb-3 mx-1">
                <div class="col-md-6 mb-3">
                    <label class="form-label d-block">Akses Parkir <span class="text-danger">*</span></label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="akses_parkir" id="parkirAda" value="Ada" required onclick="toggleParkir(true)">
                        <label class="form-check-label" for="parkirAda">Ada</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="akses_parkir" id="parkirTidak" value="Tidak Ada" required onclick="toggleParkir(false)">
                        <label class="form-check-label" for="parkirTidak">Tidak Ada</label>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Luas Area Parkir (m²) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="luas_parkir" id="luasParkir" class="form-control" value="0" required>
                    <small class="text-muted">Isi 0 jika tidak ada.</small>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label font-weight-bold">Fasilitas (Centang yang tersedia) <span class="text-danger">*</span></label>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fasilitas[]" value="PDAM" id="f_pdam">
                            <label class="form-check-label" for="f_pdam">PDAM</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fasilitas[]" value="Meter PLN" id="f_pln">
                            <label class="form-check-label" for="f_pln">Meter PLN</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fasilitas[]" value="Kasur" id="f_kasur">
                            <label class="form-check-label" for="f_kasur">Kasur</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fasilitas[]" value="Kipas Angin" id="f_kipas">
                            <label class="form-check-label" for="f_kipas">Kipas Angin</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fasilitas[]" value="Air Conditioner" id="f_ac">
                            <label class="form-check-label" for="f_ac">Air Conditioner (AC)</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fasilitas[]" value="Mesin Cuci" id="f_mesin_cuci">
                            <label class="form-check-label" for="f_mesin_cuci">Mesin Cuci</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fasilitas[]" value="Lemari" id="f_lemari">
                            <label class="form-check-label" for="f_lemari">Lemari</label>
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status Kepemilikan Lahan Mess <span class="text-danger">*</span></label>
                    <select name="status_kepemilikan" class="form-control" required>
                        <option value="">-- Pilih Status --</option>
                        <option value="Milik PT Bagong Dekaka Makmur">Milik PT Bagong Dekaka Makmur</option>
                        <option value="Sewa">Sewa</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status Renovasi <span class="text-danger">*</span></label>
                    <div class="mt-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status_renovasi" id="renovPernah" value="Pernah" required>
                            <label class="form-check-label" for="renovPernah">Pernah</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status_renovasi" id="renovBelum" value="Belum Pernah" required>
                            <label class="form-check-label" for="renovBelum">Belum Pernah</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group mt-4 text-right">
                <a href="<?= base_url('mess') ?>" class="btn btn-secondary">
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
    // Script Sederhana untuk handle logika Parkir
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
</script>

<?= $this->endSection() ?>