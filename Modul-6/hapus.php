<?php
require_once __DIR__ . '/../config/Koneksi.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ==== CEK: apakah barang dipakai di transaksi_detail? ====
$cek = $koneksi->prepare(
    "SELECT COUNT(*) AS jml FROM transaksi_detail WHERE barang_id = ?"
);
$cek->bind_param('i', $id);
$cek->execute();
$jumlah_pakai = (int)$cek->get_result()->fetch_assoc()['jml'];
$cek->close();

if ($jumlah_pakai > 0) {
    // ❌ Barang masih dipakai → tolak hapus, tampilkan notifikasi
    header('Location: ../transaksi/index.php?notif=hapus_gagal');
    exit;
}

// ✅ Barang tidak dipakai → hapus biasa
$del = $koneksi->prepare("DELETE FROM barang WHERE id = ?");
$del->bind_param('i', $id);
$del->execute();
$del->close();

header('Location: ../transaksi/index.php?notif=hapus_ok');
exit;