<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="content-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-0 font-weight-bold">
                <i class="fas fa-user-shield mr-2 text-primary"></i>Manajemen Role
            </h3>
            <small class="text-muted">Kelola role dan level akses pengguna sistem</small>
        </div>
        <button type="button" class="btn btn-primary shadow-sm" onclick="openCreateModal()">
            <i class="fas fa-plus mr-2"></i>Tambah Role
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

<!-- ── INFO LEVEL ─────────────────────────────────────────── -->
<div class="alert alert-light border mb-3 py-2">
    <small class="text-muted">
        <i class="fas fa-info-circle mr-1 text-info"></i>
        <strong>Hierarchy Level:</strong>
        <?php foreach ($levelLabels as $lvl => $lbl): ?>
            <span class="badge badge-secondary mr-1"><?= $lvl ?> = <?= $lbl ?></span>
        <?php endforeach; ?>
        &nbsp;·&nbsp; Nama role menggunakan <code>snake_case</code>, contoh: <code>admin_gudang</code>, <code>supervisor_lapangan</code>
    </small>
</div>

<!-- ── FILTER ────────────────────────────────────────────────── -->
<div class="card shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="get" class="mb-0">
            <div class="row align-items-end">
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="small font-weight-bold mb-1">Cari</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Nama role / display name..."
                           value="<?= esc($filters['search'] ?? '') ?>">
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small font-weight-bold mb-1">Level</label>
                    <select name="level" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">Semua Level</option>
                        <?php foreach ($levelLabels as $lvl => $lbl): ?>
                            <option value="<?= $lvl ?>" <?= (($filters['level'] ?? '') == $lvl) ? 'selected' : '' ?>>
                                <?= $lvl ?> — <?= $lbl ?>
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
                        <a href="<?= base_url('roles') ?>" class="btn btn-outline-secondary btn-sm" title="Reset">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-3 mb-2 mb-md-0 d-flex justify-content-md-end align-items-end">
                    <div class="d-flex align-items-center" style="gap:6px;">
                        <span class="small text-muted">Tampilkan</span>
                        <select name="per_page" class="form-control form-control-sm"
                                style="width:70px;" onchange="this.form.submit()">
                            <?php foreach ([10, 25, 50] as $n): ?>
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

<!-- ── TABLE ──────────────────────────────────────────────────── -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th width="45">No</th>
                        <th>Nama Role</th>
                        <th>Display Name</th>
                        <th>Deskripsi</th>
                        <th class="text-center">Level</th>
                        <th class="text-center">User</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" width="110">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($roles)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                Belum ada data role
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php
                        $offset = ($page - 1) * $perPage;
                        $levelColors = [1 => 'danger', 2 => 'warning', 3 => 'info', 4 => 'secondary'];
                        foreach ($roles as $i => $r):
                            $lvlColor = $levelColors[$r['level']] ?? 'secondary';
                            $lvlLabel = $levelLabels[$r['level']] ?? 'Level ' . $r['level'];
                        ?>
                        <tr id="row-<?= $r['id'] ?>">
                            <td class="text-muted"><?= $offset + $i + 1 ?></td>
                            <td>
                                <code class="text-primary"><?= esc($r['name']) ?></code>
                            </td>
                            <td class="font-weight-bold"><?= esc($r['display_name']) ?></td>
                            <td class="text-muted small" style="max-width:200px;">
                                <?= esc($r['description'] ?? '-') ?>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-<?= $lvlColor ?>">
                                    <?= $r['level'] ?> — <?= $lvlLabel ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-light border"><?= $r['user_count'] ?> user</span>
                            </td>
                            <td class="text-center">
                                <div class="custom-control custom-switch d-inline-block">
                                    <input type="checkbox" class="custom-control-input toggle-active"
                                           id="sw-<?= $r['id'] ?>"
                                           data-id="<?= $r['id'] ?>"
                                           <?= $r['is_active'] ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="sw-<?= $r['id'] ?>"></label>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-warning"
                                            onclick="openEditModal(<?= $r['id'] ?>)" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger"
                                            onclick="hapusRole(<?= $r['id'] ?>, '<?= esc($r['display_name']) ?>')"
                                            title="Hapus"
                                            <?= $r['user_count'] > 0 ? 'disabled title="Role masih dipakai user"' : '' ?>>
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
                Menampilkan <?= $offset + 1 ?>–<?= min($offset + $perPage, $total) ?>
                dari <?= $total ?> role
            </small>
            <?= $pager->links() ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- =========================================================
     MODAL TAMBAH ROLE
     ========================================================= -->
