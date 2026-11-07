<?php
require_once __DIR__ . '/../config/Koneksi.php';
require_once __DIR__ . '/update_total.php';

$id           = (int)($_GET['id'] ?? 0);
$transaksi_id = (int)($_GET['transaksi_id'] ?? 0);

// Ambil info detail sebelum dihapus (untuk mengembalikan stok)
$stmt = $koneksi->prepare(
    "SELECT barang_id, qty FROM transaksi_detail WHERE id = ? AND transaksi_id = ?"
);
$stmt->bind_param('ii', $id, $transaksi_id);
$stmt->execute();
$d = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($d) {
    // Hapus detail
    $del = $koneksi->prepare("DELETE FROM transaksi_detail WHERE id = ?");
    $del->bind_param('i', $id);
    $del->execute();
    $del->close();

    // Kembalikan stok
    $upd = $koneksi->prepare("UPDATE barang SET stok = stok + ? WHERE id = ?");
    $upd->bind_param('ii', $d['qty'], $d['barang_id']);
    $upd->execute();
    $upd->close();

    // Recalculate total transaksi
    updateTotalTransaksi($koneksi, $transaksi_id);
}

header('Location: index.php?id=' . $transaksi_id);
exit;