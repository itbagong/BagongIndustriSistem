<!DOCTYPE html>
<html>
<head>
    <title>Tambah Barang</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h2>Tambah Barang</h2>

<form action="simpan.php" method="post">
    <label>Nama Barang</label>
    <input type="text" name="nama" required>

    <label>Stok</label>
    <input type="number" name="stok" required>

    <label>Harga</label>
    <input type="number" name="harga" required>

    <button type="submit">Simpan</button>
</form>

</body>
</html>
