<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika belum login, arahkan ke login.php
if (empty($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}