<?php

ini_set('date.timezone', 'Asia/Jakarta');

if (!defined('HS')) {
    die('Tidak boleh diakses langsung.');
}

/*
 *  Programmer  : Nuril Muslichin
 *  Email       : nurilmuslichin16@gmail.com
 *  Telegram    : @nurildaman
 *
 *  Name        : Bot Telegram Monitoring Penanganan Gangguan - PHP
 *  Pembuatan   : Januari 2023
 *
 *  File        : proses.php
 *  Tujuan      : Untuk merespon semua perintah yang diberikan bot telegram
 *  ____________________________________________________________
*/

function getApiMessage($sumber)
{
    $updateid = $sumber['update_id'];

    if (isset($sumber['message'])) {
        $message = $sumber['message'];

        if (isset($message['text'])) {
            getTextMessage($message);
        } elseif (isset($message['sticker'])) {
            getStickerMessage($message);
        } else {
            // gak di proses silakan dikembangkan sendiri
        }
    }

    if (isset($sumber['callback_query'])) {
        getCallbackQuery($sumber['callback_query']);
    }

    return $updateid;
}

function getStickerMessage($message)
{
    // if ($GLOBALS['debug']) mypre($message);
}

function getCallbackQuery($message)
{
    // if ($GLOBALS['debug']) mypre($message);
}

