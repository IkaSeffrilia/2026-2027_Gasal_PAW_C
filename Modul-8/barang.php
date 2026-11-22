<?php
require 'auth.php';
require 'koneksi.php';

$levelUser = (int)($_SESSION['level'] ?? 0);
if ($levelUser !== 1) { header("Location: index.php"); exit; }

$error = ''; $editData = null;

if (isset($_GET['hapus'])) {
    try {
        $pdo->prepare("DELETE FROM barang WHERE id=:id")->execute(['id'=>(int)$_GET['hapus']]);
        header("Location: barang.php?msg=hapus"); exit;
    } catch (PDOException $e) {
        $error = "Gagal hapus: barang masih dipakai di transaksi.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $kode = trim($_POST['kode_barang'] ?? '');
    $nama = trim($_POST['nama_barang'] ?? '');
    $harga = (int)($_POST['harga'] ?? 0);
    $stok = (int)($_POST['stok'] ?? 0);
    $supplier_id = (int)($_POST['supplier_id'] ?? 0) ?: null;

    if ($kode === '' || $nama === '') {
        $error = "Kode dan Nama barang wajib diisi.";
    } elseif ($harga < 0 || $stok < 0) {
        $error = "Harga/Stok tidak boleh negatif.";
    } else {
        try {
            if ($id > 0) {
                $pdo->prepare("UPDATE barang SET kode_barang=:kode, nama_barang=:nama, harga=:harga, stok=:stok, supplier_id=:sid WHERE id=:id")
                    ->execute(['kode'=>$kode,'nama'=>$nama,'harga'=>$harga,'stok'=>$stok,'sid'=>$supplier_id,'id'=>$id]);
                header("Location: barang.php?msg=update"); exit;
            } else {
                $pdo->prepare("INSERT INTO barang (kode_barang, nama_barang, harga, stok, supplier_id) VALUES (:kode,:nama,:harga,:stok,:sid)")
                    ->execute(['kode'=>$kode,'nama'=>$nama,'harga'=>$harga,'stok'=>$stok,'sid'=>$supplier_id]);
                header("Location: barang.php?msg=tambah"); exit;
            }
        } catch (PDOException $e) {
            $error = "Gagal simpan: " . ($e->getCode() === '23000' ? "Kode barang sudah ada." : $e->getMessage());
        }
    }
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM barang WHERE id=:id");
    $stmt->execute(['id'=>(int)$_GET['edit']]);
    $editData = $stmt->fetch();
}

$barangs = $pdo->query("SELECT b.*, s.nama AS nama_supplier FROM barang b LEFT JOIN supplier s ON b.supplier_id=s.id ORDER BY b.id ASC")->fetchAll();
$suppliers = $pdo->query("SELECT id, nama FROM supplier ORDER BY nama ASC")->fetchAll();

$msg = $_GET['msg'] ?? '';
$pesan = ['tambah'=>'Barang ditambahkan.','update'=>'Barang diupdate.','hapus'=>'Barang dihapus.'][$msg] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Data Barang</title>
    <style>
        table { border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 6px 10px; }
        .form-box { background:#eef4fb; padding:15px; margin-bottom:20px; border-radius:8px; max-width:460px; }
        .form-box label { display:block; margin-top:8px; }
        .form-box input, .form-box select { width:100%; padding:6px; box-sizing:border-box; }
        .btn { padding:6px 12px; text-decoration:none; border-radius:4px; border:none; cursor:pointer; }
        .btn-primary { background:#003366; color:#fff; }
        .btn-warning { background:#e0a800; color:#fff; }
        .btn-danger  { background:#c00; color:#fff; }
        .msg { background:#d4edda; padding:8px 12px; color:#155724; border-radius:4px; margin-bottom:10px; }
        .err { background:#f8d7da; padding:8px 12px; color:#721c24; border-radius:4px; margin-bottom:10px; }
    </style>
</head>
<body>
    <h2>Data Barang</h2>
    <a href="index.php">← Kembali ke Home</a><br><br>

    <?php if ($pesan): ?><div class="msg"><?= htmlspecialchars($pesan); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="err"><?= htmlspecialchars($error); ?></div><?php endif; ?>

    <div class="form-box">
        <strong><?= $editData ? 'Edit Barang' : 'Tambah Barang'; ?></strong>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $editData ? (int)$editData['id'] : 0 ?>">
            <label>Kode Barang</label>
            <input type="text" name="kode_barang" value="<?= htmlspecialchars($editData['kode_barang'] ?? '') ?>" required>
            <label>Nama Barang</label>
            <input type="text" name="nama_barang" value="<?= htmlspecialchars($editData['nama_barang'] ?? '') ?>" required>
            <label>Harga</label>
            <input type="number" name="harga" min="0" value="<?= (int)($editData['harga'] ?? 0) ?>">
            <label>Stok</label>
            <input type="number" name="stok" min="0" value="<?= (int)($editData['stok'] ?? 0) ?>">
            <label>Supplier</label>
            <select name="supplier_id">
                <option value="0">- Pilih Supplier -</option>
                <?php foreach ($suppliers as $s): ?>
                    <option value="<?= (int)$s['id'] ?>"
                        <?= ($editData && (int)$editData['supplier_id'] === (int)$s['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>
            <button type="submit" class="btn btn-primary"><?= $editData ? 'Update' : 'Simpan' ?></button>
            <?php if ($editData): ?><a href="barang.php" class="btn">Batal</a><?php endif; ?>
        </form>
    </div>

    <table>
        <thead>
            <tr><th>No</th><th>Kode</th><th>Nama</th><th>Harga</th><th>Stok</th><th>Supplier</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            <?php if (empty($barangs)): ?>
                <tr><td colspan="7">Belum ada data barang.</td></tr>
            <?php else: $no = 1; foreach ($barangs as $b): ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($b['kode_barang']); ?></td>
                    <td><?= htmlspecialchars($b['nama_barang']); ?></td>
                    <td>Rp <?= number_format((int)$b['harga'],0,',','.'); ?></td>
                    <td><?= (int)$b['stok']; ?></td>
                    <td><?= htmlspecialchars($b['nama_supplier'] ?? '-'); ?></td>
                    <td>
                        <a href="barang.php?edit=<?= (int)$b['id']; ?>" class="btn btn-warning">Edit</a>
                        <a href="barang.php?hapus=<?= (int)$b['id']; ?>" class="btn btn-danger"
                           onclick="return confirm('Yakin hapus barang ini?')">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</body>
</html>