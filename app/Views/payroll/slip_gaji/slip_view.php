<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @page {
            margin: 15mm;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #333;
        }
        
        .container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            background: #fff;
        }
        
        /* Header Section */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #C41E3A;
        }
        
        .header-left {
            display: table-cell;
            width: 70%;
            vertical-align: middle;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo {
            width: 70px;
            height: 70px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .logo img {
            max-width: 55px;
            max-height: 55px;
            object-fit: contain;
        }
        
        .company-info h1 {
            font-size: 26pt;
            font-weight: 700;
            color: #C41E3A;
            margin-bottom: 2px;
            letter-spacing: 1px;
        }
        
        .company-info h2 {
            font-size: 11pt;
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
        }
        
        .company-info p {
            font-size: 9pt;
            color: #666;
            line-height: 1.4;
        }
        
        .header-right {
            display: table-cell;
            width: 30%;
            vertical-align: top;
            text-align: right;
            font-size: 9pt;
            color: #666;
            line-height: 1.6;
        }
        
        /* Title Section */
        .slip-title {
            text-align: center;
            background: linear-gradient(135deg, #C41E3A 0%, #8B1428 100%);
            color: white;
            padding: 12px;
            margin: 20px 0;
            border-radius: 6px;
            font-size: 16pt;
            font-weight: 700;
            letter-spacing: 2px;
        }
        
        /* Employee Info */
        .employee-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #C41E3A;
        }
        
        .employee-grid {
            display: table;
            width: 100%;
        }
        
        .employee-row {
            display: table-row;
        }
        
        .employee-cell {
            display: table-cell;
            padding: 6px 0;
            width: 25%;
        }
        
        .employee-label {
            font-weight: 600;
            color: #555;
            font-size: 10pt;
        }
        
        .employee-value {
            color: #333;
            font-size: 10pt;
            font-weight: 500;
        }
        
        .employee-colon {
            width: 20px;
            color: #999;
        }
        
        /* Section Headers */
        .section-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 10px 15px;
            margin: 20px 0 10px 0;
            border-left: 4px solid #C41E3A;
            font-weight: 700;
            font-size: 11pt;
            color: #333;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Data Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .data-table tr {
            border-bottom: 1px solid #e9ecef;
        }
        
        .data-table td {
            padding: 10px 15px;
            font-size: 10pt;
        }
        
        .data-table tr:hover {
            background: #f8f9fa;
        }
        
        .label-col {
            width: 60%;
            color: #555;
        }
        
        .currency-col {
            width: 5%;
            color: #999;
            text-align: right;
            font-size: 9pt;
        }
        
        .amount-col {
            width: 35%;
            text-align: right;
            font-weight: 600;
            color: #333;
        }
        
        /* Total Rows */
        .total-row td {
            background: #f8f9fa;
            font-weight: 700;
            font-size: 11pt;
            color: #333;
            border-top: 2px solid #C41E3A;
            border-bottom: 2px solid #C41E3A;
            padding: 12px 15px;
        }
        
        .subtotal-row td {
            background: #fff;
            font-weight: 600;
            color: #555;
            border-top: 1px solid #dee2e6;
            padding: 10px 15px;
        }
        
        /* Final Salary Box */
        .final-salary {
            background: linear-gradient(135deg, #C41E3A 0%, #8B1428 100%);
            color: white;
            padding: 18px 20px;
            border-radius: 8px;
            margin: 25px 0;
            box-shadow: 0 4px 6px rgba(196, 30, 58, 0.2);
        }
        
        .final-salary table {
            width: 100%;
        }
        
        .final-salary td {
            padding: 0;
        }
        
        .final-label {
            font-size: 13pt;
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        .final-amount {
            font-size: 18pt;
            font-weight: 700;
            text-align: right;
        }
        
        /* Footer */
        .footer {
            margin-top: 40px;
            text-align: right;
        }
        
        .signature-section {
            display: inline-block;
            text-align: center;
            min-width: 200px;
        }
        
        .signature-location {
            font-size: 10pt;
            color: #555;
            margin-bottom: 5px;
        }
        
        .signature-position {
            font-size: 9pt;
            color: #666;
            margin-bottom: 50px;
        }
        
        .signature-name {
            font-size: 10pt;
            font-weight: 700;
            color: #333;
            padding-top: 10px;
            border-top: 2px solid #333;
        }
        
        /* Notes Section */
        .notes {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px 15px;
            margin-top: 20px;
            border-radius: 4px;
            font-size: 9pt;
            color: #856404;
        }
        
        .notes strong {
            display: block;
            margin-bottom: 5px;
            color: #856404;
        }
        
        /* Confidential Watermark */
        .confidential {
            text-align: center;
            font-size: 8pt;
            color: #999;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="logo-section">
                    <div class="logo">
                        <img src="<?= base_url('assets/img/logo.png') ?>" alt="Logo PT Bagong">
                    </div>
                    <div class="company-info">
                        <h1>BAGONG</h1>
                        <h2>PT. BAGONG DEKAKA MAKMUR</h2>
                        <p>Jl. Panglima Sudirman No. 8, Kepanjen - Malang</p>
                    </div>
                </div>
            </div>
            <div class="header-right">
                <strong>Kantor Pusat</strong><br>
                Jawa Timur 65163<br>
                <br>
                <strong>Kontak</strong><br>
                Telp: (0341) 395 524<br>
                Fax: (0341) 395 724<br>
                info@bagongbis.com<br>
                www.bagongbis.com
            </div>
        </div>

        <!-- Title -->
        <div class="slip-title">
            SLIP GAJI KARYAWAN
        </div>

        <!-- Employee Information -->
        <div class="employee-section">
            <div class="employee-grid">
                <div class="employee-row">
                    <div class="employee-cell">
                        <span class="employee-label">NIK</span>
                    </div>
                    <div class="employee-cell employee-colon">:</div>
                    <div class="employee-cell">
                        <span class="employee-value"><?= $karyawan['nik'] ?></span>
                    </div>
                    <div class="employee-cell"></div>
                    <div class="employee-cell">
                        <span class="employee-label">Site</span>
                    </div>
                    <div class="employee-cell employee-colon">:</div>
                    <div class="employee-cell">
                        <span class="employee-value"><?= $karyawan['site'] ?></span>
                    </div>
                </div>
                <div class="employee-row">
                    <div class="employee-cell">
                        <span class="employee-label">Nama</span>
                    </div>
                    <div class="employee-cell employee-colon">:</div>
                    <div class="employee-cell">
                        <span class="employee-value"><?= strtoupper($karyawan['nama']) ?></span>
                    </div>
                    <div class="employee-cell"></div>
                    <div class="employee-cell">
                        <span class="employee-label">Periode</span>
                    </div>
                    <div class="employee-cell employee-colon">:</div>
                    <div class="employee-cell">
                        <span class="employee-value"><?= $karyawan['bulan'] ?></span>
                    </div>
                </div>
                <div class="employee-row">
                    <div class="employee-cell">
                        <span class="employee-label">Jabatan</span>
                    </div>
                    <div class="employee-cell employee-colon">:</div>
                    <div class="employee-cell">
                        <span class="employee-value"><?= $karyawan['jabatan'] ?></span>
                    </div>
                    <div class="employee-cell"></div>
                    <div class="employee-cell">
                        <span class="employee-label">Status</span>
                    </div>
                    <div class="employee-cell employee-colon">:</div>
                    <div class="employee-cell">
                        <span class="employee-value"><?= $karyawan['status'] ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pendapatan Section -->
        <div class="section-header">
            💰 PENDAPATAN
        </div>
        <table class="data-table">
            <tr>
                <td class="label-col">Upah Minimum Kabupaten (UMK)</td>
                <td class="currency-col">Rp</td>
                <td class="amount-col"><?= number_format($karyawan['umk'], 0, ',', '.') ?></td>
            </tr>
            <?php if($karyawan['insentif_lain'] > 0): ?>
            <tr>
                <td class="label-col">Insentif Lain-lain</td>
                <td class="currency-col">Rp</td>
                <td class="amount-col"><?= number_format($karyawan['insentif_lain'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <?php if($karyawan['insentif_pulsa'] > 0): ?>
            <tr>
                <td class="label-col">Insentif Pulsa</td>
                <td class="currency-col">Rp</td>
                <td class="amount-col"><?= number_format($karyawan['insentif_pulsa'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <?php if($karyawan['kompensasi_cuti'] > 0): ?>
            <tr>
                <td class="label-col">Kompensasi Cuti</td>
                <td class="currency-col">Rp</td>
                <td class="amount-col"><?= number_format($karyawan['kompensasi_cuti'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <?php if($karyawan['insentif_lembur'] > 0): ?>
            <tr>
                <td class="label-col">Insentif Lembur</td>
                <td class="currency-col">Rp</td>
                <td class="amount-col"><?= number_format($karyawan['insentif_lembur'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <?php if($karyawan['insentif_makan'] > 0): ?>
            <tr>
                <td class="label-col">Insentif Makan</td>
                <td class="currency-col">Rp</td>
                <td class="amount-col"><?= number_format($karyawan['insentif_makan'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <?php if($karyawan['uang_tunggu'] > 0): ?>
            <tr>
                <td class="label-col">Uang Tunggu</td>
                <td class="currency-col">Rp</td>
                <td class="amount-col"><?= number_format($karyawan['uang_tunggu'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <?php if($karyawan['gaji_prorate'] > 0): ?>
            <tr>
                <td class="label-col">Gaji Prorate</td>
                <td class="currency-col">Rp</td>
                <td class="amount-col"><?= number_format($karyawan['gaji_prorate'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <tr class="subtotal-row">
                <td class="label-col">TOTAL PENDAPATAN</td>
                <td class="currency-col">Rp</td>
                <td class="amount-col"><?= number_format($karyawan['total_pendapatan'], 0, ',', '.') ?></td>
            </tr>
        </table>

        <!-- Potongan Section -->
        <div class="section-header">
            📉 POTONGAN
        </div>
        <table class="data-table">
            <?php if($karyawan['bpjs_kes'] > 0): ?>
            <tr>
                <td class="label-col">BPJS Kesehatan</td>
                <td class="currency-col">Rp</td>
                <td class="amount-col"><?= number_format($karyawan['bpjs_kes'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <?php if($karyawan['bpjs_tk'] > 0): ?>
            <tr>
                <td class="label-col">BPJS Ketenagakerjaan</td>
                <td class="currency-col">Rp</td>
                <td class="amount-col"><?= number_format($karyawan['bpjs_tk'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <?php if($karyawan['pot_pph21'] > 0): ?>
            <tr>
                <td class="label-col">Pajak Penghasilan (PPh 21)</td>
                <td class="currency-col">Rp</td>
                <td class="amount-col"><?= number_format($karyawan['pot_pph21'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <?php if($karyawan['lainnya'] > 0): ?>
            <tr>
                <td class="label-col">Potongan Lainnya</td>
                <td class="currency-col">Rp</td>
                <td class="amount-col"><?= number_format($karyawan['lainnya'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <tr class="subtotal-row">
                <td class="label-col">TOTAL POTONGAN</td>
                <td class="currency-col">Rp</td>
                <td class="amount-col"><?= number_format($karyawan['total_pot'], 0, ',', '.') ?></td>
            </tr>
        </table>

        <!-- Final Salary -->
        <div class="final-salary">
            <table>
                <tr>
                    <td class="final-label">GAJI BERSIH YANG DITERIMA</td>
                    <td class="final-amount">Rp <?= number_format($karyawan['gaji_bersih'], 0, ',', '.') ?></td>
                </tr>
            </table>
        </div>

        <!-- Notes -->
        <div class="notes">
            <strong>Catatan:</strong>
            Slip gaji ini adalah bukti pembayaran yang sah. Mohon simpan dengan baik untuk keperluan administrasi. 
            Apabila terdapat perbedaan atau pertanyaan terkait rincian gaji, silakan hubungi HRD.
        </div>

        <!-- Footer & Signature -->
        <div class="footer">
            <div class="signature-section">
                <div class="signature-location">
                    Malang, <?= date('d F Y') ?>
                </div>
                <div class="signature-position">
                    HRD Manager
                </div>
                <div class="signature-name">
                    Tis'ah Amalia
                </div>
            </div>
        </div>

        <!-- Confidential Notice -->
        <div class="confidential">
            <strong>DOKUMEN RAHASIA & PRIBADI</strong><br>
            Slip gaji ini bersifat rahasia dan hanya untuk keperluan penerima. Dilarang memperbanyak atau menyebarluaskan tanpa izin.
        </div>
    </div>
</body>
</html>