<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<style>
    .form-page { padding:24px; max-width:960px; margin:0 auto; }

    /* ── Header ── */
    .form-header { display:flex; align-items:center; gap:14px; margin-bottom:28px; }
    .form-header-icon {
        width:48px; height:48px; border-radius:12px;
        background:linear-gradient(135deg,#4f46e5,#7c3aed);
        display:flex; align-items:center; justify-content:center; font-size:1.4rem;
        flex-shrink:0;
    }
    .form-header h1 { font-size:1.35rem; font-weight:700; color:#111827; margin:0 0 3px; }
    .form-header p  { font-size:.83rem; color:#6b7280; margin:0; }

    /* ── Card ── */
    .form-card {
        background:#fff; border:1px solid #e5e7eb;
        border-radius:14px; margin-bottom:20px; overflow:hidden;
    }
    .form-card-header {
        padding:14px 20px; border-bottom:1px solid #f3f4f6;
        display:flex; align-items:center; gap:9px;
        background:#fafafa;
    }
    .form-card-header h3 { font-size:.82rem; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:.05em; margin:0; }
    .form-card-body { padding:20px; }

    /* ── Grid ── */
    .fg-grid { display:grid; gap:16px; }
    .fg-grid-2 { grid-template-columns:1fr 1fr; }
    .fg-grid-3 { grid-template-columns:1fr 1fr 1fr; }
    .fg-full   { grid-column:1/-1; }

    /* ── Form group ── */
    .fg { display:flex; flex-direction:column; gap:5px; }
    .fg label { font-size:.75rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.04em; }
    .fg label .req { color:#ef4444; margin-left:2px; }
    .fg input,
    .fg select,
    .fg textarea {
        padding:9px 12px; border:1px solid #d1d5db; border-radius:8px;
        font-size:.875rem; color:#374151; background:#fff; outline:none;
        transition:border .15s, box-shadow .15s;
        width:100%; box-sizing:border-box;
    }
    .fg input:focus,
    .fg select:focus,
    .fg textarea:focus {
        border-color:#6366f1;
        box-shadow:0 0 0 3px rgba(99,102,241,.1);
    }
    .fg input:disabled,
    .fg select:disabled { background:#f9fafb; color:#9ca3af; cursor:not-allowed; }
    .fg .hint { font-size:.72rem; color:#9ca3af; margin-top:2px; }
    .fg .field-error { font-size:.75rem; color:#ef4444; margin-top:3px; }

    /* ── Error alert ── */
    .alert-errors {
        background:#fef2f2; border:1px solid #fecaca; border-radius:10px;
        padding:14px 18px; margin-bottom:20px;
    }
    .alert-errors h4 { font-size:.85rem; font-weight:700; color:#991b1b; margin:0 0 8px; }
    .alert-errors ul { margin:0; padding-left:18px; }
    .alert-errors li { font-size:.82rem; color:#b91c1c; margin-bottom:3px; }

    /* ── Footer ── */
    .form-footer {
        display:flex; align-items:center; justify-content:space-between;
        padding:16px 20px; background:#fafafa;
        border-top:1px solid #e5e7eb; border-radius:0 0 14px 14px;
        flex-wrap:wrap; gap:12px;
    }

    /* ── Buttons ── */
    .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 20px; border-radius:9px; font-size:.875rem; font-weight:600; cursor:pointer; border:none; transition:all .15s; text-decoration:none; }
    .btn-primary { background:#4f46e5; color:#fff; }
    .btn-primary:hover { background:#4338ca; }
    .btn-secondary { background:#f3f4f6; color:#374151; }
    .btn-secondary:hover { background:#e5e7eb; }
    .btn-danger { background:#fef2f2; color:#dc2626; }
    .btn-danger:hover { background:#fee2e2; }

    /* ── NIK preview ── */
    .nik-preview {
        font-family:monospace; background:#f3f4f6;
        padding:2px 8px; border-radius:5px; font-size:.82rem; color:#374151;
    }

    @media(max-width:640px) {
        .fg-grid-2, .fg-grid-3 { grid-template-columns:1fr; }
    }
</style>

<div class="form-page">

    <!-- ── Header ─────────────────────────────────────────────── -->
    <div class="form-header">
        <div class="form-header-icon">
            <?= $mode === 'edit' ? '✏️' : '➕' ?>
        </div>
        <div>
            <h1><?= $mode === 'edit' ? 'Edit Karyawan' : 'Tambah Karyawan' ?></h1>
            <p>
                <?php if ($mode === 'edit' && $employee): ?>
                    <span class="nik-preview"><?= esc($employee['nik']) ?></span>
                    &nbsp;—&nbsp; <?= esc($employee['name']) ?>
                <?php else: ?>
                    Isi data karyawan baru di bawah ini
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- ── Validation errors ───────────────────────────────────── -->
    <?php if (session()->getFlashdata('errors') || isset($errors)): ?>
        <?php $errs = session()->getFlashdata('errors') ?? $errors ?? []; ?>
        <div class="alert-errors">
            <h4>⚠️ Terdapat kesalahan input:</h4>
            <ul>
                <?php foreach ($errs as $e): ?>
                    <li><?= esc($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST"
          action="<?= $mode === 'edit'
              ? base_url('employees/update/' . $employee['id'])
              : base_url('employees/store') ?>">
        <?= csrf_field() ?>

        <!-- ══════════════════════════════════════════════════════
             1. IDENTITAS
        ══════════════════════════════════════════════════════ -->
        <div class="form-card">
            <div class="form-card-header">
                <span>👤</span><h3>Identitas Karyawan</h3>
            </div>
            <div class="form-card-body">
                <div class="fg-grid fg-grid-3">

                    <div class="fg">
                        <label>NIK <span class="req">*</span></label>
                        <input type="text" name="nik"
                               value="<?= old('nik', $employee['nik'] ?? '') ?>"
                               placeholder="Nomor Induk Karyawan">
                        <?= fieldError('nik') ?>
                    </div>

                    <div class="fg">
                        <label>BIS ID</label>
                        <input type="text" name="bis_id"
                               value="<?= old('bis_id', $employee['bis_id'] ?? '') ?>"
                               placeholder="BIS ID (opsional)">
                    </div>

                    <div class="fg fg-full">
                        <label>Nama Lengkap <span class="req">*</span></label>
                        <input type="text" name="name"
                               value="<?= old('name', $employee['name'] ?? '') ?>"
                               placeholder="Nama lengkap sesuai KTP">
                        <?= fieldError('name') ?>
                    </div>

                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════
             2. DATA PRIBADI
        ══════════════════════════════════════════════════════ -->
        <div class="form-card">
            <div class="form-card-header">
                <span>📋</span><h3>Data Pribadi</h3>
            </div>
            <div class="form-card-body">
                <div class="fg-grid fg-grid-3">

                    <div class="fg">
                        <label>Gender <span class="req">*</span></label>
                        <select name="gender_id">
                            <option value="">— Pilih —</option>
                            <?php foreach ($genders as $g): ?>
                                <option value="<?= esc($g['id']) ?>"
                                    <?= old('gender_id', $employee['gender_id'] ?? '') == $g['id'] ? 'selected' : '' ?>>
                                    <?= esc($g['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?= fieldError('gender_id') ?>
                    </div>

                    <div class="fg">
                        <label>Agama</label>
                        <select name="religion_id">
                            <option value="">— Pilih —</option>
                            <?php foreach ($religions as $r): ?>
                                <option value="<?= esc($r['id']) ?>"
                                    <?= old('religion_id', $employee['religion_id'] ?? '') == $r['id'] ? 'selected' : '' ?>>
                                    <?= esc($r['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="fg">
                        <label>Pendidikan Terakhir</label>
                        <select name="last_education_id">
                            <option value="">— Pilih —</option>
                            <?php foreach ($last_educations as $edu): ?>
                                <option value="<?= esc($edu['id']) ?>"
                                    <?= old('last_education_id', $employee['last_education_id'] ?? '') == $edu['id'] ? 'selected' : '' ?>>
                                    <?= esc($edu['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="fg">
                        <label>Tempat Lahir</label>
                        <input type="text" name="place_of_birth"
                               value="<?= old('place_of_birth', $employee['place_of_birth'] ?? '') ?>"
                               placeholder="Kota kelahiran">
                    </div>

                    <div class="fg">
                        <label>Tanggal Lahir</label>
                        <input type="date" name="date_of_birth"
                               value="<?= old('date_of_birth', $employee['date_of_birth'] ?? '') ?>">
                    </div>

                    <div class="fg">
                        <label>No. KTP</label>
                        <input type="text" name="national_id"
                               value="<?= old('national_id', $employee['national_id'] ?? '') ?>"
                               placeholder="16 digit NIK KTP"
                               maxlength="16">
                    </div>

                    <div class="fg">
                        <label>No. HP</label>
                        <input type="text" name="phone_number"
                               value="<?= old('phone_number', $employee['phone_number'] ?? '') ?>"
                               placeholder="08xxxxxxxxxx">
                    </div>

                    <div class="fg fg-full" style="grid-column:span 2;">
                        <label>Alamat</label>
                        <textarea name="address" rows="2"
                                  placeholder="Alamat lengkap"><?= old('address', $employee['address'] ?? '') ?></textarea>
                    </div>

                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════
             3. DATA PEKERJAAN
        ══════════════════════════════════════════════════════ -->
        <div class="form-card">
            <div class="form-card-header">
                <span>💼</span><h3>Data Pekerjaan</h3>
            </div>
            <div class="form-card-body">
                <div class="fg-grid fg-grid-3">
                    <div class="fg fg-full">
                        <label>Work User</label>
                        <input type="text" name="work_user"
                            value="<?= old('work_user', $employee['work_user'] ?? '') ?>"
                            placeholder="Work user">
                    </div>

                    <div class="fg">
                        <label>Department</label>
                        <select name="department_id">
                            <option value="">— Pilih —</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= esc($d['id']) ?>"
                                    <?= old('department_id', $employee['department_id'] ?? '') == $d['id'] ? 'selected' : '' ?>>
                                    <?= esc($d['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="fg">
                        <label>Division</label>
                        <select name="division_id">
                            <option value="">— Pilih —</option>
                            <?php foreach ($divisions as $d): ?>
                                <option value="<?= esc($d['id']) ?>"
                                    <?= old('division_id', $employee['division_id'] ?? '') == $d['id'] ? 'selected' : '' ?>>
                                    <?= esc($d['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="fg">
                        <label>Job Position</label>
                        <select name="job_position_id">
                            <option value="">— Pilih —</option>
                            <?php foreach ($job_positions as $jp): ?>
                                <option value="<?= esc($jp['id']) ?>"
                                    <?= old('job_position_id', $employee['job_position_id'] ?? '') == $jp['id'] ? 'selected' : '' ?>>
                                    <?= esc($jp['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="fg">
                        <label>Site</label>
                        <select name="site_id">
                            <option value="">— Pilih —</option>
                            <?php foreach ($sites as $s): ?>
                                <option value="<?= esc($s['id']) ?>"
                                    <?= old('site_id', $employee['site_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                                    <?= esc($s['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="fg">
                        <label>Status Karyawan <span class="req">*</span></label>
                        <select name="employee_status_id">
                            <option value="">— Pilih —</option>
                            <?php foreach ($employee_statuses as $es): ?>
                                <option value="<?= esc($es['id']) ?>"
                                    <?= old('employee_status_id', $employee['employee_status_id'] ?? '') == $es['id'] ? 'selected' : '' ?>>
                                    <?= esc($es['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?= fieldError('employee_status_id') ?>
                    </div>

                    <div class="fg">
                        <label>Status Kepegawaian <span class="req">*</span></label>
                        <select name="employment_status_id">
                            <option value="">— Pilih —</option>
                            <?php foreach ($employment_statuses as $es): ?>
                                <option value="<?= esc($es['id']) ?>"
                                    <?= old('employment_status_id', $employee['employment_status_id'] ?? '') == $es['id'] ? 'selected' : '' ?>>
                                    <?= esc($es['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?= fieldError('employment_status_id') ?>
                    </div>

                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════
             4. TANGGAL KONTRAK
        ══════════════════════════════════════════════════════ -->
        <div class="form-card">
            <div class="form-card-header">
                <span>📅</span><h3>Tanggal Kontrak</h3>
            </div>
            <div class="form-card-body">
                <div class="fg-grid fg-grid-3">

                    <div class="fg">
                        <label>PKWT Date</label>
                        <input type="date" name="pkwt_date"
                               value="<?= old('pkwt_date', $employee['pkwt_date'] ?? '') ?>">
                        <span class="hint">Tanggal mulai kontrak</span>
                    </div>

                    <div class="fg">
                        <label>Cutoff Date</label>
                        <input type="date" name="cutoff_date"
                               value="<?= old('cutoff_date', $employee['cutoff_date'] ?? '') ?>">
                        <span class="hint">Kosongkan jika masih aktif</span>
                    </div>

                    <?php if ($mode === 'edit' && !empty($employee['tenure'])): ?>
                    <div class="fg">
                        <label>Tenure (computed)</label>
                        <input type="text" value="<?= esc($employee['tenure']) ?>" disabled>
                        <span class="hint">Dihitung otomatis</span>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <!-- ── Footer ─────────────────────────────────────────── -->
        <div class="form-card">
            <div class="form-footer">
                <a href="<?= base_url('employees') ?>" class="btn btn-secondary">
                    ← Kembali
                </a>
                <div style="display:flex; gap:10px;">
                    <?php if ($mode === 'edit'): ?>
                        <a href="<?= base_url('employees') ?>" class="btn btn-danger">
                            Batal
                        </a>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <?= $mode === 'edit' ? '💾 Simpan Perubahan' : '➕ Tambah Karyawan' ?>
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>

<?php
// ── Helper: render inline field error if validation failed ──
function fieldError(string $field): string
{
    $errors = session()->getFlashdata('errors') ?? [];
    if (isset($errors[$field])) {
        return '<span class="field-error">' . esc($errors[$field]) . '</span>';
    }
    return '';
}
?>

<?= $this->endSection() ?>