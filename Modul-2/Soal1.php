<?php
// Array matkul
$matkul = ["PTI", "ALPRO", "DPW", "STRUKDAT", "JARKOM", "PAW", "PSBF", "RPL"];

// Array praktikum
$praktikum = ["JARKOM", "PAW"];

// Hitung jumlah elemen array matkul
$jumlah_matkul = count($matkul);

// Perulangan for
for ($i = 0; $i < $jumlah_matkul; $i++) {
    $nama_matkul = $matkul[$i];

    // Cek apakah matkul ada di array praktikum
    $is_praktikum = in_array($nama_matkul, $praktikum);

    if ($is_praktikum) {
        // Kondisi 1: matkul sama dengan data praktikum
        echo "Saya sedang mengambil matkul $nama_matkul termasuk praktikumnya<br>";
    } elseif ($i == 6 || $i == 7) {
        // Kondisi 2: indeks 6 atau 7
        echo "Saya belum mengambil matkul $nama_matkul<br>";
    } else {
        // Kondisi 3: selain kondisi di atas
        echo "Saya sudah mengambil matkul $nama_matkul semester lalu<br>";
    }
}
?>