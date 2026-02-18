<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; border: 2px solid #000; padding: 20px; }
        .header { display: table; width: 100%; border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 20px; }
        .logo { display: table-cell; width: 70%; vertical-align: middle; }
        .logo-content { width: 100%; }
        .contact-info { display: table-cell; width: 30%; text-align: right; font-size: 11px; line-height: 1.6; vertical-align: top; }
        .employee-info { margin-bottom: 20px; font-size: 13px; }
        .info-table { width: 100%; }
        .info-table td { padding: 3px 0; }
        .info-label { width: 120px; font-weight: bold; }
        .section-title { background: #E5E5E5; padding: 8px; font-weight: bold; margin: 15px 0 5px 0; font-size: 13px; }
        table.data { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 10px; }
        table.data td { padding: 5px; }
        .item-label { width: 60%; }
        .item-value { text-align: right; }
        .total-row { font-weight: bold; border-top: 1px solid #000; border-bottom: 1px solid #000; }
        .gaji-diterima { font-weight: bold; font-size: 14px; margin-top: 10px; }
        .footer { text-align: right; margin-top: 40px; font-size: 12px; }
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

        /* Untuk cetak: jaga agar note tetap terpisah di bawah slip */
        @media print {
            .secret-note { page-break-before: always; }
        }
    </style>
</head>
<body>
    <?php
        // Gunakan optimized images dari cache
        $logoHeader = WRITEPATH . 'barcode/logo_header.png';
        $barcodePath = WRITEPATH . 'barcode/barcode_ttd.png';
        
        // Fallback ke original jika optimized belum ada
        if (!file_exists($logoHeader)) {
            $logoHeader = FCPATH . 'assets/img/logo_header.png';
        }
        if (!file_exists($barcodePath)) {
            $barcodePath = WRITEPATH . 'uploads/barcode_ttd.png';
        }
    ?>
    <div class="container">
        <div class="header">
            <div class="logo">
                <div class="logo-content">
                    <?php if (file_exists($logoHeader)): ?>
                    <img src="<?= $logoHeader ?>"
                        alt="Logo Header"
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
        
        <div class="employee-info">
            <table class="info-table">
                <tr>
                    <td class="info-label">NIK</td>
                    <td>: <?= $karyawan['nik'] ?></td>
                    <td class="info-label">SITE</td>
                    <td>: <?= $karyawan['site'] ?></td>
                </tr>
                <tr>
                    <td class="info-label">NAMA</td>
                    <td>: <?= $karyawan['nama'] ?></td>
                    <td class="info-label">BULAN</td>
                    <td>: <?= $karyawan['bulan'] ?></td>
                </tr>
                <tr>
                    <td class="info-label">JABATAN</td>
                    <td>: <?= $karyawan['jabatan'] ?></td>
                    <td class="info-label">NO SLIP</td>
                    <td>: <?= $karyawan['nomor_slip'] ?></td>
                </tr>
            </table>
        </div>

        <div class="section-title">PENDAPATAN</div>
        <table class="data">
            <tr>
                <td class="item-label">UMK</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= number_format($karyawan['umk'], 0, ',', '.') ?></td>
            </tr>
            <?php if($karyawan['tunjangan_tidak_tetap'] > 0): ?>
            <tr>
                <td class="item-label">Tunjangan Tidak Tetap</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= number_format($karyawan['tunjangan_tidak_tetap'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <?php if($karyawan['insentif_lain'] > 0): ?>
            <tr>
                <td class="item-label">Insentif Lain</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= number_format($karyawan['insentif_lain'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <?php if($karyawan['kompensasi'] > 0): ?>
            <tr>
                <td class="item-label">Kompensasi</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= number_format($karyawan['kompensasi'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <?php if(isset($karyawan['kompensasi_cuti']) && $karyawan['kompensasi_cuti'] > 0): ?>
            <tr>
                <td class="item-label">Kompensasi Cuti</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= number_format($karyawan['kompensasi_cuti'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <?php if(isset($karyawan['insentif_lembur']) && $karyawan['insentif_lembur'] > 0): ?>
            <tr>
                <td class="item-label">Insentif Lembur</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= number_format($karyawan['insentif_lembur'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <?php if(isset($karyawan['insentif_makan']) && $karyawan['insentif_makan'] > 0): ?>
            <tr>
                <td class="item-label">Insentif Makan</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= number_format($karyawan['insentif_makan'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <?php if(isset($karyawan['insentif_pulsa']) && $karyawan['insentif_pulsa'] > 0): ?>
            <tr>
                <td class="item-label">Insentif Pulsa</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= number_format($karyawan['insentif_pulsa'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <?php if(isset($karyawan['insentif_cuci_unit']) && $karyawan['insentif_cuci_unit'] > 0): ?>
            <tr>
                <td class="item-label">Insentif Cuci Unit</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= number_format($karyawan['insentif_cuci_unit'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <?php if(isset($karyawan['uang_tunggu']) && $karyawan['uang_tunggu'] > 0): ?>
            <tr>
                <td class="item-label">Uang Tunggu</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= number_format($karyawan['uang_tunggu'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <?php if(isset($karyawan['gaji_prorate']) && $karyawan['gaji_prorate'] > 0): ?>
            <tr>
                <td class="item-label">Gaji Prorate</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= number_format($karyawan['gaji_prorate'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <tr class="total-row">
                <td class="item-label">TOTAL PENDAPATAN</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= number_format($karyawan['total_pendapatan'], 0, ',', '.') ?></td>
            </tr>
        </table>

        <div class="section-title">POTONGAN</div>
        <table class="data">
            <?php if($karyawan['bpjs_kes'] > 0): ?>
            <tr>
                <td class="item-label">BPJS Kesehatan</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= number_format($karyawan['bpjs_kes'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <?php if($karyawan['bpjs_tk'] > 0): ?>
            <tr>
                <td class="item-label">BPJS Ketenagakerjaan</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= number_format($karyawan['bpjs_tk'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <?php if($karyawan['pot_pph21'] > 0): ?>
            <tr>
                <td class="item-label">PPh 21</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= number_format($karyawan['pot_pph21'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <?php if(isset($karyawan['lainnya']) && $karyawan['lainnya'] > 0): ?>
            <tr>
                <td class="item-label">Lainnya</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= number_format($karyawan['lainnya'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <tr class="total-row">
                <td class="item-label">TOTAL POTONGAN</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= number_format($karyawan['total_pot'], 0, ',', '.') ?></td>
            </tr>
        </table>

        <div class="gaji-diterima">
            <table class="data">
                <tr class="total-row">
                    <td class="item-label">GAJI DITERIMA</td>
                    <td class="item-value">Rp</td>
                    <td class="item-value"><?= number_format($karyawan['gaji_bersih'], 0, ',', '.') ?></td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="text-align:right; vertical-align:bottom;">
                        <div style="margin-bottom:6px;">
                            Malang, <?= date('d F Y') ?>
                        </div>

                        <?php if (file_exists($barcodePath)): ?>
                        <img src="<?= $barcodePath ?>"
                             alt="Tanda Tangan Digital"
                             style="display:block; margin:0 0 6px auto; max-width:80px; height:auto; padding-right:25px;">
                        <?php endif; ?>

                        <div style="padding-right:25px;">
                            ( Zahwa Salsabila )
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="secret-note">
        DOKUMEN INI SANGAT RAHASIA
        <br>
        TIDAK UNTUK DISEBAR LUASKAN KE PIHAK LAIN
    </div>
</body>
</html>