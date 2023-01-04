<?php

/*
 *  Programmer  : Nuril Muslichin
 *  Email       : nurilmuslichin16@gmail.com
 *  Telegram    : @nurildaman
 *
 *  Name        : Bot Telegram Monitoring Penanganan Gangguan - PHP
 *  Pembuatan   : Januari 2023
 *
 *  File        : koneksi.php
 *  Tujuan      : Konfigurasi untuk menghubungkan ke database.
 *  ____________________________________________________________
*/

$hostname   = "localhost";
$user       = "root";
$password   = "";
$database   = "mopega";

$koneksi = mysqli_connect(
    $hostname,
    $user,
    $password,
    $database
);

// Check connection
if (mysqli_connect_errno()) {
    echo "Koneksi database gagal : " . mysqli_connect_error();
}
