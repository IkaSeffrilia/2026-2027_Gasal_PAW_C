<?php
// =====================================================
// laporan.php
// Halaman reporting penjualan dengan filter tanggal,
// grafik Chart.js, tabel rekapitulasi, dan card ringkasan
// =====================================================
require 'koneksi.php';

$mulai   = $_GET['mulai']   ?? '';
$selesai = $_GET['selesai'] ?? '';

$labels           = [];
$data_total       = [];
$rekap            = [];
$total_pendapatan = 0;
$total_pelanggan  = 0;

$adaFilter = ($mulai !== '' && $selesai !== '');

if ($adaFilter) {
    // --- Query 1: agregasi pendapatan harian ---
    $q = "SELECT DATE(waktu_transaksi) AS tgl,
                 SUM(total)           AS total_harian
          FROM transaksi
          WHERE waktu_transaksi BETWEEN '$mulai' AND '$selesai'
          GROUP BY DATE(waktu_transaksi)
          ORDER BY tgl ASC";
    $res = mysqli_query($koneksi, $q) or die(mysqli_error($koneksi));

    while ($r = mysqli_fetch_assoc($res)) {
        $labels[]          = $r['tgl'];
        $data_total[]      = (int) $r['total_harian'];
        $rekap[]           = $r;
        $total_pendapatan += (int) $r['total_harian'];
    }

    // --- Query 2: jumlah pelanggan unik ---
    $qp = "SELECT COUNT(DISTINCT pelanggan_id) AS jml
           FROM transaksi
           WHERE waktu_transaksi BETWEEN '$mulai' AND '$selesai'";
    $rp = mysqli_fetch_assoc(mysqli_query($koneksi, $qp));
    $total_pelanggan = (int) ($rp['jml'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Penjualan</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
  body { font-family: Arial, sans-serif; margin: 24px; color:#222; }
  h1, h2 { color:#1a3c5e; }
  table { border-collapse: collapse; width:100%; margin-top:10px; }
  th, td { border:1px solid #333; padding:7px 9px; text-align:left; }
  th { background:#d9e6f2; }
  .no-print {
    background:#f7f9fc; padding:15px;
    border:1px dashed #999; border-radius:6px;
  }
  .no-print input[type=date] { padding:6px; }
  .no-print button {
    padding:8px 16px; margin-left:6px; cursor:pointer;
    border:none; border-radius:4px; color:#fff; font-weight:bold;
  }
  .btn-tampil { background:#2b7cff; }
  .btn-cetak  { background:#e07b00; }
  .btn-excel  { background:#1e8e3e; }
  .card-summary { display:flex; gap:20px; margin-top:15px; }
  .card {
    flex:1; border:1px solid #b0c4de; padding:12px 16px;
    background:#eef4fb; border-radius:6px; font-weight:bold; color:#1a3c5e;
  }
  .card span { display:block; font-size:22px; margin-top:6px; color:#000; }

  /* ==== ATURAN CETAK ==== */
  @media print {
    .no-print { display:none !important; }
    body { margin:0; }
    table { font-size:12px; }
    h2 { page-break-after: avoid; }
    canvas { max-width:100% !important; height:auto !important; }
  }
</style>
</head>
<body>

<!-- ============ FORM FILTER + TOMBOL ============ -->
<div class="no-print">
  <h1>Filter Laporan Penjualan</h1>
  <form method="GET" action="laporan.php">
    Dari:
    <input type="date" name="mulai"   value="<?= htmlspecialchars($mulai) ?>" required>
    Sampai:
    <input type="date" name="selesai" value="<?= htmlspecialchars($selesai) ?>" required>
    <button type="submit" class="btn-tampil">Tampilkan</button>

    <?php if ($adaFilter): ?>
      &nbsp;|&nbsp;
      <button type="button" class="btn-cetak" onclick="window.print()">🖨 Cetak</button>
      <a href="export_excel.php?mulai=<?= urlencode($mulai) ?>&selesai=<?= urlencode($selesai) ?>">
        <button type="button" class="btn-excel">📊 Excel</button>
      </a>
    <?php endif; ?>
  </form>
</div>

<?php if ($adaFilter): ?>

  <h2>Rekap Laporan Penjualan <?= htmlspecialchars($mulai) ?> sampai <?= htmlspecialchars($selesai) ?></h2>

  <!-- ============ GRAFIK ============ -->
  <h2>Grafik Penjualan</h2>
  <?php if (!empty($labels)): ?>
    <div style="width:100%; max-width:800px; height:340px; position:relative;">
      <canvas id="myChart"></canvas>
    </div>
    <script>
      const ctx = document.getElementById('myChart').getContext('2d');
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: <?= json_encode($labels) ?>,
          datasets: [{
            label: 'Total Penjualan',
            data: <?= json_encode($data_total) ?>,
            backgroundColor: 'rgba(43, 124, 255, 0.55)',
            borderColor: 'rgba(43, 124, 255, 1)',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          animation: false,   /* agar canvas tidak kosong saat print */
          plugins: { legend: { display: true } },
          scales: {
            y: {
              beginAtZero: true,
              ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') }
            },
            x: { title: { display: true, text: 'Tanggal Transaksi' } }
          }
        }
      });
    </script>
  <?php else: ?>
    <p><em>Tidak ada transaksi pada rentang tanggal tersebut.</em></p>
  <?php endif; ?>

  <!-- ============ TABEL REKAPITULASI ============ -->
  <h2>Rekapitulasi Penjualan Harian</h2>
  <table>
    <thead>
      <tr>
        <th style="width:60px;">No</th>
        <th>Total Pendapatan</th>
        <th>Tanggal Transaksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rekap)): ?>
        <tr><td colspan="3" style="text-align:center;">Tidak ada data</td></tr>
      <?php else: ?>
        <?php $no = 1; foreach ($rekap as $row): ?>
          <tr>
            <td><?= $no++ ?></td>
            <td>Rp. <?= number_format((int)$row['total_harian'], 0, ',', '.') ?></td>
            <td><?= date('d M Y', strtotime($row['tgl'])) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- ============ CARD RINGKASAN KUMULATIF ============ -->
  <div class="card-summary">
    <div class="card">
      Jumlah Pelanggan Unik
      <span><?= number_format($total_pelanggan, 0, ',', '.') ?> Orang</span>
    </div>
    <div class="card">
      Jumlah Pendapatan
      <span>Rp. <?= number_format($total_pendapatan, 0, ',', '.') ?></span>
    </div>
  </div>

<?php endif; ?>
</body>
</html>