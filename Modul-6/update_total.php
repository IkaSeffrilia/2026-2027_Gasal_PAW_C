<?php
/**
 * Menghitung ulang total transaksi:
 * SUM(harga * qty) dari seluruh transaksi_detail milik transaksi tersebut.
 * Lalu disimpan ke kolom transaksi.total.
 */
function updateTotalTransaksi(mysqli $koneksi, int $transaksi_id): void
{
    // Hitung total
    $stmt = $koneksi->prepare(
        "SELECT COALESCE(SUM(harga * qty), 0) AS total
         FROM transaksi_detail
         WHERE transaksi_id = ?"
    );
    $stmt->bind_param('i', $transaksi_id);
    $stmt->execute();
    $total = (int)$stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // Simpan ke tabel transaksi
    $upd = $koneksi->prepare(
        "UPDATE transaksi SET total = ? WHERE id = ?"
    );
    $upd->bind_param('ii', $total, $transaksi_id);
    $upd->execute();
    $upd->close();
}
?>