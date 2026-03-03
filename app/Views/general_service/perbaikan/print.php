<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Pengajuan — <?= esc($repair['kode_pengajuan'] ?? '') ?></title>
    <style>
        /* ── RESET & BASE ─────────────────────────────────────────── */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11pt;
            color: #111;
            background: #e8e8e8;
        }

        /* ── PAGE WRAPPER ─────────────────────────────────────────── */
        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            background: #fff;
            padding: 15mm 15mm 20mm;
            box-shadow: 0 4px 24px rgba(0,0,0,0.15);
            position: relative;
        }

        /* ── HEADER PERUSAHAAN ────────────────────────────────────── */
        .doc-header {
            text-align: center;
            border-bottom: 3px solid #111;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .company-name {
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .company-address {
            font-size: 9pt;
            color: #444;
            margin-top: 2px;
        }

        /* ── JUDUL DOKUMEN ────────────────────────────────────────── */
        .doc-title-block {
            text-align: center;
            margin-bottom: 14px;
        }
        .doc-title {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-subtitle {
            font-size: 10pt;
            color: #333;
            margin-top: 3px;
        }

        /* ── INFO DOKUMEN (grid 2 kolom) ──────────────────────────── */
        .doc-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0 24px;
            margin-bottom: 14px;
            border: 1px solid #ccc;
            padding: 10px 12px;
            background: #fafafa;
        }
        .doc-info-row {
            display: flex;
            gap: 0;
            padding: 2px 0;
            font-size: 10pt;
        }
        .doc-info-label {
            min-width: 130px;
            color: #555;
            font-weight: 600;
        }
        .doc-info-value {
            color: #111;
        }
        .doc-info-value::before { content: ': '; }

        /* ── SECTION TITLE ────────────────────────────────────────── */
        .section-title {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            background: #fff;
            color: #111;
            padding: 5px 10px;
            margin: 14px 0 0;
            letter-spacing: 0.5px;
            border: 1.5px solid #111;
            border-bottom: none;
        }

        /* ── MAIN TABLE (RAB Style) ───────────────────────────────── */
        .rab-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
        }
        .rab-table th {
            background: #f0f0f0;
            color: #111;
            padding: 7px 8px;
            text-align: center;
            border: 1px solid #999;
            font-size: 9.5pt;
            font-weight: bold;
        }
        .rab-table td {
            padding: 6px 8px;
            border: 1px solid #ccc;
            vertical-align: top;
        }
        .rab-table tbody tr:nth-child(even) {
            background: #f7f7f7;
        }
        .rab-table .text-center { text-align: center; }
        .rab-table .text-right  { text-align: right; }
        .rab-table .font-bold   { font-weight: bold; }
        .rab-table .total-row td {
            background: #f5f5f5;
            font-weight: bold;
            border-top: 2px solid #555;
        }
        .rab-table .grand-total-row td {
            background: #ebebeb;
            font-weight: bold;
            font-size: 10.5pt;
            border-top: 2px solid #333;
        }

        /* ── DETAIL KERUSAKAN BOX ─────────────────────────────────── */
        .detail-box {
            border: 1px solid #ccc;
            padding: 10px 12px;
            margin-top: 0;
            font-size: 10pt;
            line-height: 1.6;
        }
        .detail-box p { margin: 0; }

        /* ── FASILITAS / FILE ─────────────────────────────────────── */
        .info-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0 16px;
            border: 1px solid #ccc;
            border-top: none;
        }
        .info-2col .col {
            padding: 8px 12px;
        }
        .info-2col .col:first-child {
            border-right: 1px solid #ccc;
        }
        .info-label-sm {
            font-size: 8.5pt;
            font-weight: bold;
            color: #555;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .info-value {
            font-size: 10pt;
        }
        .badge-print {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 9pt;
            font-weight: bold;
            border: 1px solid currentColor;
        }
        .badge-pending    { color: #b45309; background: #fef3c7; }
        .badge-approved   { color: #15803d; background: #dcfce7; }
        .badge-inprogress { color: #6d28d9; background: #ede9fe; }
        .badge-completed  { color: #1e40af; background: #dbeafe; }
        .badge-rejected   { color: #b91c1c; background: #fee2e2; }
        .badge-cancelled  { color: #4b5563; background: #f3f4f6; }
        .badge-segera  { color: #b91c1c; background: #fee2e2; }
        .badge-normal  { color: #b45309; background: #fef3c7; }
        .badge-rendah  { color: #374151; background: #f3f4f6; }

        /* ── KETERANGAN LIST ──────────────────────────────────────── */
        .ket-list {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-top: none;
        }
        .ket-list ol {
            margin: 0;
            padding-left: 18px;
            font-size: 10pt;
        }
        .ket-list li { margin-bottom: 2px; }

        /* ── SIGNATURE SECTION ────────────────────────────────────── */
        .signature-section {
            margin-top: 20px;
        }
        .signature-header {
            text-align: center;
            font-size: 9.5pt;
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }
        .signature-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            border: 1px solid #ccc;
        }
        .signature-col {
            text-align: center;
            padding: 8px 6px;
            border-right: 1px solid #ccc;
        }
        .signature-col:last-child { border-right: none; }
        .sig-role {
            font-size: 8.5pt;
            font-weight: bold;
            color: #555;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .sig-name-line {
            height: 50px; /* ruang tanda tangan */
        }
        .sig-name {
            font-size: 9.5pt;
            font-weight: bold;
            border-top: 1px solid #333;
            padding-top: 4px;
            min-height: 18px;
        }
        .sig-title {
            font-size: 8.5pt;
            color: #555;
            margin-top: 1px;
        }

        /* ── FOOTER ───────────────────────────────────────────────── */
        .doc-footer {
            margin-top: 16px;
            font-size: 8.5pt;
            color: #777;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 6px;
        }

        /* ── WATERMARK STATUS ─────────────────────────────────────── */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 80pt;
            font-weight: bold;
            opacity: 0.04;
            pointer-events: none;
            z-index: 0;
            white-space: nowrap;
            color: #000;
            text-transform: uppercase;
        }

        /* ── STAMP / STEMPEL ─────────────────────────────────────── */
        .stamp {
            display: inline-block;
            border: 2.5px solid;
            border-radius: 5px;
            padding: 3px 8px;
            font-size: 9pt;
            font-weight: 900;
            letter-spacing: 2px;
            text-transform: uppercase;
            white-space: nowrap;
            transform: rotate(-8deg);
            opacity: 0.82;
            margin-top: 8px;
        }
        .stamp-approved  { border-color: #15803d; color: #15803d; }
        .stamp-completed { border-color: #1e40af; color: #1e40af; }
        .stamp-rejected  { border-color: #b91c1c; color: #b91c1c; }
        .stamp-pending   { border-color: #b45309; color: #b45309; }
        .stamp-cancelled { border-color: #4b5563; color: #4b5563; }

        /* ── PRINT TOOLBAR (tidak ter-print) ──────────────────────── */
        .print-toolbar {
            width: 210mm;
            margin: 0 auto 12px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .btn-print {
            padding: 8px 20px;
            background: #111;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 11pt;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-back {
            padding: 8px 16px;
            background: #fff;
            color: #333;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 11pt;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* ── PRINT MEDIA ──────────────────────────────────────────── */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
        @media print {
            body { background: #fff; }
            .print-toolbar { display: none !important; }
            .page {
                width: 100%;
                margin: 0;
                padding: 12mm 14mm 18mm;
                box-shadow: none;
            }
            @page {
                size: A4;
                margin: 0;
            }
            /* Paksa border & background tetap muncul saat print */
            .ket-list,
            .doc-info-grid,
            .detail-box,
            .info-2col,
            .info-2col .col,
            .signature-grid,
            .signature-col,
            .rab-table,
            .rab-table th,
            .rab-table td {
                border-color: #999 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .rab-table thead th {
                background: #f0f0f0 !important;
                color: #111 !important;
            }
            .rab-table .total-row td,
            .rab-table .grand-total-row td {
                background: #ddd !important;
            }
            .rab-table tbody tr:nth-child(even) td {
                background: #f7f7f7 !important;
            }
            .section-title {
                background: #fff !important;
                color: #111 !important;
                border: 1.5px solid #111 !important;
                border-bottom: none !important;
            }
            .doc-info-grid {
                background: #fafafa !important;
            }
            /* Stamp tetap muncul */
            .stamp { display: flex !important; }
        }
    </style>
</head>
<body>

<?php
// ── DATA PREPARATION ──────────────────────────────────────────────────────
$repair      = $repair      ?? [];
$kode        = $repair['kode_pengajuan']    ?? '-';
$tipeAset    = $repair['tipe_aset']         ?? '-';
$namaKary    = $repair['nama_karyawan']     ?? '-';
$nik         = $repair['nik']               ?? '-';
$site        = $repair['site_id']           ?? ($repair['lokasi_aset'] ?? '-');
$divisi      = $repair['divisi_name']       ?? '-';
$asetCode    = $repair['aset_code']         ?? ($repair['nama_aset'] ?? '-');
$kategori    = $repair['kategori_kerusakan']?? '-';
$jenis       = $repair['jenis_kerusakan']   ?? '-';
$deskripsi   = $repair['deskripsi_kerusakan']?? '-';
$prioritas   = $repair['prioritas']         ?? '-';
$status      = $repair['status']            ?? '-';
$tglAjuan    = !empty($repair['tanggal_pengajuan'])
               ? date('d F Y', strtotime($repair['tanggal_pengajuan'])) : '-';
$tglDisetujui= !empty($repair['tanggal_disetujui'])
               ? date('d F Y', strtotime($repair['tanggal_disetujui'])) : '-';
$tglSelesai  = !empty($repair['tanggal_selesai'])
               ? date('d F Y', strtotime($repair['tanggal_selesai'])) : '-';
$estimasi    = !empty($repair['estimasi_biaya']) && $repair['estimasi_biaya'] > 0
               ? 'Rp ' . number_format($repair['estimasi_biaya'], 0, ',', '.') : '-';
$biayaAktual = !empty($repair['biaya_aktual']) && $repair['biaya_aktual'] > 0
               ? 'Rp ' . number_format($repair['biaya_aktual'], 0, ',', '.') : '-';
$catatan     = $repair['catatan']           ?? '-';
$pic         = $repair['penanggung_jawab']  ?? '';
$approver    = $repair['disetujui_oleh_name']?? '';
$createdBy   = $repair['created_by_name']   ?? '';
$catSelesai  = $repair['catatan_selesai']   ?? '';

// Status badge class
$statusClass = [
    'Pending'     => 'badge-pending',
    'Approved'    => 'badge-approved',
    'In Progress' => 'badge-inprogress',
    'Completed'   => 'badge-completed',
    'Rejected'    => 'badge-rejected',
    'Cancelled'   => 'badge-cancelled',
][$status] ?? 'badge-pending';

$prioritasClass = [
    'Segera' => 'badge-segera',
    'Normal' => 'badge-normal',
    'Rendah' => 'badge-rendah',
][$prioritas] ?? 'badge-normal';

// Items perbaikan (dari deskripsi, atau bisa dari tabel items jika ada)
// Parsing sederhana: 1 baris = 1 item
$itemLines = array_filter(array_map('trim', explode("\n", $deskripsi)));
$noItems   = count($itemLines) > 1; // mode multi-baris

// Foto kerusakan
$fotoKerusakan = is_array($repair['foto_kerusakan'])
                 ? $repair['foto_kerusakan']
                 : (json_decode($repair['foto_kerusakan'] ?? '[]', true) ?? []);
$lampiranArr = is_array($repair['lampiran'])
             ? $repair['lampiran']
             : (json_decode($repair['lampiran'] ?? '[]', true) ?? []);
$totalFile = count($fotoKerusakan) + count($lampiranArr);
?>

<!-- WATERMARK -->
<div class="watermark"><?= esc($status) ?></div>

<!-- PRINT TOOLBAR -->
<div class="print-toolbar">
    <button class="btn-print" onclick="window.print()">
        &#128438; Cetak / Save PDF
    </button>
    <a href="javascript:history.back()" class="btn-back">
        &#8592; Kembali
    </a>
    <span style="font-size:9pt; color:#666; margin-left:4px;">
        Dokumen: <?= esc($kode) ?> &mdash; <?= date('d M Y H:i') ?>
    </span>
</div>

<!-- A4 PAGE -->
<div class="page">

    <!-- HEADER PERUSAHAAN -->
    <div class="doc-header" style="display:flex; align-items:center; gap:16px; text-align:left; border-bottom:3px solid #111; padding-bottom:10px; margin-bottom:14px;">
        <!-- LOGO -->
        <div style="flex-shrink:0;">
            <img src="<?= base_url('logo/logo_header.png') ?>"
                 onerror="this.style.display='none'"
                 alt="Logo"
                 style="height:80px; width:auto; object-fit:contain;">
        </div>
        <!-- NAMA PERUSAHAAN -->
        <div style="flex:1; text-align:center;">
            <div class="company-name">Pengajuan Perbaikan Aset <?= esc($tipeAset) ?></div>
            <div class="company-address">General Service Division</div>
        </div>
        <!-- Spacer kanan agar nama tetap center -->
        <div style="flex-shrink:0; width:60px;"></div>
    </div>

    <!-- JUDUL -->
    <!-- <div class="doc-title-block">
        <div class="doc-title">Pengajuan Perbaikan Aset <?= esc($tipeAset) ?></div>
        <div class="doc-subtitle">
            <?= esc($asetCode) ?>
        </div>
    </div> -->

    <!-- INFO GRID -->
    <div class="doc-info-grid">
        <div>
            <div class="doc-info-row">
                <span class="doc-info-label">Kode Pengajuan</span>
                <span class="doc-info-value"><strong><?= esc($kode) ?></strong></span>
            </div>
            <div class="doc-info-row">
                <span class="doc-info-label">Kode Aset</span>
                <span class="doc-info-value"><?= esc($asetCode) ?></span>
            </div>
            <div class="doc-info-row">
                <span class="doc-info-label">Job Site / Lokasi</span>
                <span class="doc-info-value"><?= esc($site) ?></span>
            </div>
        </div>
        <div>
            <div class="doc-info-row">
                <span class="doc-info-label">Penanggung Jawab</span>
                <span class="doc-info-value"><?= esc($namaKary) ?></span>
            </div>
            <div class="doc-info-row">
                <span class="doc-info-label">NIK</span>
                <span class="doc-info-value"><?= esc($nik) ?></span>
            </div>
            <div class="doc-info-row">
                <span class="doc-info-label">Tanggal Pengajuan</span>
                <span class="doc-info-value"><?= esc($tglAjuan) ?></span>
            </div>
        </div>
    </div>

    <!-- TABEL RAB UTAMA -->
    <div class="section-title">Rincian Pekerjaan Perbaikan</div>
    <table class="rab-table">
        <thead>
            <tr>
                <th width="35">NO</th>
                <th>URAIAN PEKERJAAN</th>
                <th width="55">KATEGORI</th>
                <th width="60">VOLUME</th>
                <th width="40">UNIT</th>
                <th width="110">HARGA SATUAN</th>
                <th width="110">HARGA TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Jika ada items detail dari tabel terpisah — gunakan $repair_items
            // Fallback: tampilkan estimasi sebagai 1 baris
            $items = $repair_items ?? [];

            if (!empty($items)):
                $grandTotal = 0;
                foreach ($items as $idx => $itm):
                    $hargaSat  = (float)($itm['harga_satuan'] ?? 0);
                    $volume    = (float)($itm['volume'] ?? 1);
                    $total     = $hargaSat * $volume;
                    $grandTotal += $total;
            ?>
            <tr>
                <td class="text-center"><?= $idx + 1 ?></td>
                <td><?= esc($itm['uraian'] ?? '-') ?></td>
                <td class="text-center"><?= esc($itm['kategori'] ?? $kategori) ?></td>
                <td class="text-center"><?= $volume ?></td>
                <td class="text-center"><?= esc($itm['unit'] ?? 'unit') ?></td>
                <td class="text-right"><?= $hargaSat > 0 ? number_format($hargaSat, 0, ',', '.') : '-' ?></td>
                <td class="text-right"><?= $total > 0 ? number_format($total, 0, ',', '.') : '-' ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="6" class="text-right">TOTAL ESTIMASI</td>
                <td class="text-right"><?= number_format($grandTotal, 0, ',', '.') ?></td>
            </tr>
            <?php else: ?>
            <!-- Fallback: 1 baris dari deskripsi -->
            <tr>
                <td class="text-center">1</td>
                <td><?= esc($jenis) ?></td>
                <td class="text-center"><?= esc($kategori) ?></td>
                <td class="text-center">1</td>
                <td class="text-center">ls</td>
                <td class="text-right">
                    <?= ($repair['estimasi_biaya'] ?? 0) > 0
                        ? number_format($repair['estimasi_biaya'], 0, ',', '.')
                        : '-' ?>
                </td>
                <td class="text-right">
                    <?= ($repair['estimasi_biaya'] ?? 0) > 0
                        ? number_format($repair['estimasi_biaya'], 0, ',', '.')
                        : '-' ?>
                </td>
            </tr>
            <tr class="total-row">
                <td colspan="6" class="text-right">TOTAL ESTIMASI BIAYA</td>
                <td class="text-right">
                    <?= ($repair['estimasi_biaya'] ?? 0) > 0
                        ? number_format($repair['estimasi_biaya'], 0, ',', '.')
                        : '-' ?>
                </td>
            </tr>
            <?php if (!empty($repair['biaya_aktual']) && $repair['biaya_aktual'] > 0): ?>
            <tr class="grand-total-row">
                <td colspan="6" class="text-right">BIAYA AKTUAL (REALISASI)</td>
                <td class="text-right"><?= number_format($repair['biaya_aktual'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- DESKRIPSI KERUSAKAN -->
    <div class="section-title">Deskripsi Kerusakan</div>
    <div class="detail-box">
        <?= nl2br(esc($deskripsi)) ?>
    </div>

    <!-- INFO TAMBAHAN (2 kolom) -->
    <div class="info-2col">
        <div class="col">
            <div class="info-label-sm">Jenis Kerusakan</div>
            <div class="info-value"><?= esc($jenis) ?></div>
        </div>
        <div class="col">
            <div class="info-label-sm">Lampiran / Foto</div>
            <div class="info-value">
                <?= $totalFile > 0 ? $totalFile . ' file terlampir' : 'Tidak ada lampiran' ?>
            </div>
        </div>
    </div>

    <?php if (!empty($catatan) && $catatan !== '-'): ?>
    <div class="info-2col" style="margin-top: 0;">
        <div class="col" style="grid-column: span 2; border-right: none;">
            <div class="info-label-sm">Catatan</div>
            <div class="info-value"><?= nl2br(esc($catatan)) ?></div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($catSelesai)): ?>
    <div class="info-2col" style="margin-top: 0;">
        <div class="col" style="grid-column: span 2; border-right: none;">
            <div class="info-label-sm">Catatan Selesai</div>
            <div class="info-value"><?= nl2br(esc($catSelesai)) ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- KETERANGAN -->
    <div class="section-title">Keterangan</div>
    <div class="ket-list">
        <ol>
            <li>Pengajuan perbaikan <?= esc($tipeAset) ?> dengan kode <strong><?= esc($asetCode) ?></strong> di lokasi <strong><?= esc($site) ?></strong>.</li>
            <li>Kategori kerusakan: <strong><?= esc($kategori) ?></strong> &mdash; Prioritas: <strong><?= esc($prioritas) ?></strong>.</li>
            <?php if ($tglDisetujui !== '-'): ?>
            <li>Disetujui pada: <strong><?= esc($tglDisetujui) ?></strong>
                <?= !empty($approver) ? 'oleh <strong>' . esc($approver) . '</strong>' : '' ?>.
            </li>
            <?php endif; ?>
            <?php if ($tglSelesai !== '-'): ?>
            <li>Pekerjaan selesai pada: <strong><?= esc($tglSelesai) ?></strong>.</li>
            <?php endif; ?>
            <?php if (!empty($repair['alasan_penolakan'])): ?>
            <li>Alasan penolakan: <?= esc($repair['alasan_penolakan']) ?></li>
            <?php endif; ?>
        </ol>
    </div>

    <!-- GRAND TOTAL BOX -->
    <!-- <table class="rab-table" style="margin-top: 0; border-top: none;">
        <tr class="grand-total-row">
            <td style="padding: 8px 12px; border: 1px solid #bbb;">
                GRAND TOTAL PEKERJAAN
            </td>
            <td class="text-right" style="padding: 8px 12px; border: 1px solid #bbb; min-width: 160px;">
                <?= ($repair['estimasi_biaya'] ?? 0) > 0
                    ? number_format($repair['estimasi_biaya'], 0, ',', '.')
                    : '-' ?>
            </td>
        </tr>
        <?php if (!empty($repair['biaya_aktual']) && $repair['biaya_aktual'] > 0): ?>
        <tr class="grand-total-row">
            <td style="padding: 8px 12px; border: 1px solid #bbb;">
                REALISASI BIAYA AKTUAL
            </td>
            <td class="text-right" style="padding: 8px 12px; border: 1px solid #bbb;">
                <?= number_format($repair['biaya_aktual'], 0, ',', '.') ?>
            </td>
        </tr>
        <?php endif; ?>
    </table> -->

    <!-- TANDA TANGAN -->
    <?php
    $stampClass = [
        'Approved'    => 'stamp-approved',
        'Completed'   => 'stamp-approved',
        'Rejected'    => 'stamp-rejected',
        'Pending'     => 'stamp-pending',
        'In Progress' => 'stamp-pending',
        'Cancelled'   => 'stamp-cancelled',
    ][$status] ?? 'stamp-pending';
    $stampLabel = [
        'Approved'    => 'APPROVED',
        'Completed'   => 'APPROVED',
        'Rejected'    => 'REJECTED',
        'Pending'     => 'PENDING',
        'In Progress' => 'IN PROGRESS',
        'Cancelled'   => 'CANCELLED',
    ][$status] ?? $status;
    function shortName($name, $max = 17) {
    $name = trim($name ?: '');
    return mb_strlen($name) > $max 
        ? mb_substr($name, 0, $max) . '.' 
        : ($name ?: '_______________');
}
    ?>
    <div class="signature-section" style="margin-top:20px;">
        <!-- Baris label: Membuat | Memeriksa | Mengetahui | Menyetujui -->
        <table style="width:100%;border-collapse:collapse;table-layout:fixed;border:1px solid #aaa;border-bottom:none;">
            <tr>
                <td style="width:20%;text-align:center;padding:5px 4px;font-size:9.5pt;font-weight:bold;border-right:1px solid #aaa;">Membuat,</td>
                <td style="width:40%;text-align:center;padding:5px 4px;font-size:9.5pt;font-weight:bold;border-right:1px solid #aaa;" colspan="2">Memeriksa,</td>
                <td style="width:20%;text-align:center;padding:5px 4px;font-size:9.5pt;font-weight:bold;border-right:1px solid #aaa;">Mengetahui,</td>
                <td style="width:20%;text-align:center;padding:5px 4px;font-size:9.5pt;font-weight:bold;">Menyetujui,</td>
            </tr>
        </table>
        <!-- Grid 5 kolom tanda tangan -->
        <table style="width:100%;border-collapse:collapse;table-layout:fixed;border:1px solid #aaa;">
            <tr>
                <!-- Kolom 1: Membuat / Foreman GS -->
                <td style="width:20%;text-align:center;vertical-align:bottom;padding:8px 6px 6px;border-right:1px solid #aaa;">
                    <div style="height:55px;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;">
                        <!-- <?php if (in_array($status, ['Approved','Completed','In Progress'])): ?>
                        <div class="stamp <?= $stampClass ?>"><?= $stampLabel ?></div>
                        <?php endif; ?> -->

                        <div class="stamp stamp-approved">APPROVED</div>
                    </div>
                    <div style="border-top:1px solid #333;padding-top:4px;font-size:9.5pt;font-weight:bold;"><?= shortName(esc($namaKary)) ?></div>
                    <div style="font-size:8.5pt;font-weight:bold;color:#444;">Foreman GS</div>
                </td>
                <!-- Kolom 2: Memeriksa / Supervisor GS -->
                <td style="width:20%;text-align:center;vertical-align:bottom;padding:8px 6px 6px;border-right:1px solid #aaa;">
                    <div style="height:55px;"></div>
                    <div style="border-top:1px solid #333;padding-top:4px;font-size:9.5pt;font-weight:bold;">Nurman Handitiya</div>
                    <div style="font-size:8.5pt;font-weight:bold;color:#444;">Supervisor GS</div>
                </td>
                <!-- Kolom 3: Memeriksa / General Manager -->
                <td style="width:20%;text-align:center;vertical-align:bottom;padding:8px 6px 6px;border-right:1px solid #aaa;">
                    <div style="height:55px;"></div>
                    <div style="border-top:1px solid #333;padding-top:4px;font-size:9.5pt;font-weight:bold;">Arief Hardianto</div>
                    <div style="font-size:8.5pt;font-weight:bold;color:#444;">General Manager</div>
                </td>
                <!-- Kolom 4: Mengetahui / General Manager -->
                <td style="width:20%;text-align:center;vertical-align:bottom;padding:8px 6px 6px;border-right:1px solid #aaa;">
                    <div style="height:55px;"></div>
                    <div style="border-top:1px solid #333;padding-top:4px;font-size:9.5pt;font-weight:bold;">Nur Rizka DS</div>
                    <div style="font-size:8.5pt;font-weight:bold;color:#444;">General Manager</div>
                </td>
                <!-- Kolom 5: Menyetujui / Direktur Utama -->
                <td style="width:20%;text-align:center;vertical-align:bottom;padding:8px 6px 6px;">
                    <div style="height:55px;"></div>
                    <div style="border-top:1px solid #333;padding-top:4px;font-size:9.5pt;font-weight:bold;">Budi Susilo</div>
                    <div style="font-size:8.5pt;font-weight:bold;color:#444;">Direktur Utama</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- FOOTER -->
    <div class="doc-footer">
        Dicetak pada <?= date('d F Y, H:i') ?> &mdash; <?= esc($kode) ?> &mdash; PT Bagong Dekaka Makmur / General Service
    </div>

</div><!-- end .page -->

</body>
</html>