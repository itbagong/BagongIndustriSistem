<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="content-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-0 font-weight-bold">
                <i class="fas fa-key mr-2 text-primary"></i>Manajemen Permission
            </h3>
            <small class="text-muted">Kelola hak akses aksi dalam sistem</small>
        </div>
        <button type="button" class="btn btn-primary shadow-sm" onclick="openCreateModal()">
            <i class="fas fa-plus mr-2"></i>Tambah Permission
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

<!-- ── INFO ──────────────────────────────────────────────────── -->
<div class="alert alert-light border mb-3 py-2">
    <small class="text-muted">
        <i class="fas fa-info-circle mr-1 text-info"></i>
        Format nama: <code>module.aksi</code> — contoh: <code>user.view</code>, <code>laporan.export</code>, <code>role.delete</code>
    </small>
</div>

<!-- ── FILTER ─────────────────────────────────────────────────── -->
<div class="card shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="get" class="mb-0">
            <div class="row align-items-end">
                <div class="col-md-4 mb-2 mb-md-0">
                    <label class="small font-weight-bold mb-1">Cari</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Nama / display name / module..."
                           value="<?= esc($filters['search'] ?? '') ?>">
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="small font-weight-bold mb-1">Module</label>
                    <select name="module" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">Semua Module</option>
                        <?php foreach ($modules as $mod): ?>
                            <option value="<?= esc($mod) ?>" <?= ($filters['module'] === $mod) ? 'selected' : '' ?>>
                                <?= esc($mod) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small font-weight-bold mb-1">&nbsp;</label>
                    <div class="d-flex" style="gap:6px;">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="<?= base_url('permissions') ?>" class="btn btn-outline-secondary btn-sm" title="Reset">
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
                        <th>Nama Permission</th>
                        <th>Display Name</th>
                        <th>Module</th>
                        <th>Deskripsi</th>
                        <th class="text-center" width="110">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($permissions)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                Belum ada data permission
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php
                        $offset = ($page - 1) * $perPage;
                        foreach ($permissions as $i => $p):
                        ?>
                        <tr id="row-<?= $p['id'] ?>">
                            <td class="text-muted"><?= $offset + $i + 1 ?></td>
                            <td><code class="text-primary"><?= esc($p['name']) ?></code></td>
                            <td class="font-weight-bold"><?= esc($p['display_name']) ?></td>
                            <td>
                                <span class="badge badge-info"><?= esc($p['module']) ?></span>
                            </td>
                            <td class="text-muted small" style="max-width:220px;">
                                <?= esc($p['description'] ?? '-') ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-warning"
                                            onclick="openEditModal(<?= $p['id'] ?>)" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger"
                                            onclick="hapusPermission(<?= $p['id'] ?>, '<?= esc($p['display_name']) ?>')"
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
                Menampilkan <?= $offset + 1 ?>–<?= min($offset + $perPage, $total) ?>
                dari <?= $total ?> permission
            </small>
            <?= $pager->links() ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- =========================================================
     MODAL TAMBAH
     ========================================================= -->
