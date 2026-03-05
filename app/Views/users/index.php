<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="content-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-0 font-weight-bold">
                <i class="fas fa-users mr-2 text-primary"></i>Manajemen User
            </h3>
            <small class="text-muted">Kelola akun dan hak akses pengguna sistem</small>
        </div>
        <button type="button" class="btn btn-primary shadow-sm" onclick="openCreateModal()">
            <i class="fas fa-plus mr-2"></i>Tambah User
        </button>
    </div>
</div>

<!-- Flash messages -->
<?php foreach (['success' => 'alert-success', 'error' => 'alert-danger'] as $key => $cls): ?>
    <?php if ($msg = session()->getFlashdata($key)): ?>
        <div class="alert <?= $cls ?> alert-dismissible fade show">
            <?= esc($msg) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<!-- ── FILTER ───────────────────────────────────────────────── -->
<div class="card shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="get" class="mb-0">
            <div class="row align-items-end">
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="small font-weight-bold mb-1">Cari</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Username / Email..."
                           value="<?= esc($filters['search'] ?? '') ?>">
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small font-weight-bold mb-1">Role</label>
                    <select name="role_id" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">Semua Role</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role['id'] ?>"
                                <?= (($filters['role_id'] ?? '') == $role['id']) ? 'selected' : '' ?>>
                                <?= esc($role['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small font-weight-bold mb-1">Status</label>
                    <select name="is_active" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">Semua</option>
                        <option value="1" <?= (($filters['is_active'] ?? '') === '1') ? 'selected' : '' ?>>Aktif</option>
                        <option value="0" <?= (($filters['is_active'] ?? '') === '0') ? 'selected' : '' ?>>Non-Aktif</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small font-weight-bold mb-1">&nbsp;</label>
                    <div class="d-flex" style="gap:6px;">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="<?= base_url('users') ?>" class="btn btn-outline-secondary btn-sm" title="Reset">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-3 mb-2 mb-md-0 d-flex justify-content-md-end align-items-end">
                    <div class="d-flex align-items-center" style="gap:6px;">
                        <span class="small text-muted">Tampilkan</span>
                        <select name="per_page" class="form-control form-control-sm d-inline-block"
                                style="width:70px;" onchange="this.form.submit()">
                            <?php foreach ([10, 25, 50, 100] as $n): ?>
                                <option value="<?= $n ?>" <?= ($perPage == $n) ? 'selected' : '' ?>><?= $n ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="small text-muted">data</span>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ── TABLE ─────────────────────────────────────────────────── -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th width="45">No</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Karyawan</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Last Login</th>
                        <th class="text-center" width="130">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                Belum ada data user
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php
                        $offset = ($page - 1) * $perPage;
                        foreach ($users as $i => $u):
                            $initials = strtoupper(substr($u['username'], 0, 1));
                            $color    = '#' . substr(md5($u['username']), 0, 6);
                        ?>
                        <tr id="row-<?= $u['id'] ?>">
                            <td class="text-muted"><?= $offset + $i + 1 ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div style="width:32px;height:32px;border-radius:50%;background:<?= $color ?>;
                                                flex-shrink:0;display:flex;align-items:center;
                                                justify-content:center;color:#fff;
                                                font-size:.8rem;font-weight:bold;margin-right:8px;">
                                        <?= $initials ?>
                                    </div>
                                    <span class="font-weight-bold"><?= esc($u['username']) ?></span>
                                </div>
                            </td>
                            <td><?= esc($u['email']) ?></td>
                            <td>
                                <span class="badge badge-info"><?= esc($u['role_name'] ?? '-') ?></span>
                            </td>
                            <td class="text-muted small"><?= esc($u['employee_name'] ?? '-') ?></td>
                            <td class="text-center">
                                <div class="custom-control custom-switch d-inline-block">
                                    <input type="checkbox" class="custom-control-input toggle-active"
                                           id="sw-<?= $u['id'] ?>"
                                           data-id="<?= $u['id'] ?>"
                                           <?= $u['is_active'] ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="sw-<?= $u['id'] ?>"></label>
                                </div>
                            </td>
                            <td class="text-center text-muted small">
                                <?= $u['last_login'] ? date('d M Y H:i', strtotime($u['last_login'])) : '-' ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-warning" onclick="openEditModal(<?= $u['id'] ?>)" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-secondary"
                                            onclick="openResetModal(<?= $u['id'] ?>, '<?= esc($u['username']) ?>')"
                                            title="Reset Password">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <button class="btn btn-danger"
                                            onclick="hapusUser(<?= $u['id'] ?>, '<?= esc($u['username']) ?>')"
                                            title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (isset($pager) && $total > $perPage): ?>
        <div class="px-3 py-3 border-top d-flex justify-content-between align-items-center flex-wrap">
            <small class="text-muted mb-1">
                Menampilkan <?= $offset + 1 ?>–<?= min($offset + $perPage, $total) ?> dari <?= $total ?> user
            </small>
            <?= $pager->links() ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- =========================================================
     MODAL TAMBAH USER
     ========================================================= -->
<div class="modal fade" id="modalCreate" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus mr-2"></i>Tambah User</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="alert-create" class="alert alert-danger d-none small"></div>
                <div class="form-group">
                    <label>Username <span class="text-danger">*</span></label>
                    <input type="text" id="c_username" class="form-control" required minlength="3" maxlength="225">
                </div>
                <div class="form-group">
                    <label>Email <span class="text-danger">*</span></label>
                    <input type="email" id="c_email" class="form-control" required maxlength="255">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Password <span class="text-danger">*</span></label>
                            <input type="password" id="c_password" class="form-control" required minlength="8">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Konfirmasi <span class="text-danger">*</span></label>
                            <input type="password" id="c_password_confirm" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Role <span class="text-danger">*</span></label>
                            <select id="c_role_id" class="form-control" required>
                                <option value="">-- Pilih Role --</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>"><?= esc($role['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Status</label>
                            <select id="c_is_active" class="form-control">
                                <option value="1">Aktif</option>
                                <option value="0">Non-Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btn-create" onclick="submitCreate()">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- =========================================================
     MODAL EDIT USER
     ========================================================= -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-user-edit mr-2"></i>Edit User</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="alert-edit" class="alert alert-danger d-none small"></div>
                <input type="hidden" id="e_id">
                <div class="form-group">
                    <label>Username <span class="text-danger">*</span></label>
                    <input type="text" id="e_username" class="form-control" required minlength="3">
                </div>
                <div class="form-group">
                    <label>Email <span class="text-danger">*</span></label>
                    <input type="email" id="e_email" class="form-control" required>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Password Baru <small class="text-muted">(kosongkan jika tidak diubah)</small></label>
                            <input type="password" id="e_password" class="form-control" minlength="8">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Konfirmasi</label>
                            <input type="password" id="e_password_confirm" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Role <span class="text-danger">*</span></label>
                            <select id="e_role_id" class="form-control" required>
                                <option value="">-- Pilih Role --</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>"><?= esc($role['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Status</label>
                            <select id="e_is_active" class="form-control">
                                <option value="1">Aktif</option>
                                <option value="0">Non-Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning" id="btn-edit" onclick="submitEdit()">
                    <i class="fas fa-save mr-1"></i> Update
                </button>
            </div>
        </div>
    </div>
</div>

<!-- =========================================================
     MODAL RESET PASSWORD
     ========================================================= -->
<div class="modal fade" id="modalReset" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title"><i class="fas fa-key mr-2"></i>Reset Password</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="alert-reset" class="alert alert-danger d-none small"></div>
                <input type="hidden" id="r_id">
                <p class="small text-muted mb-3">Akun: <strong id="r_username"></strong></p>
                <div class="form-group mb-0">
                    <label>Password Baru <span class="text-danger">*</span></label>
                    <input type="password" id="r_password" class="form-control"
                           minlength="8" placeholder="Minimal 8 karakter">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="submitReset()">
                    <i class="fas fa-key mr-1"></i> Reset
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ── Konstanta ────────────────────────────────────────────────
const BASE  = '<?= base_url() ?>';
const CNAME = '<?= csrf_token() ?>';
let   CHASH = '<?= csrf_hash() ?>';

const upd = h => { if (h) CHASH = h; };

// ── Helper UI ────────────────────────────────────────────────
const $id  = id  => document.getElementById(id);
const show = (id, msg) => { $id(id).innerHTML = msg; $id(id).classList.remove('d-none'); };
const hide = id  => { $id(id).innerHTML = ''; $id(id).classList.add('d-none'); };

function setBtn(id, loading, label = 'Simpan') {
    const btn = $id(id);
    btn.disabled = loading;
    btn.innerHTML = loading
        ? '<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...'
        : `<i class="fas fa-save mr-1"></i>${label}`;
}

async function ajax(url, body) {
    body.append(CNAME, CHASH);
    const res  = await fetch(url, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body
    });
    const data = await res.json();
    upd(data.csrf_hash);
    return data;
}

// ── CREATE ───────────────────────────────────────────────────
function openCreateModal() {
    ['c_username','c_email','c_password','c_password_confirm'].forEach(id => $id(id).value = '');
    $id('c_role_id').value   = '';
    $id('c_is_active').value = '1';
    hide('alert-create');
    $('#modalCreate').modal('show');
}

async function submitCreate() {
    hide('alert-create');
    const pwd  = $id('c_password').value;
    const pwd2 = $id('c_password_confirm').value;
    if (pwd !== pwd2) { show('alert-create', 'Konfirmasi password tidak cocok.'); return; }

    setBtn('btn-create', true);
    const fd = new FormData();
    fd.append('username',         $id('c_username').value);
    fd.append('email',            $id('c_email').value);
    fd.append('password',         pwd);
    fd.append('password_confirm', pwd2);
    fd.append('role_id',          $id('c_role_id').value);
    fd.append('is_active',        $id('c_is_active').value);

    try {
        const data = await ajax(`${BASE}/users/store`, fd);
        if (data.success) { $('#modalCreate').modal('hide'); location.reload(); }
        else              show('alert-create', data.message);
    } catch (e) { show('alert-create', 'Error: ' + e.message); }
    finally     { setBtn('btn-create', false); }
}

// ── EDIT ─────────────────────────────────────────────────────
async function openEditModal(id) {
    hide('alert-edit');
    ['e_password','e_password_confirm'].forEach(i => $id(i).value = '');

    try {
        const res  = await fetch(`${BASE}/users/edit/${id}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (!data.success) { alert(data.message); return; }

        $id('e_id').value        = data.id;
        $id('e_username').value  = data.username;
        $id('e_email').value     = data.email;
        $id('e_role_id').value   = data.role_id;
        $id('e_is_active').value = data.is_active;
        $('#modalEdit').modal('show');
    } catch (e) { alert('Gagal memuat data: ' + e.message); }
}

async function submitEdit() {
    hide('alert-edit');
    const pwd  = $id('e_password').value;
    const pwd2 = $id('e_password_confirm').value;
    if (pwd && pwd !== pwd2) { show('alert-edit', 'Konfirmasi password tidak cocok.'); return; }

    setBtn('btn-edit', true, 'Update');
    const id = $id('e_id').value;
    const fd = new FormData();
    fd.append('username',  $id('e_username').value);
    fd.append('email',     $id('e_email').value);
    fd.append('role_id',   $id('e_role_id').value);
    fd.append('is_active', $id('e_is_active').value);
    if (pwd) { fd.append('password', pwd); fd.append('password_confirm', pwd2); }

    try {
        const data = await ajax(`${BASE}/users/update/${id}`, fd);
        if (data.success) { $('#modalEdit').modal('hide'); location.reload(); }
        else              show('alert-edit', data.message);
    } catch (e) { show('alert-edit', 'Error: ' + e.message); }
    finally     { setBtn('btn-edit', false, 'Update'); }
}

// ── DELETE ───────────────────────────────────────────────────
async function hapusUser(id, username) {
    if (!confirm(`Hapus user "${username}"? Tindakan tidak bisa dibatalkan.`)) return;

    try {
        const fd = new FormData();
        const data = await ajax(`${BASE}/users/delete/${id}`, fd);
        if (data.success) $id(`row-${id}`)?.remove();
        else alert(data.message);
    } catch (e) { alert('Error: ' + e.message); }
}

// ── TOGGLE AKTIF ─────────────────────────────────────────────
document.querySelectorAll('.toggle-active').forEach(el => {
    el.addEventListener('change', async function () {
        const id = this.dataset.id;
        const me = this;
        try {
            const data = await ajax(`${BASE}/users/toggle-active/${id}`, new FormData());
            if (!data.success) { me.checked = !me.checked; alert(data.message); }
        } catch (e) { me.checked = !me.checked; alert('Error: ' + e.message); }
    });
});

// ── RESET PASSWORD ────────────────────────────────────────────
function openResetModal(id, username) {
    $id('r_id').value        = id;
    $id('r_username').textContent = username;
    $id('r_password').value  = '';
    hide('alert-reset');
    $('#modalReset').modal('show');
}

async function submitReset() {
    const pwd = $id('r_password').value;
    if (!pwd || pwd.length < 8) { show('alert-reset', 'Password minimal 8 karakter.'); return; }

    const id = $id('r_id').value;
    const fd = new FormData();
    fd.append('new_password', pwd);

    try {
        const data = await ajax(`${BASE}/users/reset-password/${id}`, fd);
        if (data.success) { $('#modalReset').modal('hide'); alert('Password berhasil direset.'); }
        else show('alert-reset', data.message);
    } catch (e) { show('alert-reset', 'Error: ' + e.message); }
}

// Auto hide flash alerts
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        document.querySelectorAll('.alert-dismissible').forEach(el => {
            try { $(el).alert('close'); } catch(e) {}
        });
    }, 5000);
});
</script>

<?= $this->endSection() ?>