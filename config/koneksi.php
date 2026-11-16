<?php

$host = "sql302.infinityfree.com";
$user = "if0_43426130";
$password = "iYiBmw0LqMZ7W33";
$database = "if0_43426130_db_ukk_2026";

$koneksi = mysqli_connect($host, $user, $password, $database);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

?>