<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<style>
    .att-page { padding: 24px; }

    .page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
    .page-header h1 { font-size:1.5rem; font-weight:700; color:#111827; margin:0; }
    .page-header p  { font-size:.85rem; color:#6b7280; margin:4px 0 0; }
    .header-actions { display:flex; gap:10px; flex-wrap:wrap; }

    .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:9px; font-size:.875rem; font-weight:600; cursor:pointer; border:none; transition:all .15s; text-decoration:none; }
    .btn-primary { background:#4f46e5; color:#fff; }
    .btn-primary:hover { background:#4338ca; }
    .btn-success { background:#059669; color:#fff; }
    .btn-success:hover { background:#047857; }
    .btn-info { background:#0891b2; color:#fff; }
    .btn-info:hover { background:#0e7490; }

    /* Stats */
    .stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:12px; margin-bottom:20px; }
    .stat-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:14px 18px; }
    .stat-card h4  { font-size:.7rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:.06em; margin:0 0 4px; }
    .stat-card .s-val { font-size:1.5rem; font-weight:800; color:#111827; line-height:1; }

    /* Filter */
    .filter-bar { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:16px 20px; margin-bottom:18px; display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; }
    .filter-bar .fg { display:flex; flex-direction:column; gap:5px; flex:1; min-width:140px; }
    .filter-bar label { font-size:.72rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.04em; }
    .filter-bar select,
    .filter-bar input { padding:7px 10px; border:1px solid #d1d5db; border-radius:8px; font-size:.875rem; color:#374151; background:#f9fafb; outline:none; }
    .filter-bar select:focus,
    .filter-bar input:focus { border-color:#6366f1; background:#fff; box-shadow:0 0 0 3px rgba(99,102,241,.1); }

    /* Table */
    .table-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; }
    .table-card-header { padding:14px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; }
    .table-card-header h3 { font-size:1rem; font-weight:600; color:#111827; margin:0; }

    #attTable_wrapper .dataTables_length,
    #attTable_wrapper .dataTables_filter { padding:12px 20px 0; }
    #attTable_wrapper .dataTables_info,
    #attTable_wrapper .dataTables_paginate { padding:10px 20px; }

    table.dataTable thead th { background:#f9fafb; color:#6b7280; font-size:.72rem; text-transform:uppercase; letter-spacing:.05em; font-weight:600; padding:11px 14px; border-bottom:1px solid #e5e7eb; white-space:nowrap; }
    table.dataTable tbody td { padding:10px 14px; font-size:.85rem; color:#374151; vertical-align:middle; }
    table.dataTable tbody tr:hover { background:#f9fafb; }
    table.dataTable tbody tr.odd  { background:#fff; }
    table.dataTable tbody tr.even { background:#fafafa; }
    table.dataTable tbody td.dtfc-fixed-right,
    table.dataTable thead th.dtfc-fixed-right { background:#fff; box-shadow:-3px 0 6px rgba(0,0,0,.06); }
    table.dataTable tbody tr:hover td.dtfc-fixed-right { background:#f9fafb; }
    table.dataTable tbody tr.odd  td.dtfc-fixed-right { background:#fff; }
    table.dataTable tbody tr.even td.dtfc-fixed-right { background:#fafafa; }

    .badge { display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:999px; font-size:.72rem; font-weight:600; white-space:nowrap; }
    .badge-masuk  { background:#d1fae5; color:#065f46; }
    .badge-pulang { background:#fce7f3; color:#9d174d; }
    .badge-telat  { background:#fef9c3; color:#854d0e; }
    .badge-tepat  { background:#d1fae5; color:#065f46; }

    .btn-action { display:inline-flex; align-items:center; gap:4px; padding:4px 10px; border-radius:7px; font-size:.75rem; font-weight:600; cursor:pointer; border:none; transition:all .15s; }
    .btn-view   { background:#eff6ff; color:#2563eb; }
    .btn-view:hover { background:#dbeafe; }
    .btn-delete { background:#fef2f2; color:#dc2626; }
    .btn-delete:hover { background:#fecaca; }

    .nik-cell { font-family:monospace; font-size:.8rem; background:#f3f4f6; padding:2px 7px; border-radius:5px; color:#374151; }

    .dataTables_paginate .paginate_button { padding:4px 10px !important; border-radius:7px !important; font-size:.8rem !important; border:1px solid #e5e7eb !important; margin:0 2px !important; color:#374151 !important; }
    .dataTables_paginate .paginate_button.current,
    .dataTables_paginate .paginate_button.current:hover { background:#4f46e5 !important; color:#fff !important; border-color:#4f46e5 !important; }
    .dataTables_paginate .paginate_button:hover { background:#f3f4f6 !important; color:#111827 !important; }

    /* Photo modal */
    #photoModal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.75); z-index:9999; align-items:center; justify-content:center; }
    #photoModal img { max-width:90vw; max-height:85vh; border-radius:12px; box-shadow:0 25px 60px rgba(0,0,0,.5); }

    /* Delete modal */
    #deleteModal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:9999; align-items:center; justify-content:center; }
    .delete-box { background:#fff; border-radius:14px; width:100%; max-width:400px; padding:28px; box-shadow:0 25px 50px rgba(0,0,0,.15); }
</style>

<div class="att-page">

    <!-- Header -->
    <div class="page-header">
        <div>
            <h1>📋 Data Absensi Karyawan</h1>
            <p>Seluruh rekap absensi masuk & pulang</p>
        </div>
        <div class="header-actions">
            <a href="<?= base_url('attendance/admin/dashboard') ?>" class="btn btn-info">📊 Dashboard</a>
            <button id="btnExport" class="btn btn-success">📤 Export CSV</button>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card"><h4>Hari Ini</h4><div class="s-val" id="sHariIni">—</div></div>
        <div class="stat-card"><h4>Bulan Ini</h4><div class="s-val" id="sBulanIni">—</div></div>
        <div class="stat-card"><h4>Telat</h4><div class="s-val" id="sTelat">—</div></div>
        <div class="stat-card"><h4>Total</h4><div class="s-val" id="sTotal">—</div></div>
    </div>

    <!-- Filter -->
    <div class="filter-bar">
        <div class="fg">
            <label>Tanggal Mulai</label>
            <input type="date" id="fDateFrom" value="<?= date('Y-m-01') ?>">
        </div>
        <div class="fg">
            <label>Tanggal Akhir</label>
            <input type="date" id="fDateTo" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="fg">
            <label>Tipe Absen</label>
            <select id="fType">
                <option value="">Semua</option>
                <option value="masuk">Masuk</option>
                <option value="pulang">Pulang</option>
            </select>
        </div>
        <div class="fg">
            <label>Status</label>
            <select id="fStatus">
                <option value="">Semua</option>
                <option value="tepat">Tepat Waktu</option>
                <option value="telat">Telat</option>
            </select>
        </div>
        <div class="fg">
            <label>Department</label>
            <select id="fDepartment">
                <option value="">Semua</option>
                <?php foreach (($departments ?? []) as $d): ?>
                    <option value="<?= esc($d) ?>"><?= esc($d) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-primary" id="btnReset" style="align-self:flex-end;">✖ Reset</button>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="table-card-header">
            <h3>Rekap Absensi</h3>
            <span id="tableInfo" style="font-size:.8rem;color:#9ca3af;">Memuat data…</span>
        </div>
        <div style="overflow-x:auto;">
            <table id="attTable" class="dataTable" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tanggal</th>
                        <th>NIK</th>
                        <th>Nama</th>
                        <th>Department</th>
                        <th>Tipe</th>
                        <th>Jam</th>
                        <th>Status</th>
                        <th>Lokasi</th>
                        <th>Koordinat</th>
                        <th>Akurasi</th>
                        <th>Foto</th>
                        <th>IP</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>

<!-- Photo Modal -->
<div id="photoModal" onclick="this.style.display='none'">
    <img id="photoImg" src="" alt="Foto Absen" />
</div>

<!-- Delete Modal -->
<div id="deleteModal">
    <div class="delete-box">
        <h3 style="font-size:1.05rem;font-weight:700;color:#111827;margin:0 0 8px;">🗑️ Hapus Data Absen</h3>
        <p style="color:#6b7280;font-size:.875rem;margin:0 0 20px;">Yakin ingin menghapus data absen ini? Tindakan tidak dapat dibatalkan.</p>
        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <button class="btn" style="background:#f3f4f6;color:#374151;" id="btnCancelDel">Batal</button>
            <button class="btn" style="background:#dc2626;color:#fff;" id="btnConfirmDel">🗑️ Hapus</button>
        </div>
    </div>
</div>

<script>
const BASE_URL = '<?= base_url() ?>';

document.addEventListener('DOMContentLoaded', function () {

    const table = $('#attTable').DataTable({
        processing : true,
        serverSide : true,
        ajax: {
            url  : BASE_URL + 'attendance/admin/data',
            type : 'POST',
            data : function (d) {
                d.date_from  = $('#fDateFrom').val();
                d.date_to    = $('#fDateTo').val();
                d.type       = $('#fType').val();
                d.status     = $('#fStatus').val();
                d.department = $('#fDepartment').val();
            },
            dataSrc: function (json) {
                if (json.stats) {
                    $('#sHariIni').text(json.stats.hari_ini   ?? '—');
                    $('#sBulanIni').text(json.stats.bulan_ini ?? '—');
                    $('#sTelat').text(json.stats.telat        ?? '—');
                    $('#sTotal').text(json.stats.total        ?? '—');
                }
                return json.data;
            },
        },
        order      : [[1, 'desc'], [6, 'asc']],
        pageLength : 25,
        lengthMenu : [10, 25, 50, 100],
        scrollX    : true,
        scrollY    : '60vh',
        scrollCollapse: true,
        fixedColumns: { right: 1 },
        language: {
            processing : 'Memuat data…',
            search     : 'Cari:',
            lengthMenu : 'Tampilkan _MENU_ data',
            info       : 'Menampilkan _START_–_END_ dari _TOTAL_ data',
            infoEmpty  : 'Tidak ada data',
            zeroRecords: 'Tidak ada data yang cocok',
            paginate   : { previous:'‹', next:'›' },
        },
        columns: [
            { data:null, orderable:false, searchable:false,
              render: (_,__,___,m) => m.row + 1 + m.settings._iDisplayStart },
            { data:'tanggal' },
            { data:'nik',        render: v => v ? `<span class="nik-cell">${esc(v)}</span>` : '-' },
            { data:'nama',       render: v => `<strong>${esc(v??'-')}</strong>` },
            { data:'department', render: v => v ?? '-' },
            { data:'type',       render: v => v === 'masuk'
                ? `<span class="badge badge-masuk">⬆ Masuk</span>`
                : `<span class="badge badge-pulang">⬇ Pulang</span>` },
            { data:'jam',        render: v => `<span style="font-family:monospace;font-weight:700;">${v??'-'}</span>` },
            { data:'is_telat',   render: v => v
                ? `<span class="badge badge-telat">⏰ Telat</span>`
                : `<span class="badge badge-tepat">✅ Tepat</span>` },
            { data:'address',    render: v => v
                ? `<span title="${esc(v)}" style="max-width:160px;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.78rem;color:#6b7280;">${esc(v)}</span>`
                : '-' },
            { data:'koordinat',  render: v => `<span style="font-size:.75rem;color:#9ca3af;">${esc(v??'-')}</span>` },
            { data:'accuracy',   render: v => v ? `±${Math.round(v)}m` : '-' },
            { data:'photo',      orderable:false,
              render: v => v
                ? `<button class="btn-action btn-view btn-photo" data-src="${BASE_URL}${esc(v)}">📷</button>`
                : '<span style="color:#d1d5db;">—</span>' },
            { data:'ip_address', render: v => `<span style="font-size:.75rem;font-family:monospace;">${v??'-'}</span>` },
            { data:'id',         orderable:false,
              render: id => `<button class="btn-action btn-delete" data-id="${id}">🗑️</button>` },
        ],
        drawCallback: function (s) {
            $('#tableInfo').text(`${s.fnRecordsDisplay()} dari ${s.fnRecordsTotal()} data`);
        },
    });

    // Filters
    $('#fDateFrom,#fDateTo,#fType,#fStatus,#fDepartment').on('change', () => table.draw());
    $('#btnReset').on('click', function () {
        $('#fDateFrom').val('<?= date('Y-m-01') ?>');
        $('#fDateTo').val('<?= date('Y-m-d') ?>');
        $('#fType,#fStatus,#fDepartment').val('');
        table.draw();
    });

    // Photo modal
    $('#attTable').on('click', '.btn-photo', function () {
        document.getElementById('photoImg').src = this.dataset.src;
        document.getElementById('photoModal').style.display = 'flex';
    });

    // Delete
    let deleteId = null;
    $('#attTable').on('click', '.btn-delete', function () {
        deleteId = this.dataset.id;
        document.getElementById('deleteModal').style.display = 'flex';
    });
    document.getElementById('btnCancelDel').addEventListener('click', () => {
        document.getElementById('deleteModal').style.display = 'none'; deleteId = null;
    });
    document.getElementById('deleteModal').addEventListener('click', function (e) {
        if (e.target === this) { this.style.display = 'none'; deleteId = null; }
    });
    document.getElementById('btnConfirmDel').addEventListener('click', function () {
        if (!deleteId) return;
        fetch(BASE_URL + 'attendance/admin/delete/' + deleteId, { method:'POST' })
            .then(r => r.json())
            .then(d => {
                document.getElementById('deleteModal').style.display = 'none';
                deleteId = null;
                if (d.success) table.draw();
                else alert('Gagal: ' + (d.message ?? 'Error'));
            });
    });

    // Export CSV
    document.getElementById('btnExport').addEventListener('click', function () {
        const params = new URLSearchParams({
            date_from  : $('#fDateFrom').val(),
            date_to    : $('#fDateTo').val(),
            type       : $('#fType').val(),
            status     : $('#fStatus').val(),
            department : $('#fDepartment').val(),
            search     : table.search(),
        });
        window.location.href = BASE_URL + 'attendance/admin/export?' + params.toString();
    });

    function esc(str) {
        if (!str) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
});
</script>

<?= $this->endSection() ?>