function getTextMessage($message)
{
    // if ($GLOBALS['debug']) mypre($message);
    include 'bot-koneksi.php';
    $pesan      = $message['text'];
    $chatid     = $message['chat']['id'];
    $fromid     = $message['from']['id'];
    $fromname   = $message["from"]["first_name"];
    $meid       = $message["message_id"];
    $type_ms    = $message["chat"]["type"];

    if ($type_ms == "private") {
        $query = mysqli_query($koneksi, "SELECT user_id FROM tb_user_bot WHERE user_id = '$fromid' AND status = 1");
        if (mysqli_num_rows($query) > 0) {
            switch (true) {
                case $pesan == '/id':
                    sendApiAction($chatid);
                    $text = 'ID Kamu adalah: ' . $fromid;
                    sendApiMsg($chatid, $text);
                    break;

                case $pesan == '/start':
                    sendApiAction($chatid);
                    $text = "Halo $fromname /help untuk informasi penggunaan bot.";
                    sendApiMsg($chatid, $text);
                    break;

                case $pesan == '/help':
                    sendApiAction($chatid);
                    $text = "Halo $fromname 👋🏻, kenalin saya asma asisten nya tim DAMAN, saya ditugaskan untuk membantu pekerjaan validasi teman-teman.\n\n";
                    $text .= "📖 Berikut yang bisa saya lakukan buat kamu, \n\n";
                    $text .= "✅ /info [nama_odp] untuk cari informasi odp. \n";
                    $text .= "✅ /bc [barcode] untuk cari barcode.\n";
                    $text .= "✅ /ns [no_service] untuk cari no inet/voice.\n";
                    $text .= "✅ /belumvalins [ODC/ODP] untuk cari list odp yang belum di valins.\n";
                    $text .= "✅ /cekvalins [nama_odp] untuk cek odp sudah di valins atau belum.\n";
                    $text .= "✅ /format untuk melihat format laporan setelah valins.\n";
                    $text .= "✅ /formatcekpoin untuk melihat format perolehan valins oleh teknisi. \n";
                    $text .= "✅ /cekpoin untuk melihat perolehan total valins oleh teknisi dalam rentang waktu tertentu. gunakan /formatcekpoin untuk melihat formatnya.\n";
                    $text .= "✅ /donevalins untuk laporan hasil valins. gunakan /format untuk melihat formatnya.\n";
                    $text .= "✅ /help untuk melihat informasi bantuan \n\n";
                    $text .= 'Terima Kasih 😎';

                    sendApiMsg($chatid, $text, false, 'Markdown');
                    break;

                case $pesan == '/format':
                    sendApiAction($chatid);
                    $text = "/donevalins data\n";
                    $text .= "Valins ID: \n";
                    $text .= "Time: \n";
                    $text .= "Summary ODP: \n";
                    $text .= "SPL 1_2 : \n";
                    $text .= "SPL 1_4 : \n";
                    $text .= "SPL 1_8 : \n";
                    $text .= "Teknisi : \n";
                    $text .= "Nomor Link Aja : \n";
                    sendApiMsg($chatid, $text);
                    break;

                case $pesan == '/formatcekpoin':
                    sendApiAction($chatid);
                    $text = "/cekpoin no_link_aja tahun-bulan-tanggal sd tahun-bulan-tanggal";
                    sendApiMsg($chatid, $text);
                    break;

                case preg_match("/\/bc (.*)/", $pesan, $hasil):
                case preg_match("/\/Bc (.*)/", $pesan, $hasil):
                    sendApiAction($chatid);
                    $pesan = strtoupper($message['text']);
                    $barcode = str_replace("/BC ", "", $pesan);

                    $query1 = mysqli_query($koneksi, "SELECT nama_odp, barcode_odp FROM tb_odp WHERE barcode_odp = '$barcode'");
                    $query2 = mysqli_query($koneksi, "SELECT 
                            v.port_no,
                            v.onu_id,
                            v.barcode,
                            v.cli_id,
                            o.nama_odp,
                            o.teknisi,
                            o.barcode_odp,
                            o.olt,
                            o.slot,
                            o.port,
                            o.created_at,
                            o.last_edit,
                            v.onu_sn,
                            v.sip_username,
                            v.hsi_username
                        FROM tb_odp o
                        LEFT JOIN tb_validasi v ON v.id_odp = o.id_odp
                        WHERE v.barcode = '$barcode'");

                    if (mysqli_num_rows($query1) > 0) {
                        while ($d = mysqli_fetch_array($query1)) {
                            $text = "(STATUS : BARCODE ODP)\n";
                            $text .= "$d[nama_odp] \n";
                            $text .= "$d[barcode_odp] \n";
                        }
                    } else {
                        if (mysqli_num_rows($query2) > 0) {
                            while ($d = mysqli_fetch_array($query2)) {
                                $text = "$d[nama_odp] \n";
                                $text .= "Olt : $d[cli_id] \n";
                                $text .= "Port No : $d[port_no] \n";
                                $text .= "Onu ID : $d[onu_id] \n";
                                $text .= "Barcode : $d[barcode] \n";
                                $text .= "Internet : $d[hsi_username] \n";
                                $text .= "Voice : $d[sip_username]";
                            }
                        } else {
                            $text = "Maaf Barcode $barcode tidak ditemukan.. \n";
                        }
                    }
                    sendApiMsg($chatid, $text, $meid);
                    break;

                default:
                    sendApiAction($chatid);
                    if (stripos($pesan, 'Summary ODP:') !== false) {

                        preg_match('/Summary ODP\s{0,1}:(.+)/i', $pesan, $odp);
                        preg_match('/IP OLT\s{0,1}:(.+)/i', $pesan, $oltip);
                        preg_match('/Slot\s{0,1}:(.+)/i', $pesan, $slot);
                        preg_match('/Port\s{0,1}:(.+)/i', $pesan, $port);
                        preg_match('/longitude\s{0,1}:(.+)/i', $pesan, $long);
                        preg_match('/latitude\s{0,1}:(.+)/i', $pesan, $lat);
                        preg_match('/QR-Code ODP\s{0,1}\n(.+)/i', $pesan, $qrodp);

                        $odp        = (trim($odp[1]) != '' ? trim($odp[1]) : '-');
                        $odp        = strlen($odp) < 14 ? str_replace('/', '/0', $odp) : $odp;
                        $oltip      = (trim($oltip[1]) != '' ? trim($oltip[1]) : '-');
                        $slot       = (trim($slot[1]) != '' ? trim($slot[1]) : '-');
                        $port       = (trim($port[1]) != '' ? trim($port[1]) : '-');
                        $long       = (trim($long[1]) != '' ? trim($long[1]) : '-');
                        $lat        = (trim($lat[1]) != '' ? trim($lat[1]) : '-');
                        $longlat    = $lat . ',' . $long;
                        $datenow    = date('Y-m-d');
                        $qrodp      = (trim($qrodp[1]) != '' ? trim($qrodp[1]) : '-');
                        $cekodp     = mysqli_query($koneksi, "SELECT id_odp,nama_odp,barcode_odp FROM tb_odp WHERE barcode_odp = '$qrodp' LIMIT 1");

                        if (mysqli_num_rows($cekodp) > 0) {
                            $text = "QR-Code ODP : $qrodp sudah digunakan.";
                        } else {
                            $cekipolt = mysqli_query($koneksi, "SELECT olt_ip, olt_name FROM tb_olt_pkl WHERE olt_ip = '$oltip'");
                            if (mysqli_num_rows($cekipolt) > 0) {
                                while ($d = mysqli_fetch_array($cekipolt)) {
                                    $oltname = $d['olt_name'];
                                }
                                $sql = mysqli_query($koneksi, "INSERT INTO tb_odp (nama_odp, olt, frame, slot, port, barcode_odp, teknisi, lat_lng, created_at, last_edit, created_by) VALUES ('$odp', '$oltname', '1', $slot, $port, '$qrodp', 'VALINS', '$longlat', '$datenow', '$datenow', 10)");
                                if ($sql) {
                                    $text = "ODP Berhasil ditambahkan!";
                                } else {
                                    $text = "Error Insert Data!";
                                }
                            } else {
                                $text = "Tidak dapat menemukan OLT IP : $oltip.";
                            }
                        }
                    } elseif (stripos($pesan, 'info') !== false) {
                        $text = 'Untuk cek info ODP hasil validasi, gunakan /info nama_odp';
                    } else {
                        $text = 'Ketik /help  untuk melihat informasi bantuan.';
                    }
                    sendApiMsg($chatid, $text, $meid);
                    break;
            }
        } else {
            switch (true) {
                case preg_match("/\/cekvalins (.*)/", $pesan, $hasil):
                case preg_match("/\/Cekvalins (.*)/", $pesan, $hasil):
                    $pesan  = strtoupper($message['text']);
                    $odp    = str_replace("/CEKVALINS ", "", $pesan);
                    $query1 = mysqli_query($koneksi, "SELECT * FROM valins WHERE odp_name = '$odp'");
                    if (mysqli_num_rows($query1) > 0) {
                        while ($d = mysqli_fetch_array($query1)) {
                            $text = "(STATUS : $d[status_valins] VALINS)\n";
                            $text .= "Valins ID : $d[valins_id] \n";
                            $text .= "ODP : $d[odp_name] \n";
                            $text .= "Lokasi : \nhttps://www.google.co.id/maps/search/$d[odp_location] \n";
                        }
                    } else {
                        $text = "ODP : $odp tidak ditemukan.";
                    }
                    sendApiMsg($chatid, $text, $meid);
                    break;

                case preg_match("/\/belumvalins (.*)/", $pesan, $hasil):
                case preg_match("/\/Belumvalins (.*)/", $pesan, $hasil):
                    $pesan  = strtoupper($message['text']);
                    $odp    = str_replace("/BELUMVALINS ", "", $pesan);
                    if (strlen($odp) > 3) {
                        $query1 = mysqli_query($koneksi, "SELECT * FROM valins WHERE odp_name LIKE '%$odp%' AND status_valins LIKE 'BELUM%'");
                        if (mysqli_num_rows($query1) > 0) {
                            while ($d = mysqli_fetch_array($query1)) {
                                $text = "(ODP $d[status_valins] VALINS)\n";
                            }
                            foreach ($query1 as $row) {
                                $text .= "$row[odp_name]\n";
                                $text .= "Lokasi : \nhttps://www.google.co.id/maps/search/$row[odp_location]\n\n";
                            }
                        } else {
                            $text = "Kata Kunci : $odp tidak ditemukan.";
                        }
                    } else {
                        $text = "Gunakan nama odc untuk mencari odp belum valins. Misal : PKL-FA";
                    }

                    sendApiMsg($chatid, $text, $meid);
                    break;

                case preg_match("/\/donevalins (.*)/", $pesan, $hasil):

                    trim($pesan);
                    $pesan = str_replace("/donevalins ", "", $pesan);
                    $pesan = strtoupper($pesan);
                    preg_match('/SUMMARY ODP\s{0,1}:(.+)/i', $pesan, $odp);
                    preg_match('/VALINS ID\s{0,1}:(.+)/i', $pesan, $valins_id);
                    preg_match('/TIME\s{0,1}:(.+)/i', $pesan, $time_valins);
                    preg_match('/NOMOR LINK AJA\s{0,1}:(.+)/i', $pesan, $nomor_hp);
                    preg_match('/TEKNISI\s{0,1}:(.+)/i', $pesan, $teknisi);
                    preg_match('/SPL 1_2\s{0,1}:(.+)/i', $pesan, $spl1_2);
                    preg_match('/SPL 1_4\s{0,1}:(.+)/i', $pesan, $spl1_4);
                    preg_match('/SPL 1_8\s{0,1}:(.+)/i', $pesan, $spl1_8);

                    $odp        = (trim($odp[1]) != '' ? trim($odp[1]) : '-');
                    $valins_id  = (trim($valins_id[1]) != '' ? trim($valins_id[1]) : '-');
                    $datenow    = date('Y-m-d');
                    $teknisi    = (trim($teknisi[1]) != '' ? trim($teknisi[1]) : '-');
                    $time_valins = (trim($time_valins[1]) != '' ? trim($time_valins[1]) : '-');
                    $nomor_hp    = (trim($nomor_hp[1]) != '' ? trim($nomor_hp[1]) : '-');
                    $spl1_2    = (trim($spl1_2[1]) != '' ? trim($spl1_2[1]) : '-');
                    $spl1_4    = (trim($spl1_4[1]) != '' ? trim($spl1_4[1]) : '-');
                    $spl1_8    = (trim($spl1_8[1]) != '' ? trim($spl1_8[1]) : '-');

                    //cek kelengkapan isian

                    if ($odp == '-') {
                        $text = "Nama ODP harus diisi!";
                    } elseif ($valins_id == '-') {
                        $text = "Valins ID harus diisi!";
                    } elseif ($time_valins == '-') {
                        $text = "Time Valins harus diisi! Copykan dari hasil valins isian Time";
                    } elseif ($teknisi == '-') {
                        $text = "Teknisi harus diisi!";
                    } elseif ($nomor_hp == '-') {
                        $text = "Nomor Link Aja harus diisi!";
                    } elseif ($spl1_8 == '-') {
                        $text = "SPL 1_8 Harus diisi!";
                    } elseif ($teknisi != 'QE' && $teknisi != 'VALDAT BPP' && $teknisi != 'VALDAT BTS' && $teknisi != 'PROV PEKALONGAN' && $teknisi != 'PROV BATANG' && $teknisi != 'PROV BREBES' && $teknisi != 'PROV SLAWI' && $teknisi != 'PROV TEGAL' && $teknisi != 'PROV PEMALANG' && $teknisi != 'DEPLOYMENT' && $teknisi != 'MIGRATION' && $teknisi != 'ASSURANCE' && $teknisi != 'MAINTENANCE' && $teknisi != 'KOPEG') {
                        $text = "Pilih Teknisi dengan : \nVALDAT BPP\nVALDAT BTS\nDEPLOYMEMT\nMIGRATION\nASSURANCE\nMAINTENANCE\nPROV PEKALONGAN\nPROV BREBES\nPROV PEMALANG\nPROV BATANG\nPROV TEGAL\nPROV SLAWI\nQE\nKOPEG";
                    } else {
                        // data lengkap
                        $cekodp    = mysqli_query($koneksi, "SELECT * FROM valins WHERE odp_name = '$odp' ORDER BY id DESC LIMIT 1");
                        $cekvalins = mysqli_query($koneksi, "SELECT * FROM valins WHERE odp_name = '$odp' AND status_valins LIKE 'BELUM%' ORDER BY id DESC LIMIT 1");

                        if (mysqli_num_rows($cekodp) > 0) {
                            if (mysqli_num_rows($cekvalins) > 0) {
                                $updateodp = mysqli_query($koneksi, "UPDATE valins SET status_valins='SUDAH', valins_id='$valins_id', teknisi='$teknisi', time_valins='$time_valins', spl_1_2='$spl1_2', spl_1_4='$spl1_4', spl_1_8='$spl1_8', nomor_hp='$nomor_hp', fromname='$fromname' WHERE odp_name='$odp'");
                                if ($updateodp) {
                                    $text = "✅ Laporan Valins Diterima.";
                                } else {
                                    $text = mysqli_error($koneksi);
                                }
                            } else {
                                $text = "❌ ODP $odp sudah di valins.";
                            }
                        } else {
                            $text = "❌ ODP $odp tidak ditemukan.";
                        }
                    }
                    sendApiMsg($chatid, $text, $meid);
                    break;

                case preg_match("/\/cekpoin (.*)/", $pesan, $hasil):
                case preg_match("/\/cekpoin (.*)/", $pesan, $hasil):
                    $pesan  = strtoupper($message['text']);
                    $pesannya = str_replace("/CEKPOIN ", "", $pesan);
                    $pecah  = explode(" ", $pesannya);
                    $hp     = $pecah[0];
                    $start  = $pecah[1];
                    $end    = $pecah[3];
                    if ($pecah[2] != 'SD') {
                        $text = "❌ Format cek poin salah. /cekpoin no_link_aja tahun-bulan-tanggal sd tahun-bulan-tanggal";
                    } else {
                        $query1 = mysqli_query($koneksi, "SELECT COUNT(valins_id) as poin FROM valins WHERE (DATE(updated_at) BETWEEN '$start' AND '$end')  AND nomor_hp = '$hp'");
                        $startdate = tgl_indo($start);
                        $enddate   = tgl_indo($end);
                        if (mysqli_num_rows($query1) > 0) {
                            while ($d = mysqli_fetch_array($query1)) {
                                $text = "<b>VALINS ODP BY $hp $startdate - $enddate </b>\n";
                                $text .= "Total ODP : $d[poin] \n";
                            }
                        } else {
                            $text = "Data tidak ditemukan!";
                        }
                    }

                    sendApiMsg($chatid, $text, $meid, 'html');
                    break;

                case $pesan == '/start':
                    sendApiAction($chatid);
                    $text = "Halo $fromname /help untuk informasi penggunaan bot.";
                    sendApiMsg($chatid, $text);
                    break;

                case $pesan == '/format':
                    sendApiAction($chatid);
                    $text = "/donevalins data\n";
                    $text .= "Valins ID: \n";
                    $text .= "Time: \n";
                    $text .= "Summary ODP: \n";
                    $text .= "SPL 1_2 : \n";
                    $text .= "SPL 1_4 : \n";
                    $text .= "SPL 1_8 : \n";
                    $text .= "Teknisi : \n";
                    $text .= "Nomor Link Aja : \n";
                    sendApiMsg($chatid, $text);
                    break;

                case $pesan == '/register':
                    sendApiAction($chatid);
                    $cekuser = mysqli_query($koneksi, "SELECT * FROM tb_user_bot WHERE user_id = '$fromid' LIMIT 1");
                    if (mysqli_num_rows($cekuser) > 0) {
                        $text = "Akun sudah terdaftar!";
                    } else {
                        $query = mysqli_query($koneksi, "INSERT INTO tb_user_bot (user_id, username) VALUES ($fromid, '$fromname')");
                        $text = "Telegram account $fromname successfully registered, please wait for approval account by admin.";
                    }
                    sendApiMsg($chatid, $text);
                    break;

                case $pesan == '/help':
                    sendApiAction($chatid);
                    $text = "Halo $fromname 👋🏻, kenalin saya asma asisten nya tim DAMAN, saya ditugaskan untuk membantu pekerjaan validasi teman-teman.\n\n";
                    $text .= "📖 Berikut yang bisa saya lakukan buat kamu, \n\n";
                    $text .= "✅ /belumvalins [ODC/ODP] untuk cari list odp yang belum di valins.\n";
                    $text .= "✅ /cekvalins [nama_odp] untuk cek odp sudah di valins atau belum.\n";
                    $text .= "✅ /format untuk melihat format laporan setelah valins.\n";
                    $text .= "✅ /formatcekpoin untuk melihat format perolehan valins oleh teknisi. \n";
                    $text .= "✅ /cekpoin untuk melihat perolehan total valins oleh teknisi dalam rentang waktu tertentu. gunakan /formatcekpoin untuk melihat formatnya.\n";
                    $text .= "✅ /donevalins untuk laporan hasil valins. gunakan /format untuk melihat formatnya.\n";
                    $text .= "✅ /infovalins untuk melihat informasi ODP hasil valins\n";
                    $text .= "✅ /register untuk Registrasi akun ASMA\n";
                    $text .= "✅ /help untuk melihat informasi bantuan \n\n";
                    $text .= 'Terima Kasih 😎';

                    sendApiMsg($chatid, $text, false, 'Markdown');
                    break;

                case $pesan == '/formatcekpoin':
                    sendApiAction($chatid);
                    $text = "/cekpoin no_link_aja tahun-bulan-tanggal sd tahun-bulan-tanggal";
                    sendApiMsg($chatid, $text);
                    break;

                case preg_match("/\/ceklive (.*)/", $pesan, $hasil):
                case preg_match("/\/Ceklive (.*)/", $pesan, $hasil):
                    $pesan  = strtoupper($message['text']);
                    $odp    = str_replace("/CEKLIVE ", "", $pesan);
                    $query1 = mysqli_query($koneksi, "SELECT * FROM tb_odp_uim WHERE odp_location = '$odp'");
                    if (mysqli_num_rows($query1) > 0) {
                        while ($d = mysqli_fetch_array($query1)) {
                            $text = "🟢 STATUS : LIVE\n";
                            $text .= "ODP : $d[odp_location] \n";
                            $text .= "Lokasi : \nhttps://www.google.co.id/maps/search/$d[latitude],$d[longitude] \n";
                        }
                    } else {
                        $pecah_odp = explode("/", $odp);
                        $digit = strlen($pecah_odp[1]);
                        if ($digit >= 3) {
                            $belakang = substr($pecah_odp[1], 1);
                            $suggest_odp = $pecah_odp[0] . '/' . $belakang;
                        } else {
                            $belakang = substr_replace($pecah_odp[1], '0', 1, 0);
                            $suggest_odp = $pecah_odp[0] . '/' . $belakang;
                        }
                        $query2 = mysqli_query($koneksi, "SELECT * FROM tb_odp_uim WHERE odp_location = '$suggest_odp'");
                        if (mysqli_num_rows($query2) > 0) {
                            while ($z = mysqli_fetch_array($query2)) {
                                $text = "🟢 STATUS : LIVE\n";
                                $text .= "ODP : $z[odp_location] \n";
                                $text .= "Lokasi : \nhttps://www.google.co.id/maps/search/$z[latitude],$z[longitude] \n";
                            }
                        } else {
                            $text = "🔴 STATUS : BLM LIVE\n";
                            $text .= "ODP : $odp\n\n";
                        }
                    }
                    sendApiMsg($chatid, $text, $meid);
                    break;

                default:
                    sendApiAction($chatid);
                    $text = "/start untuk memulai percakapan";
                    sendApiMsg($chatid, $text);
                    break;
            }
        }
    } else {
        if (isset($message['forward_from']['id']) && $message['forward_from']['id'] == 785336599) {

            preg_match('/Summary ODP\s{0,1}:(.+)/i', $pesan, $odp);
            preg_match('/IP OLT\s{0,1}:(.+)/i', $pesan, $oltip);
            preg_match('/Slot\s{0,1}:(.+)/i', $pesan, $slot);
            preg_match('/Port\s{0,1}:(.+)/i', $pesan, $port);
            preg_match('/longitude\s{0,1}:(.+)/i', $pesan, $long);
            preg_match('/latitude\s{0,1}:(.+)/i', $pesan, $lat);
            preg_match('/QR-Code ODP\s{0,1}\n(.+)/i', $pesan, $qrodp);
            preg_match_all('/(\d+). (TQ.*)/', $pesan, $qrcodedc);

            $odp        = (trim($odp[1]) != '' ? trim($odp[1]) : '-');
            $odp        = strlen($odp) < 14 ? str_replace('/', '/0', $odp) : $odp;
            $oltip      = (trim($oltip[1]) != '' ? trim($oltip[1]) : '-');
            $slot       = (trim($slot[1]) != '' ? trim($slot[1]) : '-');
            $port       = (trim($port[1]) != '' ? trim($port[1]) : '-');
            $long       = (trim($long[1]) != '' ? trim($long[1]) : '-');
            $lat        = (trim($lat[1]) != '' ? trim($lat[1]) : '-');
            $longlat    = $lat . ',' . $long;
            $datenow    = date('Y-m-d');
            $qrodp      = (trim($qrodp[1]) != '' ? trim($qrodp[1]) : '-');
            $portdc     = $qrcodedc[1];
            $qrdc       = $qrcodedc[2];
            $lanjut     = 'UIM Tools aja';
            $cekodp     = mysqli_query($koneksi, "SELECT id_odp,nama_odp FROM tb_odp WHERE nama_odp = '$odp' LIMIT 1");
            if ($qrodp == '-') {
                if (mysqli_num_rows($cekodp) > 0) {
                    while ($d = mysqli_fetch_array($cekodp)) {
                        $odp_id = $d['id_odp'];
                    }
                    $cekipolt = mysqli_query($koneksi, "SELECT olt_ip, olt_name FROM tb_olt_pkl WHERE olt_ip = '$oltip'");
                    if (mysqli_num_rows($cekipolt) > 0) {
                        while ($d = mysqli_fetch_array($cekipolt)) {
                            $oltname = $d['olt_name'];
                        }
                        $updateodp = mysqli_query($koneksi, "UPDATE tb_odp SET olt='$oltname', slot='$slot', port='$port', barcode_odp='-', teknisi='VALINS', lat_lng='$longlat', last_edit='$datenow', updated_by=10 WHERE id_odp='$odp_id'");
                        if ($updateodp) {
                            $text = "[ODP KOSONG] Well Done, $odp successfully updated!";
                        }
                    } else {
                        $text = "Tidak dapat menemukan OLT IP : $oltip.";
                    }
                } else {
                    $text = "Tidak dapat menemukan ODP : $odp.";
                }
            } else {
                if (mysqli_num_rows($cekodp) > 0) {
                    while ($d = mysqli_fetch_array($cekodp)) {
                        $odp_id = $d['id_odp'];
                    }
                    // action update
                    $cekipolt = mysqli_query($koneksi, "SELECT olt_ip, olt_name FROM tb_olt_pkl WHERE olt_ip = '$oltip'");
                    if (mysqli_num_rows($cekipolt) > 0) {
                        while ($d = mysqli_fetch_array($cekipolt)) {
                            $oltname = $d['olt_name'];
                        }
                        // update data ODP
                        $updateodp = mysqli_query($koneksi, "UPDATE tb_odp SET olt='$oltname', slot='$slot', port='$port', barcode_odp='$qrodp', teknisi='VALINS', lat_lng='$longlat', last_edit='$datenow', updated_by=10 WHERE id_odp='$odp_id'");
                        if ($updateodp) {
                            // update data PORT
                            $text1 = explode("\n", $pesan);
                            foreach ($text1 as $part) {
                                $text2 = explode("|", $part);
                                $result[array_shift($text2)] = $text2;
                            }
                            //$dataport = array();
                            $isi = array_filter($result);
                            $totalarr = count($isi) - 8;
                            for ($i = 1; $i <= $totalarr; $i++) {
                                $array_keys = array_keys($isi);
                                $portno  = $array_keys[$i];
                                $onu_id  = $isi[$portno][0];
                                $onu_sn  = $isi[$portno][1];
                                $sip_1   = $isi[$portno][2];
                                $sip_2   = $isi[$portno][3];
                                $no_hsi  = $isi[$portno][4];
                                $cli_id  = $oltname . ':' . $slot . '/' . $port . '/' . $onu_id;
                                $odp_slot_port = $oltname . ':' . $slot . '/' . $port;

                                $cekportodp = mysqli_query($koneksi, "SELECT id_validasi,id_odp,port_no,onu_id FROM tb_validasi WHERE id_odp = '$odp_id' AND port_no = '$portno'");
                                $cekonuidodp = mysqli_query($koneksi, "SELECT id_validasi,id_odp,port_no,onu_id FROM tb_validasi WHERE id_odp = '$odp_id' AND onu_id = '$onu_id'");
                                if (mysqli_num_rows($cekportodp) > 0) {
                                    if (mysqli_num_rows($cekonuidodp) > 0) {
                                        while ($d = mysqli_fetch_array($cekonuidodp)) {
                                            $portexsodp = $d['port_no'];
                                        }
                                        // set null dulu onu id sebelumnya
                                        $setnullonuidex = mysqli_query($koneksi, "UPDATE tb_validasi SET onu_id='unidentified' WHERE id_odp='$odp_id' AND port_no = '$portexsodp'");
                                        if ($setnullonuidex) {
                                            //update port
                                            $updateport = mysqli_query($koneksi, "UPDATE tb_validasi SET port_no='$portno', onu_id='$onu_id', odp_slot_port='$odp_slot_port', onu_sn='$onu_sn', onu_desc='-', sip_username='$sip1', hsi_username='$no_hsi', cli_id='$cli_id', last_edit='$datenow', updated_by=10 WHERE id_odp='$odp_id' AND port_no = '$portno'");
                                        }
                                    } else {
                                        $updateport = mysqli_query($koneksi, "UPDATE tb_validasi SET port_no='$portno', onu_id='$onu_id', odp_slot_port='$odp_slot_port', onu_sn='$onu_sn', onu_desc='-', sip_username='$sip1', hsi_username='$no_hsi', cli_id='$cli_id', last_edit='$datenow', updated_by=10 WHERE id_odp='$odp_id' AND port_no = '$portno'");
                                    }
                                } else {
                                    if (mysqli_num_rows($cekonuidodp) > 0) {
                                        while ($d = mysqli_fetch_array($cekonuidodp)) {
                                            $portexsodp = $d['port_no'];
                                        }
                                        // set null dulu onu id sebelumnya
                                        $setnullonuidex = mysqli_query($koneksi, "UPDATE tb_validasi SET onu_id='unidentified' WHERE id_odp='$odp_id' AND port_no = '$portexsodp'");
                                        if ($setnullonuidex) {
                                            //insert new port
                                            $insertnewport = mysqli_query($koneksi, "INSERT INTO tb_validasi (id_validasi, id_odp, port_no, onu_id, odp_slot_port, cli_id, created_at, last_edit, created_by, updated_by, onu_sn, onu_desc, sip_username, hsi_username) VALUES (NULL, '$odp_id', '$portno', '$onu_id', '$odp_slot_port', '$cli_id', '$datenow', '$datenow', '10', '10', '$onu_sn', NULL, '$sip_1', '$no_hsi')");
                                        }
                                    } else {
                                        //insert new port
                                        $insertnewport = mysqli_query($koneksi, "INSERT INTO tb_validasi (id_validasi, id_odp, port_no, onu_id, odp_slot_port, cli_id, created_at, last_edit, created_by, updated_by, onu_sn, onu_desc, sip_username, hsi_username) VALUES (NULL, '$odp_id', '$portno', '$onu_id', '$odp_slot_port', '$cli_id', '$datenow', '$datenow', '10', '10', '$onu_sn', '-', '$sip_1', '$no_hsi')");
                                    }
                                }
                            }

                            $totaldc = count($qrcodedc[1]);
                            $totalbc = count($qrcodedc[2]);
                            for ($i = 0; $i < $totalbc; $i++) {
                                $portdc = $qrcodedc[1][$i];
                                $bcdc   = $qrcodedc[2][$i];
                                $cli_id_un  = $oltname . ':' . $slot . '/' . $port . '/unidentified';
                                $cekdcbcodp = mysqli_query($koneksi, "SELECT id_validasi,id_odp,port_no FROM tb_validasi WHERE id_odp = '$odp_id' AND port_no = '$portdc'");
                                $cekbcdiodp = mysqli_query($koneksi, "SELECT id_validasi,id_odp,port_no,barcode FROM tb_validasi WHERE id_odp = '$odp_id' AND barcode = '$bcdc'");
                                if (mysqli_num_rows($cekdcbcodp) > 0) {
                                    if (mysqli_num_rows($cekbcdiodp) > 0) {
                                        while ($d = mysqli_fetch_array($cekbcdiodp)) {
                                            $portexsodp = $d['port_no'];
                                        }

                                        $setnullbceks = mysqli_query($koneksi, "UPDATE tb_validasi SET barcode='-', last_edit='$datenow' WHERE id_odp='$odp_id' AND port_no = '$portexsodp'");
                                        if ($setnullbceks) {
                                            $updatebcport = mysqli_query($koneksi, "UPDATE tb_validasi SET barcode='$bcdc', last_edit='$datenow' WHERE id_odp='$odp_id' AND port_no = '$portdc'");
                                        }
                                    } else {
                                        $updatebcport = mysqli_query($koneksi, "UPDATE tb_validasi SET barcode='$bcdc', last_edit='$datenow' WHERE id_odp='$odp_id' AND port_no = '$portdc'");
                                    }
                                } else {
                                    if (mysqli_num_rows($cekbcdiodp) > 0) {
                                        while ($d = mysqli_fetch_array($cekbcdiodp)) {
                                            $portexsodp = $d['port_no'];
                                        }
                                        $setnullbceks = mysqli_query($koneksi, "UPDATE tb_validasi SET barcode='-', last_edit='$datenow' WHERE id_odp='$odp_id' AND port_no = '$portexsodp'");
                                        if ($setnullbceks) {
                                            $insertnewport = mysqli_query($koneksi, "INSERT INTO tb_validasi (id_validasi, id_odp, port_no, onu_id, odp_slot_port, barcode, cli_id, created_at, last_edit, created_by, updated_by, onu_sn, onu_desc, sip_username, hsi_username) VALUES (NULL, '$odp_id', '$portdc', 'unidentified', '$odp_slot_port', '$bcdc' , '$cli_id_un', '$datenow', '$datenow', '10', '10', '-', NULL, '-', '-')");
                                        }
                                    } else {
                                        //insert barcode new port
                                        $insertnewport = mysqli_query($koneksi, "INSERT INTO tb_validasi (id_validasi, id_odp, port_no, onu_id, odp_slot_port, barcode, cli_id, created_at, last_edit, created_by, updated_by, onu_sn, onu_desc, sip_username, hsi_username) VALUES (NULL, '$odp_id', '$portdc', 'unidentified', '$odp_slot_port', '$bcdc' , '$cli_id_un', '$datenow', '$datenow', '10', '10', '-', NULL, '-', '-')");
                                    }
                                }
                            }

                            // $text = "Well Done, $odp successfully updated!";
                            $text = "✍️ oke mas $fromname, $odp aku update yaa.. Lanjut $lanjut";
                        } else {
                            $text = "Tidak dapat mengupdate ODP : $odp.";
                        }
                    } else {
                        $text = "Tidak dapat menemukan OLT IP : $oltip.";
                    }
                } else {
                    // report tidak menemukan odp
                    $text = "Tidak dapat menemukan ODP : $odp.";
                }
            }


            sendApiMsg($chatid, $text, $meid);
        } else {
            switch (true) {
                    /*case preg_match("/\/infovalins (.*)/", $pesan, $hasil):
                    case preg_match("/\/Infovalins (.*)/", $pesan, $hasil):
                        $pesan          = strtoupper($message['text']);
                        $odp            = str_replace("/INFOVALINS ", "", $pesan);
                        $query_group_by = mysqli_query($koneksi, "SELECT * FROM valins_summary WHERE nama_odp = '$odp' AND online_at != '0000-00-00' GROUP BY id_summary ORDER BY online_at DESC");
                        if (mysqli_num_rows($query_group_by) > 0) {
                            $text = "✅ *$odp* \n";
                            while ($d = mysqli_fetch_assoc($query_group_by)) {
                                $tgl_valins = tgl_indo("$d[online_at]");
                                $text .= "---------------------------------- \n";
                                $text .= "*Valins ID* : $d[id_summary] \n";
                                $text .= "*Tgl Valins* : $tgl_valins \n";
                                $text .= "*Teknisi* : $d[nama] \n";
                                $text .= "*IP OLT* : $d[ip_olt] \n";
                                $text .= "*Slot* : $d[slot] \n";
                                $text .= "*Port* : $d[port] \n";
                                $text .= "*Barcode ODP* : $d[qrcode_odp] \n";
                                $text .= "*Isi* : \n";
                                $text .= "*No Port | SN ONT | No Inet | No Voice | Barcode* \n";
                                $query_id_summary = mysqli_query($koneksi, "SELECT * FROM valins_summary WHERE id_summary = '$d[id_summary]' ORDER BY port_odp ASC");
                                foreach ($query_id_summary as $q) {
                                    $voice = $q['sip1'] == '' ? $q['sip2'] : $q['sip1'];
                                    $text .= "$q[port_odp] | $q[onu_sn] | $q[ppoe] | $voice | $q[qrcode_dropcore] \n";
                                }
                            }
                        } else {
                            $text = "Valins $odp tidak ditemukan.";
                        }
                        sendApiMsg($chatid, $text, $meid, 'Markdown');
                        break;
                    
                    case preg_match("/\/laporvaldat (.*)/", $pesan, $hasil):

                        trim($pesan);
                        $pesan = str_replace("/laporvaldat ", "", $pesan);
                        preg_match('/Nama ODP\s{0,1}:(.+)/i', $pesan, $odp);
                        preg_match('/Lokasi\s{0,1}:(.+)/i', $pesan, $lokasi);
                        preg_match('/QR-Code ODP\s{0,1}:(.+)/i', $pesan, $qrodp);
                        preg_match_all('/(\d+). (TQ.*)/', $pesan, $qrcodedc);

                        $odp        = (trim($odp[1]) != '' ? trim($odp[1]) : '-');
                        $digit      = explode("/", $odp);
                        if (strlen($digit[1]) != 3) {
                            $odp = str_replace('/', '/0', $odp);
                        }
                        $lokasi     = (trim($lokasi[1]) != '' ? trim($lokasi[1]) : '-');
                        $datenow    = date('Y-m-d');
                        $qrodp      = (trim($qrodp[1]) != '' ? trim($qrodp[1]) : '-');
                        $portdc     = $qrcodedc[1];
                        $qrdc       = $qrcodedc[2];
                        $ceklat     = str_split($lokasi);
                        $totaldc    = count($qrcodedc[1]);
                        $totalbc    = count($qrcodedc[2]);
                        //cek kelengkapan isian

                        if ($odp == '-') {
                            $text = "Nama ODP harus diisi!";
                        } elseif ($lokasi == '-') {
                            $text = "Lokasi (latitude,longitude) harus diisi!";
                        } elseif (stripos($lokasi, 'https') !== false) {
                            $text = 'Isi koordinat dengan latitude,longitude. misal : -6.89041,109.620956';
                            $text .= 'Silahkan perbaiki dan kirim ulang.';
                        } elseif (stripos($lokasi, ',') == false) {
                            $text = 'Penulisan koordinat tidak sesuai. contoh yang sesuai : -6.89041,109.620956';
                            $text .= 'Silahkan perbaiki dan kirim ulang.';
                        } elseif ($ceklat[2] != '.') {
                            $text = 'Penulisan koordinat tidak sesuai. contoh yang sesuai : -6.89041,109.620956';
                            $text .= 'Silahkan perbaiki dan kirim ulang.';
                        } elseif ($qrodp == '-') {
                            $text = "ODP harus ada QR-Code ODP! Lengkapi terlebih dahulu, kemudian kirim ulang.";
                        } elseif (empty($portdc)) {
                            $text = "Laporkan Dropcore yang divalidasi!";
                        } elseif (empty($qrdc)) {
                            $text = "Laporkan QR-Code dropcore yang divalidasi!";
                        } else {
                            // data lengkap
                            $cekodpvaldat         = mysqli_query($koneksi, "SELECT request_odp FROM tb_request WHERE request_odp = '$odp' ORDER BY request_id DESC LIMIT 1");
                            $ceklaporanvaldat     = mysqli_query($koneksi, "SELECT request_odp,barcode_status FROM tb_request WHERE request_odp = '$odp' AND barcode_status = 0 ORDER BY request_id DESC LIMIT 1");
                            if (mysqli_num_rows($cekodpvaldat) > 0) {
                                //odp ditemukan
                                if (mysqli_num_rows($ceklaporanvaldat) > 0) {
                                    // cek apakah sudah ada yang menggunakan?
                                    $cekqrused = mysqli_query($koneksi, "SELECT id_odp,nama_odp,barcode_odp FROM tb_odp WHERE barcode_odp = '$qrodp' AND nama_odp <> '$odp' ORDER BY id_odp DESC LIMIT 1");
                                    if (mysqli_num_rows($cekqrused) <= 0) {
                                        // barcode odp belum digunakan untuk ODP manapun.
                                        // odp belum dilaporkan
                                        // cek dropcore yang dilaporkan, sesuai atau tidak.
                                        $cekodp     = mysqli_query($koneksi, "SELECT id_odp,nama_odp FROM tb_odp WHERE nama_odp = '$odp' LIMIT 1");
                                        while ($d = mysqli_fetch_array($cekodp)) {
                                            $odp_id = $d['id_odp'];
                                        }
                                        $cekportodp = mysqli_query($koneksi, "SELECT count(port_no) as total_dc FROM tb_validasi WHERE id_odp = '$odp_id'");
                                        while ($d = mysqli_fetch_array($cekportodp)) {
                                            $totalportvaldat = $d['total_dc'];
                                        }

                                        if ($totalportvaldat != $totaldc) {
                                            $text = "Jumlah dropcore yang anda laporkan tidak sesuai dengan dropcore yang anda validasi! Perbaiki laporan dan kirim ulang.";
                                        } else {
                                            $updateodp = mysqli_query($koneksi, "UPDATE tb_odp SET lat_lng='$lokasi', barcode_odp='$qrodp' WHERE id_odp='$odp_id'");
                                            if ($updateodp) {
                                                for ($i = 0; $i < $totalbc; $i++) {
                                                    $err    = 0;
                                                    $portdc = $qrcodedc[1][$i];
                                                    $bcdc   = $qrcodedc[2][$i];
                                                    $cekdcbcodp = mysqli_query($koneksi, "SELECT id_validasi,id_odp,port_no FROM tb_validasi WHERE id_odp = '$odp_id' AND port_no = '$portdc'");
                                                    $cekbcdiodp = mysqli_query($koneksi, "SELECT id_validasi,id_odp,port_no,barcode FROM tb_validasi WHERE id_odp = '$odp_id' AND barcode = '$bcdc'");
                                                    if (mysqli_num_rows($cekdcbcodp) > 0) {
                                                        if (mysqli_num_rows($cekbcdiodp) > 0) {
                                                            while ($d = mysqli_fetch_array($cekbcdiodp)) {
                                                                $portexsodp = $d['port_no'];
                                                            }

                                                            $setnullbceks = mysqli_query($koneksi, "UPDATE tb_validasi SET barcode='-', last_edit='$datenow' WHERE id_odp='$odp_id' AND port_no = '$portexsodp'");
                                                            if ($setnullbceks) {
                                                                $updatebcport = mysqli_query($koneksi, "UPDATE tb_validasi SET barcode='$bcdc', last_edit='$datenow' WHERE id_odp='$odp_id' AND port_no = '$portdc'");
                                                            }
                                                        } else {
                                                            $updatebcport = mysqli_query($koneksi, "UPDATE tb_validasi SET barcode='$bcdc', last_edit='$datenow' WHERE id_odp='$odp_id' AND port_no = '$portdc'");
                                                        }
                                                    } else {
                                                        $text = "Port : $portdc , ODP : $odp belum di validasi!";
                                                        $err++;
                                                    }
                                                }
                                                if ($err == 0) {
                                                    // jika tidak ada error saat update port
                                                    $updatestatusvalidasi = mysqli_query($koneksi, "UPDATE tb_request SET barcode_status=1, reported_by = '$fromname' WHERE request_odp = '$odp' ORDER BY request_id DESC LIMIT 1");
                                                    if ($updatestatusvalidasi) {
                                                        $text = "Well Done! Laporan validasi ODP : $odp diterima.";
                                                    } else {
                                                        $text = "Error on update request!";
                                                    }
                                                }
                                            } else {
                                                $text = "Error on updated ODP!";
                                            }
                                        }
                                    } else {
                                        while ($d = mysqli_fetch_array($cekqrused)) {
                                            $odpusedqr = $d['nama_odp'];
                                        }
                                        $text = "QR Code ODP : $qrodp sudah digunakan untuk $odpusedqr. Silahkan cek lagi, dan kirim ulang laporan!";
                                    }
                                } else {
                                    $text = "Laporan validasi untuk ODP : $odp sudah diterima. Terima Kasih";
                                }
                            } else {
                                $text = "Tidak ada permintaan validasi untuk ODP : $odp";
                            }
                        }
                        sendApiMsg($chatid, $text, $meid);
                        break;

                    case preg_match("/\/validasi (.*)/", $pesan, $hasil):
                        sendApiAction($chatid);
                        $text = "Silahkan validasi menggunakan bot valins.";
                        // $query = mysqli_query($koneksi,"SELECT request_from_id,request_odp FROM tb_request WHERE request_from_id = '$fromid' AND barcode_status = 0 AND request_status = 3");
                        // if (mysqli_num_rows($query)>0){
                        //     while($d = mysqli_fetch_array($query)){
                        //         $text = "Halo $fromname, selesaikan dulu laporan QR-Code untuk :\n";
                        //         $no = 1;
                        //         foreach ($query as $row) {
                        //             $text .= "$no. $row[request_odp]\n";
                        //             $no++;
                        //         }
                        //         $text .= "\nSilahkan laporan dengan menggunakan format /formatlaporvalidasi";
                        //     }
                        // }
                        // else{
                        //     if(date("Hi") > "0100" && date("Hi") < "0800" ) {
                        //         $text = "start jam 8 ya..";
                        //     }
                        //     elseif(date("Hi") >= "1655") {
                        //         $text = "lanjut besok lagi ya :)";
                        //     }
                        //     else{
                        //         $pesan      = strtoupper($message['text']);
                        //         $olah       = str_replace("/VALIDASI ", "", $pesan);
                        //         $string     = trim(preg_replace('/\s+/', ' ', $olah));
                        //         $pecah      = explode(" ", $string);
                        //         $odp        = $pecah[0];
                        //         $call       = $pecah[1];
                        //         $sholat     = file_get_contents("https://api.banghasan.com/sholat/format/json/jadwal/kota/723/tanggal/".date('Y-m-d')."");
                        //         $json       = json_decode($sholat, TRUE);
                        //         $ashar    =  str_replace(':', '', $json['jadwal']['data']['ashar']);
                        //         $dzuhur   =  str_replace(':', '', $json['jadwal']['data']['dzuhur']);
                        //         $sto        = array('BRB','BKA','BMU','KTM','TTL','BTG','BDY','SBA','PKL','KJE','KDW','PML','CMA','RDD','SLW','ADW','BLU','MGN','TEG');
                        //         if ($odp == '') {
                        //             $text = 'Gunakan format /validasi diikuti dengan nama odp yang mau divalidasi, kemudian enter, dan tulis no telp aktif nya untuk dihubungi.';
                        //         }
                        //         elseif ($call == '') {
                        //             $text = 'No telp nya dilengkapi. Silahkan kirim ulang dengan no telp aktif nya.';
                        //         }
                        //         else{
                        //             $tanda1   = array('-');
                        //             $tanda2   = array('/');
                        //             $post_on  = date('Y-m-d H:i:s');
                        //             $m_id     = $message["message_id"];

                        //             if (strlen($odp) < 14 || strlen($odp) > 15) {
                        //                 $text = "ODP Tidak valid. Perbaiki penulisan ODP";
                        //             }
                        //             else{
                        //                 $query = mysqli_query($koneksi,"INSERT INTO tb_request (request_odp, request_call, request_by, request_from_id, request_on, r_m_id) VALUES ('$odp', '$call', '$fromname', '$fromid', '$post_on', '$m_id')");
                        //                 if ($query) {
                        //                     $antri = mysqli_query($koneksi,"SELECT * FROM tb_request WHERE request_status != 3 AND request_on >= CURDATE() ORDER BY request_id ASC");
                        //                     if (mysqli_num_rows($antri)>0){
                        //                         if(date("Hi") > "$dzuhur" && date("Hi") < "1300" && date("N") == 5) {
                        //                             $text = "waktunya sholat jum'at dan istirahat.. lanjut setelah istirahat";
                        //                         }
                        //                         elseif(date("Hi") >= "$dzuhur" && date("Hi") < "1300") {
                        //                             $text = "waktunya sholat dhuhur dan istirahat.. lanjut setelah istirahat \n";
                        //                             //$text = "Maaf hd sedang tidak ditempat..";
                        //                         }
                        //                         else if(date("Hi") >= "$ashar" && date("Hi") < "1540" ) {
                        //                             $text = "📢 sudah masuk waktu ashar.. yuk sholat dulu ya 🕌";
                        //                             //$text = "Maaf hd sedang tidak ditempat..";
                        //                         }
                        //                         else{
                        //                             //$text = "Mohon maaf ondesk sedang tidak ditempat. Silahkan coba beberapa saat lagi..";
                        //                             while($d = mysqli_fetch_array($antri)){
                        //                                 $text = "Urutan Validasi :\n";
                        //                                 $no = 1;
                        //                                 foreach ($antri as $row) {
                        //                                     if ($row['request_status'] == 1) {
                        //                                         $statusr = 'WAIT';
                        //                                     }
                        //                                     else if ($row['request_status'] == 2) {
                        //                                         $statusr = 'ON CALL';
                        //                                     }
                        //                                     else if ($row['request_status'] == 4) {
                        //                                         $statusr = 'KENDALA';
                        //                                     }
                        //                                     $text .= "$no. $row[request_by] $row[request_odp] $statusr\n\n";
                        //                                     $no++;
                        //                                 }
                        //                                 $text .= "Silahkan ditunggu ya";
                        //                                 sendApiMsg(232335772, $text); //nuril
                        //                                 sendApiMsg(227983573, $text); //ardian
                        //                                 sendApiMsg(221536902, $text); //bobby
                        //                                 sendApiMsg(233116801, $text); //hakim
                        //                             }
                        //                         }
                        //                     }
                        //                     else{
                        //                         $text  = "OK. Posisi Antrian : 1 ";
                        //                     }
                        //                 }
                        //                 else{
                        //                     $text  = "Maaf Ada Kesalahan!";
                        //                 }
                        //             }
                        //         }
                        //     }
                        // }

                        sendApiMsg($chatid, $text, $meid);
                        break;

                    case preg_match("/\/bc (.*)/", $pesan, $hasil):
                        sendApiAction($chatid);
                        $pesan = strtoupper($message['text']);
                        $barcode = str_replace("/BC ", "", $pesan);

                        $query1 = mysqli_query($koneksi, "SELECT nama_odp, barcode_odp FROM tb_odp WHERE barcode_odp = '$barcode'");
                        $query2 = mysqli_query($koneksi, "SELECT 
                                v.port_no,
                                v.onu_id,
                                v.barcode,
                                v.cli_id,
                                o.nama_odp,
                                o.teknisi,
                                o.barcode_odp,
                                o.olt,
                                o.slot,
                                o.port,
                                o.created_at,
                                o.last_edit,
                                v.onu_sn,
                                v.sip_username,
                                v.hsi_username
                            FROM tb_odp o
                            LEFT JOIN tb_validasi v ON v.id_odp = o.id_odp
                            WHERE v.barcode = '$barcode'");

                        if (mysqli_num_rows($query1) > 0) {
                            while ($d = mysqli_fetch_array($query1)) {
                                $text = "(STATUS : BARCODE ODP)\n";
                                $text .= "$d[nama_odp] \n";
                                $text .= "$d[barcode_odp] \n";
                            }
                        } else {
                            if (mysqli_num_rows($query2) > 0) {
                                while ($d = mysqli_fetch_array($query2)) {
                                    $text = "$d[nama_odp] \n";
                                    $text .= "Olt : $d[cli_id] \n";
                                    $text .= "Port No : $d[port_no] \n";
                                    $text .= "Onu ID : $d[onu_id] \n";
                                    $text .= "Barcode : $d[barcode] \n";
                                    $text .= "Internet : $d[hsi_username] \n";
                                    $text .= "Voice : $d[sip_username]";
                                }
                            } else {
                                $text = "Maaf Barcode $barcode tidak ditemukan.. \n";
                            }
                        }
                        sendApiMsg($chatid, $text, $meid);
                        break;

                    case preg_match("/\/info (.*)/", $pesan, $hasil):
                        sendApiAction($chatid);
                        $pesan = strtoupper($message['text']);
                        $odp   = str_replace("/INFO ", "", $pesan);
                        $query = mysqli_query($koneksi, "SELECT n.*, o.nama_odp, o.created_at, o.last_edit, o.barcode_odp, o.teknisi, o.olt, o.slot, o.port
                                    FROM tb_validasi as n
                                    JOIN tb_odp AS o ON o.id_odp = n.id_odp
                                    WHERE o.nama_odp LIKE '$odp'
                                    ORDER BY o.id_odp DESC, n.port_no ASC");
                        if (mysqli_num_rows($query) > 0) {
                            while ($d = mysqli_fetch_array($query)) {
                                $text = "$d[nama_odp]\n";
                                $text .= "Barcode ODP  : $d[barcode_odp] \n";
                                $text .= "OLT : $d[olt]:$d[slot]/$d[port] \n";
                                $text .= "Teknisi  : $d[teknisi] \n";
                                $text .= "Tgl Validasi : " . tgl_indo($d['created_at']) . " \n";
                                $text .= "Diperbarui : " . tgl_indo($d['last_edit']) . " \n\n";
                            }
                            foreach ($query as $row) {
                                if ($row['hsi_username'] != '-' && $row['hsi_username'] != 'N') {
                                    $no_service = $row['hsi_username'];
                                } elseif ($row['sip_username'] != '-' && $row['sip_username'] != 'N') {
                                    $no_service = $row['sip_username'];
                                } elseif ($row['onu_desc'] != '-' && $row['onu_desc'] != 'N') {
                                    $no_service = $row['onu_desc'];
                                } else {
                                    $no_service = 'N/A';
                                }
                                $text .= "$row[port_no]. $no_service $row[barcode]\n";
                            }
                        } else {
                            $text = "Maaf $odp tidak ditemukan.";
                        }
                        sendApiMsg($chatid, $text, $meid);
                        break;
                    
                    case $pesan == '/help':
                    case $pesan == '/help@AsistenDava_bot':
                        sendApiAction($chatid);
                        $text = "Halo $fromname 👋🏻, kenalin saya asma asisten nya tim DAMAN, saya ditugaskan untuk membantu pekerjaan validasi teman-teman.\n\n";
                        $text .= "📖 Berikut yang bisa saya lakukan buat kamu, \n\n";
                        $text .= "✅ /info [nama_odp] untuk cari informasi odp \n";
                        $text .= "✅ /bc [barcode] untuk cari barcode\n";
                        $text .= "✅ /ns [no_service] untuk cari no inet/voice\n";
                        $text .= "✅ /help untuk melihat informasi bantuan \n\n";
                        $text .= "✅ /infovalins untuk melihat informasi ODP hasil valins\n";
                        $text .= 'Terima Kasih 😎';

                        sendApiMsg($chatid, $text, false, 'Markdown');
                        break;

                    case $pesan == '/formatlaporvalidasi':
                    case $pesan == '/formatlaporvalidasi@AsistenDava_bot':
                        sendApiAction($chatid);
                        $text = "/laporvaldat data\n";
                        $text .= "Nama ODP : ODP-STO-AB/001\n";
                        $text .= "Lokasi : latitide,longitude\n";
                        $text .= "QR-Code ODP : TQO06IW60W5Q\n\n";
                        $text .= "QR-Code Dropcore\n";
                        $text .= "1. TQO0VE5AFHRO\n";
                        $text .= "2. TQO0ATI5HMGE\n";
                        $text .= "3. TQO0AJXE6IW0\n";
                        $text .= "4. TQO076G1GJTX\n";
                        $text .= "5. TQO0WI4KBUEO\n";
                        $text .= "6. TQO0VE5AFHRO\n";
                        $text .= "7. TQO0X7P11AZR\n";
                        $text .= "8. TQO07D4402VR";

                        sendApiMsg($chatid, $text, false, 'Markdown');
                        break;

                    case preg_match("/\/ns (.*)/", $pesan, $hasil):
                        sendApiAction($chatid);
                        $pesan = strtoupper($message['text']);
                        $service = str_replace("/NS ", "", $pesan);
                        $query1 = mysqli_query($koneksi, "SELECT 
                                    v.port_no,
                                    v.onu_id,
                                    v.barcode,
                                    v.cli_id,
                                    o.nama_odp,
                                    o.teknisi,
                                    o.barcode_odp,
                                    o.olt,
                                    o.slot,
                                    o.port,
                                    o.created_at,
                                    v.last_edit,
                                    v.onu_sn,
                                    v.onu_desc,
                                    v.sip_username,
                                    v.hsi_username
                                    FROM tb_odp o
                                    LEFT JOIN tb_validasi v ON v.id_odp = o.id_odp
                                    WHERE v.sip_username LIKE '%$service%'");
                        $query2 = mysqli_query($koneksi, "SELECT 
                                    v.port_no,
                                    v.onu_id,
                                    v.barcode,
                                    v.cli_id,
                                    o.nama_odp,
                                    o.teknisi,
                                    o.barcode_odp,
                                    o.olt,
                                    o.slot,
                                    o.port,
                                    o.created_at,
                                    v.last_edit,
                                    v.onu_sn,
                                    v.onu_desc,
                                    v.sip_username,
                                    v.hsi_username
                                    FROM tb_odp o
                                    LEFT JOIN tb_validasi v ON v.id_odp = o.id_odp
                                    WHERE v.hsi_username LIKE '%$service%'");
                        if (mysqli_num_rows($query1) > 0) {
                            while ($d = mysqli_fetch_array($query1)) {
                                $text = "$d[nama_odp] \n";
                                $text .= "Olt : $d[cli_id] \n";
                                $text .= "Port No : $d[port_no] \n";
                                $text .= "Onu ID : $d[onu_id] \n";
                                $text .= "Barcode : $d[barcode] \n";
                                $text .= "Internet : $d[hsi_username] \n";
                                $text .= "Voice : $d[sip_username] \n";
                                $text .= "Desc : $d[onu_desc]";
                            }
                        } else {
                            if (mysqli_num_rows($query2) > 0) {
                                while ($d = mysqli_fetch_array($query2)) {
                                    $text = "$d[nama_odp] \n";
                                    $text .= "Olt : $d[cli_id] \n";
                                    $text .= "Port No : $d[port_no] \n";
                                    $text .= "Onu ID : $d[onu_id] \n";
                                    $text .= "Barcode : $d[barcode] \n";
                                    $text .= "Internet : $d[hsi_username] \n";
                                    $text .= "Voice : $d[sip_username] \n";
                                    $text .= "Desc : $d[onu_desc]";
                                }
                            } else {
                                $text = "Maaf nomor service $service tidak ditemukan.";
                            }
                        }
                        sendApiMsg($chatid, $text, $meid);
                        break;

                    case preg_match("/\/kerjake (.*)/", $pesan, $hasil):
                        sendApiAction($chatid);
                        $pesan = str_replace("/kerjake ", "", $pesan);
                        preg_match('/Summary ODP\s{0,1}:(.+)/i', $pesan, $odp);
                        preg_match('/IP OLT\s{0,1}:(.+)/i', $pesan, $oltip);
                        preg_match('/Slot\s{0,1}:(.+)/i', $pesan, $slot);
                        preg_match('/Port\s{0,1}:(.+)/i', $pesan, $port);
                        preg_match('/longitude\s{0,1}:(.+)/i', $pesan, $long);
                        preg_match('/latitude\s{0,1}:(.+)/i', $pesan, $lat);
                        preg_match('/QR-Code ODP\s{0,1}\n(.+)/i', $pesan, $qrodp);
                        preg_match_all('/(\d+). (TQ.*)/', $pesan, $qrcodedc);

                        $odp        = (trim($odp[1]) != '' ? trim($odp[1]) : '-');
                        $odp        = strlen($odp) < 14 ? str_replace('/', '/0', $odp) : $odp;
                        $oltip      = (trim($oltip[1]) != '' ? trim($oltip[1]) : '-');
                        $slot       = (trim($slot[1]) != '' ? trim($slot[1]) : '-');
                        $port       = (trim($port[1]) != '' ? trim($port[1]) : '-');
                        $long       = (trim($long[1]) != '' ? trim($long[1]) : '-');
                        $lat        = (trim($lat[1]) != '' ? trim($lat[1]) : '-');
                        $longlat    = $lat . ',' . $long;
                        $datenow    = date('Y-m-d');
                        $qrodp      = (trim($qrodp[1]) != '' ? trim($qrodp[1]) : '-');
                        $portdc     = $qrcodedc[1];
                        $qrdc       = $qrcodedc[2];
                        $lanjut     = 'UIM Tools aja';
                        $cekodp     = mysqli_query($koneksi, "SELECT id_odp,nama_odp FROM tb_odp WHERE nama_odp = '$odp' LIMIT 1");
                        if ($qrodp == '-') {
                            if (mysqli_num_rows($cekodp) > 0) {
                                while ($d = mysqli_fetch_array($cekodp)) {
                                    $odp_id = $d['id_odp'];
                                }
                                $cekipolt = mysqli_query($koneksi, "SELECT olt_ip, olt_name FROM tb_olt_pkl WHERE olt_ip = '$oltip'");
                                if (mysqli_num_rows($cekipolt) > 0) {
                                    while ($d = mysqli_fetch_array($cekipolt)) {
                                        $oltname = $d['olt_name'];
                                    }
                                    $updateodp = mysqli_query($koneksi, "UPDATE tb_odp SET olt='$oltname', slot='$slot', port='$port', barcode_odp='-', teknisi='VALINS', lat_lng='$longlat', last_edit='$datenow', updated_by=10 WHERE id_odp='$odp_id'");
                                    if ($updateodp) {
                                        $text = "[ODP KOSONG] Well Done, $odp successfully updated!";
                                    }
                                } else {
                                    $text = "Tidak dapat menemukan OLT IP : $oltip.";
                                }
                            } else {
                                $text = "Tidak dapat menemukan ODP : $odp.";
                            }
                        } else {
                            if (mysqli_num_rows($cekodp) > 0) {
                                while ($d = mysqli_fetch_array($cekodp)) {
                                    $odp_id = $d['id_odp'];
                                }
                                // action update
                                $cekipolt = mysqli_query($koneksi, "SELECT olt_ip, olt_name FROM tb_olt_pkl WHERE olt_ip = '$oltip'");
                                if (mysqli_num_rows($cekipolt) > 0) {
                                    while ($d = mysqli_fetch_array($cekipolt)) {
                                        $oltname = $d['olt_name'];
                                    }
                                    // update data ODP
                                    $updateodp = mysqli_query($koneksi, "UPDATE tb_odp SET olt='$oltname', slot='$slot', port='$port', barcode_odp='$qrodp', teknisi='VALINS', lat_lng='$longlat', last_edit='$datenow', updated_by=10 WHERE id_odp='$odp_id'");
                                    if ($updateodp) {
                                        // update data PORT
                                        $text1 = explode("\n", $pesan);
                                        foreach ($text1 as $part) {
                                            $text2 = explode("|", $part);
                                            $result[array_shift($text2)] = $text2;
                                        }
                                        //$dataport = array();
                                        $isi = array_filter($result);
                                        $totalarr = count($isi) - 8;
                                        for ($i = 1; $i <= $totalarr; $i++) {
                                            $array_keys = array_keys($isi);
                                            $portno  = $array_keys[$i];
                                            $onu_id  = $isi[$portno][0];
                                            $onu_sn  = $isi[$portno][1];
                                            $sip_1   = $isi[$portno][2];
                                            $sip_2   = $isi[$portno][3];
                                            $no_hsi  = $isi[$portno][4];
                                            $cli_id  = $oltname . ':' . $slot . '/' . $port . '/' . $onu_id;
                                            $odp_slot_port = $oltname . ':' . $slot . '/' . $port;

                                            $cekportodp = mysqli_query($koneksi, "SELECT id_validasi,id_odp,port_no,onu_id FROM tb_validasi WHERE id_odp = '$odp_id' AND port_no = '$portno'");
                                            $cekonuidodp = mysqli_query($koneksi, "SELECT id_validasi,id_odp,port_no,onu_id FROM tb_validasi WHERE id_odp = '$odp_id' AND onu_id = '$onu_id'");
                                            if (mysqli_num_rows($cekportodp) > 0) {
                                                if (mysqli_num_rows($cekonuidodp) > 0) {
                                                    while ($d = mysqli_fetch_array($cekonuidodp)) {
                                                        $portexsodp = $d['port_no'];
                                                    }
                                                    // set null dulu onu id sebelumnya
                                                    $setnullonuidex = mysqli_query($koneksi, "UPDATE tb_validasi SET onu_id='unidentified' WHERE id_odp='$odp_id' AND port_no = '$portexsodp'");
                                                    if ($setnullonuidex) {
                                                        //update port
                                                        $updateport = mysqli_query($koneksi, "UPDATE tb_validasi SET port_no='$portno', onu_id='$onu_id', odp_slot_port='$odp_slot_port', onu_sn='$onu_sn', onu_desc='-', sip_username='$sip1', hsi_username='$no_hsi', cli_id='$cli_id', last_edit='$datenow', updated_by=10 WHERE id_odp='$odp_id' AND port_no = '$portno'");
                                                    }
                                                } else {
                                                    $updateport = mysqli_query($koneksi, "UPDATE tb_validasi SET port_no='$portno', onu_id='$onu_id', odp_slot_port='$odp_slot_port', onu_sn='$onu_sn', onu_desc='-', sip_username='$sip1', hsi_username='$no_hsi', cli_id='$cli_id', last_edit='$datenow', updated_by=10 WHERE id_odp='$odp_id' AND port_no = '$portno'");
                                                }
                                            } else {
                                                if (mysqli_num_rows($cekonuidodp) > 0) {
                                                    while ($d = mysqli_fetch_array($cekonuidodp)) {
                                                        $portexsodp = $d['port_no'];
                                                    }
                                                    // set null dulu onu id sebelumnya
                                                    $setnullonuidex = mysqli_query($koneksi, "UPDATE tb_validasi SET onu_id='unidentified' WHERE id_odp='$odp_id' AND port_no = '$portexsodp'");
                                                    if ($setnullonuidex) {
                                                        //insert new port
                                                        $insertnewport = mysqli_query($koneksi, "INSERT INTO tb_validasi (id_validasi, id_odp, port_no, onu_id, odp_slot_port, cli_id, created_at, last_edit, created_by, updated_by, onu_sn, onu_desc, sip_username, hsi_username) VALUES (NULL, '$odp_id', '$portno', '$onu_id', '$odp_slot_port', '$cli_id', '$datenow', '$datenow', '10', '10', '$onu_sn', NULL, '$sip_1', '$no_hsi')");
                                                    }
                                                } else {
                                                    //insert new port
                                                    $insertnewport = mysqli_query($koneksi, "INSERT INTO tb_validasi (id_validasi, id_odp, port_no, onu_id, odp_slot_port, cli_id, created_at, last_edit, created_by, updated_by, onu_sn, onu_desc, sip_username, hsi_username) VALUES (NULL, '$odp_id', '$portno', '$onu_id', '$odp_slot_port', '$cli_id', '$datenow', '$datenow', '10', '10', '$onu_sn', '-', '$sip_1', '$no_hsi')");
                                                }
                                            }
                                        }

                                        $totaldc = count($qrcodedc[1]);
                                        $totalbc = count($qrcodedc[2]);
                                        for ($i = 0; $i < $totalbc; $i++) {
                                            $portdc = $qrcodedc[1][$i];
                                            $bcdc   = $qrcodedc[2][$i];
                                            $cli_id_un  = $oltname . ':' . $slot . '/' . $port . '/unidentified';
                                            $cekdcbcodp = mysqli_query($koneksi, "SELECT id_validasi,id_odp,port_no FROM tb_validasi WHERE id_odp = '$odp_id' AND port_no = '$portdc'");
                                            $cekbcdiodp = mysqli_query($koneksi, "SELECT id_validasi,id_odp,port_no,barcode FROM tb_validasi WHERE id_odp = '$odp_id' AND barcode = '$bcdc'");
                                            if (mysqli_num_rows($cekdcbcodp) > 0) {
                                                if (mysqli_num_rows($cekbcdiodp) > 0) {
                                                    while ($d = mysqli_fetch_array($cekbcdiodp)) {
                                                        $portexsodp = $d['port_no'];
                                                    }

                                                    $setnullbceks = mysqli_query($koneksi, "UPDATE tb_validasi SET barcode='-', last_edit='$datenow' WHERE id_odp='$odp_id' AND port_no = '$portexsodp'");
                                                    if ($setnullbceks) {
                                                        $updatebcport = mysqli_query($koneksi, "UPDATE tb_validasi SET barcode='$bcdc', last_edit='$datenow' WHERE id_odp='$odp_id' AND port_no = '$portdc'");
                                                    }
                                                } else {
                                                    $updatebcport = mysqli_query($koneksi, "UPDATE tb_validasi SET barcode='$bcdc', last_edit='$datenow' WHERE id_odp='$odp_id' AND port_no = '$portdc'");
                                                }
                                            } else {
                                                if (mysqli_num_rows($cekbcdiodp) > 0) {
                                                    while ($d = mysqli_fetch_array($cekbcdiodp)) {
                                                        $portexsodp = $d['port_no'];
                                                    }
                                                    $setnullbceks = mysqli_query($koneksi, "UPDATE tb_validasi SET barcode='-', last_edit='$datenow' WHERE id_odp='$odp_id' AND port_no = '$portexsodp'");
                                                    if ($setnullbceks) {
                                                        $insertnewport = mysqli_query($koneksi, "INSERT INTO tb_validasi (id_validasi, id_odp, port_no, onu_id, odp_slot_port, barcode, cli_id, created_at, last_edit, created_by, updated_by, onu_sn, onu_desc, sip_username, hsi_username) VALUES (NULL, '$odp_id', '$portdc', 'unidentified', '$odp_slot_port', '$bcdc' , '$cli_id_un', '$datenow', '$datenow', '10', '10', '-', NULL, '-', '-')");
                                                    }
                                                } else {
                                                    //insert barcode new port
                                                    $insertnewport = mysqli_query($koneksi, "INSERT INTO tb_validasi (id_validasi, id_odp, port_no, onu_id, odp_slot_port, barcode, cli_id, created_at, last_edit, created_by, updated_by, onu_sn, onu_desc, sip_username, hsi_username) VALUES (NULL, '$odp_id', '$portdc', 'unidentified', '$odp_slot_port', '$bcdc' , '$cli_id_un', '$datenow', '$datenow', '10', '10', '-', NULL, '-', '-')");
                                                }
                                            }
                                        }

                                        $text = "✍️ oke mas $fromname, $odp aku update yaa.. Lanjut $lanjut";
                                    } else {
                                        $text = "Tidak dapat mengupdate ODP : $odp.";
                                    }
                                } else {
                                    $text = "Tidak dapat menemukan OLT IP : $oltip.";
                                }
                            } else {
                                // report tidak menemukan odp
                                $text = "Tidak dapat menemukan ODP : $odp.";
                            }
                        }


                        sendApiMsg($chatid, $text, $meid);
                        break;
                    */
                case preg_match("/\/ceklive (.*)/", $pesan, $hasil):
                case preg_match("/\/Ceklive (.*)/", $pesan, $hasil):
                    $pesan  = strtoupper($message['text']);
                    $odp    = str_replace("/CEKLIVE ", "", $pesan);
                    $query1 = mysqli_query($koneksi, "SELECT * FROM tb_odp_uim WHERE odp_location = '$odp'");
                    if (mysqli_num_rows($query1) > 0) {
                        while ($d = mysqli_fetch_array($query1)) {
                            $text = "🟢 STATUS : LIVE\n";
                            $text .= "ODP : $d[odp_location] \n";
                            $text .= "Kap : $d[total] (Isi $d[isi] Kosong $d[kosong]) \n";
                            $text .= "Lokasi : \nhttps://www.google.co.id/maps/search/$d[latitude],$d[longitude] \n";
                        }
                    } else {
                        $pecah_odp = explode("/", $odp);
                        $digit = strlen($pecah_odp[1]);
                        if ($digit >= 3) {
                            $belakang = substr($pecah_odp[1], 1);
                            $suggest_odp = $pecah_odp[0] . '/' . $belakang;
                        } else {
                            $belakang = substr_replace($pecah_odp[1], '0', 1, 0);
                            $suggest_odp = $pecah_odp[0] . '/' . $belakang;
                        }
                        $query2 = mysqli_query($koneksi, "SELECT * FROM tb_odp_uim WHERE odp_location = '$suggest_odp'");
                        if (mysqli_num_rows($query2) > 0) {
                            while ($z = mysqli_fetch_array($query2)) {
                                $text = "🟢 STATUS : LIVE\n";
                                $text .= "ODP : $z[odp_location] \n";
                                $text .= "Kap : $z[total] (Isi $z[isi] Kosong $z[kosong]) \n";
                                $text .= "Lokasi : \nhttps://www.google.co.id/maps/search/$z[latitude],$z[longitude] \n";
                            }
                        } else {
                            $text = "🔴 STATUS : BLM LIVE\n";
                            $text .= "ODP : $odp\n\n";
                        }
                    }
                    sendApiMsg($chatid, $text, $meid);
                    break;

                default:

                    break;
            }
        }
    }
}
