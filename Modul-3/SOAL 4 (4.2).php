<!-- Membuat array $weight dan menampilkan dengan perulangan FOR -->

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
echo ")<br><br>";

// Menampilkan dengan perulangan FOR
$keys = array_keys($weight);
$values = array_values($weight);
$count = count($keys);

for ($i = 0; $i < $count; $i++) {
    echo $keys[$i] . " is " . $values[$i] . " kg.<br>";
}
?>