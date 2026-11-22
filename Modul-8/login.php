<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika user sudah login, tidak boleh mengakses halaman login
if (!empty($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

require 'koneksi.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Username dan Password wajib diisi!";
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password, nama_lengkap, level FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        // Mendukung hash password (password_hash) dan fallback jika DB masih plain text
        $validPassword = false;
        if ($user) {
            if (password_verify($password, $user['password'])) {
                $validPassword = true;
            } elseif (hash_equals($user['password'], $password)) {
                $validPassword = true;
            }
        }

        if ($validPassword) {
            session_regenerate_id(true);
            $_SESSION['login']    = true;
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama']     = $user['nama_lengkap'];
            $_SESSION['level']    = (int)$user['level'];

            header("Location: index.php");
            exit;
        } else {
            $error = "Username atau Password salah!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistem Penjualan</title>
</head>
<body>
    <h2>Halaman Login</h2>
    <?php if ($error !== ''): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label for="username">Username:</label><br>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit" name="login">Login</button>
    </form>
</body>
</html>