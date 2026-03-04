<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

        <!-- Content -->
        <main class="content">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <span class="icon">🩸</span>
                    <h1>Manajemen Golongan Darah</h1>
                </div>
                <div class="header-actions">
                    <button class="btn btn-primary" id="btnTambah">
                        <span class="btn-icon">➕</span>
                        Tambah Golongan Darah
                    </button>
                </div>
            </div>

            <!-- Stats Summary -->
            <div class="stats-summary">
                <div class="stat-box">
                    <span class="icon">🩸</span>
                    <div class="content">
                        <h4>Total Golongan Darah</h4>
                        <div class="value"><?= $pager->getTotal() ?></div>
                    </div>
                </div>
                <div class="stat-box">
                    <span class="icon">✅</span>
                    <div class="content">
                        <h4>Aktif</h4>
                        <div class="value">
                            <?= count(array_filter($bloodTypes, fn($bt) => !($bt['is_deleted'] === true || $bt['is_deleted'] === 't' || $bt['is_deleted'] === '1' || $bt['is_deleted'] === 1))) ?>
                        </div>
                    </div>
                </div>
                <div class="stat-box">
                    <span class="icon">⏸️</span>
                    <div class="content">
                        <h4>Non-Aktif</h4>
                        <div class="value">
                            <?= count(array_filter($bloodTypes, fn($bt) => ($bt['is_deleted'] === true || $bt['is_deleted'] === 't' || $bt['is_deleted'] === '1' || $bt['is_deleted'] === 1))) ?>
                        </div>
                    </div>
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
                    <h3>Daftar Golongan Darah</h3>
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
                                <th>Nama Golongan Darah</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($bloodTypes)): ?>
                                <?php foreach ($bloodTypes as $i => $bt):
                                    $isDeleted = ($bt['is_deleted'] === true || $bt['is_deleted'] === 't' || $bt['is_deleted'] === '1' || $bt['is_deleted'] === 1);
                                ?>
                                <tr>
                                    <td><?= ($pager->getCurrentPage() - 1) * $perPage + $i + 1 ?></td>
                                    <td><code><?= esc($bt['id']) ?></code></td>
                                    <td><strong><?= esc($bt['name']) ?></strong></td>
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
                                                data-id="<?= esc($bt['id']) ?>"
                                                data-name="<?= esc($bt['name']) ?>">
                                                ✏️ Edit
                                            </button>

                                            <?php if ($isDeleted): ?>
                                                <button class="btn btn-success btn-sm btn-toggle"
                                                    data-id="<?= esc($bt['id']) ?>"
                                                    data-name="<?= esc($bt['name']) ?>"
                                                    data-action="enable">
                                                    ✅ Aktifkan
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-danger btn-sm btn-toggle"
                                                    data-id="<?= esc($bt['id']) ?>"
                                                    data-name="<?= esc($bt['name']) ?>"
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
                                    <td colspan="5" style="text-align:center; padding: 40px; color: #999;">
                                        Belum ada data golongan darah. Klik "Tambah" untuk menambahkan.
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
    <div id="bloodTypeModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999; align-items:center; justify-content:center;">
        <div class="modal-content modal-small" style="background:#fff; border-radius:12px; width:100%; max-width:460px; margin:auto;">
            <div class="modal-header">
                <h2 id="btModalTitle">➕ Tambah Golongan Darah</h2>
                <button class="modal-close" id="btnCloseBtModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="bloodTypeForm" method="POST" action="">
                    <?= csrf_field() ?>
                    <input type="hidden" id="bloodTypeId" name="id">
                    <div class="form-group">
                        <label>Nama Golongan Darah <span class="required">*</span></label>
                        <input type="text" class="form-control" id="bloodTypeName" name="name" placeholder="contoh: A+, AB-, O+">
                        <div id="nameError" style="color:#dc3545; font-size:.85em; margin-top:5px; display:none;"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="btnCancelBtModal">Batal</button>
                <button class="btn btn-primary" id="btnSaveBtModal">
                    <span class="btn-icon">💾</span> Simpan
                </button>
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
        .status-badge { display:inline-flex; align-items:center; gap:4px; padding:4px 12px; border-radius:20px; font-size:.8em; font-weight:600; }
        .status-active   { background:#d4edda; color:#155724; }
        .status-inactive { background:#fff3cd; color:#856404; }
        .action-buttons  { display:flex; gap:6px; flex-wrap:wrap; }
        .btn-sm { padding:5px 12px; font-size:.82em; }
        .alert { padding:12px 16px; border-radius:8px; margin-bottom:20px; font-weight:500; }
        .alert-success { background:#d4edda; border:1px solid #c3e6cb; color:#155724; }
        .alert-danger  { background:#f8d7da; border:1px solid #f5c6cb; color:#721c24; }
        code { font-family:monospace; background:#f1f3f5; padding:2px 8px; border-radius:4px; font-size:.85em; color:#555; }

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

        // ── Helpers ──────────────────────────────────────────
        function showModal(el) {
            el.style.display = 'flex';
        }
        function hideModal(el) {
            el.style.display = 'none';
        }

        // ── Elements ─────────────────────────────────────────
        const btModal       = document.getElementById('bloodTypeModal');
        const btForm        = document.getElementById('bloodTypeForm');
        const btModalTitle  = document.getElementById('btModalTitle');
        const btId          = document.getElementById('bloodTypeId');
        const btName        = document.getElementById('bloodTypeName');
        const nameError     = document.getElementById('nameError');

        const toggleModal   = document.getElementById('toggleModal');
        const toggleForm    = document.getElementById('toggleForm');
        const toggleTitle   = document.getElementById('toggleModalTitle');
        const toggleMessage = document.getElementById('toggleModalMessage');
        const toggleName    = document.getElementById('toggleModalName');
        const toggleConfirm = document.getElementById('toggleConfirmBtn');

        // ── Tambah Button ─────────────────────────────────────
        document.getElementById('btnTambah').addEventListener('click', function () {
            btModalTitle.textContent = '➕ Tambah Golongan Darah';
            btId.value   = '';
            btName.value = '';
            btForm.action = BASE_URL + 'employees/blood-type/store';
            nameError.style.display = 'none';
            showModal(btModal);
        });

        // ── Edit Buttons ──────────────────────────────────────
        document.querySelectorAll('.btn-edit').forEach(function (btn) {
            btn.addEventListener('click', function () {
                btModalTitle.textContent = '✏️ Edit Golongan Darah';
                btId.value   = this.dataset.id;
                btName.value = this.dataset.name;
                btForm.action = BASE_URL + 'employees/blood-type/update/' + this.dataset.id;
                nameError.style.display = 'none';
                showModal(btModal);
            });
        });

        // ── Save ──────────────────────────────────────────────
        document.getElementById('btnSaveBtModal').addEventListener('click', function () {
            if (!btName.value.trim()) {
                nameError.textContent   = '⚠️ Nama golongan darah wajib diisi.';
                nameError.style.display = 'block';
                return;
            }
            nameError.style.display = 'none';
            btForm.submit();
        });

        // ── Close Add/Edit Modal ──────────────────────────────
        document.getElementById('btnCloseBtModal').addEventListener('click',   function () { hideModal(btModal); });
        document.getElementById('btnCancelBtModal').addEventListener('click',  function () { hideModal(btModal); });
        btModal.addEventListener('click', function (e) { if (e.target === btModal) hideModal(btModal); });

        // ── Toggle Buttons ────────────────────────────────────
        document.querySelectorAll('.btn-toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const id     = this.dataset.id;
                const name   = this.dataset.name;
                const action = this.dataset.action;

                toggleForm.action      = BASE_URL + 'employees/blood-type/toggle/' + id;
                toggleName.textContent = name;

                if (action === 'disable') {
                    toggleTitle.textContent      = '⏸️ Konfirmasi Nonaktifkan';
                    toggleMessage.textContent    = 'Apakah Anda yakin ingin menonaktifkan golongan darah:';
                    toggleConfirm.className      = 'btn btn-danger';
                    toggleConfirm.textContent    = '⏸️ Nonaktifkan';
                } else {
                    toggleTitle.textContent      = '✅ Konfirmasi Aktifkan';
                    toggleMessage.textContent    = 'Apakah Anda yakin ingin mengaktifkan kembali golongan darah:';
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