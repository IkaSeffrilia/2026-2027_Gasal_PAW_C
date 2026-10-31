<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tambah Data Master Supplier Baru</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { color: #2c7a8a; }
        label { display: inline-block; width: 80px; font-weight: bold; }
        input[type=text] { width: 350px; padding: 6px; margin-bottom: 12px; border: 1px solid #ccc; border-radius: 3px; }
        .btn-simpan { background: #6cb52d; color: white; padding: 8px 20px; border: none; border-radius: 3px; cursor: pointer; }
        .btn-batal { background: #c0392b; color: white; padding: 8px 20px; text-decoration: none; border-radius: 3px; }
    </style>
</head>
<body>
    <h2>Tambah Data Master Supplier Baru</h2>
    <form action="SIMPAN.php" method="POST">
        <label>Nama</label>   <input type="text" name="nama" placeholder="Nama" required><br>
        <label>Telp</label>   <input type="text" name="telp" placeholder="telp" required><br>
        <label>Alamat</label> <input type="text" name="alamat" placeholder="alamat" required><br>
        <br>
        <button type="submit" class="btn-simpan">Simpan</button>
        <a href="index.php" class="btn-batal">Batal</a>
    </form>
</body>
</html>