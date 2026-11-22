<?php
require 'auth.php';
require 'koneksi.php';

$levelUser = (int)($_SESSION['level'] ?? 0);
if ($levelUser !== 1) {
    header("Location: index.php");
    exit;
}

$dari = $_GET['dari'] ?? '';
$sampai = $_GET['sampai'] ?? '';

$where = '';
$params = [];

// PERBAIKAN: Menggunakan t.waktu_transaksi, bukan t.tanggal
if ($dari !== '' && $sampai !== '') {
    $where = "WHERE DATE(t.waktu_transaksi) BETWEEN :dari AND :sampai";
    $params = ['dari' => $dari, 'sampai' => $sampai];
} elseif ($dari !== '') {
    $where = "WHERE DATE(t.waktu_transaksi) >= :dari";
    $params = ['dari' => $dari];
} elseif ($sampai !== '') {
    $where = "WHERE DATE(t.waktu_transaksi) <= :sampai";
    $params = ['sampai' => $sampai];
}

// Rekap
$stmt = $pdo->prepare("SELECT COUNT(*) AS jumlah_transaksi, COALESCE(SUM(t.total), 0) AS total_penjualan FROM transaksi t $where");
$stmt->execute($params);
$rekap = $stmt->fetch();

// Daftar transaksi
// PERBAIKAN: Menghapus kode_transaksi dan nama_kasir, mengganti tanggal menjadi waktu_transaksi
$stmt = $pdo->prepare("SELECT t.id, t.waktu_transaksi AS tanggal, t.total, p.nama AS nama_pelanggan
                       FROM transaksi t
                       LEFT JOIN pelanggan p ON t.pelanggan_id = p.id
                       $where
                       ORDER BY t.waktu_transaksi DESC, t.id DESC");
$stmt->execute($params);
$transaksis = $stmt->fetchAll();

// Barang terlaris
// PERBAIKAN: Menghitung total_omzet secara otomatis (qty * harga) karena tidak ada kolom subtotal
$stmt = $pdo->prepare("SELECT b.nama_barang, SUM(td.qty) AS total_qty, SUM(td.qty * td.harga) AS total_omzet
                       FROM transaksi_detail td
                       LEFT JOIN barang b ON td.barang_id = b.id
                       INNER JOIN transaksi t ON td.transaksi_id = t.id
                       $where
                       GROUP BY td.barang_id, b.nama_barang
                       ORDER BY total_qty DESC
                       LIMIT 10");
$stmt->execute($params);
$barangTerlaris = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan - Sistem Penjualan</title>
    <style>
        table { border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #999; padding: 6px 10px; }
        .rekap { display: flex; gap: 20px; margin-bottom: 20px; }
        .card { background: #eef4fb; padding: 15px 25px; border-radius: 8px; }
    </style>
</head>
<body>
    <h2>Laporan Penjualan</h2>
    <a href="index.php">← Kembali ke Home</a><br><br>

    <form method="GET" action="">
        <label>Dari: <input type="date" name="dari" value="<?= htmlspecialchars($dari); ?>"></label>
        <label>Sampai: <input type="date" name="sampai" value="<?= htmlspecialchars($sampai); ?>"></label>
        <button type="submit">Filter</button>
        <a href="laporan.php">Reset</a>
    </form>
    <br>

    <div class="rekap">
        <div class="card">
            <div>Jumlah Transaksi</div>
            <strong><?= (int)$rekap['jumlah_transaksi']; ?></strong>
        </div>
        <div class="card">
            <div>Total Penjualan</div>
            <strong>Rp <?= number_format((int)$rekap['total_penjualan'], 0, ',', '.'); ?></strong>
        </div>
    </div>

    <h3>Daftar Transaksi</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID Transaksi</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($transaksis)): ?>
                <tr><td colspan="5">Tidak ada transaksi pada periode ini.</td></tr>
            <?php else: ?>
                <?php $no = 1; foreach ($transaksis as $t): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <!-- Mengganti Kode menjadi ID Transaksi -->
                        <td>#<?= htmlspecialchars($t['id']); ?></td>
                        <td><?= htmlspecialchars($t['tanggal']); ?></td>
                        <td><?= htmlspecialchars($t['nama_pelanggan'] ?? 'Umum/Tanpa Nama'); ?></td>
                        <!-- Kolom Kasir dihapus karena tidak ada relasi ke tabel users -->
                        <td>Rp <?= number_format((int)$t['total'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <h3>10 Barang Terlaris</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Total Qty Terjual</th>
                <th>Total Omzet</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($barangTerlaris)): ?>
                <tr><td colspan="4">Belum ada data penjualan.</td></tr>
            <?php else: ?>
                <?php $no = 1; foreach ($barangTerlaris as $b): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($b['nama_barang'] ?? '-'); ?></td>
                        <td><?= (int)$b['total_qty']; ?></td>
                        <td>Rp <?= number_format((int)$b['total_omzet'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>