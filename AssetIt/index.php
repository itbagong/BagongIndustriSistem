<?php
include 'koneksi.php';
$data = mysqli_query($koneksi, "SELECT * FROM barang");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Stok Barang</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<h2>Data Stok Barang</h2>
<a href="/add.php" class="btn">Tambah Barang</a>

<table>
    <tr>
        <th>No</th>
        <th>Nama Barang</th>
        <th>Stok</th>
        <th>Harga</th>
    </tr>

    <?php $no = 1; ?>
    <?php while ($row = mysqli_fetch_assoc($data)) { ?>
    <tr>
        <td><?= $no++ ?></td>
        <td><?= $row['nama_barang'] ?></td>
        <td><?= $row['stok'] ?></td>
        <td>Rp <?= number_format($row['harga']) ?></td>
    </tr>
    <?php } ?>
</table>

</body>
</html>