<div class="modal fade" id="modalCreate" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i>Tambah Role</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="alert-create" class="alert alert-danger d-none small"></div>

                <div class="form-group">
                    <label>Nama Role <span class="text-danger">*</span></label>
                    <input type="text" id="c_name" class="form-control"
                           placeholder="contoh: admin_gudang, supervisor_lapangan"
                           required>
                    <small class="text-muted">Gunakan huruf kecil, angka, underscore. Tidak boleh ada spasi.</small>
                </div>
                <div class="form-group">
                    <label>Display Name <span class="text-danger">*</span></label>
                    <input type="text" id="c_display_name" class="form-control"
                           placeholder="contoh: Admin Gudang, Supervisor Lapangan"
                           required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea id="c_description" class="form-control" rows="2"
                              placeholder="Deskripsi singkat tentang role ini..."></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Level <span class="text-danger">*</span></label>
                            <select id="c_level" class="form-control" required>
                                <option value="">-- Pilih Level --</option>
                                <?php foreach ($levelLabels as $lvl => $lbl): ?>
                                    <option value="<?= $lvl ?>"><?= $lvl ?> — <?= $lbl ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Level menentukan hierarki akses.</small>
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
     MODAL EDIT ROLE
     ========================================================= -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Edit Role</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="alert-edit" class="alert alert-danger d-none small"></div>
                <input type="hidden" id="e_id">

                <div class="form-group">
                    <label>Nama Role <span class="text-danger">*</span></label>
                    <input type="text" id="e_name" class="form-control" required>
                    <small class="text-muted">Gunakan huruf kecil, angka, underscore.</small>
                </div>
                <div class="form-group">
                    <label>Display Name <span class="text-danger">*</span></label>
                    <input type="text" id="e_display_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea id="e_description" class="form-control" rows="2"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Level <span class="text-danger">*</span></label>
                            <select id="e_level" class="form-control" required>
                                <option value="">-- Pilih Level --</option>
                                <?php foreach ($levelLabels as $lvl => $lbl): ?>
                                    <option value="<?= $lvl ?>"><?= $lvl ?> — <?= $lbl ?></option>
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

<script>
const BASE  = '<?= base_url() ?>';
const CNAME = '<?= csrf_token() ?>';
let   CHASH = '<?= csrf_hash() ?>';
const upd   = h => { if (h) CHASH = h; };

// ── Helpers ──────────────────────────────────────────────────
const gid  = id => document.getElementById(id);

const show = (id, msg) => {
    const el = gid(id);
    if (!el) return;
    el.innerHTML = msg;
    el.classList.remove('d-none');
};

const hide = id => {
    const el = gid(id);
    if (!el) return;
    el.innerHTML = '';
    el.classList.add('d-none');
};

function setBtn(id, loading, label = 'Simpan') {
    const btn = gid(id);
    if (!btn) return;
    btn.disabled  = loading;
    btn.innerHTML = loading
        ? '<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...'
        : `<i class="fas fa-save mr-1"></i>${label}`;
}

async function ajax(url, fd) {
    fd.append(CNAME, CHASH);
    const res  = await fetch(url, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
    });
    const data = await res.json();
    upd(data.csrf_hash);
    return data;
}

// ── Reset modal state (fix aria-hidden & backdrop) ───────────
function resetModal(modalId) {
    const $m = $(modalId);
    $m.removeAttr('aria-hidden');           // fix aria-hidden blocking
    $m.removeAttr('style');                 // reset inline style
    $m.removeClass('show');
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open').css('padding-right', '');
}

// ── Auto-format nama role ────────────────────────────────────
gid('c_name')?.addEventListener('input', function () {
    this.value = this.value.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_-]/g, '');
});
gid('e_name')?.addEventListener('input', function () {
    this.value = this.value.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_-]/g, '');
});

