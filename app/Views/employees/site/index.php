<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

        <!-- Content -->
        <main class="content">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <span class="icon">🗺️</span>
                    <h1>Manajemen Site</h1>
                </div>
                <div class="header-actions">
                    <button class="btn btn-primary" id="btnTambah">
                        <span class="btn-icon">➕</span>
                        Tambah Site
                    </button>
                </div>
            </div>

            <!-- Stats Summary -->
            <div class="stats-summary">
                <div class="stat-box">
                    <span class="icon">🗺️</span>
                    <div class="content">
                        <h4>Total Site</h4>
                        <div class="value"><?= count($sites) ?></div>
                    </div>
                </div>
                <div class="stat-box">
                    <span class="icon">✅</span>
                    <div class="content">
                        <h4>Aktif</h4>
                        <div class="value">
                            <?= count(array_filter($sites, fn($es) => !($es['is_deleted'] === true || $es['is_deleted'] === 't' || $es['is_deleted'] === '1' || $es['is_deleted'] === 1))) ?>
                        </div>
                    </div>
                </div>
                <div class="stat-box">
                    <span class="icon">⏸️</span>
                    <div class="content">
                        <h4>Non-Aktif</h4>
                        <div class="value">
                            <?= count(array_filter($sites, fn($es) => ($es['is_deleted'] === true || $es['is_deleted'] === 't' || $es['is_deleted'] === '1' || $es['is_deleted'] === 1))) ?>
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
                    <h3>Daftar Site</h3>
                    <div class="table-info">
                        Menampilkan <strong><?= count($sites) ?></strong> dari <strong><?= $pager->getTotal() ?></strong> data
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="employee-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Alamat</th>
                                <th>Deskripsi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($sites)): ?>
                                <?php foreach ($sites as $i => $es):
                                    $isDeleted = (
                                        $es['is_deleted'] === true ||
                                        $es['is_deleted'] === 't'  ||
                                        $es['is_deleted'] === '1'  ||
                                        $es['is_deleted'] === 1
                                    );
                                ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><code><?= esc($es['id']) ?></code></td>
                                    <td><strong><?= esc($es['name']) ?></strong></td>
                                    <td style="color:#666; font-size:.9em;"><?= esc($es['address'] ?? '-') ?></td>
                                    <td style="color:#666; font-size:.9em; max-width:260px;"><?= esc($es['description'] ?? '-') ?></td>
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
                                                data-address="<?= esc($es['address'] ?? '') ?>"
                                                data-description="<?= esc($es['description'] ?? '') ?>">
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
                                    <td colspan="7" style="text-align:center; padding:40px; color:#999;">
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
    <div id="siteModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999; align-items:center; justify-content:center;">
        <div class="modal-content" style="background:#fff; border-radius:12px; width:100%; max-width:520px; margin:auto;">
            <div class="modal-header">
                <h2 id="siteModalTitle">➕ Tambah Site</h2>
                <button class="modal-close" id="btnCloseSiteModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="siteForm" method="POST" action="">
                    <?= csrf_field() ?>
                    <input type="hidden" id="siteId" name="id">

                    <div class="form-group">
                        <label>Nama <span class="required">*</span></label>
                        <input type="text" class="form-control" id="siteName" name="name" placeholder="contoh: Permanen, PKWT, Trainee">
                        <div id="nameError" style="color:#dc3545; font-size:.85em; margin-top:4px; display:none;"></div>
                    </div>

                    <div class="form-group">
                        <label>Alamat</label>
                        <input type="text" class="form-control" id="siteAddress" name="address" placeholder="Alamat site">
                    </div>

                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea class="form-control" id="siteDescription" name="description" rows="3" placeholder="Deskripsi site (opsional)"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="btnCancelSiteModal">Batal</button>
                <button class="btn btn-primary" id="btnSaveSiteModal">
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
        .status-badge    { display:inline-flex; align-items:center; gap:4px; padding:4px 12px; border-radius:20px; font-size:.8em; font-weight:600; }
        .status-active   { background:#d4edda; color:#155724; }
        .status-inactive { background:#fff3cd; color:#856404; }
        .action-buttons  { display:flex; gap:6px; flex-wrap:wrap; }
        .btn-sm          { padding:5px 12px; font-size:.82em; }
        .alert           { padding:12px 16px; border-radius:8px; margin-bottom:20px; font-weight:500; }
        .alert-success   { background:#d4edda; border:1px solid #c3e6cb; color:#155724; }
        .alert-danger    { background:#f8d7da; border:1px solid #f5c6cb; color:#721c24; }
        code             { font-family:monospace; background:#f1f3f5; padding:2px 8px; border-radius:4px; font-size:.85em; color:#555; }
        .pager-wrapper {
            padding: 16px 20px;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: flex-end;
        }

        /* CI4 default pager uses <ul class="pagination"> */
        .pagination {
            display: flex;
            gap: 4px;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .pagination li a,
        .pagination li span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 10px;
            border-radius: 8px;
            font-size: .85em;
            font-weight: 500;
            border: 1px solid #dee2e6;
            color: #495057;
            text-decoration: none;
            background: #fff;
            transition: all .15s;
        }
        .pagination li a:hover        { background: #f1f3f5; border-color: #adb5bd; }
        .pagination li.active span    { background: #0d6efd; color: #fff; border-color: #0d6efd; }
        .pagination li.disabled span  { color: #adb5bd; background: #f8f9fa; }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function () {

        const BASE_URL = '<?= base_url() ?>';

        function showModal(el) { el.style.display = 'flex'; }
        function hideModal(el) { el.style.display = 'none'; }

        // ── Elements ──────────────────────────────────────────
        const siteModal      = document.getElementById('siteModal');
        const siteForm       = document.getElementById('siteForm');
        const siteTitle      = document.getElementById('siteModalTitle');
        const siteId         = document.getElementById('siteId');
        const siteName       = document.getElementById('siteName');
        const siteAddress    = document.getElementById('siteAddress');
        const siteDesc       = document.getElementById('siteDescription');
        const nameError    = document.getElementById('nameError');

        const toggleModal   = document.getElementById('toggleModal');
        const toggleForm    = document.getElementById('toggleForm');
        const toggleTitle   = document.getElementById('toggleModalTitle');
        const toggleMessage = document.getElementById('toggleModalMessage');
        const toggleName    = document.getElementById('toggleModalName');
        const toggleConfirm = document.getElementById('toggleConfirmBtn');

        // ── Tambah ───────────────────────────────────────────
        document.getElementById('btnTambah').addEventListener('click', function () {
            siteTitle.textContent     = '➕ Tambah Site';
            siteId.value              = '';
            siteName.value            = '';
            siteAddress.value         = '';
            siteDesc.value            = '';
            siteForm.action           = BASE_URL + 'employees/site/store';
            nameError.style.display = 'none';
            showModal(siteModal);
        });

        // ── Edit ─────────────────────────────────────────────
        document.querySelectorAll('.btn-edit').forEach(function (btn) {
            btn.addEventListener('click', function () {
                siteTitle.textContent     = '✏️ Edit Site';
                siteId.value              = this.dataset.id;
                siteName.value            = this.dataset.name;
                siteAddress.value         = this.dataset.address;
                siteDesc.value            = this.dataset.description;
                siteForm.action           = BASE_URL + 'employees/site/update/' + this.dataset.id;
                nameError.style.display = 'none';
                showModal(siteModal);
            });
        });

        // ── Save ─────────────────────────────────────────────
        document.getElementById('btnSaveSiteModal').addEventListener('click', function () {
            if (!siteName.value.trim()) {
                nameError.textContent   = '⚠️ Nama employment status wajib diisi.';
                nameError.style.display = 'block';
                return;
            }
            nameError.style.display = 'none';
            siteForm.submit();
        });

        // ── Close Add/Edit Modal ──────────────────────────────
        document.getElementById('btnCloseSiteModal').addEventListener('click',  function () { hideModal(siteModal); });
        document.getElementById('btnCancelSiteModal').addEventListener('click', function () { hideModal(siteModal); });
        siteModal.addEventListener('click', function (e) { if (e.target === siteModal) hideModal(siteModal); });

        // ── Toggle Buttons ────────────────────────────────────
        document.querySelectorAll('.btn-toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                toggleForm.action      = BASE_URL + 'employees/site/toggle/' + this.dataset.id;
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