<!-- 1.1. Menambahkan lima data baru dan menampilkan nilai dengan indeks tertinggi -->

<?php
$fruits = array("Avocado", "Blueberry", "Cherry");

// Menambahkan lima data baru
array_push($fruits, "Durian", "Elderberry", "Fig", "Grape", "Honeydew");

// Menampilkan seluruh array
echo "fruits = (" . implode(", ", $fruits) . ")<br>";

// Menampilkan nilai dengan indeks tertinggi
$highestIndex = count($fruits) - 1;
echo "Nilai dengan indeks tertinggi: " . $fruits[$highestIndex];
?>  