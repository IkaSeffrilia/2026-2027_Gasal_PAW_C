<!-- Menambahkan lima data baru dan menampilkan dengan perulangan -->

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
echo ")<br><br>";

// Menampilkan dengan perulangan
foreach ($height as $nama => $tinggi) {
    echo "$nama is $tinggi cm tall.<br>";
}
?>