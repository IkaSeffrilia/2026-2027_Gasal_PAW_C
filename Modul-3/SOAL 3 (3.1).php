<!-- Menambahkan lima data baru, menampilkan indeks terakhir, lalu menghapus satu data -->

<?php
$height = array("Andy" => "176", "Barry" => "165", "Charlie" => "170");

// Menambahkan lima data baru
$height["David"] = "180";
$height["Ethan"] = "172";
$height["Frank"] = "168";
$height["George"] = "175";
$height["Harry"] = "182";

// Menampilkan seluruh array
echo "height = (";
$first = true;
foreach ($height as $key => $value) {
    if (!$first) echo ", ";
    echo "\"$key\"=>\"$value\"";
    $first = false;
}
echo ")<br>";

// Menampilkan nilai dengan indeks terakhir
end($height);
echo "Nilai dengan indeks terakhir: " . current($height) . "<br><br>";

// Menghapus satu data (Barry)
unset($height["Barry"]);

// Menampilkan kembali array setelah dihapus
echo "height = (";
$first = true;
foreach ($height as $key => $value) {
    if (!$first) echo ", ";
    echo "\"$key\"=>\"$value\"";
    $first = false;
}
echo ")<br>";

// Menampilkan nilai dengan indeks terakhir setelah dihapus
end($height);
echo "Nilai dengan indeks terakhir setelah dihapus: " . current($height);
?>