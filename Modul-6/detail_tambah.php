<!-- Form Tambah Detail (DropDown Barang) -->
 <?php
require_once __DIR__ . '/../config/Koneksi.php';

$transaksi_id = isset($_GET['transaksi_id']) ? (int)$_GET['transaksi_id'] : 0;
$error = '';

// ==== Ambil barang yang BELUM dipakai pada transaksi ini ====
// Subquery NOT IN => barang yang sudah ada di detail transaksi ini tidak muncul
// di dropdown (memenuhi syarat: "tidak boleh dipilih lagi")
$sql_barang = "SELECT id, kode_barang, nama_barang, harga, stok
               FROM barang
               WHERE stok > 0
                 AND id NOT IN (
                     SELECT barang_id FROM transaksi_detail
                     WHERE transaksi_id = $transaksi_id
                 )
               ORDER BY nama_barang";
$result_barang = $koneksi->query($sql_barang);

// ==== Proses submit ====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barang_id = (int)($_POST['barang_id'] ?? 0);
    $qty       = (int)($_POST['qty'] ?? 0);

    // Validasi input
    if ($barang_id <= 0) {
        $error = 'Barang wajib dipilih.';
    } elseif ($qty <= 0) {
        $error = 'Qty harus lebih dari 0.';
    } else {
        // Cek ulang di server: apakah barang sudah ada di detail transaksi ini?
        $cek = $koneksi->prepare(
            "SELECT COUNT(*) AS jml FROM transaksi_detail
             WHERE transaksi_id = ? AND barang_id = ?"
        );
        $cek->bind_param('ii', $transaksi_id, $barang_id);
        $cek->execute();
        $jml = $cek->get_result()->fetch_assoc()['jml'];
        $cek->close();

        if ($jml > 0) {
            $error = 'Barang tersebut sudah ada pada transaksi ini.';
        } else {
            // Ambil harga dari master barang (server-side, bukan dari form)
            $stmt = $koneksi->prepare(
                "SELECT harga, stok FROM barang WHERE id = ?"
            );
            $stmt->bind_param('i', $barang_id);
            $stmt->execute();
            $brg = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$brg) {
                $error = 'Barang tidak ditemukan.';
            } elseif ($brg['stok'] < $qty) {
                $error = 'Stok tidak mencukupi. Sisa stok: ' . $brg['stok'];
            } else {
                $harga = (int)$brg['harga'];

                // 1) Simpan detail
                $ins = $koneksi->prepare(
                    "INSERT INTO transaksi_detail
                     (transaksi_id, barang_id, harga, qty)
                     VALUES (?, ?, ?, ?)"
                );
                $ins->bind_param('iiii', $transaksi_id, $barang_id, $harga, $qty);
                $ins->execute();
                $ins->close();

                // 2) Kurangi stok barang
                $upd_stok = $koneksi->prepare(
                    "UPDATE barang SET stok = stok - ? WHERE id = ?"
                );
                $upd_stok->bind_param('ii', $qty, $barang_id);
                $upd_stok->execute();
                $upd_stok->close();

                // 3) Update total transaksi (lihat juga soal no. 4)
                require_once __DIR__ . '/update_total.php';
                updateTotalTransaksi($koneksi, $transaksi_id);

                header('Location: index.php?id=' . $transaksi_id);
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Detail Transaksi</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        label { display:block; margin-top:10px; }
        select, input { padding:6px; width:300px; }
        .btn { padding:6px 14px; background:#0d6efd; color:#fff;
               border:none; border-radius:4px; cursor:pointer; }
        .error { color:#dc3545; margin-bottom:10px; }
    </style>
</head>
<body>
    <h2>Tambah Detail Transaksi #<?= $transaksi_id ?></h2>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <label>Barang (hanya yang belum dipilih)</label>
        <select name="barang_id" required>
            <option value="">-- Pilih Barang --</option>
            <?php while ($b = $result_barang->fetch_assoc()): ?>
                <option value="<?= $b['id'] ?>">
                    <?= htmlspecialchars($b['kode_barang']) ?> -
                    <?= htmlspecialchars($b['nama_barang']) ?>
                    (Rp <?= number_format($b['harga'], 0, ',', '.') ?>, stok: <?= $b['stok'] ?>)
                </option>
            <?php endwhile; ?>
        </select>

        <label>Qty</label>
        <input type="number" name="qty" min="1" value="1" required>

        <br><br>
        <button class="btn" type="submit">Simpan Detail</button>
        <a href="index.php?id=<?= $transaksi_id ?>">Batal</a>
    </form>
</body>
</html>