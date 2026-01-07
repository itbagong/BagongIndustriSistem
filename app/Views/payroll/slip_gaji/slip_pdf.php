<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; border: 2px solid #000; padding: 20px; }
        .header { display: table; width: 100%; border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 20px; }
        .logo { display: table-cell; width: 50%; }
        .logo-content { display: table; }
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
        .logo-text { display: table-cell; vertical-align: middle; padding-left: 15px; }
        .company-name { font-size: 32px; font-weight: bold; color: #C41E3A; }
        .company-subtitle { font-size: 14px; color: #000; }
        .contact-info { display: table-cell; width: 50%; text-align: right; font-size: 11px; line-height: 1.6; vertical-align: top; }
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
        .signature { margin-top: 60px; }
</style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <div class="logo-content">
                    <div class="logo-img"><img src="<?= base_url('assets/img/logo.png') ?>" alt="Logo"></div>
                    <div class="logo-text">
                        <div class="company-name">BAGONG</div>
                        <div class="company-subtitle">PT. BAGONG DEKAKA MAKMUR</div>
                    </div>
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
                <td class="info-label">NO.</td>
                <td>: <?= $karyawan['id'] ?></td>
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
    <table class="data">
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
        <table class="data">
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