// ── CREATE ───────────────────────────────────────────────────
function openCreateModal() {
    resetModal('#modalCreate');
    ['c_name','c_display_name','c_description'].forEach(id => {
        const el = gid(id); if (el) el.value = '';
    });
    gid('c_level').value     = '';
    gid('c_is_active').value = '1';
    hide('alert-create');
    $('#modalCreate').modal({ backdrop: true, keyboard: true });
    $('#modalCreate').modal('show');
}

async function submitCreate() {
    hide('alert-create');
    setBtn('btn-create', true);

    const fd = new FormData();
    fd.append('name',         gid('c_name').value.trim());
    fd.append('display_name', gid('c_display_name').value.trim());
    fd.append('description',  gid('c_description').value.trim());
    fd.append('level',        gid('c_level').value);
    fd.append('is_active',    gid('c_is_active').value);

    try {
        const data = await ajax(`${BASE}roles/store`, fd);
        if (data.status === 'success') {
            $('#modalCreate').modal('hide');
            location.reload();
        } else {
            show('alert-create', data.message);
        }
    } catch (e) {
        show('alert-create', 'Error: ' + e.message);
    } finally {
        setBtn('btn-create', false);
    }
}

// ── EDIT ─────────────────────────────────────────────────────
async function openEditModal(id) {
    resetModal('#modalEdit');
    hide('alert-edit');

    try {
        const res  = await fetch(`${BASE}roles/edit/${id}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const resp = await res.json();
        if (resp.status !== 'success') { alert(resp.message); return; }

        const d = resp.data;
        gid('e_id').value           = d.id;
        gid('e_name').value         = d.name;
        gid('e_display_name').value = d.display_name;
        gid('e_description').value  = d.description ?? '';
        gid('e_level').value        = parseInt(d.level);
        gid('e_is_active').value    = parseInt(d.is_active);

        $('#modalEdit').modal({ backdrop: true, keyboard: true });
        $('#modalEdit').modal('show');

    } catch (e) {
        alert('Gagal memuat data: ' + e.message);
    }
}

async function submitEdit() {
    hide('alert-edit');
    setBtn('btn-edit', true, 'Update');

    const id = gid('e_id').value;
    const fd = new FormData();
    fd.append('name',         gid('e_name').value.trim());
    fd.append('display_name', gid('e_display_name').value.trim());
    fd.append('description',  gid('e_description').value.trim());
    fd.append('level',        gid('e_level').value);
    fd.append('is_active',    gid('e_is_active').value);

    try {
        const data = await ajax(`${BASE}roles/update/${id}`, fd);
        if (data.status === 'success') {
            $('#modalEdit').modal('hide');
            location.reload();
        } else {
            show('alert-edit', data.message);
        }
    } catch (e) {
        show('alert-edit', 'Error: ' + e.message);
    } finally {
        setBtn('btn-edit', false, 'Update');
    }
}

// ── DELETE ───────────────────────────────────────────────────
async function hapusRole(id, nama) {
    if (!confirm(`Hapus role "${nama}"?\nRole yang masih dipakai user tidak bisa dihapus.`)) return;

    try {
        const data = await ajax(`${BASE}/roles/delete/${id}`, new FormData());
        if (data.status === 'success') {
            const row = document.getElementById(`row-${id}`);
            if (row) row.remove();
        } else {
            alert(data.message);
        }
    } catch (e) {
        alert('Error: ' + e.message);
    }
}

// ── TOGGLE AKTIF ─────────────────────────────────────────────
document.querySelectorAll('.toggle-active').forEach(el => {
    el.addEventListener('change', async function () {
        const me = this;
        const id = me.dataset.id;
        try {
            const data = await ajax(`${BASE}/roles/toggle-active/${id}`, new FormData());
            if (data.status !== 'success') {
                me.checked = !me.checked;
                alert(data.message);
            }
        } catch (e) {
            me.checked = !me.checked;
            alert('Error: ' + e.message);
        }
    });
});



// ── Auto hide flash ───────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        document.querySelectorAll('.alert-dismissible').forEach(el => {
            $(el).alert('close');
        });
    }, 5000);
});
</script>

<?= $this->endSection() ?>