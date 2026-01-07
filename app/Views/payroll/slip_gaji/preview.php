<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Slip Gaji - <?= $karyawan['nama'] ?></title>
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
            display: flex; 
            justify-content: space-between; 
            border-bottom: 2px solid #000; 
            padding-bottom: 15px; 
            margin-bottom: 20px; 
        }
        .logo {
        display: flex;
        align-items: center;
        }

        .logo-img {
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        overflow: hidden; /* supaya logo tidak keluar lingkaran */
        }

        .logo-img img {
        width: 70%;
        height: auto;
        }

        .company-name { font-size: 32px; font-weight: bold; color: #C41E3A; }
        .company-subtitle { font-size: 14px; color: #000; }
        .contact-info { text-align: right; font-size: 11px; line-height: 1.6; }
        .employee-info { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 10px; 
            margin-bottom: 20px; 
            font-size: 13px; 
        }
        .info-row { display: flex; }
        .info-label { width: 120px; font-weight: bold; }
        .section-title { 
            background: #E5E5E5; 
            padding: 8px; 
            font-weight: bold; 
            margin: 15px 0 5px 0; 
            font-size: 13px; 
        }
        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 10px; }
        td { padding: 5px; }
        .item-label { width: 60%; }
        .item-value { text-align: right; }
        .total-row { 
            font-weight: bold; 
            border-top: 1px solid #000; 
            border-bottom: 1px solid #000; 
        }
        .gaji-diterima { font-weight: bold; font-size: 14px; margin-top: 10px; }
        .footer { text-align: right; margin-top: 40px; font-size: 12px; }
        .signature { margin-top: 60px; }
        .action-buttons {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .action-buttons button, .action-buttons a {
            padding: 10px 20px;
            margin: 0 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        .btn-print { background: #4CAF50; color: white; }
        .btn-download { background: #2196F3; color: white; }
        .btn-back { background: #9E9E9E; color: white; }
        @media print {
            .action-buttons { display: none; }
            body { background: white; }
            .container { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="action-buttons">
        <button onclick="window.print()" class="btn-print">
            🖨️ Print / Save as PDF
        </button>
        <a href="<?= base_url('slip-gaji/generate-pdf/' . $karyawan['id']) ?>" class="btn-download">
            📥 Download PDF
        </a>
        <a href="<?= base_url('slip-gaji') ?>" class="btn-back">
            ← Kembali
        </a>
    </div>

    <div class="container">
        <div class="header">
            <div class="logo">
                <div class="logo-img"><img src="<?= base_url('assets/img/logo.png') ?>" alt="Logo"></div>
                <div>
                    <div class="company-name">BAGONG</div>
                    <div class="company-subtitle">PT. BAGONG DEKAKA MAKMUR</div>
                </div>
            </div>
            <div class="contact-info">
                Jl. Panglima Sudirman No. 8 Kepanjen - Malang<br>
                Jawa Timur - Indonesia 65163<br>
                ☎ : 62 341 395 524, 393 382<br>
                Fax. : 62 341 395 724<br>
                📧 : info@bagongbis.com<br>
                🌐 : www.bagongbis.com
            </div>
        </div>

        <div class="employee-info">
            <div>
                <div class="info-row">
                    <span class="info-label">NIK</span>
                    <span>: <?= $karyawan['nik'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">NAMA</span>
                    <span>: <?= $karyawan['nama'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">JABATAN</span>
                    <span>: <?= $karyawan['jabatan'] ?></span>
                </div>
            </div>
            <div>
                <div class="info-row">
                    <span class="info-label">SITE</span>
                    <span>: <?= $karyawan['site'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">BULAN</span>
                    <span>: <?= $karyawan['bulan'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">NO.</span>
                    <span>: <?= $karyawan['id'] ?></span>
                </div>
            </div>
        </div>

        <div class="section-title">PENDAPATAN</div>
        <table>
            <tr>
                <td class="item-label">UMK</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= number_format($karyawan['umk'], 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td class="item-label">Insentif Lain</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= number_format($karyawan['insentif_lain'], 0, ',', '.') ?></td>
            </tr>
            <?php if($karyawan['gaji_prorate'] > 0): ?>
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
        <table>
            <tr>
                <td class="item-label">BPJS Kesehatan</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= number_format($karyawan['bpjs_kes'], 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td class="item-label">BPJS Ketenagakerjaan</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= number_format($karyawan['bpjs_tk'], 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td class="item-label">PPh 21</td>
                <td class="item-value">Rp</td>
                <td class="item-value"><?= number_format($karyawan['pot_pph21'], 0, ',', '.') ?></td>
            </tr>
            <?php if($karyawan['lainnya'] > 0): ?>
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
            <table>
                <tr class="total-row">
                    <td class="item-label">GAJI DITERIMA</td>
                    <td class="item-value">Rp</td>
                    <td class="item-value"><?= number_format($karyawan['gaji_bersih'], 0, ',', '.') ?></td>
                </tr>
            </table>
        </div>

        <div class="footer">
            Malang, <?= date('d F Y') ?><br>
            <div class="signature">
                ( Tis'ah Amalia )
            </div>
        </div>
    </div>
</body>
</html>