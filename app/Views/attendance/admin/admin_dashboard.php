<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<style>
    .att-page { padding: 24px; }

    /* ── Header ── */
    .page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
    .page-header h1 { font-size:1.5rem; font-weight:700; color:#111827; margin:0; }
    .page-header p  { font-size:.85rem; color:#6b7280; margin:4px 0 0; }
    .header-actions { display:flex; gap:10px; flex-wrap:wrap; }

    /* ── Buttons ── */
    .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:9px; font-size:.875rem; font-weight:600; cursor:pointer; border:none; transition:all .15s; text-decoration:none; }
    .btn-primary { background:#4f46e5; color:#fff; }
    .btn-primary:hover { background:#4338ca; }
    .btn-success { background:#059669; color:#fff; }
    .btn-success:hover { background:#047857; }

    /* ── Stat cards ── */
    .stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:14px; margin-bottom:24px; }
    .stat-card {
        background:#fff; border:1px solid #e5e7eb; border-radius:12px;
        padding:18px 20px; display:flex; align-items:flex-start; gap:14px;
        transition: box-shadow .15s;
    }
    .stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.07); }
    .stat-card .s-icon { font-size:1.8rem; line-height:1; }
    .stat-card h4  { font-size:.72rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:.06em; margin:0 0 5px; }
    .stat-card .s-val { font-size:1.75rem; font-weight:800; color:#111827; line-height:1; font-variant-numeric: tabular-nums; }
    .stat-card .s-sub { font-size:.75rem; color:#9ca3af; margin-top:4px; }

    /* ── Two-column grid ── */
    .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:18px; }
    @media(max-width:860px){ .grid-2 { grid-template-columns:1fr; } }

    /* ── Cards ── */
    .card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; }
    .card-header { padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; }
    .card-header h3 { font-size:.95rem; font-weight:700; color:#111827; margin:0; }
    .card-body { padding:20px; }

    /* ── Chart ── */
    #chartWeek { max-height: 240px; }

    /* ── Attendance status today table ── */
    .mini-table { width:100%; border-collapse:collapse; font-size:.82rem; }
    .mini-table th { padding:8px 12px; background:#f9fafb; color:#6b7280; font-size:.72rem; text-transform:uppercase; letter-spacing:.05em; font-weight:600; text-align:left; border-bottom:1px solid #e5e7eb; }
    .mini-table td { padding:9px 12px; color:#374151; border-bottom:1px solid #f3f4f6; vertical-align:middle; }
    .mini-table tr:last-child td { border-bottom:none; }
    .mini-table tr:hover td { background:#f9fafb; }

    /* ── Badges ── */
    .badge { display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:999px; font-size:.72rem; font-weight:600; white-space:nowrap; }
    .badge-green  { background:#d1fae5; color:#065f46; }
    .badge-red    { background:#fee2e2; color:#991b1b; }
    .badge-yellow { background:#fef9c3; color:#854d0e; }
    .badge-gray   { background:#f3f4f6; color:#6b7280; }
    .badge-blue   { background:#dbeafe; color:#1e40af; }

    /* ── Progress bar ── */
    .progress-wrap { background:#f3f4f6; border-radius:999px; height:7px; overflow:hidden; }
    .progress-fill { height:100%; border-radius:999px; transition:width .6s ease; }
    .fill-green  { background:#10b981; }
    .fill-yellow { background:#f59e0b; }
    .fill-red    { background:#ef4444; }

    /* ── Top absen telat list ── */
    .late-item { display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid #f3f4f6; }
    .late-item:last-child { border-bottom:none; }
    .late-avatar { width:34px; height:34px; border-radius:50%; background:#e0e7ff; display:flex; align-items:center; justify-content:center; font-size:.82rem; font-weight:700; color:#4f46e5; flex-shrink:0; }
    .late-info { flex:1; min-width:0; }
    .late-info .li-name { font-size:.85rem; font-weight:600; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .late-info .li-sub  { font-size:.75rem; color:#9ca3af; }
    .late-count { font-family:monospace; font-size:.85rem; font-weight:700; color:#dc2626; background:#fee2e2; padding:2px 9px; border-radius:7px; flex-shrink:0; }
</style>

<div class="att-page">

    <!-- Header -->
    <div class="page-header">
        <div>
            <h1>📊 Dashboard Absensi</h1>
            <p>Rekap dan monitoring kehadiran karyawan</p>
        </div>
        <div class="header-actions">
            <a href="<?= base_url('attendance/admin') ?>" class="btn btn-primary">📋 Lihat Semua Data</a>
            <a href="<?= base_url('attendance/admin/export') ?>" class="btn btn-success">📤 Export CSV</a>
        </div>
    </div>

    <!-- Stat cards -->
    <div class="stats-row">
        <div class="stat-card">
            <span class="s-icon">👥</span>
            <div>
                <h4>Total Karyawan</h4>
                <div class="s-val"><?= $stats['total_karyawan'] ?></div>
                <div class="s-sub">terdaftar</div>
            </div>
        </div>
        <div class="stat-card">
            <span class="s-icon">✅</span>
            <div>
                <h4>Hadir Hari Ini</h4>
                <div class="s-val"><?= $stats['hadir_hari_ini'] ?></div>
                <div class="s-sub">sudah absen masuk</div>
            </div>
        </div>
        <div class="stat-card">
            <span class="s-icon">🕐</span>
            <div>
                <h4>Telat Hari Ini</h4>
                <div class="s-val"><?= $stats['telat_hari_ini'] ?></div>
                <div class="s-sub">masuk &gt; 08:00</div>
            </div>
        </div>
        <div class="stat-card">
            <span class="s-icon">❌</span>
            <div>
                <h4>Belum Absen</h4>
                <div class="s-val"><?= $stats['belum_absen'] ?></div>
                <div class="s-sub">hari ini</div>
            </div>
        </div>
        <div class="stat-card">
            <span class="s-icon">🏠</span>
            <div>
                <h4>Sudah Pulang</h4>
                <div class="s-val"><?= $stats['sudah_pulang'] ?></div>
                <div class="s-sub">absen pulang</div>
            </div>
        </div>
        <div class="stat-card">
            <span class="s-icon">📅</span>
            <div>
                <h4>Total Bulan Ini</h4>
                <div class="s-val"><?= $stats['total_bulan_ini'] ?></div>
                <div class="s-sub">record absensi</div>
            </div>
        </div>
    </div>

    <!-- Row 1: chart + kehadiran hari ini -->
    <div class="grid-2">

        <!-- Chart 7 hari terakhir -->
        <div class="card">
            <div class="card-header">
                <h3>📈 Tren Kehadiran 7 Hari</h3>
            </div>
            <div class="card-body">
                <canvas id="chartWeek"></canvas>
            </div>
        </div>

        <!-- Persentase kehadiran bulan ini -->
        <div class="card">
            <div class="card-header">
                <h3>📊 Rekap Bulan Ini</h3>
                <span style="font-size:.78rem;color:#9ca3af;"><?= date('F Y') ?></span>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:16px;">
                <?php
                $totalHariKerja = $stats['hari_kerja_bulan_ini'] ?: 1;
                $pctHadir  = min(100, round(($stats['hari_hadir_bulan_ini']  / $totalHariKerja) * 100));
                $pctTelat  = min(100, round(($stats['hari_telat_bulan_ini']  / $totalHariKerja) * 100));
                $pctAbsen  = min(100, round(($stats['hari_absen_bulan_ini']  / $totalHariKerja) * 100));
                ?>
                <div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:5px;">
                        <span style="font-size:.82rem;font-weight:600;color:#374151;">Hadir Tepat Waktu</span>
                        <span style="font-size:.82rem;font-weight:700;color:#059669;"><?= $pctHadir ?>%</span>
                    </div>
                    <div class="progress-wrap"><div class="progress-fill fill-green" style="width:<?= $pctHadir ?>%"></div></div>
                    <div style="font-size:.72rem;color:#9ca3af;margin-top:3px;"><?= $stats['hari_hadir_bulan_ini'] ?> dari <?= $totalHariKerja ?> hari kerja</div>
                </div>
                <div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:5px;">
                        <span style="font-size:.82rem;font-weight:600;color:#374151;">Telat Masuk</span>
                        <span style="font-size:.82rem;font-weight:700;color:#f59e0b;"><?= $pctTelat ?>%</span>
                    </div>
                    <div class="progress-wrap"><div class="progress-fill fill-yellow" style="width:<?= $pctTelat ?>%"></div></div>
                    <div style="font-size:.72rem;color:#9ca3af;margin-top:3px;"><?= $stats['hari_telat_bulan_ini'] ?> hari</div>
                </div>
                <div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:5px;">
                        <span style="font-size:.82rem;font-weight:600;color:#374151;">Tidak Hadir</span>
                        <span style="font-size:.82rem;font-weight:700;color:#dc2626;"><?= $pctAbsen ?>%</span>
                    </div>
                    <div class="progress-wrap"><div class="progress-fill fill-red" style="width:<?= $pctAbsen ?>%"></div></div>
                    <div style="font-size:.72rem;color:#9ca3af;margin-top:3px;"><?= $stats['hari_absen_bulan_ini'] ?> hari</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: absen hari ini + paling sering telat -->
    <div class="grid-2">

        <!-- Absen masuk hari ini (10 terbaru) -->
        <div class="card">
            <div class="card-header">
                <h3>🕐 Absen Masuk Hari Ini</h3>
                <a href="<?= base_url('attendance/admin?date='.date('Y-m-d').'&type=masuk') ?>" style="font-size:.78rem;color:#4f46e5;text-decoration:none;">Lihat semua →</a>
            </div>
            <div style="overflow-x:auto;">
                <table class="mini-table">
                    <thead><tr><th>Nama</th><th>Jam</th><th>Status</th><th>Lokasi</th></tr></thead>
                    <tbody>
                    <?php if (empty($todayRecords)): ?>
                        <tr><td colspan="4" style="text-align:center;color:#9ca3af;padding:24px;">Belum ada absen hari ini</td></tr>
                    <?php else: foreach ($todayRecords as $r):
                        $jam     = date('H:i', strtotime($r['created_at']));
                        $isTelat = (int)date('H', strtotime($r['created_at'])) >= 8;
                    ?>
                        <tr>
                            <td><strong><?= esc($r['user_name'] ?? '-') ?></strong></td>
                            <td style="font-family:monospace;font-weight:700;"><?= $jam ?></td>
                            <td>
                                <?php if ($isTelat): ?>
                                    <span class="badge badge-yellow">⏰ Telat</span>
                                <?php else: ?>
                                    <span class="badge badge-green">✅ Tepat</span>
                                <?php endif; ?>
                            </td>
                            <td style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.75rem;color:#6b7280;" title="<?= esc($r['address'] ?? '') ?>"><?= esc($r['address'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top telat bulan ini -->
        <div class="card">
            <div class="card-header">
                <h3>⚠️ Paling Sering Telat</h3>
                <span style="font-size:.78rem;color:#9ca3af;"><?= date('F Y') ?></span>
            </div>
            <div class="card-body">
                <?php if (empty($topLate)): ?>
                    <p style="text-align:center;color:#9ca3af;font-size:.85rem;padding:20px 0;">Tidak ada data keterlambatan</p>
                <?php else: foreach ($topLate as $l): ?>
                    <div class="late-item">
                        <div class="late-avatar"><?= strtoupper(substr($l['user_name'] ?? 'X', 0, 1)) ?></div>
                        <div class="late-info">
                            <div class="li-name"><?= esc($l['user_name'] ?? '-') ?></div>
                            <div class="li-sub"><?= esc($l['department'] ?? '') ?></div>
                        </div>
                        <div class="late-count"><?= $l['jumlah_telat'] ?>x</div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>

    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
const weekData  = <?= json_encode($weekChart) ?>;
const labels    = weekData.map(d => d.label);
const hadirData = weekData.map(d => d.hadir);
const telatData = weekData.map(d => d.telat);

new Chart(document.getElementById('chartWeek'), {
    type: 'bar',
    data: {
        labels,
        datasets: [
            {
                label     : 'Tepat Waktu',
                data      : hadirData,
                backgroundColor: 'rgba(16,185,129,.75)',
                borderRadius  : 5,
                borderSkipped : false,
            },
            {
                label     : 'Telat',
                data      : telatData,
                backgroundColor: 'rgba(245,158,11,.75)',
                borderRadius  : 5,
                borderSkipped : false,
            },
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { position:'bottom', labels:{ font:{ size:11 }, boxWidth:12 } },
            tooltip: { mode:'index', intersect:false },
        },
        scales: {
            x: { stacked:false, grid:{ display:false }, ticks:{ font:{size:11} } },
            y: { beginAtZero:true, grid:{ color:'rgba(0,0,0,.05)' }, ticks:{ font:{size:11}, stepSize:1 } },
        },
    },
});
</script>

<?= $this->endSection() ?>