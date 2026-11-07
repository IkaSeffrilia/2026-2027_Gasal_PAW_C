<?php
require_once __DIR__ . '/../config/Koneksi.php';
$result = $koneksi->query("SELECT * FROM barang ORDER BY id");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Barang</title>
    <style>
        body { font-family: Arial, sans-serif; margin:20px; }
        table { border-collapse:collapse; width:100%; }
        th,td { border:1px solid #999; padding:6px 8px; }
        th { background:#e9ecef; }
        .btn-danger { background:#dc3545; color:#fff; padding:4px 10px;
                      border-radius:4px; text-decoration:none; font-size:13px; }
    </style>
</head>
<body>
    <h2>Daftar Barang</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Kode</th><th>Nama Barang</th>
                <th>Harga</th><th>Stok</th><th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($b = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $b['id'] ?></td>
                <td><?= htmlspecialchars($b['kode_barang']) ?></td>
                <td><?= htmlspecialchars($b['nama_barang']) ?></td>
                <td align="right">Rp <?= number_format($b['harga'],0,',','.') ?></td>
                <td align="center"><?= $b['stok'] ?></td>
                <td>
                    <a class="btn-danger"
                       href="hapus.php?id=<?= $b['id'] ?>"
                       onclick="return confirm('Yakin hapus barang ini?')">Hapus</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>