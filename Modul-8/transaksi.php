<?php
require 'auth.php';
require 'koneksi.php';

$error = '';

// Normalisasi input datetime-local (2026-11-22T06:04 -> 2026-11-22 06:04:00)
function normalizeDatetime(?string $val): string {
    $val = trim((string)$val);
    if ($val === '') {
        return date('Y-m-d H:i:s');
    }
    $val = str_replace('T', ' ', $val);
    if (strlen($val) === 16) {
        $val .= ':00';
    }
    return $val;
}

// SIMPAN TRANSAKSI
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_transaksi'])) {
    $pelanggan_id = (int)($_POST['pelanggan_id'] ?? 0) ?: null;
    $barang_ids   = $_POST['barang_id'] ?? [];
    $qtys         = $_POST['qty'] ?? [];
    $waktu        = normalizeDatetime($_POST['tanggal'] ?? '');

    // Validasi & gabungkan jika barang yang sama dipilih lebih dari sekali
    $items = [];
    foreach ($barang_ids as $i => $bid) {
        $bid = (int)$bid;
        $qty = (int)($qtys[$i] ?? 0);
        if ($bid > 0 && $qty > 0) {
            $items[$bid] = ($items[$bid] ?? 0) + $qty;
        }
    }

    if (empty($items)) {
        $error = "Minimal pilih satu barang dengan qty lebih dari 0.";
    }

    if (!$error) {
        try {
            $pdo->beginTransaction();

            $total = 0;
            $itemDetail = [];

            foreach ($items as $bid => $qty) {
                $stmt = $pdo->prepare("SELECT id, nama_barang, harga, stok FROM barang WHERE id = :id FOR UPDATE");
                $stmt->execute(['id' => $bid]);
                $b = $stmt->fetch();

                if (!$b) {
                    throw new Exception("Barang tidak ditemukan.");
                }
                if ((int)$b['stok'] < $qty) {
                    throw new Exception("Stok {$b['nama_barang']} tidak cukup (tersisa {$b['stok']}).");
                }

                $harga = (int)$b['harga'];
                $subtotal = $harga * $qty;
                $total += $subtotal;

                $itemDetail[] = [
                    'barang_id' => (int)$b['id'],
                    'qty'       => $qty,
                    'harga'     => $harga,
                    'subtotal'  => $subtotal,
                ];
            }

            // Menggunakan waktu_transaksi & total (tanpa kode_transaksi/user_id)
            $stmtTrx = $pdo->prepare("INSERT INTO transaksi (waktu_transaksi, pelanggan_id, total) VALUES (:waktu, :pel, :total)");
            $stmtTrx->execute([
                'waktu' => $waktu,
                'pel'   => $pelanggan_id,
                'total' => $total,
            ]);

            $trxId = (int)$pdo->lastInsertId();

            // Menggunakan transaksi_detail (barang_id, qty, harga) & mengurangi stok
            $insDetail = $pdo->prepare("INSERT INTO transaksi_detail (transaksi_id, barang_id, qty, harga) VALUES (:tid, :bid, :qty, :harga)");
            $updStok   = $pdo->prepare("UPDATE barang SET stok = stok - :qty WHERE id = :bid");

            foreach ($itemDetail as $d) {
                $insDetail->execute([
                    'tid'   => $trxId,
                    'bid'   => $d['barang_id'],
                    'qty'   => $d['qty'],
                    'harga' => $d['harga'],
                ]);
                $updStok->execute([
                    'qty' => $d['qty'],
                    'bid' => $d['barang_id'],
                ]);
            }

            $pdo->commit();
            header("Location: transaksi.php?msg=sukses");
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = $e->getMessage();
        }
    }
}

