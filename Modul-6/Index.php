<!-- Halaman Master Detail -->
<?php
require_once __DIR__ . '/../config/Koneksi.php';

// Ambil transaksi terpilih dari query string
$id_terpilih = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ==== MASTER: daftar transaksi ====
$sql_master = "SELECT t.*, p.nama AS nama_pelanggan
               FROM transaksi t
               LEFT JOIN pelanggan p ON p.id = t.pelanggan_id
               ORDER BY t.id DESC";
$result_master = $koneksi->query($sql_master);

// Jika belum ada yang dipilih, pilih transaksi pertama
if ($id_terpilih === 0 && $result_master->num_rows > 0) {
    $row_pertama = $result_master->fetch_assoc();
    $id_terpilih = (int)$row_pertama['id'];
    $result_master->data_seek(0);
}

// ==== DETAIL: detail transaksi terpilih ====
$sql_detail = "SELECT d.*, b.nama_barang, b.kode_barang
               FROM transaksi_detail d
               JOIN barang b ON b.id = d.barang_id
               WHERE d.transaksi_id = $id_terpilih
               ORDER BY d.id";
$result_detail = $koneksi->query($sql_detail);

// Notifikasi dari proses hapus detail
$notif = $_GET['notif'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Master-Detail Transaksi Penjualan</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { background:#0d6efd; color:#fff; padding:8px; margin-bottom:0; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #999; padding: 6px 8px; }
        th { background:#e9ecef; }
        tr.aktif td { background:#fff3cd; }
        .master a { color:#0d6efd; text-decoration:none; }
        .master a:hover { text-decoration:underline; }
        .btn { display:inline-block; padding:4px 10px; background:#0d6efd; color:#fff;
               border-radius:4px; text-decoration:none; font-size:13px; }
        .btn-danger { background:#dc3545; }
        .notif { padding:8px; border-radius:4px; margin-bottom:10px; }
        .notif-error { background:#f8d7da; color:#842029; }
        .notif-ok    { background:#d1e7dd; color:#0f5132; }
    </style>
</head>
<body>

<?php if ($notif === 'hapus_gagal'): ?>
    <div class="notif notif-error">
        ❌ Barang gagal dihapus: barang tersebut masih digunakan pada transaksi!
    </div>
<?php elseif ($notif === 'hapus_ok'): ?>
    <div class="notif notif-ok">✅ Barang berhasil dihapus.</div>
<?php endif; ?>

<!-- ============ MASTER ============ -->
<h2>MASTER — Daftar Transaksi</h2>
<table class="master">
    <thead>
        <tr>
            <th>ID</th><th>Tanggal</th><th>Pelanggan</th>
            <th>Keterangan</th><th>Total</th><th>Aksi</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($t = $result_master->fetch_assoc()): ?>
        <tr class="<?= ((int)$t['id'] === $id_terpilih) ? 'aktif' : '' ?>">
            <td><?= $t['id'] ?></td>
            <td><?= htmlspecialchars($t['waktu_transaksi']) ?></td>
            <td><?= htmlspecialchars($t['nama_pelanggan'] ?? '-') ?></td>
            <td><?= htmlspecialchars($t['keterangan']) ?></td>
            <td align="right">Rp <?= number_format($t['total'], 0, ',', '.') ?></td>
            <td><a href="?id=<?= $t['id'] ?>">Pilih</a></td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<!-- ============ DETAIL ============ -->
<h2>DETAIL — Item Transaksi #<?= $id_terpilih ?></h2>
<p>
    <a class="btn" href="detail_tambah.php?transaksi_id=<?= $id_terpilih ?>">
        + Tambah Detail
    </a>
</p>
<table>
    <thead>
        <tr>
            <th>No</th><th>Kode</th><th>Nama Barang</th>
            <th>Harga</th><th>Qty</th><th>Subtotal</th><th>Aksi</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $no = 1;
    $grand_total = 0;
    while ($d = $result_detail->fetch_assoc()):
        $subtotal = $d['harga'] * $d['qty'];
        $grand_total += $subtotal;
    ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($d['kode_barang']) ?></td>
            <td><?= htmlspecialchars($d['nama_barang']) ?></td>
            <td align="right">Rp <?= number_format($d['harga'], 0, ',', '.') ?></td>
            <td align="center"><?= $d['qty'] ?></td>
            <td align="right">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
            <td>
                <a class="btn btn-danger"
                   href="detail_hapus.php?id=<?= $d['id'] ?>&transaksi_id=<?= $id_terpilih ?>"
                   onclick="return confirm('Hapus detail ini?')">Hapus</a>
            </td>
        </tr>
    <?php endwhile; ?>
    <?php if ($no === 1): ?>
        <tr><td colspan="7" align="center"><em>Belum ada detail.</em></td></tr>
    <?php endif; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="5" align="right">GRAND TOTAL</th>
            <th align="right">Rp <?= number_format($grand_total, 0, ',', '.') ?></th>
            <th></th>
        </tr>
    </tfoot>
</table>

</body>
</html>