<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<style>
    /* ── Page layout ── */
    .emp-page { padding: 24px; }

    /* ── Header ── */
    .page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
    .page-header h1 { font-size:1.5rem; font-weight:700; color:#111827; margin:0; }
    .page-header p  { font-size:.85rem; color:#6b7280; margin:4px 0 0; }
    .header-actions { display:flex; gap:10px; flex-wrap:wrap; }

    /* ── Stat cards ── */
    .stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:14px; margin-bottom:24px; }
    .stat-card {
        background:#fff; border:1px solid #e5e7eb; border-radius:12px;
        padding:16px 20px; display:flex; align-items:center; gap:14px;
    }
    .stat-card .icon { font-size:1.6rem; }
    .stat-card h4 { font-size:.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:.05em; margin:0 0 4px; }
    .stat-card .val { font-size:1.5rem; font-weight:700; color:#111827; line-height:1; }

    /* ── Filter bar ── */
    .filter-bar {
        background:#fff; border:1px solid #e5e7eb; border-radius:12px;
        padding:16px 20px; margin-bottom:20px;
        display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;
    }
    .filter-bar .fg { display:flex; flex-direction:column; gap:5px; flex:1; min-width:160px; }
    .filter-bar label { font-size:.75rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.04em; }
    .filter-bar select,
    .filter-bar input  { padding:7px 10px; border:1px solid #d1d5db; border-radius:8px; font-size:.875rem; color:#374151; background:#f9fafb; outline:none; }
    .filter-bar select:focus,
    .filter-bar input:focus  { border-color:#6366f1; background:#fff; box-shadow:0 0 0 3px rgba(99,102,241,.1); }

    /* ── Table card ── */
    .table-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; }
    .table-card-header { padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; }
    .table-card-header h3 { font-size:1rem; font-weight:600; color:#111827; margin:0; }

    /* ── DataTable overrides ── */
    #employeeTable_wrapper .dataTables_length,
    #employeeTable_wrapper .dataTables_filter { padding:12px 20px 0; }
    #employeeTable_wrapper .dataTables_info,
    #employeeTable_wrapper .dataTables_paginate { padding:12px 20px; }

    table.dataTable thead th {
        background:#f9fafb; color:#6b7280; font-size:.75rem;
        text-transform:uppercase; letter-spacing:.05em; font-weight:600;
        padding:11px 14px; border-bottom:1px solid #e5e7eb; white-space:nowrap;
    }
    table.dataTable tbody td { padding:11px 14px; font-size:.875rem; color:#374151; vertical-align:middle; }
    table.dataTable tbody tr:hover { background:#f9fafb; }
    table.dataTable tbody tr.odd  { background:#fff; }
    table.dataTable tbody tr.even { background:#fafafa; }
    /* Fixed action column styling */
    table.dataTable tbody td.dtfc-fixed-right,
    table.dataTable thead th.dtfc-fixed-right {
        background: #fff;
        box-shadow: -3px 0 6px rgba(0,0,0,.06);
    }
    table.dataTable tbody tr:hover td.dtfc-fixed-right {
        background: #f9fafb;
    }
    table.dataTable tbody tr.odd  td.dtfc-fixed-right { background: #fff; }
    table.dataTable tbody tr.even td.dtfc-fixed-right { background: #fafafa; }

    /* ── Badges ── */
    .badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:999px; font-size:.75rem; font-weight:600; white-space:nowrap; }
    .badge-active   { background:#d1fae5; color:#065f46; }
    .badge-inactive { background:#fef9c3; color:#854d0e; }
    .badge-pkwt     { background:#dbeafe; color:#1e40af; }
    .badge-pks      { background:#ede9fe; color:#5b21b6; }
    .badge-gray     { background:#f3f4f6; color:#6b7280; }

    /* ── Action buttons ── */
    .btn-action { display:inline-flex; align-items:center; gap:5px; padding:5px 11px; border-radius:7px; font-size:.78rem; font-weight:600; cursor:pointer; border:none; transition:all .15s; }
    .btn-view   { background:#eff6ff; color:#2563eb; }
    .btn-edit   { background:#fefce8; color:#ca8a04; }
    .btn-delete { background:#fef2f2; color:#dc2626; }
    .btn-view:hover   { background:#dbeafe; }
    .btn-edit:hover   { background:#fef08a; }
    .btn-delete:hover { background:#fecaca; }

    /* ── Top buttons ── */
    .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:9px; font-size:.875rem; font-weight:600; cursor:pointer; border:none; transition:all .15s; text-decoration:none; }
    .btn-primary { background:#4f46e5; color:#fff; }
    .btn-primary:hover { background:#4338ca; }
    .btn-success { background:#059669; color:#fff; }
    .btn-success:hover { background:#047857; }
    .btn-info    { background:#0891b2; color:#fff; }
    .btn-info:hover { background:#0e7490; }

    /* ── Detail modal ── */
    #detailModal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:9999; align-items:center; justify-content:center; padding:20px; }
    .detail-box  {
        background:#fff;
        border-radius:14px;
        width:100%;
        max-width:900px;        /* ← was 680px */
        max-height:92vh;        /* ← was 90vh */
        overflow-y:auto;
        box-shadow:0 25px 50px rgba(0,0,0,.15);
    }
    .detail-header { padding:20px 24px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; background:#fff; z-index:1; }
    .detail-header h2 { font-size:1.1rem; font-weight:700; color:#111827; margin:0; }
    .modal-close { background:none; border:none; font-size:1.4rem; color:#9ca3af; cursor:pointer; line-height:1; padding:0 4px; }
    .modal-close:hover { color:#374151; }

    /* ── Delete confirm modal ── */
    #deleteModal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:9999; align-items:center; justify-content:center; }
    .delete-box  { background:#fff; border-radius:14px; width:100%; max-width:420px; padding:28px; box-shadow:0 25px 50px rgba(0,0,0,.15); }

    /* ── Flash alerts ── */
    .alert { padding:12px 16px; border-radius:9px; margin-bottom:20px; font-weight:500; font-size:.875rem; }
    .alert-success { background:#d1fae5; border:1px solid #a7f3d0; color:#065f46; }
    .alert-danger  { background:#fee2e2; border:1px solid #fecaca; color:#991b1b; }

    /* ── NIK monospace ── */
    .nik-cell { font-family:monospace; font-size:.82rem; background:#f3f4f6; padding:2px 8px; border-radius:5px; color:#374151; }

    /* ── Pagination style ── */
    .dataTables_paginate .paginate_button {
        padding:4px 10px !important; border-radius:7px !important; font-size:.82rem !important;
        border:1px solid #e5e7eb !important; margin:0 2px !important; color:#374151 !important;
    }
    .dataTables_paginate .paginate_button.current,
    .dataTables_paginate .paginate_button.current:hover {
        background:#4f46e5 !important; color:#fff !important; border-color:#4f46e5 !important;
    }
    .dataTables_paginate .paginate_button:hover {
        background:#f3f4f6 !important; color:#111827 !important;
    }
</style>

<div class="emp-page">

    <!-- ── Header ───────────────────────────────────────────────── -->
    <div class="page-header">
        <div>
            <h1>👥 Data Karyawan</h1>
            <p>Manajemen data seluruh karyawan perusahaan</p>
        </div>
        <div class="header-actions">
            <a href="<?= base_url('employees/import') ?>" class="btn btn-info">
                📥 Import Excel
            </a>
            <button id="btnExport" class="btn btn-success">
                📤 Export CSV
            </button>
            <a href="<?= base_url('employees/create') ?>" class="btn btn-primary">
                ➕ Tambah Karyawan
            </a>
        </div>
    </div>

    <!-- ── Flash messages ───────────────────────────────────────── -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">✅ <?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">❌ <?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <!-- ── Stat cards ───────────────────────────────────────────── -->
    <div class="stats-row">
        <div class="stat-card">
            <span class="icon">👥</span>
            <div>
                <h4>Total</h4>
                <div class="val" id="statTotal">—</div>
            </div>
        </div>
        <div class="stat-card">
            <span class="icon">✅</span>
            <div>
                <h4>Aktif</h4>
                <div class="val" id="statAktif">—</div>
            </div>
        </div>
        <div class="stat-card">
            <span class="icon">⏸️</span>
            <div>
                <h4>Non-Aktif</h4>
                <div class="val" id="statNonaktif">—</div>
            </div>
        </div>
        <div class="stat-card">
            <span class="icon">🆕</span>
            <div>
                <h4>Bulan Ini</h4>
                <div class="val" id="statBulan">—</div>
            </div>
        </div>
    </div>

    <!-- ── Filters ──────────────────────────────────────────────── -->
    <div class="filter-bar">
        <div class="fg">
            <label>Department</label>
            <select id="filterDepartment">
                <option value="">Semua</option>
                <?php foreach ($departments as $d): ?>
                    <option value="<?= esc($d) ?>"><?= esc($d) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="fg">
            <label>Division</label>
            <select id="filterDivision">
                <option value="">Semua</option>
                <?php foreach ($divisions as $d): ?>
                    <option value="<?= esc($d) ?>"><?= esc($d) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="fg">
            <label>Status Karyawan</label>
            <select id="filterEmployeeStatus">
                <option value="">Semua</option>
                <?php foreach (($employee_statuses ?? []) as $s): ?>
                    <option value="<?= esc($s) ?>"><?= esc($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="fg">
            <label>Status Kepegawaian</label>
            <select id="filterEmploymentStatus">
                <option value="">Semua</option>
                <?php foreach (($employment_statuses ?? []) as $s): ?>
                    <option value="<?= esc($s) ?>"><?= esc($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-primary" id="btnResetFilter" style="align-self:flex-end;">
            ✖ Reset
        </button>
    </div>

    <!-- ── Table card ───────────────────────────────────────────── -->
    <div class="table-card">
        <div class="table-card-header">
            <h3>Daftar Karyawan</h3>
            <span id="tableInfo" style="font-size:.82rem; color:#9ca3af;">Memuat data…</span>
        </div>
        <div style="overflow-x:auto;">
            <table id="employeeTable" class="dataTable" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>NIK</th>
                        <th>Nama</th>
                        <th>Gender</th>
                        <th>Department</th>
                        <th>Division</th>
                        <th>User</th>
                        <th>Job Position</th>
                        <th>PKWT Date</th>
                        <th>Tenure</th>
                        <th>Emp. Status</th>
                        <th>Kepegawaian</th>
                        <th>Cutoff</th>
                        <th>KTP</th>
                        <th>Phone</th>
                        <th>Tempat Lahir</th>
                        <th>Tanggal Lahir</th>
                        <th>Umur</th>
                        <th>Pendidikan</th>
                        <th>Site</th>
                        <th>Alamat</th>
                        <th>Agama</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>

<!-- ── Detail Modal ─────────────────────────────────────────────────── -->
<div id="detailModal">
    <div class="detail-box">
        <div class="detail-header">
            <h2>👤 Detail Karyawan</h2>
            <button class="modal-close" id="btnCloseDetail">&times;</button>
        </div>
        <div id="detailContent" style="padding:24px;">
            <p style="color:#9ca3af; text-align:center;">Memuat…</p>
        </div>
    </div>
</div>

<!-- ── Delete Confirm Modal ──────────────────────────────────────────── -->
<div id="deleteModal">
    <div class="delete-box">
        <h3 style="font-size:1.1rem; font-weight:700; color:#111827; margin:0 0 8px;">🗑️ Hapus Karyawan</h3>
        <p style="color:#6b7280; font-size:.875rem; margin:0 0 6px;">Apakah Anda yakin ingin menghapus:</p>
        <p style="font-weight:700; color:#111827; margin:0 0 24px;" id="deleteTargetName"></p>
        <div style="display:flex; justify-content:flex-end; gap:10px;">
            <button class="btn" style="background:#f3f4f6; color:#374151;" id="btnCancelDelete">Batal</button>
            <button class="btn" style="background:#dc2626; color:#fff;" id="btnConfirmDelete">🗑️ Hapus</button>
        </div>
    </div>
</div>

<!-- ── Scripts ───────────────────────────────────────────────────────── -->

<script>
const BASE_URL = '<?= base_url() ?>';

document.addEventListener('DOMContentLoaded', function () {
    // ── Init DataTable ──────────────────────────────────────────────
    const table = $('#employeeTable').DataTable({
        processing  : true,
        serverSide  : true,
        ajax        : {
            url  : BASE_URL + 'employees/data',
            type : 'POST',
            data : function (d) {
                d.department        = $('#filterDepartment').val();
                d.division          = $('#filterDivision').val();
                d.employee_status   = $('#filterEmployeeStatus').val();
                d.employment_status = $('#filterEmploymentStatus').val();
            },
            dataSrc : function (json) {
                // Update stat cards from the first response
                if (json.stats) {
                    $('#statTotal').text(json.stats.total    ?? '—');
                    $('#statAktif').text(json.stats.aktif    ?? '—');
                    $('#statNonaktif').text(json.stats.nonaktif ?? '—');
                    $('#statBulan').text(json.stats.bulanIni ?? '—');
                }
                return json.data;
            },
        },
        order       : [[2, 'asc']],
        pageLength  : 25,
        lengthMenu  : [10, 25, 50, 100],
        scrollX     : true,
        scrollY     : '60vh',       // ← required for FixedColumns to work
        scrollCollapse: true,
        fixedColumns: {
            right: 1,               // ← freeze last column (actions)
        },
        language    : {
            processing : 'Memuat data…',
            search     : 'Cari:',
            lengthMenu : 'Tampilkan _MENU_ data',
            info       : 'Menampilkan _START_–_END_ dari _TOTAL_ data',
            infoEmpty  : 'Tidak ada data',
            zeroRecords: 'Tidak ada data yang cocok',
            paginate   : { previous:'‹', next:'›' },
        },
        columns: [
            // 0 — row number (not sortable)
            {
                data       : null,
                orderable  : false,
                searchable : false,
                render     : (_, __, ___, meta) => meta.row + 1 + meta.settings._iDisplayStart,
            },
            { data:'nik',                render: v => `<span class="nik-cell">${v ?? '-'}</span>` },
            { data:'name',               render: v => `<strong>${esc(v)}</strong>` },
            { data:'gender',             render: v => v ?? '-' },
            { data:'department',         render: v => v ?? '-' },
            { data:'division',           render: v => v ?? '-' },
            { data:'work_user',          render: v => v ?? '-' },
            { data:'job_position',       render: v => v ?? '-' },
            { data:'pkwt_date',          render: v => v ?? '-' },
            { data:'tenure',             render: v => v ? `<span class="badge badge-gray">${esc(v)}</span>` : '-' },
            { data:'employee_status',    render: v => statusBadge(v) },
            { data:'employment_status',  render: v => empBadge(v) },
            { data:'cutoff_date',        render: v => v ?? '-' },
            { data:'national_id',        render: v => v ? `<span class="nik-cell">${esc(v)}</span>` : '-' },
            { data:'phone_number',       render: v => v ?? '-' },
            { data:'place_of_birth',     render: v => v ?? '-' },
            { data:'date_of_birth',     render: v => v ?? '-' },
            { data:'age',                render: v => v != null ? `${v} thn` : '-' },
            { data:'last_education',     render: v => v ?? '-' },
            { data:'site',               render: v => v ?? '-' },
            { data:'address',            render: v => v ? `<span title="${esc(v)}" style="max-width:180px;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${esc(v)}</span>` : '-' },
            { data:'religion',           render: v => v ?? '-' },
            // 21 — actions
            {
                data      : 'id',
                orderable : false,
                render    : (id, _, row) => `
                    <div style="display:flex;gap:5px;">
                        <button class="btn-action btn-view"  data-id="${id}" title="Detail">🔍</button>
                        <a      class="btn-action btn-edit"  href="${BASE_URL}employees/edit/${id}" title="Edit">✏️</a>
                        <button class="btn-action btn-delete" data-id="${id}" data-name="${esc(row.name)}" title="Hapus">🗑️</button>
                    </div>`,
            },
        ],
        drawCallback: function (settings) {
            const info = settings.fnRecordsTotal();
            $('#tableInfo').text(`${settings.fnRecordsDisplay()} dari ${info} data`);
        },
    });

    // ── Filters ─────────────────────────────────────────────────────
    $('#filterDepartment, #filterDivision, #filterEmployeeStatus, #filterEmploymentStatus')
        .on('change', () => table.draw());

    $('#btnResetFilter').on('click', function () {
        $('#filterDepartment, #filterDivision, #filterEmployeeStatus, #filterEmploymentStatus')
            .val('');
        table.draw();
    });

    // ── Detail modal ─────────────────────────────────────────────────
    $('#employeeTable').on('click', '.btn-view', function () {
        const id = this.dataset.id;

        // Show modal immediately with loading state
        $('#detailContent').html(`
            <div style="padding:60px; text-align:center;">
                <div style="font-size:2rem; margin-bottom:12px;">⏳</div>
                <p style="color:#9ca3af;">Memuat data…</p>
            </div>
        `);
        document.getElementById('detailModal').style.display = 'flex';

        fetch(BASE_URL + 'employees/detail/' + id, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.text();
        })
        .then(html => {
            $('#detailContent').html(html);
        })
        .catch(err => {
            $('#detailContent').html(`
                <div style="padding:40px; text-align:center; color:#dc2626;">
                    ❌ Gagal memuat data: ${err.message}
                </div>
            `);
        });
    });

    document.getElementById('btnCloseDetail').addEventListener('click', () => {
        document.getElementById('detailModal').style.display = 'none';
    });
    document.getElementById('detailModal').addEventListener('click', function (e) {
        if (e.target === this) this.style.display = 'none';
    });

    // ── Delete modal ──────────────────────────────────────────────────
    let deleteId = null;

    $('#employeeTable').on('click', '.btn-delete', function () {
        deleteId = this.dataset.id;
        document.getElementById('deleteTargetName').textContent = this.dataset.name;
        document.getElementById('deleteModal').style.display = 'flex';
    });

    document.getElementById('btnCancelDelete').addEventListener('click', () => {
        document.getElementById('deleteModal').style.display = 'none';
        deleteId = null;
    });
    document.getElementById('deleteModal').addEventListener('click', function (e) {
        if (e.target === this) { this.style.display = 'none'; deleteId = null; }
    });

    document.getElementById('btnConfirmDelete').addEventListener('click', function () {
        if (!deleteId) return;
        fetch(BASE_URL + 'employees/delete/' + deleteId, { method:'POST' })
            .then(r => r.json())
            .then(data => {
                document.getElementById('deleteModal').style.display = 'none';
                deleteId = null;
                if (data.success) {
                    table.draw();
                } else {
                    alert('Gagal menghapus: ' + (data.message ?? 'Unknown error'));
                }
            });
    });

    document.getElementById('btnExport').addEventListener('click', function () {
        const order     = table.order()[0];          // [columnIndex, 'asc'/'desc']
        const orderCol  = order ? order[0] : 2;
        const orderDir  = order ? order[1] : 'asc';
        const search    = table.search();            // current global search string

        const params = new URLSearchParams({
            department        : $('#filterDepartment').val(),
            division          : $('#filterDivision').val(),
            employee_status   : $('#filterEmployeeStatus').val(),
            employment_status : $('#filterEmploymentStatus').val(),
            search            : search,
            order_col         : orderCol,
            order_dir         : orderDir,
        });

        window.location.href = BASE_URL + 'employees/export?' + params.toString();
    });

    // ── Helpers ───────────────────────────────────────────────────────
    function esc(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g,'&amp;')
            .replace(/</g,'&lt;')
            .replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;');
    }

    function statusBadge(v) {
        if (!v) return '-';
        const cls = v.toLowerCase().includes('aktif') ? 'badge-active' : 'badge-inactive';
        return `<span class="badge ${cls}">${esc(v)}</span>`;
    }

    function empBadge(v) {
        if (!v) return '-';
        const lower = v.toLowerCase();
        let cls = 'badge-gray';
        if (lower.includes('pkwt'))      cls = 'badge-pkwt';
        else if (lower.includes('pks'))  cls = 'badge-pks';
        else if (lower.includes('aktif')) cls = 'badge-active';
        return `<span class="badge ${cls}">${esc(v)}</span>`;
    }

});
</script>

<?= $this->endSection() ?>