<?php
$passwordAsli = '12345';
$hasilHash = password_hash($passwordAsli, PASSWORD_DEFAULT);
echo htmlspecialchars($hasilHash);