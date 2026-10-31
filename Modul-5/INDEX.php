<?php
include 'KONEKSI.php';
$result = mysqli_query($conn, "SELECT * FROM supplier ORDER BY id ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Data Master Supplier</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { color: #2c7a8a; }
        .btn-tambah {
            background: #6cb52d; color: white; padding: 8px 15px;
            text-decoration: none; border-radius: 4px; float: right;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #cfe2f3; padding: 10px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #ddd; }
        .btn-edit { background: #e67e22; color: white; padding: 5px 12px; text-decoration: none; border-radius: 3px; }
        .btn-hapus { background: #c0392b; color: white; padding: 5px 12px; text-decoration: none; border-radius: 3px; }
    </style>
</head>
<body>
    <h2>Data Master Supplier</h2>
    <a href="TAMBAH.php" class="btn-tambah">Tambah Data</a>
    <table>
        <tr>
            <th>No</th><th>Nama</th><th>Telp</th><th>Alamat</th><th>Tindakan</th>
        </tr>
        <?php
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $no++ . "</td>";
            echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
            echo "<td>" . htmlspecialchars($row['telp']) . "</td>";
            echo "<td>" . htmlspecialchars($row['alamat']) . "</td>";
            echo "<td>
                    <a href='edit.php?id=" . $row['id'] . "' class='btn-edit'>Edit</a>
                    <a href='hapus.php?id=" . $row['id'] . "' class='btn-hapus'
                       onclick=\"return confirm('Anda yakin akan menghapus supplier ini?');\">Hapus</a>
                  </td>";
            echo "</tr>";
        }
        ?>
    </table>
</body>
</html>