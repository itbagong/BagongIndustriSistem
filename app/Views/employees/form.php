<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<style>
    .form-page { padding:24px; max-width:960px; margin:0 auto; }

    .form-header { display:flex; align-items:center; gap:14px; margin-bottom:28px; }
    .form-header-icon {
        width:48px; height:48px; border-radius:12px;
        background:linear-gradient(135deg,#4f46e5,#7c3aed);
        display:flex; align-items:center; justify-content:center; font-size:1.4rem;
        flex-shrink:0;
    }
    .form-header h1 { font-size:1.35rem; font-weight:700; color:#111827; margin:0 0 3px; }
    .form-header p  { font-size:.83rem; color:#6b7280; margin:0; }

    .form-card { background:#fff; border:1px solid #e5e7eb; border-radius:14px; margin-bottom:20px; overflow:hidden; }
    .form-card-header {
        padding:14px 20px; border-bottom:1px solid #f3f4f6;
        display:flex; align-items:center; gap:9px; background:#fafafa;
    }
    .form-card-header h3 { font-size:.82rem; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:.05em; margin:0; }
    .form-card-body { padding:20px; }

    .fg-grid { display:grid; gap:16px; }
    .fg-grid-2 { grid-template-columns:1fr 1fr; }
    .fg-grid-3 { grid-template-columns:1fr 1fr 1fr; }
    .fg-full   { grid-column:1/-1; }

    .fg { display:flex; flex-direction:column; gap:5px; }
    .fg label { font-size:.75rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.04em; }
    .fg label .req { color:#ef4444; margin-left:2px; }
    .fg input, .fg select, .fg textarea {
        padding:9px 12px; border:1px solid #d1d5db; border-radius:8px;
        font-size:.875rem; color:#374151; background:#fff; outline:none;
        transition:border .15s, box-shadow .15s; width:100%; box-sizing:border-box;
    }
    .fg input:focus, .fg select:focus, .fg textarea:focus {
        border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.1);
    }
    .fg input:disabled, .fg select:disabled { background:#f9fafb; color:#9ca3af; cursor:not-allowed; }
    .fg .hint { font-size:.72rem; color:#9ca3af; margin-top:2px; }
    .fg .field-error { font-size:.75rem; color:#ef4444; margin-top:3px; }

    .alert-errors {
        background:#fef2f2; border:1px solid #fecaca; border-radius:10px;
        padding:14px 18px; margin-bottom:20px;
    }
    .alert-errors h4 { font-size:.85rem; font-weight:700; color:#991b1b; margin:0 0 8px; }
    .alert-errors ul { margin:0; padding-left:18px; }
    .alert-errors li { font-size:.82rem; color:#b91c1c; margin-bottom:3px; }

    .form-footer {
        display:flex; align-items:center; justify-content:space-between;
        padding:16px 20px; background:#fafafa;
        border-top:1px solid #e5e7eb; border-radius:0 0 14px 14px;
        flex-wrap:wrap; gap:12px;
    }

    .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 20px; border-radius:9px; font-size:.875rem; font-weight:600; cursor:pointer; border:none; transition:all .15s; text-decoration:none; }
    .btn-primary   { background:#4f46e5; color:#fff; }
    .btn-primary:hover   { background:#4338ca; }
    .btn-secondary { background:#f3f4f6; color:#374151; }
    .btn-secondary:hover { background:#e5e7eb; }
    .btn-danger    { background:#fef2f2; color:#dc2626; }
    .btn-danger:hover    { background:#fee2e2; }
    .btn-success   { background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; }
    .btn-success:hover   { background:#dcfce7; }

    .nik-preview {
        font-family:monospace; background:#f3f4f6;
        padding:2px 8px; border-radius:5px; font-size:.82rem; color:#374151;
    }

    /* ── Modal ── */
    .modal-overlay {
        display:none; position:fixed; inset:0; z-index:1000;
        background:rgba(0,0,0,.45); backdrop-filter:blur(3px);
        align-items:center; justify-content:center;
    }
    .modal-overlay.active { display:flex; }
    .modal-box {
        background:#fff; border-radius:16px; width:100%; max-width:420px;
        margin:16px; box-shadow:0 20px 60px rgba(0,0,0,.18);
        animation:modalIn .18s ease; overflow:hidden;
    }
    @keyframes modalIn {
        from { transform:scale(.94) translateY(10px); opacity:0; }
        to   { transform:scale(1)   translateY(0);    opacity:1; }
    }
    .modal-header {
        display:flex; align-items:center; gap:12px;
        padding:18px 22px; border-bottom:1px solid #f3f4f6;
        background:linear-gradient(135deg,#f0f9ff,#e0f2fe);
    }
    .modal-header-icon {
        width:40px; height:40px; border-radius:10px;
        background:linear-gradient(135deg,#0ea5e9,#6366f1);
        display:flex; align-items:center; justify-content:center; font-size:1.2rem;
        flex-shrink:0;
    }
    .modal-header h2 { font-size:1rem; font-weight:700; color:#0f172a; margin:0 0 2px; }
    .modal-header p  { font-size:.78rem; color:#64748b; margin:0; }
    .modal-close {
        margin-left:auto; background:none; border:none; cursor:pointer;
        color:#94a3b8; font-size:1.2rem; padding:4px; border-radius:6px;
        transition:color .15s, background .15s;
    }
    .modal-close:hover { color:#374151; background:#f1f5f9; }

    .modal-body { padding:22px; display:flex; flex-direction:column; gap:14px; }

    /* info rows */
    .info-row {
        display:flex; align-items:center; gap:10px;
        background:#f8fafc; border:1px solid #e2e8f0; border-radius:9px;
        padding:11px 14px;
    }
    .info-row .info-label {
        font-size:.72rem; font-weight:700; color:#64748b;
        text-transform:uppercase; letter-spacing:.05em; min-width:80px;
    }
    .info-row .info-value { font-family:monospace; font-size:.9rem; font-weight:700; color:#4f46e5; }
    .info-row .info-value.name-val { font-family:inherit; color:#374151; font-size:.88rem; }

    /* default password notice */
    .pw-notice {
        display:flex; align-items:flex-start; gap:10px;
        background:#fffbeb; border:1px solid #fde68a; border-radius:9px;
        padding:12px 14px;
    }
    .pw-notice .pw-notice-icon { font-size:1.1rem; flex-shrink:0; margin-top:1px; }
    .pw-notice p { font-size:.8rem; color:#92400e; margin:0; line-height:1.5; }
    .pw-notice strong { color:#78350f; }

    .modal-footer {
        padding:14px 22px; border-top:1px solid #f3f4f6; background:#fafafa;
        display:flex; gap:10px; justify-content:flex-end;
    }

    /* toast */
    .toast {
        position:fixed; bottom:24px; right:24px; z-index:2000;
        display:flex; align-items:center; gap:10px;
        background:#1e293b; color:#f8fafc; padding:12px 18px;
        border-radius:10px; font-size:.85rem; font-weight:500;
        box-shadow:0 8px 24px rgba(0,0,0,.18);
        transform:translateY(80px); opacity:0;
        transition:transform .25s, opacity .25s; max-width:360px;
    }
    .toast.show { transform:translateY(0); opacity:1; }
    .toast.toast-success { background:#15803d; }
    .toast.toast-error   { background:#b91c1c; }

    @media(max-width:640px) {
        .fg-grid-2, .fg-grid-3 { grid-template-columns:1fr; }
    }
</style>

<div class="form-page">

    <!-- Header -->
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

    <!-- Validation errors -->
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

        <!-- 1. IDENTITAS -->
        <div class="form-card">
            <div class="form-card-header"><span>👤</span><h3>Identitas Karyawan</h3></div>
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

        <!-- 2. DATA PRIBADI -->
        <div class="form-card">
            <div class="form-card-header"><span>📋</span><h3>Data Pribadi</h3></div>
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
                               placeholder="16 digit NIK KTP" maxlength="16">
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

        <!-- 3. DATA PEKERJAAN -->
        <div class="form-card">
            <div class="form-card-header"><span>💼</span><h3>Data Pekerjaan</h3></div>
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

        <!-- 4. TANGGAL KONTRAK -->
        <div class="form-card">
            <div class="form-card-header"><span>📅</span><h3>Tanggal Kontrak</h3></div>
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

        <!-- Footer -->
        <div class="form-card">
            <div class="form-footer">
                <a href="<?= base_url('employees') ?>" class="btn btn-secondary">← Kembali</a>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <?php if ($mode === 'edit'): ?>
                        <?php if ($hasLogin): ?>
                            <!-- Sudah punya akun — tampilkan badge, sembunyikan tombol -->
                            <span style="
                                display:inline-flex; align-items:center; gap:6px;
                                background:#f0fdf4; border:1px solid #bbf7d0;
                                color:#15803d; padding:8px 14px; border-radius:9px;
                                font-size:.82rem; font-weight:600;
                            ">
                                ✅ Sudah punya akun login
                            </span>
                        <?php else: ?>
                            <!-- Belum punya akun — tampilkan tombol buat -->
                            <button type="button" class="btn btn-success" id="btnCreateLogin"
                                    data-nik="<?= esc($employee['nik']) ?>"
                                    data-name="<?= esc($employee['name']) ?>"
                                    data-employee-id="<?= esc($employee['id']) ?>">
                                🔑 Buat Login
                            </button>
                        <?php endif; ?>
                        <a href="<?= base_url('employees') ?>" class="btn btn-danger">Batal</a>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <?= $mode === 'edit' ? '💾 Simpan Perubahan' : '➕ Tambah Karyawan' ?>
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>

<!-- ══════════════════════════════════════════════════════════════
     MODAL — Buat Login (konfirmasi saja, tanpa input password)
══════════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="loginModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-box">

        <div class="modal-header">
            <div class="modal-header-icon">🔑</div>
            <div>
                <h2 id="modalTitle">Buat Akun Login</h2>
                <p id="modalSubtitle">Konfirmasi pembuatan akun karyawan</p>
            </div>
            <button class="modal-close" id="modalClose" aria-label="Tutup">✕</button>
        </div>

        <div class="modal-body">

            <!-- Info username -->
            <div class="info-row">
                <span class="info-label">Username</span>
                <code class="info-value" id="modalUsername">—</code>
            </div>

            <!-- Info nama -->
            <div class="info-row">
                <span class="info-label">Nama</span>
                <span class="info-value name-val" id="modalName">—</span>
            </div>

            <!-- Notifikasi password default -->
            <div class="pw-notice">
                <span class="pw-notice-icon">ℹ️</span>
                <p>
                    Password akan diset ke <strong>Password.1</strong> secara otomatis.<br>
                    Karyawan disarankan mengganti password setelah login pertama.
                </p>
            </div>

        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="modalCancel">Batal</button>
            <button type="button" class="btn btn-primary" id="btnSubmitLogin">
                🔑 Ya, Buat Akun
            </button>
        </div>

    </div>
</div>

<div class="toast" id="toast"></div>

<script>
(function () {
    const modal       = document.getElementById('loginModal');
    const btnOpen     = document.getElementById('btnCreateLogin');
    const btnClose    = document.getElementById('modalClose');
    const btnCancel   = document.getElementById('modalCancel');
    const btnSubmit   = document.getElementById('btnSubmitLogin');
    const modalUser   = document.getElementById('modalUsername');
    const modalName   = document.getElementById('modalName');
    const modalSub    = document.getElementById('modalSubtitle');
    const toast       = document.getElementById('toast');

    let currentEmployeeId  = null;
    let currentEmployeeNik = null;

    /* ── Open ── */
    if (btnOpen) {
        btnOpen.addEventListener('click', () => {
            currentEmployeeId  = btnOpen.dataset.employeeId;
            currentEmployeeNik = btnOpen.dataset.nik;
            const name         = btnOpen.dataset.name;

            modalUser.textContent = currentEmployeeNik;
            modalName.textContent = name;
            modalSub.textContent  = 'Konfirmasi pembuatan akun untuk ' + name;

            modal.classList.add('active');
        });
    }

    /* ── Close ── */
    function closeModal() { modal.classList.remove('active'); }
    btnClose.addEventListener('click', closeModal);
    btnCancel.addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

    /* ── Toast ── */
    function showToast(msg, type = '') {
        toast.textContent = msg;
        toast.className   = 'toast' + (type ? ' toast-' + type : '');
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3500);
    }

    /* ── Submit ── */
    btnSubmit.addEventListener('click', async () => {
        btnSubmit.disabled    = true;
        btnSubmit.textContent = '⏳ Menyimpan...';

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                           ?? document.querySelector('input[name="<?= csrf_token() ?>"]')?.value
                           ?? '';

            const res = await fetch('<?= base_url('employees/create-login') ?>', {
                method : 'POST',
                headers: {
                    'Content-Type'     : 'application/json',
                    'X-Requested-With' : 'XMLHttpRequest',
                    '<?= csrf_header() ?>': csrfToken,
                },
                body: JSON.stringify({
                    employee_id : currentEmployeeId,
                    username    : currentEmployeeNik,
                    '<?= csrf_token() ?>': csrfToken,
                }),
            });

            const data = await res.json();

            if (res.ok && data.success) {
                showToast('✅ ' + data.message, 'success');
                closeModal();
                location.reload();
            } else {
                showToast('❌ ' + (data.message ?? 'Terjadi kesalahan'), 'error');
            }

        } catch (err) {
            showToast('❌ Gagal menghubungi server', 'error');
            console.error(err);
        } finally {
            btnSubmit.disabled    = false;
            btnSubmit.textContent = '🔑 Ya, Buat Akun';
        }
    });
})();
</script>

<?php
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