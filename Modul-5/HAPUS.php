<?php
include 'KONEKSI.php';
$id = intval($_GET['id'] ?? 0);

// Cek apakah supplier masih dipakai di tabel barang
$cek = mysqli_query($conn, "SELECT COUNT(*) AS jml FROM barang WHERE supplier_id = $id");
$hasil = mysqli_fetch_assoc($cek);

if ($hasil['jml'] > 0) {
    echo "<script>
            alert('Supplier tidak bisa dihapus karena masih digunakan oleh data barang!');
            window.location='INDEX.php';
          </script>";
    exit;
}

$query = "DELETE FROM supplier WHERE id = $id";
if (mysqli_query($conn, $query)) {
    // TIDAK pakai alert lagi — popup confirm sudah di INDEX.php
    header("Location: INDEX.php");
    exit;
} else {
    echo "Gagal hapus: " . mysqli_error($conn);
}
?>