<!-- Array multidimensi dengan tabel data dan tambahan lima data -->

<?php
$students = array(
    array("Name" => "Alex", "NIM" => "2204010812345678", "Mobile" => "0812345678"),
    array("Name" => "Bianca", "NIM" => "2204020812345687", "Mobile" => "0812345687"),
    array("Name" => "Candice", "NIM" => "2204030812345665", "Mobile" => "0812345665")
);

// Menambahkan lima data baru
$students[] = array("Name" => "David", "NIM" => "2204040812345699", "Mobile" => "0812345699");
$students[] = array("Name" => "Elena", "NIM" => "2204050812345700", "Mobile" => "0812345700");
$students[] = array("Name" => "Fiona", "NIM" => "2204060812345711", "Mobile" => "0812345711");
$students[] = array("Name" => "George", "NIM" => "2204070812345722", "Mobile" => "0812345722");
$students[] = array("Name" => "Hannah", "NIM" => "2204080812345733", "Mobile" => "0812345733");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Mahasiswa</title>
    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h2>Data Mahasiswa</h2>
    <table>
        <tr>
            <th>Name</th>
            <th>NIM</th>
            <th>Mobile</th>
        </tr>
        <?php foreach ($students as $student): ?>
        <tr>
            <td><?php echo $student["Name"]; ?></td>
            <td><?php echo $student["NIM"]; ?></td>
            <td><?php echo $student["Mobile"]; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>