<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Slip Gaji - <?= esc($karyawan['nama'] ?? '') ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 20px;
            background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .logo {
            display: table-cell;
            width: 70%;
            vertical-align: middle;
        }
        .logo-content {
            width: 100%;
        }
        .contact-info {
            display: table-cell;
            width: 30%;
            text-align: right;
            font-size: 11px;
            line-height: 1.6;
            vertical-align: top;
        }
        .employee-info {
            margin-bottom: 20px;
            font-size: 13px;
        }
        .info-table {
            width: 100%;
        }
        .info-table td {
            padding: 3px 0;
        }
        .info-label {
            width: 120px;
            font-weight: bold;
        }
        .section-title {
            background: #E5E5E5;
            padding: 8px;
            font-weight: bold;
            margin: 15px 0 5px 0;
            font-size: 13px;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 10px;
        }
        table.data td {
            padding: 5px;
        }
        .item-label {
            width: 60%;
        }
        .item-value {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }
        .gaji-diterima {
            font-weight: bold;
            font-size: 14px;
            margin-top: 10px;
        }
        .footer {
            text-align: right;
            margin-top: 40px;
            font-size: 12px;
        }
        /* NOTE RAHASIA */
        .secret-note {
            width: 80%;
            margin: 18px auto 0 auto;
            text-align: center;
            font-weight: 700;
            font-size: 14px;
            padding: 10px 12px;
            border: 2px dashed #a00000;
            background: #fff0f0;
            color: #8b0000;
            letter-spacing: 1px;
            border-radius: 6px;
        }
        .action-buttons {
            max-width: 800px;
            margin: 0 auto 20px auto;
            text-align: center;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .action-buttons button,
        .action-buttons a {
            padding: 10px 20px;
            margin: 0 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        .btn-print    { background: #4CAF50; color: white; }
        .btn-download { background: #2196F3; color: white; }
        .btn-back     { background: #9E9E9E; color: white; }

        @media print {
            .action-buttons { display: none; }
            body { background: white; padding: 0; }
            .container { box-shadow: none; }
            .secret-note { page-break-before: always; }
        }
    </style>
</head>
<body>

    <div class="action-buttons">
        <button onclick="window.print()" class="btn-print">🖨️ Print / Save as PDF</button>
        <a href="<?= base_url('slip-gaji/generate-pdf/' . ($karyawan['id'] ?? '')) ?>" class="btn-download">📥 Download PDF</a>
        <a href="<?= base_url('slip-gaji') ?>" class="btn-back">← Kembali</a>
    </div>

    <?php
        // Path logo & barcode — sama persis dengan versi PDF
        $logoHeader = WRITEPATH . 'barcode/logo_header.png';
        $barcodePath = WRITEPATH . 'barcode/barcode_ttd.png';

        if (!file_exists($logoHeader)) {
            $logoHeader = FCPATH . 'assets/img/logo_header.png';
        }
        if (!file_exists($barcodePath)) {
            $barcodePath = WRITEPATH . 'uploads/barcode_ttd.png';
        }

        // Helper format rupiah
        function fmtIDR($v) {
            return number_format((float)$v, 0, ',', '.');
        }
    ?>

    <div class="container">

        <!-- HEADER -->
        <div class="header">
            <div class="logo">
                <div class="logo-content">
                    <?php if (file_exists($logoHeader)): 
                        $logoData = base64_encode(file_get_contents($logoHeader));
                        $logoSrc  = 'data:image/png;base64,' . $logoData;
                    ?>
                    <img src="<?= $logoSrc ?>" alt="Logo Header"
                        style="display:block; max-width:400px; height:auto;">
                    <?php endif; ?>
                </div>
            </div>
            <div class="contact-info">
                Jl. Panglima Sudirman No. 8 Kepanjen - Malang<br>
                Jawa Timur - Indonesia 65163<br>
                Telp : 62 341 395 524, 393 382<br>
                Fax. : 62 341 395 724<br>
                Email : info@bagongbis.com<br>
                Web : www.bagongbis.com
            </div>
        </div>

        <!-- INFO KARYAWAN -->
        <div class="employee-info">
            <table class="info-table">
                <tr>
                    <td class="info-label">NIK</td>
                    <td>: <?= esc($karyawan['nik'] ?? '') ?></td>
                    <td class="info-label">SITE</td>
                    <td>: <?= esc($karyawan['site'] ?? '') ?></td>
                </tr>
                <tr>
                    <td class="info-label">NAMA</td>
                    <td>: <?= esc($karyawan['nama'] ?? '') ?></td>
                    <td class="info-label">BULAN</td>
                    <td>: <?= esc($karyawan['bulan'] ?? '') ?></td>
                </tr>
                <tr>
                    <td class="info-label">JABATAN</td>
                    <td>: <?= esc($karyawan['jabatan'] ?? '') ?></td>
                    <td class="info-label">NO SLIP</td>
                    <td>: <?= esc($karyawan['nomor_slip'] ?? '') ?></td>
                </tr>
            </table>
        </div>

        <!-- PENDAPATAN -->
        <div class="section-title">PENDAPATAN</div>
        <table class="data">
            <tr>
                <td class="item-label">UMK</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= fmtIDR($karyawan['umk'] ?? 0) ?></td>
            </tr>

            <?php if (($karyawan['tunjangan_tidak_tetap'] ?? 0) > 0): ?>
            <tr>
                <td class="item-label">Tunjangan Tidak Tetap</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= fmtIDR($karyawan['tunjangan_tidak_tetap']) ?></td>
            </tr>
            <?php endif; ?>

            <?php if (($karyawan['insentif_lain'] ?? 0) > 0): ?>
            <tr>
                <td class="item-label">Insentif Lain</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= fmtIDR($karyawan['insentif_lain']) ?></td>
            </tr>
            <?php endif; ?>

            <?php if (($karyawan['kompensasi'] ?? 0) > 0): ?>
            <tr>
                <td class="item-label">Kompensasi</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= fmtIDR($karyawan['kompensasi']) ?></td>
            </tr>
            <?php endif; ?>

            <?php if (($karyawan['insentif_pulsa'] ?? 0) > 0): ?>
            <tr>
                <td class="item-label">Insentif Pulsa</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= fmtIDR($karyawan['insentif_pulsa']) ?></td>
            </tr>
            <?php endif; ?>

            <?php if (($karyawan['kompensasi_cuti'] ?? 0) > 0): ?>
            <tr>
                <td class="item-label">Kompensasi Cuti</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= fmtIDR($karyawan['kompensasi_cuti']) ?></td>
            </tr>
            <?php endif; ?>

            <?php if (($karyawan['insentif_lembur'] ?? 0) > 0): ?>
            <tr>
                <td class="item-label">Insentif Lembur</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= fmtIDR($karyawan['insentif_lembur']) ?></td>
            </tr>
            <?php endif; ?>

            <?php if (($karyawan['insentif_makan'] ?? 0) > 0): ?>
            <tr>
                <td class="item-label">Insentif Makan</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= fmtIDR($karyawan['insentif_makan']) ?></td>
            </tr>
            <?php endif; ?>

            <?php if (($karyawan['insentif_cuci_unit'] ?? 0) > 0): ?>
            <tr>
                <td class="item-label">Insentif Cuci Unit</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= fmtIDR($karyawan['insentif_cuci_unit']) ?></td>
            </tr>
            <?php endif; ?>

            <?php if (($karyawan['uang_tunggu'] ?? 0) > 0): ?>
            <tr>
                <td class="item-label">Uang Tunggu</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= fmtIDR($karyawan['uang_tunggu']) ?></td>
            </tr>
            <?php endif; ?>

            <?php if (($karyawan['gaji_prorate'] ?? 0) > 0): ?>
            <tr>
                <td class="item-label">Gaji Prorate</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= fmtIDR($karyawan['gaji_prorate']) ?></td>
            </tr>
            <?php endif; ?>

            <tr class="total-row">
                <td class="item-label">TOTAL PENDAPATAN</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= fmtIDR($karyawan['total_pendapatan'] ?? 0) ?></td>
            </tr>
        </table>

        <!-- POTONGAN -->
        <div class="section-title">POTONGAN</div>
        <table class="data">
            <?php if (($karyawan['bpjs_kes'] ?? 0) > 0): ?>
            <tr>
                <td class="item-label">BPJS Kesehatan</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= fmtIDR($karyawan['bpjs_kes']) ?></td>
            </tr>
            <?php endif; ?>

            <?php if (($karyawan['bpjs_tk'] ?? 0) > 0): ?>
            <tr>
                <td class="item-label">BPJS Ketenagakerjaan</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= fmtIDR($karyawan['bpjs_tk']) ?></td>
            </tr>
            <?php endif; ?>

            <?php if (($karyawan['pot_pph21'] ?? 0) > 0): ?>
            <tr>
                <td class="item-label">PPh 21</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= fmtIDR($karyawan['pot_pph21']) ?></td>
            </tr>
            <?php endif; ?>

            <?php if (($karyawan['lainnya'] ?? 0) > 0): ?>
            <tr>
                <td class="item-label">Lainnya</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= fmtIDR($karyawan['lainnya']) ?></td>
            </tr>
            <?php endif; ?>

            <tr class="total-row">
                <td class="item-label">TOTAL POTONGAN</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= fmtIDR($karyawan['total_pot'] ?? 0) ?></td>
            </tr>
        </table>

        <!-- GAJI DITERIMA -->
        <div class="gaji-diterima">
            <table class="data">
                <tr class="total-row">
                    <td class="item-label">GAJI DITERIMA</td>
                    <td class="item-value">Rp</td>
                    <td class="item-value"><?= fmtIDR($karyawan['gaji_bersih'] ?? 0) ?></td>
                </tr>
            </table>
        </div>

        <!-- FOOTER / TANDA TANGAN -->
        <div class="footer">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="text-align:right; vertical-align:bottom;">
                        <div style="margin-bottom:6px;">
                            Malang, <?= date('d F Y') ?>
                        </div>

                        <?php if (file_exists($barcodePath)): 
                            $ttdData = base64_encode(file_get_contents($barcodePath));
                            $ttdSrc  = 'data:image/png;base64,' . $ttdData;
                        ?>
                        <img src="<?= $ttdSrc ?>" alt="Tanda Tangan Digital"
                            style="display:block; margin:0 0 6px auto; max-width:80px; height:auto; padding-right:25px;">
                        <?php endif; ?>


                        <div style="padding-right:25px;">
                            ( Zahwa Salsabila )
                        </div>
                    </td>
                </tr>
            </table>
        </div>

    </div><!-- /.container -->

    <!-- NOTE RAHASIA -->
    <div class="secret-note">
        DOKUMEN INI SANGAT RAHASIA<br>
        TIDAK UNTUK DISEBAR LUASKAN KE PIHAK LAIN
    </div>

</body>
</html>