// HAPUS TRANSAKSI (khusus owner - restore stok)
$levelUser = (int)($_SESSION['level'] ?? 0);
if (isset($_GET['hapus']) && $levelUser === 1) {
    try {
        $pdo->beginTransaction();
        $tid = (int)$_GET['hapus'];

        $stmtD = $pdo->prepare("SELECT barang_id, qty FROM transaksi_detail WHERE transaksi_id = :id");
        $stmtD->execute(['id' => $tid]);
        $details = $stmtD->fetchAll();

        $updStok = $pdo->prepare("UPDATE barang SET stok = stok + :qty WHERE id = :bid");
        foreach ($details as $d) {
            $updStok->execute([
                'qty' => (int)$d['qty'],
                'bid' => (int)$d['barang_id'],
            ]);
        }

        $pdo->prepare("DELETE FROM transaksi_detail WHERE transaksi_id = :id")->execute(['id' => $tid]);
        $pdo->prepare("DELETE FROM transaksi WHERE id = :id")->execute(['id' => $tid]);

        $pdo->commit();
        header("Location: transaksi.php?msg=hapus");
        exit;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = "Gagal hapus transaksi: " . $e->getMessage();
    }
}

// DATA UNTUK FORM
$barangs    = $pdo->query("SELECT id, kode_barang, nama_barang, harga, stok FROM barang WHERE stok > 0 ORDER BY nama_barang ASC")->fetchAll();
$pelanggans = $pdo->query("SELECT id, nama FROM pelanggan ORDER BY nama ASC")->fetchAll();

// DAFTAR TRANSAKSI + DETAIL
$transaksis = $pdo->query("SELECT t.id, t.waktu_transaksi, t.pelanggan_id, t.total, p.nama AS nama_pelanggan
                           FROM transaksi t
                           LEFT JOIN pelanggan p ON t.pelanggan_id = p.id
                           ORDER BY t.waktu_transaksi DESC, t.id DESC")->fetchAll();

$detailByTransaksi = [];
if ($transaksis) {
    $ids = array_column($transaksis, 'id');
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT td.transaksi_id, b.nama_barang, td.qty, td.harga, (td.qty * td.harga) AS subtotal
                           FROM transaksi_detail td
                           LEFT JOIN barang b ON td.barang_id = b.id
                           WHERE td.transaksi_id IN ($ph)
                           ORDER BY td.id ASC");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $d) {
        $detailByTransaksi[$d['transaksi_id']][] = $d;
    }
}