<div class="modal fade" id="modalCreate" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i>Tambah Permission</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="alert-create" class="alert alert-danger d-none small"></div>

                <div class="form-group">
                    <label>Nama Permission <span class="text-danger">*</span></label>
                    <input type="text" id="c_name" class="form-control"
                           placeholder="contoh: user.view, laporan.export" required>
                    <small class="text-muted">Format: <code>module.aksi</code> — huruf kecil, titik, underscore.</small>
                </div>
                <div class="form-group">
                    <label>Display Name <span class="text-danger">*</span></label>
                    <input type="text" id="c_display_name" class="form-control"
                           placeholder="contoh: Lihat User, Export Laporan" required>
                </div>
                <div class="form-group">
                    <label>Module <span class="text-danger">*</span></label>
                    <input type="text" id="c_module" class="form-control"
                           placeholder="contoh: user, laporan, role" required>
                    <small class="text-muted">Nama grup/module tempat permission ini berada.</small>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea id="c_description" class="form-control" rows="2"
                              placeholder="Deskripsi singkat..."></textarea>
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
     MODAL EDIT
     ========================================================= -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Edit Permission</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="alert-edit" class="alert alert-danger d-none small"></div>
                <input type="hidden" id="e_id">

                <div class="form-group">
                    <label>Nama Permission <span class="text-danger">*</span></label>
                    <input type="text" id="e_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Display Name <span class="text-danger">*</span></label>
                    <input type="text" id="e_display_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Module <span class="text-danger">*</span></label>
                    <input type="text" id="e_module" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea id="e_description" class="form-control" rows="2"></textarea>
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
$(document).ready(function () {

    const BASE  = '<?= rtrim(base_url(), '/') ?>';
    const CNAME = '<?= csrf_token() ?>';
    let   CHASH = '<?= csrf_hash() ?>';
    const upd   = h => { if (h) CHASH = h; };

    // ── Helpers ──────────────────────────────────────────────
    const gid  = id => document.getElementById(id);

    const show = (id, msg) => {
        const el = gid(id); if (!el) return;
        el.innerHTML = msg; el.classList.remove('d-none');
    };
    const hide = id => {
        const el = gid(id); if (!el) return;
        el.innerHTML = ''; el.classList.add('d-none');
    };
    const setBtn = (id, loading, label = 'Simpan') => {
        const btn = gid(id); if (!btn) return;
        btn.disabled  = loading;
        btn.innerHTML = loading
            ? '<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...'
            : `<i class="fas fa-save mr-1"></i>${label}`;
    };

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

    function resetModal(id) {
        $(id).removeAttr('aria-hidden').removeAttr('style').removeClass('show');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
    }

    // ── Auto-format ──────────────────────────────────────────
    gid('c_name')?.addEventListener('input', function () {
        this.value = this.value.toLowerCase().replace(/\s+/g, '.').replace(/[^a-z0-9._-]/g, '');
    });
    gid('c_module')?.addEventListener('input', function () {
        this.value = this.value.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_-]/g, '');
    });
    gid('e_name')?.addEventListener('input', function () {
        this.value = this.value.toLowerCase().replace(/\s+/g, '.').replace(/[^a-z0-9._-]/g, '');
    });
    gid('e_module')?.addEventListener('input', function () {
        this.value = this.value.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_-]/g, '');
    });

    // ── CREATE ───────────────────────────────────────────────
    window.openCreateModal = function () {
        resetModal('#modalCreate');
        ['c_name','c_display_name','c_module','c_description'].forEach(id => {
            const el = gid(id); if (el) el.value = '';
        });
        hide('alert-create');
        $('#modalCreate').modal({ backdrop: true, keyboard: true });
        $('#modalCreate').modal('show');
    };

    window.submitCreate = async function () {
        hide('alert-create');
        setBtn('btn-create', true);

        const fd = new FormData();
        fd.append('name',         gid('c_name').value.trim());
        fd.append('display_name', gid('c_display_name').value.trim());
        fd.append('module',       gid('c_module').value.trim());
        fd.append('description',  gid('c_description').value.trim());

        try {
            const data = await ajax(`${BASE}permissions/store`, fd);
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
    };

    // ── EDIT ─────────────────────────────────────────────────
    window.openEditModal = async function (id) {
        resetModal('#modalEdit');
        hide('alert-edit');

        try {
            const res  = await fetch(`${BASE}permissions/edit/${id}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const resp = await res.json();
            if (resp.status !== 'success') { alert(resp.message); return; }

            const d = resp.data;
            gid('e_id').value           = d.id;
            gid('e_name').value         = d.name;
            gid('e_display_name').value = d.display_name;
            gid('e_module').value       = d.module;
            gid('e_description').value  = d.description ?? '';

            $('#modalEdit').modal({ backdrop: true, keyboard: true });
            $('#modalEdit').modal('show');
        } catch (e) {
            alert('Gagal memuat data: ' + e.message);
        }
    };

    window.submitEdit = async function () {
        hide('alert-edit');
        setBtn('btn-edit', true, 'Update');

        const id = gid('e_id').value;
        const fd = new FormData();
        fd.append('name',         gid('e_name').value.trim());
        fd.append('display_name', gid('e_display_name').value.trim());
        fd.append('module',       gid('e_module').value.trim());
        fd.append('description',  gid('e_description').value.trim());

        try {
            const data = await ajax(`${BASE}permissions/update/${id}`, fd);
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
    };

    // ── DELETE ───────────────────────────────────────────────
    window.hapusPermission = async function (id, nama) {
        if (!confirm(`Hapus permission "${nama}"?\nPermission yang masih dipakai role tidak bisa dihapus.`)) return;

        try {
            const fd = new FormData();
            const data = await ajax(`${BASE}permissions/delete/${id}`, fd);
            if (data.status === 'success') {
                const row = document.getElementById(`row-${id}`);
                if (row) row.remove();
            } else {
                alert(data.message);
            }
        } catch (e) {
            alert('Error: ' + e.message);
        }
    };

    // ── Cleanup modal backdrop ────────────────────────────────
    $(document).on('hidden.bs.modal', '.modal', function () {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
        $(this).removeAttr('aria-hidden');
    });

    // ── Auto hide flash ───────────────────────────────────────
    setTimeout(() => {
        document.querySelectorAll('.alert-dismissible').forEach(el => {
            $(el).alert('close');
        });
    }, 5000);

});
</script>

<?= $this->endSection() ?>