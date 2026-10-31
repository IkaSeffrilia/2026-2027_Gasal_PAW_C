<?php
include 'KONEKSI.php';

$id     = intval($_POST['id']);
$nama   = mysqli_real_escape_string($conn, $_POST['nama']);
$telp   = mysqli_real_escape_string($conn, $_POST['telp']);
$alamat = mysqli_real_escape_string($conn, $_POST['alamat']);

$query = "UPDATE supplier SET nama='$nama', telp='$telp', alamat='$alamat' WHERE id=$id";
if (mysqli_query($conn, $query)) {
    header("Location: INDEX.php");
    exit;
} else {
    echo "Gagal update: " . mysqli_error($conn);
}
?>