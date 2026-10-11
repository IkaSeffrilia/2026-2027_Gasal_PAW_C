<!-- Menghapus satu data tertentu dan menampilkan nilai dengan indeks tertinggi -->

<?php
$fruits = array("Avocado", "Blueberry", "Cherry", "Durian", "Elderberry", "Fig", "Grape", "Honeydew");

// Menghapus data "Blueberry"
$key = array_search("Blueberry", $fruits);
if ($key !== false) {
    unset($fruits[$key]);
    $fruits = array_values($fruits); // Re-index array
}
echo "Data Blueberry dihapus.<br>";

// Menampilkan seluruh array
echo "fruits = (" . implode(", ", $fruits) . ")<br>";

// Menampilkan nilai dengan indeks tertinggi
$highestIndex = count($fruits) - 1;
echo "Nilai dengan indeks tertinggi: " . $fruits[$highestIndex];
?>