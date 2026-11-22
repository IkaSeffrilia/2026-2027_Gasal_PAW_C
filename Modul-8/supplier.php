<?php
require 'auth.php';
require 'koneksi.php';

$levelUser = (int)($_SESSION['level'] ?? 0);
if ($levelUser !== 1) {
    header("Location: index.php");
    exit;
}

$error = '';
$editData = null;

// HAPUS
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    try {
        $stmt = $pdo->prepare("DELETE FROM supplier WHERE id = :id");
        $stmt->execute(['id' => $id]);
        header("Location: supplier.php?msg=hapus");
        exit;
    } catch (PDOException $e) {
        $error = "Gagal menghapus: supplier masih digunakan oleh data barang.";
    }
}

// SIMPAN (tambah / update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id      = (int)($_POST['id'] ?? 0);
    $nama    = trim($_POST['nama'] ?? '');
    $alamat  = trim($_POST['alamat'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');

    if ($nama === '') {
        $error = "Nama supplier wajib diisi.";
    } else {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE supplier SET nama=:nama, alamat=:alamat, telepon=:telepon WHERE id=:id");
            $stmt->execute(['nama'=>$nama,'alamat'=>$alamat,'telepon'=>$telepon,'id'=>$id]);
            header("Location: supplier.php?msg=update");
            exit;
        } else {
            $stmt = $pdo->prepare("INSERT INTO supplier (nama, alamat, telepon) VALUES (:nama, :alamat, :telepon)");
            $stmt->execute(['nama'=>$nama,'alamat'=>$alamat,'telepon'=>$telepon]);
            header("Location: supplier.php?msg=tambah");
            exit;
        }
    }
}

// EDIT
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM supplier WHERE id = :id");
    $stmt->execute(['id' => (int)$_GET['edit']]);
    $editData = $stmt->fetch();
}

$suppliers = $pdo->query("SELECT * FROM supplier ORDER BY id ASC")->fetchAll();

$msg = $_GET['msg'] ?? '';
$pesan = ['tambah'=>'Data supplier berhasil ditambahkan.','update'=>'Data supplier berhasil diupdate.','hapus'=>'Data supplier berhasil dihapus.'][$msg] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Supplier</title>
    <style>
        table { border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 6px 10px; }
        .form-box { background:#eef4fb; padding:15px; margin-bottom:20px; border-radius:8px; max-width:420px; }
        .form-box label { display:block; margin-top:8px; }
        .form-box input { width:100%; padding:6px; box-sizing:border-box; }
        .btn { padding:6px 12px; text-decoration:none; border-radius:4px; border:none; cursor:pointer; }
        .btn-primary { background:#003366; color:#fff; }
        .btn-warning { background:#e0a800; color:#fff; }
        .btn-danger  { background:#c00; color:#fff; }
        .msg { background:#d4edda; padding:8px 12px; color:#155724; border-radius:4px; margin-bottom:10px; }
        .err { background:#f8d7da; padding:8px 12px; color:#721c24; border-radius:4px; margin-bottom:10px; }
    </style>
</head>
<body>
    <h2>Data Supplier</h2>
    <a href="index.php">← Kembali ke Home</a><br><br>

    <?php if ($pesan): ?><div class="msg"><?= htmlspecialchars($pesan); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="err"><?= htmlspecialchars($error); ?></div><?php endif; ?>

    <div class="form-box">
        <strong><?= $editData ? 'Edit Supplier' : 'Tambah Supplier'; ?></strong>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $editData ? (int)$editData['id'] : 0 ?>">
            <label>Nama</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($editData['nama'] ?? '') ?>" required>
            <label>Alamat</label>
            <input type="text" name="alamat" value="<?= htmlspecialchars($editData['alamat'] ?? '') ?>">
            <label>Telepon</label>
            <input type="text" name="telepon" value="<?= htmlspecialchars($editData['telepon'] ?? '') ?>">
            <br><br>
            <button type="submit" class="btn btn-primary"><?= $editData ? 'Update' : 'Simpan' ?></button>
            <?php if ($editData): ?><a href="supplier.php" class="btn">Batal</a><?php endif; ?>
        </form>
    </div>

    <table>
        <thead>
            <tr><th>No</th><th>Nama</th><th>Alamat</th><th>Telepon</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            <?php if (empty($suppliers)): ?>
                <tr><td colspan="5">Belum ada data supplier.</td></tr>
            <?php else: ?>
                <?php $no = 1; foreach ($suppliers as $s): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($s['nama']); ?></td>
                        <td><?= htmlspecialchars($s['alamat'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($s['telepon'] ?? '-'); ?></td>
                        <td>
                            <a href="supplier.php?edit=<?= (int)$s['id']; ?>" class="btn btn-warning">Edit</a>
                            <a href="supplier.php?hapus=<?= (int)$s['id']; ?>" class="btn btn-danger"
                               onclick="return confirm('Yakin hapus supplier ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>