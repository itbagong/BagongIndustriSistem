<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

<style>
* { box-sizing: border-box; }

body, .content-wrapper {
    font-family: 'DM Sans', sans-serif;
    background: #f4f5f7;
}

.gs-dashboard {
    padding: 20px 24px;
    max-width: 1440px;
}

/* ── PAGE HEADER ── */
.gs-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 24px;
}
.gs-page-header h1 {
    font-size: 1.35rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 2px;
    letter-spacing: -0.3px;
}
.gs-page-header p {
    font-size: 0.8rem;
    color: #6b7280;
    margin: 0;
}
.gs-header-actions {
    display: flex;
    gap: 8px;
}
.btn-gs {
    padding: 7px 16px;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 500;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s;
    text-decoration: none;
}
.btn-gs-primary   { background:#111827; color:#fff; }
.btn-gs-primary:hover { background:#1f2937; color:#fff; }
.btn-gs-outline   { background:#fff; color:#374151; border:1px solid #e5e7eb; }
.btn-gs-outline:hover { background:#f9fafb; }

/* ── SUMMARY CARDS ── */
.gs-cards-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}
.gs-card {
    background: #fff;
    border-radius: 12px;
    padding: 16px 18px;
    border: 1px solid #e9eaec;
    position: relative;
    overflow: hidden;
}
.gs-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--accent, #e5e7eb);
    border-radius: 12px 12px 0 0;
}
.gs-card-label {
    font-size: 0.72rem;
    font-weight: 500;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}
.gs-card-value {
    font-size: 1.9rem;
    font-weight: 600;
    color: #111827;
    line-height: 1;
    font-family: 'DM Mono', monospace;
    margin-bottom: 4px;
}
.gs-card-sub {
    font-size: 0.73rem;
    color: #9ca3af;
}
.gs-card-icon {
    position: absolute;
    right: 14px;
    top: 18px;
    font-size: 1.4rem;
    opacity: 0.08;
}
.gs-card.blue   { --accent: #3b82f6; }
.gs-card.indigo { --accent: #6366f1; }
.gs-card.amber  { --accent: #f59e0b; }
.gs-card.green  { --accent: #10b981; }
.gs-card.red    { --accent: #ef4444; }
.gs-card.violet { --accent: #8b5cf6; }

/* count-up */
.gs-card-value span { display: inline-block; }

/* ── CHARTS LAYOUT ── */
.gs-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}
.gs-grid-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}
.gs-grid-32 {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}
.gs-chart-card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e9eaec;
    overflow: hidden;
}
.gs-chart-header {
    padding: 16px 20px 12px;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.gs-chart-title {
    font-size: 0.88rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}
.gs-chart-sub {
    font-size: 0.73rem;
    color: #9ca3af;
    margin: 2px 0 0;
}
.gs-chart-body {
    padding: 16px 20px 20px;
    position: relative;
}
.gs-chart-body canvas {
    max-height: 230px;
}

/* ── REPAIR STATUS ROW ── */
.gs-repair-row {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 4px 0;
}
.gs-repair-stat {
    display: flex;
    align-items: center;
    gap: 10px;
}
.gs-repair-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}
.gs-repair-label {
    font-size: 0.78rem;
    color: #4b5563;
    flex: 1;
}
.gs-repair-bar-wrap {
    flex: 2;
    height: 6px;
    background: #f3f4f6;
    border-radius: 99px;
    overflow: hidden;
}
.gs-repair-bar {
    height: 100%;
    border-radius: 99px;
    transition: width 1s cubic-bezier(0.16, 1, 0.3, 1);
}
.gs-repair-count {
    font-size: 0.78rem;
    font-weight: 600;
    color: #111827;
    font-family: 'DM Mono', monospace;
    min-width: 24px;
    text-align: right;
}

/* ── LEGEND ── */
.gs-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}
.gs-legend-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.73rem;
    color: #6b7280;
}
.gs-legend-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
}

/* ── DONUT CENTER LABEL ── */
.gs-donut-wrap {
    position: relative;
    display: flex;
    justify-content: center;
}
.gs-donut-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    pointer-events: none;
}
.gs-donut-center-val {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    line-height: 1;
    font-family: 'DM Mono', monospace;
}
.gs-donut-center-lbl {
    font-size: 0.67rem;
    color: #9ca3af;
    margin-top: 2px;
}

/* ── INSIGHT BADGES ── */
.gs-insight {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    padding: 12px 20px;
    border-top: 1px solid #f3f4f6;
}
.gs-badge {
    padding: 3px 9px;
    border-radius: 99px;
    font-size: 0.7rem;
    font-weight: 500;
}
.gs-badge-blue   { background:#eff6ff; color:#2563eb; }
.gs-badge-amber  { background:#fffbeb; color:#d97706; }
.gs-badge-green  { background:#f0fdf4; color:#16a34a; }
.gs-badge-red    { background:#fef2f2; color:#dc2626; }
.gs-badge-gray   { background:#f9fafb; color:#6b7280; }

/* ── TOP SITE TABLE ── */
.gs-site-table { width: 100%; border-collapse: collapse; }
.gs-site-table th {
    font-size: 0.68rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: #9ca3af;
    padding: 0 0 8px;
    text-align: left;
    border-bottom: 1px solid #f3f4f6;
}
.gs-site-table td {
    font-size: 0.78rem;
    color: #374151;
    padding: 8px 0;
    border-bottom: 1px solid #f9fafb;
    vertical-align: middle;
}
.gs-site-table tr:last-child td { border-bottom: none; }
.gs-site-rank {
    width: 22px; height: 22px;
    border-radius: 6px;
    background: #f3f4f6;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.68rem;
    font-weight: 600;
    color: #374151;
    margin-right: 6px;
}
.gs-site-rank.top1 { background: #111827; color: #fff; }
.gs-site-rank.top2 { background: #374151; color: #fff; }
.gs-site-rank.top3 { background: #6b7280; color: #fff; }

/* ── RESPONSIVE ── */
@media (max-width: 1200px) {
    .gs-cards-grid { grid-template-columns: repeat(3, 1fr); }
    .gs-grid-3     { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 768px) {
    .gs-cards-grid { grid-template-columns: repeat(2, 1fr); }
    .gs-grid-2, .gs-grid-3, .gs-grid-32 { grid-template-columns: 1fr; }
}

/* animate in */
@keyframes fadeUp {
    from { opacity:0; transform:translateY(12px); }
    to   { opacity:1; transform:translateY(0); }
}
.gs-card, .gs-chart-card {
    animation: fadeUp 0.35s ease both;
}
.gs-card:nth-child(1)  { animation-delay: 0.03s; }
.gs-card:nth-child(2)  { animation-delay: 0.06s; }
.gs-card:nth-child(3)  { animation-delay: 0.09s; }
.gs-card:nth-child(4)  { animation-delay: 0.12s; }
.gs-card:nth-child(5)  { animation-delay: 0.15s; }
.gs-card:nth-child(6)  { animation-delay: 0.18s; }
</style>

<?php
// ── HITUNG DATA ──────────────────────────────────────────────────────────────
$totalMess      = $total_mess      ?? 0;
$totalWorkshop  = $total_workshop  ?? 0;
$totalAset      = $totalMess + $totalWorkshop;
$totalLuasMess  = $total_luas_mess     ?? 0;
$totalLuasWS    = $total_luas_workshop ?? 0;
$totalLuas      = $totalLuasMess + $totalLuasWS;

$totalMilik     = ($total_milik_mess ?? 0) + ($total_milik_workshop ?? 0);
$totalSewa      = ($total_sewa_mess  ?? 0) + ($total_sewa_workshop  ?? 0);

// Repair stats — sediakan fallback jika belum ada dari controller
$repairPending    = $total_repair_pending    ?? 0;
$repairApproved   = $total_repair_approved   ?? 0;
$repairInProgress = $total_repair_inprogress ?? 0;
$repairCompleted  = $total_repair_completed  ?? 0;
$repairRejected   = $total_repair_rejected   ?? 0;
$repairCancelled  = $total_repair_cancelled  ?? 0;
$totalRepair      = $repairPending + $repairApproved + $repairInProgress + $repairCompleted + $repairRejected + $repairCancelled;
$repairOpen       = $repairPending + $repairApproved + $repairInProgress; // belum selesai
$repairClosed     = $repairCompleted + $repairRejected + $repairCancelled;

// Site breakdown — dari controller (array ['site_name'=>..., 'total'=>...])
$siteBreakdown  = $site_breakdown  ?? [];
$trendMonthly   = $trend_monthly   ?? [];   // [['bulan'=>'Jan', 'total'=>N], ...]

// Bulan 6 terakhir untuk line chart (fallback dummy bila kosong)
if (empty($trendMonthly)) {
    $trendMonthly = [
        ['bulan'=>'Sep','total_mess'=>2,'total_ws'=>1,'total_repair'=>3],
        ['bulan'=>'Okt','total_mess'=>4,'total_ws'=>2,'total_repair'=>5],
        ['bulan'=>'Nov','total_mess'=>3,'total_ws'=>3,'total_repair'=>4],
        ['bulan'=>'Des','total_mess'=>5,'total_ws'=>1,'total_repair'=>6],
        ['bulan'=>'Jan','total_mess'=>6,'total_ws'=>4,'total_repair'=>8],
        ['bulan'=>'Feb','total_mess'=>4,'total_ws'=>3,'total_repair'=>5],
    ];
}
?>

<div class="gs-dashboard">

    <!-- PAGE HEADER -->
    <div class="gs-page-header">
        <div>
            <h1><i class="fas fa-th-large mr-2" style="color:#6b7280;font-size:1.1rem;"></i>Dashboard General Service</h1>
            <p>Overview aset mess, workshop & perbaikan &mdash; Update: <?= date('d M Y, H:i') ?></p>
        </div>
        <div class="gs-header-actions">
            <a href="<?= base_url('general-service?tab=mess') ?>" class="btn-gs btn-gs-outline">
                <i class="fas fa-home" style="font-size:0.75rem;"></i> Data Mess
            </a>
            <a href="<?= base_url('general-service?tab=workshop') ?>" class="btn-gs btn-gs-outline">
                <i class="fas fa-tools" style="font-size:0.75rem;"></i> Data Workshop
            </a>
            <a href="<?= base_url('general-service/repair-request') ?>" class="btn-gs btn-gs-primary">
                <i class="fas fa-wrench" style="font-size:0.75rem;"></i> Perbaikan
            </a>
        </div>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="gs-cards-grid">
        <div class="gs-card blue">
            <div class="gs-card-icon"><i class="fas fa-layer-group"></i></div>
            <div class="gs-card-label">Total Aset</div>
            <div class="gs-card-value" data-count="<?= $totalAset ?>"><span>0</span></div>
            <div class="gs-card-sub"><?= $totalMess ?> Mess · <?= $totalWorkshop ?> Workshop</div>
        </div>
        <div class="gs-card indigo">
            <div class="gs-card-icon"><i class="fas fa-ruler-combined"></i></div>
            <div class="gs-card-label">Total Luas</div>
            <div class="gs-card-value" data-count="<?= round($totalLuas) ?>"><span>0</span></div>
            <div class="gs-card-sub">m² keseluruhan aset</div>
        </div>
        <div class="gs-card green">
            <div class="gs-card-icon"><i class="fas fa-building"></i></div>
            <div class="gs-card-label">Milik Perusahaan</div>
            <div class="gs-card-value" data-count="<?= $totalMilik ?>"><span>0</span></div>
            <div class="gs-card-sub"><?= $totalSewa ?> aset sewa</div>
        </div>
        <div class="gs-card amber">
            <div class="gs-card-icon"><i class="fas fa-tools"></i></div>
            <div class="gs-card-label">Total Perbaikan</div>
            <div class="gs-card-value" data-count="<?= $totalRepair ?>"><span>0</span></div>
            <div class="gs-card-sub">Sepanjang waktu</div>
        </div>
        <div class="gs-card violet">
            <div class="gs-card-icon"><i class="fas fa-folder-open"></i></div>
            <div class="gs-card-label">Open / Aktif</div>
            <div class="gs-card-value" data-count="<?= $repairOpen ?>"><span>0</span></div>
            <div class="gs-card-sub">Pending · Approved · Proses</div>
        </div>
        <div class="gs-card red">
            <div class="gs-card-icon"><i class="fas fa-check-double"></i></div>
            <div class="gs-card-label">Closed</div>
            <div class="gs-card-value" data-count="<?= $repairClosed ?>"><span>0</span></div>
            <div class="gs-card-sub">Selesai · Ditolak · Batal</div>
        </div>
    </div>

    <!-- ROW 1: Line Trend + Donut Kepemilikan + Donut Mess vs Workshop -->
    <div class="gs-grid-32">
        <!-- Line Chart: Tren Bulanan -->
        <div class="gs-chart-card">
            <div class="gs-chart-header">
                <div>
                    <div class="gs-chart-title">Tren Penambahan Aset & Perbaikan</div>
                    <div class="gs-chart-sub">6 bulan terakhir</div>
                </div>
                <div class="gs-legend">
                    <div class="gs-legend-item"><div class="gs-legend-dot" style="background:#3b82f6;"></div>Mess</div>
                    <div class="gs-legend-item"><div class="gs-legend-dot" style="background:#f59e0b;"></div>Workshop</div>
                    <div class="gs-legend-item"><div class="gs-legend-dot" style="background:#ef4444;"></div>Perbaikan</div>
                </div>
            </div>
            <div class="gs-chart-body">
                <canvas id="chartTrend"></canvas>
            </div>
        </div>

        <!-- Donut: Kepemilikan -->
        <div class="gs-chart-card">
            <div class="gs-chart-header">
                <div>
                    <div class="gs-chart-title">Status Kepemilikan</div>
                    <div class="gs-chart-sub">Milik vs Sewa</div>
                </div>
            </div>
            <div class="gs-chart-body">
                <div class="gs-donut-wrap">
                    <canvas id="chartKepemilikan" style="max-height:190px;"></canvas>
                    <div class="gs-donut-center">
                        <div class="gs-donut-center-val"><?= $totalAset ?></div>
                        <div class="gs-donut-center-lbl">Total Aset</div>
                    </div>
                </div>
                <div class="gs-legend" style="justify-content:center; margin-top:12px;">
                    <div class="gs-legend-item"><div class="gs-legend-dot" style="background:#111827;"></div>Milik (<?= $totalMilik ?>)</div>
                    <div class="gs-legend-item"><div class="gs-legend-dot" style="background:#d1d5db;"></div>Sewa (<?= $totalSewa ?>)</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ROW 2: Bar Site + Repair Status + Mess vs Workshop -->
    <div class="gs-grid-3">
        <!-- Bar Chart: Aset per Job Site -->
        <div class="gs-chart-card">
            <div class="gs-chart-header">
                <div>
                    <div class="gs-chart-title">Aset per Job Site</div>
                    <div class="gs-chart-sub">Mess &amp; Workshop</div>
                </div>
            </div>
            <div class="gs-chart-body">
                <canvas id="chartSite"></canvas>
            </div>
        </div>

        <!-- Repair Status Breakdown -->
        <div class="gs-chart-card">
            <div class="gs-chart-header">
                <div>
                    <div class="gs-chart-title">Status Perbaikan</div>
                    <div class="gs-chart-sub">Distribusi semua pengajuan</div>
                </div>
                <span class="gs-badge gs-badge-gray"><?= $totalRepair ?> total</span>
            </div>
            <div class="gs-chart-body" style="padding-top:12px;">
                <?php
                $repairStatuses = [
                    ['label'=>'Pending',     'count'=>$repairPending,    'color'=>'#f59e0b'],
                    ['label'=>'Approved',    'count'=>$repairApproved,   'color'=>'#3b82f6'],
                    ['label'=>'In Progress', 'count'=>$repairInProgress, 'color'=>'#8b5cf6'],
                    ['label'=>'Completed',   'count'=>$repairCompleted,  'color'=>'#10b981'],
                    ['label'=>'Rejected',    'count'=>$repairRejected,   'color'=>'#ef4444'],
                    ['label'=>'Cancelled',   'count'=>$repairCancelled,  'color'=>'#9ca3af'],
                ];
                $maxRepair = max(array_column($repairStatuses, 'count')) ?: 1;
                ?>
                <div class="gs-repair-row">
                <?php foreach($repairStatuses as $rs): ?>
                    <div class="gs-repair-stat">
                        <div class="gs-repair-dot" style="background:<?= $rs['color'] ?>;"></div>
                        <div class="gs-repair-label"><?= $rs['label'] ?></div>
                        <div class="gs-repair-bar-wrap">
                            <div class="gs-repair-bar"
                                 style="width:0%; background:<?= $rs['color'] ?>;"
                                 data-width="<?= $totalRepair > 0 ? round($rs['count']/$totalRepair*100) : 0 ?>%">
                            </div>
                        </div>
                        <div class="gs-repair-count"><?= $rs['count'] ?></div>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
            <div class="gs-insight">
                <?php if($repairOpen > 0): ?>
                <span class="gs-badge gs-badge-amber"><i class="fas fa-circle-notch fa-spin mr-1" style="font-size:0.6rem;"></i><?= $repairOpen ?> Open</span>
                <?php endif; ?>
                <?php if($repairCompleted > 0): ?>
                <span class="gs-badge gs-badge-green"><i class="fas fa-check mr-1" style="font-size:0.6rem;"></i><?= $repairCompleted ?> Selesai</span>
                <?php endif; ?>
                <?php if($repairPending > 0): ?>
                <span class="gs-badge gs-badge-amber"><?= $repairPending ?> Menunggu</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Donut: Mess vs Workshop -->
        <div class="gs-chart-card">
            <div class="gs-chart-header">
                <div>
                    <div class="gs-chart-title">Komposisi Aset</div>
                    <div class="gs-chart-sub">Mess vs Workshop</div>
                </div>
            </div>
            <div class="gs-chart-body">
                <div class="gs-donut-wrap">
                    <canvas id="chartKomposisi" style="max-height:180px;"></canvas>
                    <div class="gs-donut-center">
                        <div class="gs-donut-center-val"><?= $totalAset ?></div>
                        <div class="gs-donut-center-lbl">Aset</div>
                    </div>
                </div>
                <div class="gs-legend" style="justify-content:center; margin-top:12px;">
                    <div class="gs-legend-item"><div class="gs-legend-dot" style="background:#3b82f6;"></div>Mess (<?= $totalMess ?>)</div>
                    <div class="gs-legend-item"><div class="gs-legend-dot" style="background:#f59e0b;"></div>Workshop (<?= $totalWorkshop ?>)</div>
                </div>
                <!-- Luas -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:16px;padding-top:12px;border-top:1px solid #f3f4f6;">
                    <div style="text-align:center;">
                        <div style="font-size:0.68rem;color:#9ca3af;margin-bottom:2px;">Luas Mess</div>
                        <div style="font-size:0.9rem;font-weight:600;color:#3b82f6;font-family:'DM Mono',monospace;"><?= number_format($totalLuasMess, 0, ',', '.') ?> m²</div>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-size:0.68rem;color:#9ca3af;margin-bottom:2px;">Luas Workshop</div>
                        <div style="font-size:0.9rem;font-weight:600;color:#f59e0b;font-family:'DM Mono',monospace;"><?= number_format($totalLuasWS, 0, ',', '.') ?> m²</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ROW 3: Bar Repair per Tipe + Top Sites Table -->
    <div class="gs-grid-2">
        <!-- Stacked Bar: Repair by Tipe Aset -->
        <div class="gs-chart-card">
            <div class="gs-chart-header">
                <div>
                    <div class="gs-chart-title">Perbaikan per Kategori</div>
                    <div class="gs-chart-sub">Ringan · Sedang · Berat · Darurat</div>
                </div>
            </div>
            <div class="gs-chart-body">
                <canvas id="chartRepairKategori"></canvas>
            </div>
        </div>

        <!-- Top Sites Table -->
        <div class="gs-chart-card">
            <div class="gs-chart-header">
                <div>
                    <div class="gs-chart-title">Ringkasan per Job Site</div>
                    <div class="gs-chart-sub">Total aset & perbaikan</div>
                </div>
            </div>
            <div class="gs-chart-body" style="padding-top:8px;">
                <table class="gs-site-table">
                    <thead>
                        <tr>
                            <th>Job Site</th>
                            <th style="text-align:right;">Mess</th>
                            <th style="text-align:right;">Workshop</th>
                            <th style="text-align:right;">Perbaikan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($siteBreakdown)): ?>
                            <?php foreach($siteBreakdown as $idx => $site): ?>
                            <tr>
                                <td>
                                    <span class="gs-site-rank <?= $idx===0?'top1':($idx===1?'top2':($idx===2?'top3':'')) ?>"><?= $idx+1 ?></span>
                                    <?= esc($site['site_name'] ?? $site['site'] ?? '-') ?>
                                </td>
                                <td style="text-align:right;font-family:'DM Mono',monospace;font-size:0.75rem;"><?= $site['total_mess'] ?? 0 ?></td>
                                <td style="text-align:right;font-family:'DM Mono',monospace;font-size:0.75rem;"><?= $site['total_workshop'] ?? 0 ?></td>
                                <td style="text-align:right;">
                                    <span class="gs-badge <?= ($site['total_repair']??0) > 0 ? 'gs-badge-amber' : 'gs-badge-gray' ?>">
                                        <?= $site['total_repair'] ?? 0 ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align:center;color:#9ca3af;padding:24px 0;font-size:0.8rem;">Data site belum tersedia</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div><!-- end gs-dashboard -->

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── GLOBAL CHART DEFAULTS ─────────────────────────────────────────────
    Chart.defaults.font.family = "'DM Sans', sans-serif";
    Chart.defaults.font.size   = 11;
    Chart.defaults.color       = '#9ca3af';
    Chart.defaults.plugins.legend.display = false;

    // ── 1. COUNT-UP ANIMATION ─────────────────────────────────────────────
    document.querySelectorAll('.gs-card-value[data-count]').forEach(function(el) {
        const target = parseInt(el.dataset.count, 10);
        const span   = el.querySelector('span');
        let start = 0;
        const dur = 900, step = 16;
        const inc = target / (dur / step);
        const timer = setInterval(function() {
            start = Math.min(start + inc, target);
            span.textContent = Math.floor(start).toLocaleString('id-ID');
            if (start >= target) clearInterval(timer);
        }, step);
    });

    // ── 2. PROGRESS BAR ANIMATION ─────────────────────────────────────────
    setTimeout(function() {
        document.querySelectorAll('.gs-repair-bar[data-width]').forEach(function(bar) {
            bar.style.width = bar.dataset.width;
        });
    }, 300);

    // ── 3. TREND LINE CHART ───────────────────────────────────────────────
    const trendData = <?= json_encode($trendMonthly) ?>;
    const trendLabels   = trendData.map(d => d.bulan);
    const trendMess     = trendData.map(d => parseInt(d.total_mess  || 0));
    const trendWS       = trendData.map(d => parseInt(d.total_ws    || 0));
    const trendRepair   = trendData.map(d => parseInt(d.total_repair|| 0));

    new Chart(document.getElementById('chartTrend'), {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [
                {
                    label: 'Mess',
                    data: trendMess,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.08)',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: '#3b82f6',
                    fill: true,
                    tension: 0.4,
                },
                {
                    label: 'Workshop',
                    data: trendWS,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245,158,11,0.07)',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: '#f59e0b',
                    fill: true,
                    tension: 0.4,
                },
                {
                    label: 'Perbaikan',
                    data: trendRepair,
                    borderColor: '#ef4444',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    borderDash: [4, 3],
                    pointRadius: 3,
                    pointBackgroundColor: '#ef4444',
                    tension: 0.4,
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                tooltip: {
                    backgroundColor: '#111827',
                    padding: 10,
                    titleFont: { size: 11 },
                    bodyFont: { size: 11 },
                    callbacks: {
                        label: ctx => ' ' + ctx.dataset.label + ': ' + ctx.parsed.y
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, border: { display: false } },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f3f4f6' },
                    border: { display: false },
                    ticks: { stepSize: 1 }
                }
            }
        }
    });

    // ── 4. DONUT: KEPEMILIKAN ─────────────────────────────────────────────
    new Chart(document.getElementById('chartKepemilikan'), {
        type: 'doughnut',
        data: {
            labels: ['Milik', 'Sewa'],
            datasets: [{
                data: [<?= $totalMilik ?>, <?= $totalSewa ?>],
                backgroundColor: ['#111827', '#e5e7eb'],
                borderWidth: 0,
                hoverOffset: 4,
            }]
        },
        options: {
            cutout: '70%',
            plugins: {
                tooltip: {
                    backgroundColor: '#111827',
                    callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.parsed }
                }
            }
        }
    });

    // ── 5. DONUT: KOMPOSISI MESS vs WORKSHOP ──────────────────────────────
    new Chart(document.getElementById('chartKomposisi'), {
        type: 'doughnut',
        data: {
            labels: ['Mess', 'Workshop'],
            datasets: [{
                data: [<?= $totalMess ?>, <?= $totalWorkshop ?>],
                backgroundColor: ['#3b82f6', '#f59e0b'],
                borderWidth: 0,
                hoverOffset: 4,
            }]
        },
        options: {
            cutout: '70%',
            plugins: {
                tooltip: {
                    backgroundColor: '#111827',
                    callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.parsed }
                }
            }
        }
    });

    // ── 6. BAR: ASET PER SITE ─────────────────────────────────────────────
    const siteData   = <?= json_encode($siteBreakdown) ?>;
    const siteLabels = siteData.map(d => d.site_name || d.site || 'N/A');
    const siteMess   = siteData.map(d => parseInt(d.total_mess || 0));
    const siteWS     = siteData.map(d => parseInt(d.total_workshop || 0));

    new Chart(document.getElementById('chartSite'), {
        type: 'bar',
        data: {
            labels: siteLabels.length ? siteLabels : ['Belum ada data'],
            datasets: [
                {
                    label: 'Mess',
                    data: siteMess.length ? siteMess : [0],
                    backgroundColor: '#3b82f6',
                    borderRadius: 5,
                    barThickness: 14,
                },
                {
                    label: 'Workshop',
                    data: siteWS.length ? siteWS : [0],
                    backgroundColor: '#f59e0b',
                    borderRadius: 5,
                    barThickness: 14,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    backgroundColor: '#111827',
                    callbacks: { label: ctx => ' ' + ctx.dataset.label + ': ' + ctx.parsed.y }
                }
            },
            scales: {
                x: { stacked: false, grid: { display: false }, border: { display: false } },
                y: { stacked: false, beginAtZero: true, grid: { color: '#f3f4f6' }, border: { display: false }, ticks: { stepSize: 1 } }
            }
        }
    });

    // ── 7. BAR: PERBAIKAN PER KATEGORI ───────────────────────────────────
    const repairKategori = <?= json_encode($repair_by_kategori ?? [
        ['kategori'=>'Ringan', 'total'=>0],
        ['kategori'=>'Sedang', 'total'=>0],
        ['kategori'=>'Berat',  'total'=>0],
        ['kategori'=>'Darurat','total'=>0],
    ]) ?>;

    new Chart(document.getElementById('chartRepairKategori'), {
        type: 'bar',
        data: {
            labels: repairKategori.map(d => d.kategori),
            datasets: [{
                label: 'Jumlah',
                data: repairKategori.map(d => parseInt(d.total || 0)),
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#7f1d1d'],
                borderRadius: 6,
                barThickness: 28,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    backgroundColor: '#111827',
                    callbacks: { label: ctx => ' ' + ctx.parsed.y + ' pengajuan' }
                }
            },
            scales: {
                x: { grid: { display: false }, border: { display: false } },
                y: { beginAtZero: true, grid: { color: '#f3f4f6' }, border: { display: false }, ticks: { stepSize: 1 } }
            }
        }
    });

});
</script>

<?= $this->endSection() ?>