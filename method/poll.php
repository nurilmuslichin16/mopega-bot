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
 *  File        : poll.php
 *  Tujuan      : Metode poll untuk menjalankan bot telegram
 *  ____________________________________________________________
*/

require_once '../config/konfigurasi.php';

require_once '../helper/fungsi.php';

require_once '../proses.php';


function myloop()
{
    global $debug;

    $idfile = '../botprosesid.txt';
    $update_id = 0;

    if (file_exists($idfile)) {
        $update_id = (int) file_get_contents($idfile);
        echo '-';
    }

    $updates = getApiUpdate($update_id);

    foreach ($updates as $message) {
        $update_id = getApiMessage($message);
        echo '+';
    }

    file_put_contents($idfile, $update_id + 1);
}

while (true) {
    myloop();
}
