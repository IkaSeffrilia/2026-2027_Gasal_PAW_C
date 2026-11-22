<?php
require 'auth.php';

$namaUser = $_SESSION['nama'] ?? $_SESSION['username'] ?? 'User';
$levelUser = (int)($_SESSION['level'] ?? 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sistem Penjualan</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; }
        .navbar {
            background: #003366;
            padding: 12px 20px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .nav-left { display: flex; align-items: center; gap: 15px; }
        .navbar a { color: white; text-decoration: none; }
        .dropdown { position: relative; display: inline-block; }
        .dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: #ffffff;
            min-width: 170px;
            box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
            z-index: 10;
        }
        .dropdown-content a {
            color: #222;
            padding: 10px 14px;
            display: block;
        }
        .dropdown-content a:hover { background: #f0f0f0; }
        .dropdown:hover .dropdown-content { display: block; }
        .content { padding: 20px; }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="nav-left">
            <strong>Sistem Penjualan</strong>
            <a href="index.php">Home</a>

            <?php if ($levelUser === 1): ?>
                <div class="dropdown">
                    <a href="javascript:void(0)">Data Master ▾</a>
                    <div class="dropdown-content">
                        <a href="barang.php">Data Barang</a>
                        <a href="supplier.php">Data Supplier</a>
                        <a href="pelanggan.php">Data Pelanggan</a>
                        <a href="user.php">Data User</a>
                    </div>
                </div>
            <?php endif; ?>

            <a href="transaksi.php">Transaksi</a>
            <a href="laporan.php">Laporan</a>
        </div>

        <div>
            <span><?= htmlspecialchars($namaUser) ?></span> |
            <a href="logout.php" onclick="return confirm('Yakin ingin logout?')">Logout</a>
        </div>
    </div>

    <div class="content">
        <h1>Selamat Datang di Halaman Home</h1>
        <p>Login sebagai: <strong><?= htmlspecialchars($namaUser) ?></strong> (Level <?= $levelUser === 1 ? '1 - Owner' : ($levelUser === 2 ? '2 - Kasir' : $levelUser) ?>).</p>
    </div>
</body>
</html>