$msg = $_GET['msg'] ?? '';
$pesan = [
    'sukses' => 'Transaksi berhasil disimpan.',
    'hapus'  => 'Transaksi berhasil dihapus.',
][$msg] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Transaksi</title>
    <style>
        table { border-collapse: collapse; margin: 10px 0 20px; }
        th, td { border: 1px solid #999; padding: 6px 10px; }
        .form-box { background:#eef4fb; padding:15px; border-radius:8px; margin-bottom:20px; }
        .form-box label { display:inline-block; margin-right:10px; }
        .form-box input, .form-box select { padding:5px; }
        .item-row { margin-bottom: 6px; }
        .btn { padding:6px 12px; text-decoration:none; border-radius:4px; border:none; cursor:pointer; }
        .btn-primary { background:#003366; color:#fff; }
        .btn-danger  { background:#c00; color:#fff; }
        .btn-small { padding:3px 8px; font-size:12px; }
        .msg { background:#d4edda; padding:8px 12px; color:#155724; border-radius:4px; margin-bottom:10px; }
        .err { background:#f8d7da; padding:8px 12px; color:#721c24; border-radius:4px; margin-bottom:10px; }
        .detail-table { font-size:13px; background:#f9f9f9; }
        #totalDisplay { font-weight:bold; font-size:16px; color:#003366; }
    </style>
</head>
<body>
    <h2>Data Transaksi</h2>
    <a href="index.php">← Kembali ke Home</a><br><br>

    <?php if ($pesan): ?><div class="msg"><?= htmlspecialchars($pesan); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="err"><?= htmlspecialchars($error); ?></div><?php endif; ?>

    <div class="form-box">
        <strong>Tambah Transaksi Baru</strong>
        <form method="POST" id="formTransaksi">
            <label>Tanggal: <input type="datetime-local" name="tanggal" value="<?= date('Y-m-d\TH:i') ?>"></label>
            <label>Pelanggan:
                <select name="pelanggan_id">
                    <option value="0">- Umum -</option>
                    <?php foreach ($pelanggans as $p): ?>
                        <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <br><br>
            <div id="items">
                <div class="item-row">
                    <select name="barang_id[]" class="pilih-barang">
                        <option value="0">- Pilih Barang -</option>
                        <?php foreach ($barangs as $b): ?>
                            <option value="<?= (int)$b['id'] ?>" data-harga="<?= (int)$b['harga'] ?>">
                                <?= htmlspecialchars($b['kode_barang'].' - '.$b['nama_barang'].' (Rp '.number_format((int)$b['harga'],0,',','.').', stok '.(int)$b['stok'].')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="qty[]" class="qty" min="1" value="1" style="width:70px">
                </div>
            </div>
            <button type="button" class="btn btn-small btn-primary" onclick="tambahBaris()">+ Tambah Baris</button>
            <br><br>
            <div>Total: <span id="totalDisplay">Rp 0</span></div>
            <br>
            <button type="submit" name="simpan_transaksi" class="btn btn-primary">Simpan Transaksi</button>
        </form>
    </div>

    <h3>Daftar Transaksi</h3>
    <?php if (empty($transaksis)): ?>
        <p>Belum ada transaksi.</p>
    <?php else: $no = 1; foreach ($transaksis as $t): ?>
        <table>
            <tr>
                <th>No</th><td><?= $no++ ?></td>
                <th>ID Transaksi</th><td>#<?= (int)$t['id'] ?></td>
                <th>Tanggal</th><td><?= htmlspecialchars($t['waktu_transaksi']) ?></td>
            </tr>
            <tr>
                <th>Pelanggan</th><td><?= htmlspecialchars($t['nama_pelanggan'] ?? 'Umum') ?></td>
                <th>Total</th><td colspan="3">Rp <?= number_format((int)$t['total'], 0, ',', '.') ?></td>
            </tr>
            <tr><td colspan="6">
                <table class="detail-table" width="100%">
                    <thead><tr><th>Barang</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr></thead>
                    <tbody>
                        <?php foreach (($detailByTransaksi[$t['id']] ?? []) as $d): ?>
                            <tr>
                                <td><?= htmlspecialchars($d['nama_barang'] ?? '-') ?></td>
                                <td><?= (int)$d['qty'] ?></td>
                                <td>Rp <?= number_format((int)$d['harga'], 0, ',', '.') ?></td>
                                <td>Rp <?= number_format((int)$d['subtotal'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if ($levelUser === 1): ?>
                    <a href="transaksi.php?hapus=<?= (int)$t['id'] ?>" class="btn btn-danger btn-small"
                       onclick="return confirm('Yakin hapus transaksi ini? Stok barang akan dikembalikan.')">Hapus Transaksi</a>
                <?php endif; ?>
            </td></tr>
        </table>
    <?php endforeach; endif; ?>

    <script>
    function hitungTotal() {
        let total = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const sel = row.querySelector('.pilih-barang');
            const qty = parseInt(row.querySelector('.qty').value) || 0;
            const opt = sel.options[sel.selectedIndex];
            const harga = parseInt(opt?.dataset.harga) || 0;
            total += harga * qty;
        });
        document.getElementById('totalDisplay').innerText = 'Rp ' + total.toLocaleString('id-ID');
    }

    function tambahBaris() {
        const first = document.querySelector('.item-row');
        const clone = first.cloneNode(true);
        clone.querySelector('.qty').value = 1;
        clone.querySelector('.pilih-barang').selectedIndex = 0;
        document.getElementById('items').appendChild(clone);
        bindRow(clone);
    }

    function bindRow(row) {
        row.querySelector('.pilih-barang').addEventListener('change', hitungTotal);
        row.querySelector('.qty').addEventListener('input', hitungTotal);
    }

    document.querySelectorAll('.item-row').forEach(bindRow);
    hitungTotal();
    </script>
</body>
</html>