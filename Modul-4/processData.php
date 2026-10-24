<?php
// Tampilkan data POST (hanya jika method POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h1>Data yang Dikirim</h1>";
    echo "<table border='1' cellpadding='8'>";
    echo "<tr><th>Field</th><th>Nilai</th></tr>";

    $fields = ['surname', 'email', 'password', 'street', 'state', 'country', 'gender', 'vegetarian'];
    foreach ($fields as $field) {
        $value = isset($_POST[$field]) ? htmlspecialchars($_POST[$field]) : '(tidak diisi)';
        echo "<tr><td>" . ucfirst($field) . "</td><td>" . $value . "</td></tr>";
    }
    echo "</table>";

    // Validasi
require 'validate.inc';

$errors = array();
validateName($errors, $_POST, 'surname');

if ($errors) {
    echo 'Errors:<br/>';
    foreach ($errors as $field => $error)
        echo " $field $error</br>";
} else {
    echo 'Data OK!';
}
}
?>