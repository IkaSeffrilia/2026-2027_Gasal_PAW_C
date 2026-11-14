<?php
// =====================================================
// export_excel.php
// Ekspor rekap penjualan ke Microsoft Excel (.xls)
// tanpa library tambahan (murni HTML table + header HTTP)
// =====================================================
require 'koneksi.php';

$mulai   = $_GET['mulai']   ?? '';
$selesai = $_GET['selesai'] ?? '';

if ($mulai === '' || $selesai === '') {
    die("Rentang tanggal belum dipilih.");
}

// Query agregasi (sama seperti di laporan)
$q = "SELECT DATE(waktu_transaksi) AS tgl,
             SUM(total)           AS total_harian
      FROM transaksi
      WHERE waktu_transaksi BETWEEN '$mulai' AND '$selesai'
      GROUP BY DATE(waktu_transaksi)
      ORDER BY tgl ASC";
$res = mysqli_query($koneksi, $q) or die(mysqli_error($koneksi));

// === HEADER HTTP: paksa browser unduh file .xls ===
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"laporan_penjualan_{$mulai}_sd_{$selesai}.xls\"");
header("Pragma: no-cache");
header("Expires: 0");
?>
<table border="1">
  <thead>
    <tr>
      <th colspan="3" style="font-size:14pt; font-weight:bold; text-align:center;">
        Rekap Laporan Penjualan <?= htmlspecialchars($mulai) ?> s/d <?= htmlspecialchars($selesai) ?>
      </th>
    </tr>
    <tr style="background:#d9e6f2; font-weight:bold;">
      <th>No</th>
      <th>Total Pendapatan</th>
      <th>Tanggal Transaksi</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $no    = 1;
    $grand = 0;
    while ($r = mysqli_fetch_assoc($res)):
        $grand += (int) $r['total_harian'];
    ?>
    <tr>
      <td><?= $no++ ?></td>
      <td><?= $r['total_harian'] ?></td>
      <td><?= $r['tgl'] ?></td>
    </tr>
    <?php endwhile; ?>
    <tr style="font-weight:bold; background:#eef4fb;">
      <td colspan="2" style="text-align:right;">TOTAL KESELURUHAN</td>
      <td><?= $grand ?></td>
    </tr>
  </tbody>
</table>