<?php
require 'auth.php';
require 'koneksi.php';

$levelUser = (int)($_SESSION['level'] ?? 0);
if ($levelUser !== 1) { header("Location: index.php"); exit; }

$error = ''; $editData = null;

// Cegah owner hapus dirinya sendiri
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if ($id === (int)$_SESSION['user_id']) {
        $error = "Anda tidak bisa menghapus akun sendiri.";
    } else {
        try {
            $pdo->prepare("DELETE FROM users WHERE id=:id")->execute(['id'=>$id]);
            header("Location: user.php?msg=hapus"); exit;
        } catch (PDOException $e) {
            $error = "Gagal hapus user: sudah terhubung dengan transaksi.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = (int)($_POST['id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $nama     = trim($_POST['nama_lengkap'] ?? '');
    $level    = (int)($_POST['level'] ?? 2);
    $password = $_POST['password'] ?? '';

    if ($username === '' || $nama === '') {
        $error = "Username dan Nama Lengkap wajib diisi.";
    } elseif (!in_array($level, [1,2], true)) {
        $error = "Level tidak valid.";
    } else {
        try {
            if ($id > 0) {
                if ($password !== '') {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $pdo->prepare("UPDATE users SET username=:u, nama_lengkap=:n, level=:l, password=:p WHERE id=:id")
                        ->execute(['u'=>$username,'n'=>$nama,'l'=>$level,'p'=>$hash,'id'=>$id]);
                } else {
                    $pdo->prepare("UPDATE users SET username=:u, nama_lengkap=:n, level=:l WHERE id=:id")
                        ->execute(['u'=>$username,'n'=>$nama,'l'=>$level,'id'=>$id]);
                }
                header("Location: user.php?msg=update"); exit;
            } else {
                if ($password === '') { $error = "Password wajib diisi untuk user baru."; }
                else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $pdo->prepare("INSERT INTO users (username, nama_lengkap, level, password) VALUES (:u,:n,:l,:p)")
                        ->execute(['u'=>$username,'n'=>$nama,'l'=>$level,'p'=>$hash]);
                    header("Location: user.php?msg=tambah"); exit;
                }
            }
        } catch (PDOException $e) {
            $error = "Gagal simpan: " . ($e->getCode() === '23000' ? "Username sudah dipakai." : $e->getMessage());
        }
    }
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=:id");
    $stmt->execute(['id'=>(int)$_GET['edit']]);
    $editData = $stmt->fetch();
}

$users = $pdo->query("SELECT id, username, nama_lengkap, level FROM users ORDER BY id ASC")->fetchAll();

$msg = $_GET['msg'] ?? '';
$pesan = ['tambah'=>'User ditambahkan.','update'=>'User diupdate.','hapus'=>'User dihapus.'][$msg] ?? '';
function labelLevel(int $level): string { return $level === 1 ? 'Owner' : ($level === 2 ? 'Kasir' : 'Level '.$level); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Data User</title>
    <style>
        table { border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 6px 10px; }
        .form-box { background:#eef4fb; padding:15px; margin-bottom:20px; border-radius:8px; max-width:420px; }
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
    <h2>Data User</h2>
    <a href="index.php">← Kembali ke Home</a><br><br>

    <?php if ($pesan): ?><div class="msg"><?= htmlspecialchars($pesan); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="err"><?= htmlspecialchars($error); ?></div><?php endif; ?>

    <div class="form-box">
        <strong><?= $editData ? 'Edit User' : 'Tambah User'; ?></strong>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $editData ? (int)$editData['id'] : 0 ?>">
            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($editData['username'] ?? '') ?>" required>
            <label>Nama Lengkap</label>
            <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($editData['nama_lengkap'] ?? '') ?>" required>
            <label>Level</label>
            <select name="level">
                <option value="1" <?= ($editData && (int)$editData['level']===1)?'selected':'' ?>>Owner</option>
                <option value="2" <?= ($editData && (int)$editData['level']===2)?'selected':($editData?'':'selected') ?>>Kasir</option>
            </select>
            <label>Password <?= $editData ? '(kosongkan jika tidak diubah)' : '' ?></label>
            <input type="password" name="password" <?= $editData ? '' : 'required' ?>>
            <br><br>
            <button type="submit" class="btn btn-primary"><?= $editData ? 'Update' : 'Simpan' ?></button>
            <?php if ($editData): ?><a href="user.php" class="btn">Batal</a><?php endif; ?>
        </form>
    </div>

    <table>
        <thead><tr><th>No</th><th>Username</th><th>Nama Lengkap</th><th>Level</th><th>Aksi</th></tr></thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr><td colspan="5">Belum ada user.</td></tr>
            <?php else: $no=1; foreach ($users as $u): ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($u['username']); ?></td>
                    <td><?= htmlspecialchars($u['nama_lengkap']); ?></td>
                    <td><?= htmlspecialchars(labelLevel((int)$u['level'])); ?></td>
                    <td>
                        <a href="user.php?edit=<?= (int)$u['id']; ?>" class="btn btn-warning">Edit</a>
                        <?php if ((int)$u['id'] !== (int)$_SESSION['user_id']): ?>
                            <a href="user.php?hapus=<?= (int)$u['id']; ?>" class="btn btn-danger"
                               onclick="return confirm('Yakin hapus user ini?')">Hapus</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</body>
</html>