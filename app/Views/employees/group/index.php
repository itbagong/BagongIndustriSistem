<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

        <!-- Content -->
        <main class="content">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <span class="icon">👥</span>
                    <h1>Manajemen Group</h1>
                </div>
                <div class="header-actions">
                    <button class="btn btn-primary" id="btnTambah">
                        <span class="btn-icon">➕</span>
                        Tambah Group
                    </button>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success">✅ <?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger">❌ <?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <!-- Search Bar -->
            <div class="search-filter-bar" style="margin-bottom:20px;">
                <form method="GET" action="" id="searchForm" style="display:flex; gap:12px; align-items:flex-end;">
                    <div class="form-group" style="flex:1; margin:0;">
                        <label>🔍 Cari</label>
                        <input type="text" class="form-control" name="search" id="searchInput"
                               placeholder="Cari name..."
                               value="<?= esc($search) ?>">
                    </div>
                    <button type="submit" class="btn btn-info">
                        <span class="btn-icon">🔍</span> Cari
                    </button>
                    <?php if ($search): ?>
                        <a href="?" class="btn btn-secondary">✖ Reset</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Table Card -->
            <div class="table-card">
                <div class="table-header">
                    <h3>Daftar Group</h3>
                    <div class="table-info">
                        Total <strong><?= $pager->getTotal() ?></strong> data
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="employee-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Deskripsi</th>
                                <th>Aliases</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($groups)): ?>
                                <?php foreach ($groups as $i => $es):
                                    $isDeleted = (
                                        $es['is_deleted'] === true ||
                                        $es['is_deleted'] === 't'  ||
                                        $es['is_deleted'] === '1'  ||
                                        $es['is_deleted'] === 1
                                    );
                                ?>
                                <tr>
                                    <td><?= ($pager->getCurrentPage() - 1) * $perPage + $i + 1 ?></td>
                                    <td><code><?= esc($es['id']) ?></code></td>
                                    <td><strong><?= esc($es['name']) ?></strong></td>
                                    <td style="color:#666; font-size:.9em; max-width:300px;">
                                        <?= esc($es['description'] ?? '-') ?>
                                    </td>
                                    <td>
                                        <?php
                                        $aliases = is_array($es['aliases']) ? $es['aliases'] : [];
                                        foreach ($aliases as $alias): ?>
                                            <span class="badge bg-info text-dark"><?= esc(trim($alias)) ?></span>
                                        <?php endforeach; ?>
                                    </td>
                                    <td>
                                        <?php if ($isDeleted): ?>
                                            <span class="status-badge status-inactive">⏸️ Non-Aktif</span>
                                        <?php else: ?>
                                            <span class="status-badge status-active">✅ Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-warning btn-sm btn-edit"
                                                data-id="<?= esc($es['id']) ?>"
                                                data-name="<?= esc($es['name']) ?>"
                                                data-description="<?= esc($es['description'] ?? '') ?>"
                                                data-aliases="<?= esc(json_encode(is_array($es['aliases']) ? $es['aliases'] : [])) ?>">
                                                ✏️ Edit
                                            </button>

                                            <?php if ($isDeleted): ?>
                                                <button class="btn btn-success btn-sm btn-toggle"
                                                    data-id="<?= esc($es['id']) ?>"
                                                    data-name="<?= esc($es['name']) ?>"
                                                    data-action="enable">
                                                    ✅ Aktifkan
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-danger btn-sm btn-toggle"
                                                    data-id="<?= esc($es['id']) ?>"
                                                    data-name="<?= esc($es['name']) ?>"
                                                    data-action="disable">
                                                    ⏸️ Nonaktifkan
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align:center; padding:40px; color:#999;">
                                        Belum ada data employment status. Klik "Tambah" untuk menambahkan.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <?php if ($pager): ?>
                    <div class="pager-wrapper">
                        <?= $pager->links() ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>

    </div><!-- end layout wrapper -->

    <!-- ═══════════════════════════════════════════════
         Add / Edit Modal
    ═══════════════════════════════════════════════ -->
    <div id="groupModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999; align-items:center; justify-content:center;">
        <div class="modal-content" style="background:#fff; border-radius:12px; width:100%; max-width:520px; margin:auto;">
            <div class="modal-header">
                <h2 id="groupModalTitle">➕ Tambah Group</h2>
                <button class="modal-close" id="btnCloseGroupModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="groupForm" method="POST" action="">
                    <?= csrf_field() ?>
                    <input type="hidden" id="oldGroupId" name="old_id">

                    <div class="form-group">
                        <label>ID <span class="required">*</span></label>
                        <input type="text" class="form-control" id="groupId" name="id" placeholder="GRO-BDM-...">
                        <div id="idError" style="color:#dc3545; font-size:.85em; margin-top:4px; display:none;"></div>
                    </div>

                    <div class="form-group">
                        <label>Nama <span class="required">*</span></label>
                        <input type="text" class="form-control" id="groupName" name="name" placeholder="contoh: Direktur, Admin...">
                        <div id="nameError" style="color:#dc3545; font-size:.85em; margin-top:4px; display:none;"></div>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea class="form-control" id="groupDescription" name="description" rows="3" placeholder="Deskripsi golongan (opsional)"></textarea>
                    </div>

                    <div class="form-group" style="margin-top: 15px;">
                        <label>Identitas Tambahan</label>
                        <button type="button" id="btnManageAliases" class="btn btn-secondary" style="width: 100%; display: flex; justify-content: center; align-items: center; gap: 8px; background: #f8f9fa; color: #333; border: 1px solid #ddd;">
                            <span>🔍</span> Lihat & Edit Aliases
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="btnCancelGroupModal">Batal</button>
                <button class="btn btn-primary" id="btnSaveGroupModal">
                    <span class="btn-icon">💾</span> Simpan
                </button>
            </div>
        </div>
    </div>

    <div id="aliasModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:10000; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:8px; width:100%; max-width:400px; padding:20px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <h3>Manage Aliases</h3>
            
            <div id="aliasList" style="margin: 15px 0; max-height: 200px; overflow-y: auto; border: 1px solid #eee; padding: 10px; border-radius: 5px;">
                </div>

            <div class="form-group">
                <input type="text" id="newAliasInput" class="form-control" placeholder="Type new alias...">
                <div id="aliasError" style="color:#dc3545; font-size:.85em; display:none; margin-top:5px;"></div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button class="btn btn-secondary" id="btnCloseAliasModal">Done</button>
                <button class="btn btn-primary" id="btnAddAlias">Add</button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════
         Toggle Confirm Modal
    ═══════════════════════════════════════════════ -->
    <div id="toggleModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999; align-items:center; justify-content:center;">
        <div class="modal-content modal-small" style="background:#fff; border-radius:12px; width:100%; max-width:420px; margin:auto;">
            <div class="modal-header">
                <h2 id="toggleModalTitle">⚠️ Konfirmasi</h2>
                <button class="modal-close" id="btnCloseToggleModal">&times;</button>
            </div>
            <div class="modal-body">
                <p id="toggleModalMessage"></p>
                <p><strong id="toggleModalName"></strong></p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="btnCancelToggleModal">Batal</button>
                <form id="toggleForm" method="POST" action="" style="display:inline;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn" id="toggleConfirmBtn">Konfirmasi</button>
                </form>
            </div>
        </div>
    </div>

    <style>
        .status-badge    { display:inline-flex; align-items:center; gap:4px; padding:4px 12px; border-radius:20px; font-size:.8em; font-weight:600; }
        .status-active   { background:#d4edda; color:#155724; }
        .status-inactive { background:#fff3cd; color:#856404; }
        .action-buttons  { display:flex; gap:6px; flex-wrap:wrap; }
        .btn-sm          { padding:5px 12px; font-size:.82em; }
        .alert           { padding:12px 16px; border-radius:8px; margin-bottom:20px; font-weight:500; }
        .alert-success   { background:#d4edda; border:1px solid #c3e6cb; color:#155724; }
        .alert-danger    { background:#f8d7da; border:1px solid #f5c6cb; color:#721c24; }
        code             { font-family:monospace; background:#f1f3f5; padding:2px 8px; border-radius:4px; font-size:.85em; color:#555; }

        .pager-wrapper { padding:14px 20px; border-top:1px solid #e9ecef; display:flex; justify-content:flex-end; }
        .pagination { display:flex; gap:4px; list-style:none; margin:0; padding:0; flex-wrap:wrap; }
        .pagination li a, .pagination li span { display:inline-flex; align-items:center; justify-content:center; min-width:36px; height:36px; padding:0 10px; border-radius:8px; font-size:.85em; font-weight:500; border:1px solid #dee2e6; color:#495057; text-decoration:none; background:#fff; transition:all .15s; }
        .pagination li a:hover { background:#f1f3f5; border-color:#adb5bd; }
        .pagination li.active span { background:#0d6efd; color:#fff; border-color:#0d6efd; }
        .pagination li.disabled span { color:#adb5bd; background:#f8f9fa; }
        .search-filter-bar label { display:block; font-size:.8rem; font-weight:600; margin-bottom:6px; color:#555; }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function () {

        const BASE_URL = '<?= base_url() ?>';

        function showModal(el) { el.style.display = 'flex'; }
        function hideModal(el) { el.style.display = 'none'; }

        // ── Elements ──────────────────────────────────────────
        const groupModal      = document.getElementById('groupModal');
        const groupForm       = document.getElementById('groupForm');
        const groupTitle      = document.getElementById('groupModalTitle');
        const groupId         = document.getElementById('groupId');
        const oldGroupId      = document.getElementById('oldGroupId');
        const groupName       = document.getElementById('groupName');
        const groupDesc       = document.getElementById('groupDescription');
        const nameError    = document.getElementById('nameError');

        const toggleModal   = document.getElementById('toggleModal');
        const toggleForm    = document.getElementById('toggleForm');
        const toggleTitle   = document.getElementById('toggleModalTitle');
        const toggleMessage = document.getElementById('toggleModalMessage');
        const toggleName    = document.getElementById('toggleModalName');
        const toggleConfirm = document.getElementById('toggleConfirmBtn');

        let currentAliases = [];

        // --- Open Sub-Modal ---
        document.getElementById('btnManageAliases').addEventListener('click', () => {
            renderAliasList();
            document.getElementById('aliasModal').style.display = 'flex';
        });

        // --- Add Alias with Duplicate Handling ---
        document.getElementById('btnAddAlias').addEventListener('click', () => {
            const input = document.getElementById('newAliasInput');
            const error = document.getElementById('aliasError');
            const val = input.value.trim();

            if (!val) return;

            // Check for duplicates (case insensitive)
            if (currentAliases.some(a => a.toLowerCase() === val.toLowerCase())) {
                error.innerText = "Alias sudah ada!";
                error.style.display = 'block';
                return;
            }

            currentAliases.push(val);
            input.value = '';
            error.style.display = 'none';
            renderAliasList();
        });

        // --- Render List with Delete Option ---
        // 1. Updated Render Function (Removed the 'onclick' attribute)
        function renderAliasList() {
            const container = document.getElementById('aliasList');
            container.innerHTML = currentAliases.length === 0 ? '<p class="text-muted">Belum ada alias.</p>' : '';
            
            currentAliases.forEach((alias, index) => {
                const item = document.createElement('div');
                item.style = "display:flex; justify-content:space-between; align-items:center; padding:8px; border-bottom:1px solid #f0f0f0;";
                item.innerHTML = `
                    <span>${alias}</span>
                    <button type="button" class="delete-alias-btn" data-index="${index}" style="background:none; border:none; color:#dc3545; cursor:pointer; font-weight:bold; font-size:1.2em;">
                        &times;
                    </button>
                `;
                container.appendChild(item);
            });

            // Update the counter on the main modal
            const info = document.getElementById('aliasCountInfo');
            if(info) info.innerText = `${currentAliases.length} alias ditambahkan.`;
        }

        // 2. The Global Event Listener (Add this once in your script)
        document.getElementById('aliasList').addEventListener('click', function(e) {
            // Check if the clicked element is a delete button (or inside one)
            if (e.target.classList.contains('delete-alias-btn')) {
                const index = e.target.getAttribute('data-index');
                
                // Remove from the array
                currentAliases.splice(index, 1);
                
                // Refresh the UI
                renderAliasList();
            }
        });

        // Close sub-modal
        document.getElementById('btnCloseAliasModal').addEventListener('click', () => {
            document.getElementById('aliasModal').style.display = 'none';
        });

        // ── Tambah ───────────────────────────────────────────
        document.getElementById('btnTambah').addEventListener('click', function () {
            groupTitle.textContent     = '➕ Tambah Group';
            oldGroupId.value           = '';
            groupId.value              = '';
            groupName.value            = '';
            groupDesc.value            = '';
            currentAliases             = [];
            renderAliasList();
            groupForm.action           = BASE_URL + 'employees/group/store';
            nameError.style.display = 'none';
            showModal(groupModal);
        });

        // ── Edit ─────────────────────────────────────────────
        document.querySelectorAll('.btn-edit').forEach(function (btn) {
            btn.addEventListener('click', function () {
                groupTitle.textContent     = '✏️ Edit Group';
                oldGroupId.value                 = this.dataset.id;
                groupId.value              = this.dataset.id;
                groupName.value            = this.dataset.name;
                groupDesc.value            = this.dataset.description;
                currentAliases                = JSON.parse(this.dataset.aliases || '[]');
                renderAliasList();              // ← populate the list UI
                groupForm.action           = BASE_URL + 'employees/group/update/' + this.dataset.id;
                nameError.style.display = 'none';
                showModal(groupModal);
            });
        });

        // ── Save ─────────────────────────────────────────────
        document.getElementById('btnSaveGroupModal').addEventListener('click', function () {
            // Inject aliases as a hidden field before submitting
            const existingAliasInput = document.getElementById('aliasesInput');
            if (existingAliasInput) existingAliasInput.remove();

            const aliasInput = document.createElement('input');
            aliasInput.type  = 'hidden';
            aliasInput.name  = 'aliases';
            aliasInput.id    = 'aliasesInput';
            aliasInput.value = JSON.stringify(currentAliases);
            groupForm.appendChild(aliasInput);

            groupForm.submit();
        });

        // ── Close Add/Edit Modal ──────────────────────────────
        document.getElementById('btnCloseGroupModal').addEventListener('click',  function () { hideModal(groupModal); });
        document.getElementById('btnCancelGroupModal').addEventListener('click', function () { hideModal(groupModal); });
        groupModal.addEventListener('click', function (e) { if (e.target === groupModal) hideModal(groupModal); });

        // ── Toggle Buttons ────────────────────────────────────
        document.querySelectorAll('.btn-toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                toggleForm.action      = BASE_URL + 'employees/group/toggle/' + this.dataset.id;
                toggleName.textContent = this.dataset.name;

                if (this.dataset.action === 'disable') {
                    toggleTitle.textContent      = '⏸️ Konfirmasi Nonaktifkan';
                    toggleMessage.textContent    = 'Apakah Anda yakin ingin menonaktifkan employment status:';
                    toggleConfirm.className      = 'btn btn-danger';
                    toggleConfirm.textContent    = '⏸️ Nonaktifkan';
                } else {
                    toggleTitle.textContent      = '✅ Konfirmasi Aktifkan';
                    toggleMessage.textContent    = 'Apakah Anda yakin ingin mengaktifkan kembali employment status:';
                    toggleConfirm.className      = 'btn btn-success';
                    toggleConfirm.textContent    = '✅ Aktifkan';
                }

                showModal(toggleModal);
            });
        });

        // ── Close Toggle Modal ────────────────────────────────
        document.getElementById('btnCloseToggleModal').addEventListener('click',  function () { hideModal(toggleModal); });
        document.getElementById('btnCancelToggleModal').addEventListener('click', function () { hideModal(toggleModal); });
        toggleModal.addEventListener('click', function (e) { if (e.target === toggleModal) hideModal(toggleModal); });

    });
    </script>

<?= $this->endSection() ?>