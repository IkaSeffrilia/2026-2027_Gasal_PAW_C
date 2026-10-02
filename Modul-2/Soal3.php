<?php
// Inisialisasi angka dari 0
$angka = 0;

// Do While
do {
    // Cek apakah angka kelipatan 4
    if ($angka % 4 == 0) {
        echo "Angka kelipatan 4: $angka<br>";
    }
    $angka++;
} while ($angka <= 20);
?>