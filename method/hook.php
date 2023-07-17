<?php

define('HS', true);

/*
 *  Programmer  : Nuril Muslichin
 *  Email       : nurilmuslichin16@gmail.com
 *  Telegram    : @nurildaman
 *
 *  Name        : Bot Telegram Monitoring Penanganan Gangguan - PHP
 *  Pembuatan   : Januari 2023
 *
 *  File        : hook.php
 *  Tujuan      : Metode hook untuk menjalankan bot telegram
 *  ____________________________________________________________
*/

require_once '../config/konfigurasi.php';

require_once '../config/koneksi.php';

require_once '../helper/fungsi.php';

require_once '../proses.php';



$entityBody = file_get_contents('php://input');
$message = json_decode($entityBody, true);
getApiMessage($message);
