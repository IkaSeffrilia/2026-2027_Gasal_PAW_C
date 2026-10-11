<?php
$weight = array("Andy" => "70", "Barry" => "65", "Charlie" => "75");

// Menampilkan seluruh array
echo "weight = (";
$first = true;
foreach ($weight as $key => $value) {
    if (!$first) echo ", ";
    echo "\"$key\"=>\"$value\"";
    $first = false;
}
echo ")<br>";

// Menampilkan data kedua
$keys = array_keys($weight);
echo "Data kedua: " . $weight[$keys[1]];
?>