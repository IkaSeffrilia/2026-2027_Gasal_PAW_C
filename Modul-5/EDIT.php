<?php
include 'KONEKSI.php';
$id = intval($_GET['id'] ?? 0);
$data = mysqli_query($conn, "SELECT * FROM supplier WHERE id = $id");
$row = mysqli_fetch_assoc($data);
if (!$row) { die("Data tidak ditemukan"); }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Data Master Supplier</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { color: #2c7a8a; }
        label { display: inline-block; width: 80px; font-weight: bold; }
        input[type=text] { width: 350px; padding: 6px; margin-bottom: 12px; border: 1px solid #ccc; border-radius: 3px; }
        .btn-update { background: #6cb52d; color: white; padding: 8px 20px; border: none; border-radius: 3px; cursor: pointer; }
        .btn-batal { background: #c0392b; color: white; padding: 8px 20px; text-decoration: none; border-radius: 3px; }
    </style>
</head>
<body>
    <h2>Edit Data Master Supplier</h2>
    <form action="UPDATE.php" method="POST">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">
        <label>Nama</label>   <input type="text" name="nama" value="<?= htmlspecialchars($row['nama']) ?>" required><br>
        <label>Telp</label>   <input type="text" name="telp" value="<?= htmlspecialchars($row['telp']) ?>" required><br>
        <label>Alamat</label> <input type="text" name="alamat" value="<?= htmlspecialchars($row['alamat']) ?>" required><br>
        <br>
        <button type="submit" class="btn-update">Update</button>
        <a href="INDEX.php" class="btn-batal">Batal</a>
    </form>
</body>
</html>