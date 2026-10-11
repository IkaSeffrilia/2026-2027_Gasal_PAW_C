<!-- Implementasi berbagai fungsi array -->

<?php
echo "<h3>1. array_push()</h3>";
$arr1 = array("A");
echo "Array awal: (" . implode(", ", $arr1) . ")<br>";
array_push($arr1, "B");
echo "Hasil array_push: " . implode(" ", $arr1) . "<br><br>";

echo "<h3>2. array_merge()</h3>";
$arr2a = array("A", "B");
$arr2b = array("C");
echo "Array awal: (" . implode(", ", $arr2a) . ") digabung dengan (" . implode(", ", $arr2b) . ")<br>";
$merged = array_merge($arr2a, $arr2b);
echo "Hasil array_merge: " . implode(" ", $merged) . "<br><br>";

echo "<h3>3. array_values()</h3>";
$arr3 = array("x" => 1, "y" => 2);
echo "Array awal: (\"x\" => 1, \"y\" => 2)<br>";
$values = array_values($arr3);
echo "Hasil array_values: " . implode(" ", $values) . "<br><br>";

echo "<h3>4. array_search()</h3>";
$arr4 = array("A", "B", "C");
echo "Mencari \"B\" pada array: (" . implode(", ", $arr4) . ")<br>";
$search = array_search("B", $arr4);
echo "Hasil array_search: " . $search . "<br><br>";

echo "<h3>5. array_filter()</h3>";
$arr5 = array(0, 1, false, 2, " ", 3, "array");
echo "Array awal: (0, 1, false, 2, \" \", 3, \"array\")<br>";
$filtered = array_filter($arr5);
echo "Hasil array_filter: " . implode(" ", $filtered) . "<br><br>";

echo "<h3>6. Sorting Array</h3>";
$arr6 = array(3, 1, 2);
echo "Array awal: (" . implode(", ", $arr6) . ")<br>";

$sortArr = $arr6;
sort($sortArr);
echo "Hasil sort: " . implode(" ", $sortArr) . "<br>";

$rsortArr = $arr6;
rsort($rsortArr);
echo "Hasil rsort: " . implode(" ", $rsortArr) . "<br><br>";

$arr7 = array("Peter" => 35, "Ben" => 37, "Joe" => 43);
echo "Array awal: (\"Peter\"=>35, \"Ben\"=>37, \"Joe\"=>43)<br>";

$asortArr = $arr7;
asort($asortArr);
echo "Hasil asort: ";
foreach ($asortArr as $k => $v) echo "$k=> $v, ";
echo "<br>";

$ksortArr = $arr7;
ksort($ksortArr);
echo "Hasil ksort: ";
foreach ($ksortArr as $k => $v) echo "$k=> $v, ";
echo "<br>";

$arsortArr = $arr7;
arsort($arsortArr);
echo "Hasil arsort: ";
foreach ($arsortArr as $k => $v) echo "$k=> $v, ";
echo "<br>";

$krsortArr = $arr7;
krsort($krsortArr);
echo "Hasil krsort: ";
foreach ($krsortArr as $k => $v) echo "$k=> $v, ";
echo "<br>";
?>