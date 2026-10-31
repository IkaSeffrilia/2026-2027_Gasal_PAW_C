<?php
include 'KONEKSI.php';

$nama   = mysqli_real_escape_string($conn, $_POST['nama']);
$telp   = mysqli_real_escape_string($conn, $_POST['telp']);
$alamat = mysqli_real_escape_string($conn, $_POST['alamat']);

$query = "INSERT INTO supplier (nama, telp, alamat) VALUES ('$nama', '$telp', '$alamat')";
if (mysqli_query($conn, $query)) {
    header("Location: index.php");
    exit;
} else {
    echo "Gagal menyimpan: " . mysqli_error($conn);
}
?>