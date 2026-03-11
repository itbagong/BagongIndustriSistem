<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<style>
    .att-page { padding: 24px; }

    /* Header */
    .page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
    .page-header h1 { font-size:1.5rem; font-weight:700; color:#111827; margin:0; }
    .page-header p  { font-size:.85rem; color:#6b7280; margin:4px 0 0; }

    /* Buttons */
    .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:9px; font-size:.875rem; font-weight:600; cursor:pointer; border:none; transition:all .15s; text-decoration:none; }
    .btn-primary { background:#4f46e5; color:#fff; }
    .btn-primary:hover { background:#4338ca; }
    .btn-success { background:#059669; color:#fff; }
    .btn-success:hover { background:#047857; }
    .btn-info    { background:#0891b2; color:#fff; }
    .btn-info:hover { background:#0e7490; }

    /* Date picker bar */
    .date-bar { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:14px 20px; margin-bottom:18px; display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
    .date-bar label { font-size:.78rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.04em; }
    .date-bar input[type=date] { padding:7px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:.875rem; color:#374151; background:#f9fafb; outline:none; }
    .date-bar input[type=date]:focus { border-color:#6366f1; background:#fff; box-shadow:0 0 0 3px rgba(99,102,241,.1); }

    /* Stat cards */
    .stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:12px; margin-bottom:20px; }
    .stat-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:14px 18px; display:flex; align-items:center; gap:12px; }
    .stat-card .s-icon { font-size:1.6rem; line-height:1; }
    .stat-card h4  { font-size:.7rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:.06em; margin:0 0 3px; }
    .stat-card .s-val { font-size:1.5rem; font-weight:800; color:#111827; line-height:1; }

    /* Table */
    .table-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; }
    .table-card-header { padding:14px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; }
    .table-card-header h3 { font-size:1rem; font-weight:600; color:#111827; margin:0; }

    #presentTable_wrapper .dataTables_length,
    #presentTable_wrapper .dataTables_filter { padding:12px 20px 0; }
    #presentTable_wrapper .dataTables_info,
    #presentTable_wrapper .dataTables_paginate { padding:10px 20px; }

    table.dataTable thead th { background:#f9fafb; color:#6b7280; font-size:.72rem; text-transform:uppercase; letter-spacing:.05em; font-weight:600; padding:11px 14px; border-bottom:1px solid #e5e7eb; white-space:nowrap; }
    table.dataTable tbody td { padding:10px 14px; font-size:.85rem; color:#374151; vertical-align:middle; }
    table.dataTable tbody tr:hover { background:#f9fafb; }
    table.dataTable tbody tr.odd  { background:#fff; }
    table.dataTable tbody tr.even { background:#fafafa; }

    /* Badges */
    .badge { display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:999px; font-size:.72rem; font-weight:600; white-space:nowrap; }
    .badge-green  { background:#d1fae5; color:#065f46; }
    .badge-yellow { background:#fef9c3; color:#854d0e; }
    .badge-blue   { background:#dbeafe; color:#1e40af; }
    .badge-gray   { background:#f3f4f6; color:#6b7280; }
    .badge-red    { background:#fee2e2; color:#991b1b; }

    .nik-cell { font-family:monospace; font-size:.8rem; background:#f3f4f6; padding:2px 7px; border-radius:5px; }

    /* Avatar */
    .avatar { width:32px; height:32px; border-radius:50%; background:#e0e7ff; display:inline-flex; align-items:center; justify-content:center; font-size:.78rem; font-weight:700; color:#4f46e5; flex-shrink:0; vertical-align:middle; margin-right:8px; }

    /* Photo modal */
    #photoModal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.75); z-index:9999; align-items:center; justify-content:center; }
    #photoModal img { max-width:90vw; max-height:85vh; border-radius:12px; box-shadow:0 25px 60px rgba(0,0,0,.5); }

    /* Pagination */
    .dataTables_paginate .paginate_button { padding:4px 10px !important; border-radius:7px !important; font-size:.8rem !important; border:1px solid #e5e7eb !important; margin:0 2px !important; color:#374151 !important; }
    .dataTables_paginate .paginate_button.current,
    .dataTables_paginate .paginate_button.current:hover { background:#4f46e5 !important; color:#fff !important; border-color:#4f46e5 !important; }
    .dataTables_paginate .paginate_button:hover { background:#f3f4f6 !important; color:#111827 !important; }
</style>

<div class="att-page">

    <!-- Header -->
    <div class="page-header">
        <div>
            <h1>✅ Kehadiran Karyawan</h1>
            <p>Daftar karyawan yang sudah absen masuk</p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="<?= base_url('attendance/admin') ?>" class="btn btn-info">📊 Dashboard</a>
            <a href="<?= base_url('attendance/admin/history') ?>" class="btn btn-primary">📋 Semua Data</a>
            <button id="btnExport" class="btn btn-success">📤 Export CSV</button>
        </div>
    </div>

    <!-- Date picker -->
    <div class="date-bar">
        <label>Tanggal</label>
        <input type="date" id="pickDate" value="<?= esc($date) ?>">
        <span style="font-size:.82rem;color:#9ca3af;">
            <?= date('l, d F Y', strtotime($date)) ?>
        </span>
    </div>

    <!-- Stat cards -->
    <div class="stats-row">
        <div class="stat-card">
            <span class="s-icon">👥</span>
            <div><h4>Total Hadir</h4><div class="s-val"><?= $summary['total'] ?></div></div>
        </div>
        <div class="stat-card">
            <span class="s-icon">✅</span>
            <div><h4>Tepat Waktu</h4><div class="s-val" style="color:#059669;"><?= $summary['tepat'] ?></div></div>
        </div>
        <div class="stat-card">
            <span class="s-icon">⏰</span>
            <div><h4>Telat</h4><div class="s-val" style="color:#d97706;"><?= $summary['telat'] ?></div></div>
        </div>
        <div class="stat-card">
            <span class="s-icon">🏠</span>
            <div><h4>Sudah Pulang</h4><div class="s-val" style="color:#2563eb;"><?= $summary['pulang'] ?></div></div>
        </div>
        <div class="stat-card">
            <span class="s-icon">🏢</span>
            <div><h4>Belum Pulang</h4><div class="s-val" style="color:#dc2626;"><?= $summary['blmPlg'] ?></div></div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="table-card-header">
            <h3>Daftar Hadir — <?= date('d F Y', strtotime($date)) ?></h3>
            <span id="tableInfo" style="font-size:.8rem;color:#9ca3af;"><?= $summary['total'] ?> karyawan</span>
        </div>
        <div style="overflow-x:auto;">
            <table id="presentTable" class="dataTable" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>NIK</th>
                        <th>Nama</th>
                        <th>Department</th>
                        <th>Jam Masuk</th>
                        <th>Status</th>
                        <th>Jam Pulang</th>
                        <th>Lokasi</th>
                        <th>Foto</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $i => $r): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><span class="nik-cell"><?= esc($r['nik']) ?></span></td>
                        <td>
                            <span class="avatar"><?= strtoupper(substr($r['nama'] ?? 'X', 0, 1)) ?></span>
                            <strong><?= esc($r['nama'] ?? '-') ?></strong>
                        </td>
                        <td><?= esc($r['department'] ?? '-') ?></td>
                        <td style="font-family:monospace;font-weight:700;">
                            <?= esc($r['jam_masuk'] ?? '-') ?>
                        </td>
                        <td>
                            <?php if ($r['is_telat']): ?>
                                <span class="badge badge-yellow">⏰ Telat</span>
                            <?php else: ?>
                                <span class="badge badge-green">✅ Tepat</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($r['jam_pulang']): ?>
                                <span class="badge badge-blue">🏠 <?= esc($r['jam_pulang']) ?></span>
                            <?php else: ?>
                                <span class="badge badge-red">🏢 Belum</span>
                            <?php endif; ?>
                        </td>
                        <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.78rem;color:#6b7280;" title="<?= esc($r['address'] ?? '') ?>">
                            <?= esc($r['address'] ?? '-') ?>
                        </td>
                        <td>
                            <?php if ($r['photo']): ?>
                                <button class="btn-photo" data-src="<?= base_url($r['photo']) ?>"
                                    style="background:#eff6ff;color:#2563eb;border:none;padding:4px 10px;border-radius:7px;font-size:.78rem;font-weight:600;cursor:pointer;">
                                    📷 Lihat
                                </button>
                            <?php else: ?>
                                <span style="color:#d1d5db;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Photo modal -->
<div id="photoModal" onclick="this.style.display='none'">
    <img id="photoImg" src="" alt="Foto Absen" />
</div>

<script>
const BASE_URL  = '<?= base_url() ?>';
const CURR_DATE = '<?= $date ?>';

document.addEventListener('DOMContentLoaded', function () {

    // Init DataTable (client-side karena data sudah di PHP)
    const table = $('#presentTable').DataTable({
        pageLength : 25,
        lengthMenu : [10, 25, 50, 100],
        order      : [[4, 'asc']], // sort jam masuk
        language   : {
            search     : 'Cari:',
            lengthMenu : 'Tampilkan _MENU_ data',
            info       : 'Menampilkan _START_–_END_ dari _TOTAL_ data',
            infoEmpty  : 'Tidak ada data',
            zeroRecords: 'Tidak ada yang cocok',
            paginate   : { previous:'‹', next:'›' },
        },
        drawCallback: function (s) {
            $('#tableInfo').text(s.fnRecordsDisplay() + ' dari ' + s.fnRecordsTotal() + ' karyawan');
        },
    });

    // Ganti tanggal → reload dengan date baru
    document.getElementById('pickDate').addEventListener('change', function () {
        window.location.href = BASE_URL + 'attendance/admin/present?date=' + this.value;
    });

    // Photo modal
    document.querySelectorAll('.btn-photo').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('photoImg').src = this.dataset.src;
            document.getElementById('photoModal').style.display = 'flex';
        });
    });

    // Export CSV
    document.getElementById('btnExport').addEventListener('click', function () {
        window.location.href = BASE_URL + 'attendance/admin/present/export?date=' + CURR_DATE;
    });

});
</script>

<?= $this->endSection() ?>