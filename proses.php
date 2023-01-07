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
    include '../config/koneksi.php';

    $pesan          = $message['text'];
    $chatid         = $message['chat']['id'];
    $fromid         = $message['from']['id'];
    $fromfname      = $message['from']['first_name'];
    $fromname       = $message['from']['username'];
    $telegramname   = is_null($fromfname) ? $fromname : $fromfname;
    $grupname       = !empty($message["chat"]["title"]) ? $message["chat"]["title"] : 'null';
    $str            = !empty($message["reply_to_message"]["text"]) ? strtok($message["reply_to_message"]["text"], "\n") : 'NOTHING';
    $meid           = $message["message_id"];
    $type_ms        = $message["chat"]["type"];
    $time           = date('Y-m-d H:i:s');
    $masuk_switch_r = false;
    $masuk_switch_m = true;

    if (isset($message['reply_to_message']['text'])) {
        $masuk_switch_r = true;
        $masuk_switch_m = false;
    }

    if ($masuk_switch_r) {
        $reply      = $message["reply_to_message"]["text"];
        $reqsc      = array();

        switch (true) {
            case $reply == 'Silahkan tulis siapa nama kamu?':

                $query = mysqli_query($koneksi, "INSERT INTO tb_helpdesk (h_telegram_id, nama_hd) VALUES ($fromid, '$pesan')");

                sendApiAction($chatid);

                if ($query) {
                    $text = "Halo $pesan 👋🏻, pendaftaran berhasil dilakukan, kamu akan diberitahu kembali jika akun ini sudah diapprove oleh admin :)";
                } else {
                    $text = "❗️ Maaf pendaftaran gagal dilakukan. Silahkan coba beberapa saat lagi..";
                }

                sendApiMsg($chatid, $text);
                break;

            case $str   == '!create data':
                if ($pesan == 'wait') {
                    sendApiAction($chatid);

                    $replymid   = $message["reply_to_message"]["message_id"];
                    $replygid   = $message["reply_to_message"]["chat"]["id"];
                    $hd         = $message["from"]["username"];
                    $tgl_up     = date('Y-m-d H:i:s');
                    $cekhd      = mysqli_query($koneksi, "SELECT status_id FROM tb_provisioning WHERE message_id=$replymid AND group_id=$replygid AND status_id >= 2");
                    $cekdata    = mysqli_query($koneksi, "SELECT status_id,sc_baru FROM tb_provisioning WHERE message_id=$replymid AND group_id=$replygid");
                    $cekuh      = mysqli_query($koneksi, "SELECT h_telegram_id FROM tb_helpdesk WHERE h_telegram_id = '$fromid'");
                    $cekha      = mysqli_query($koneksi, "SELECT h_telegram_id,active FROM tb_helpdesk WHERE h_telegram_id = '$fromid' AND active = 1");
                    if (mysqli_num_rows($cekuh) > 0) {
                        if (mysqli_num_rows($cekha) > 0) {
                            if (mysqli_num_rows($cekhd) > 0) {
                                $text = "order sudah ada yang progress.. silahkan progress yg lain ya";
                            } else {
                                if (mysqli_num_rows($cekdata) > 0) {
                                    $queryupp = mysqli_query($koneksi, "UPDATE tb_provisioning SET 
                                    hd_penerima='$fromid', 
                                    tgl_update='$tgl_up',
                                    status='progress', status_id=2 WHERE message_id=$replymid AND group_id=$replygid");

                                    while ($z = mysqli_fetch_array($cekdata)) {
                                        $sc_sales = $z['sc_baru'];
                                        $queryupp_sales = mysqli_query($koneksi, "UPDATE tb_sales SET 
                                        tgl_update='$tgl_up',
                                        status='prog act', status_id=71 WHERE new_sc = " . $sc_sales . " ");
                                    }

                                    if ($queryupp) {
                                        $text  = "Order di progress @$hd";
                                    } else {
                                        $text  = "Maaf ada masalah! Gagal Update Data!";
                                    }
                                } else {
                                    $text = "Datanya belum masuk HD 🙈";
                                }
                            }
                        } else {
                            $text = 'Kamu sudah terdaftar sebagai Help Desk, namun belum di approve admin..';
                        }
                    } else {
                        $text = 'Kamu belum terdaftar sebagai HD, silahkan register dengan /registerhd';
                    }
                    sendApiMsg($chatid, $text, $meid);
                } else {
                    /*bersih bersih pesan*/
                    $pesan = strtoupper($message["reply_to_message"]["text"]);
                    $pesan = str_replace('A/N', 'AN', $pesan);
                    $pesan = str_replace('A/N ', 'AN', $pesan);
                    $pesan = str_replace('ID VALINS', 'IDVALINS', $pesan);
                    $pesan = str_replace('NAMA', 'AN', $pesan);
                    $pesan = str_replace('NO.TELP', 'TELP', $pesan);
                    $pesan = str_replace('NO.INET', 'INET', $pesan);
                    $pesan = str_replace('STB.ID', 'STB ID', $pesan);
                    $pesan = str_replace('SPL 1/2', 'SPL 1:2', $pesan);
                    $pesan = str_replace('SPLITER 1/2', 'SPL 1:2', $pesan);
                    $pesan = str_replace('SPLITTER 1/2', 'SPL 1:2', $pesan);
                    $pesan = str_replace('SPLITTER 1:2', 'SPL 1:2', $pesan);
                    $pesan = str_replace('SPL 1/4', 'SPL 1:4', $pesan);
                    $pesan = str_replace('SPLITER 1/4', 'SPL 1:4', $pesan);
                    $pesan = str_replace('SPLITTER 1/4', 'SPL 1:4', $pesan);
                    $pesan = str_replace('SPLITTER 1:4', 'SPL 1:4', $pesan);
                    $pesan = str_replace('SPL 1/8', 'SPL 1:8', $pesan);
                    $pesan = str_replace('SPLITER 1/8', 'SPL 1:8', $pesan);
                    $pesan = str_replace('SPLITTER 1/8', 'SPL 1:8', $pesan);
                    $pesan = str_replace('SPLITTER 1:8', 'SPL 1:8', $pesan);
                    $pesan = str_replace('CASET', 'CASSETE', $pesan);
                    $pesan = str_replace('KASET', 'CASSETE', $pesan);
                    $pesan = str_replace('CASETTE', 'CASSETE', $pesan);
                    $pesan = str_replace('BRIKET', 'BREKET', $pesan);
                    $pesan = str_replace('BRACET', 'BREKET', $pesan);
                    $pesan = str_replace('S CLAM', 'SCLAM', $pesan);
                    $pesan = str_replace('SN ONT', 'SN', $pesan);
                    $pesan = str_replace('STB ID', 'STBID', $pesan);
                    $pesan = str_replace('IP CAM', 'IPCAM', $pesan);
                    $pesan = str_replace('WIFI EXT', 'WIFIEXT', $pesan);
                    $pesan = str_replace('ASS TIANG', 'ASSTIANG', $pesan);

                    /* Data Provi */
                    preg_match('/IDVALINS\s{0,1}:(.+)/i', $pesan, $id_valins);
                    preg_match('/ORDER\s{0,1}:(.+)/i', $pesan, $order);
                    preg_match('/SC\s{0,1}:(.+)/i', $pesan, $sc);
                    preg_match('/AN\s{0,1}:(.+)/i', $pesan, $an);
                    preg_match('/ALAMAT\s{0,1}:(.+)/i', $pesan, $alamat);
                    preg_match('/CP\s{0,1}:(.+)/i', $pesan, $cp);
                    preg_match('/TELP\s{0,1}:(.+)/i', $pesan, $telp);
                    preg_match('/INET\s{0,1}:(.+)/i', $pesan, $inet);
                    preg_match('/ODP\s{0,1}:(.+)/i', $pesan, $odp);
                    preg_match('/PORT\s{0,1}:(.+)/i', $pesan, $port);
                    preg_match('/SISA\s{0,1}:(.+)/i', $pesan, $sisa);
                    preg_match('/SN\s{0,1}:(.+)/i', $pesan, $sn);
                    preg_match('/STBID\s{0,1}:(.+)/i', $pesan, $stb_id);
                    preg_match('/MITRA\s{0,1}:(.+)/i', $pesan, $mitra);
                    preg_match('/TEKNISI\s{0,1}:(.+)/i', $pesan, $teknisi);
                    preg_match('/CREW\s{0,1}:(.+)/i', $pesan, $crew);
                    preg_match('/BC\s{0,1}:(.+)/i', $pesan, $bc);

                    /* Material */
                    preg_match('/DC\s{0,1}:(.+)/i', $pesan, $dc);
                    preg_match('/SCLAM\s{0,1}:(.+)/i', $pesan, $sclam);
                    preg_match('/BREKET\s{0,1}:(.+)/i', $pesan, $breket);
                    preg_match('/T7\s{0,1}:(.+)/i', $pesan, $t7);
                    preg_match('/IPCAM\s{0,1}:(.+)/i', $pesan, $ip_cam);
                    preg_match('/WIFIEXT\s{0,1}:(.+)/i', $pesan, $wifi_ext);
                    preg_match('/ORBIT\s{0,1}:(.+)/i', $pesan, $orbit);
                    preg_match('/SOC\s{0,1}:(.+)/i', $pesan, $soc);
                    preg_match('/PRECON\s{0,1}:(.+)/i', $pesan, $precon);
                    preg_match('/ASSTIANG\s{0,1}:(.+)/i', $pesan, $ass_tiang);
                    preg_match('/ROS\s{0,1}:(.+)/i', $pesan, $ros);
                    preg_match('/SPL 1:2\s{0,1}:(.+)/i', $pesan, $spl12);
                    preg_match('/SPL 1:4\s{0,1}:(.+)/i', $pesan, $spl14);
                    preg_match('/SPL 1:8\s{0,1}:(.+)/i', $pesan, $spl18);
                    preg_match('/CASSETE\s{0,1}:(.+)/i', $pesan, $cassete);
                    preg_match('/ADAPTER\s{0,1}:(.+)/i', $pesan, $adapter);
                    preg_match('/UTP\s{0,1}:(.+)/i', $pesan, $utp);
                    preg_match('/RJ45\s{0,1}:(.+)/i', $pesan, $rj45);
                    preg_match('/REDAMAN\s{0,1}:(.+)/i', $pesan, $redaman);
                    preg_match('/PREKSO\s{0,1}:(.+)/i', $pesan, $prekso);
                    preg_match('/OTP\s{0,1}:(.+)/i', $pesan, $otp);

                    $order      = (trim($order[1]) != '' ? trim($order[1]) : '-');
                    $an         = (trim($an[1]) != '' ? trim($an[1]) : '-');
                    $alamat     = (trim($alamat[1]) != '' ? trim($alamat[1]) : '-');
                    $telp       = (trim($telp[1]) != '' ? trim($telp[1]) : '-');
                    $inet       = (trim($inet[1]) != '' ? trim($inet[1]) : '-');
                    $odp        = (trim($odp[1]) != '' ? trim($odp[1]) : '-');
                    $port       = (trim($port[1]) != '' ? trim($port[1]) : '-');
                    $sisa       = (trim($sisa[1]) != '' ? trim($sisa[1]) : '-');
                    $sn         = (trim($sn[1]) != '' ? trim($sn[1]) : '-');
                    $sc         = intval(preg_replace('/[^0-9]+/', '', trim($sc[1])), 10);
                    $dc         = (trim($dc[1]) != '' ? trim($dc[1]) : '-');
                    $mitra      = (trim($mitra[1]) != '' ? trim($mitra[1]) : '-');
                    $teknisi    = (trim($teknisi[1]) != '' ? trim($teknisi[1]) : '-');
                    $crew       = (trim($crew[1]) != '' ? trim($crew[1]) : '-');
                    $bc         = (trim($bc[1]) != '' ? trim($bc[1]) : '-');
                    $sclam      = !empty($sclam) ? (trim($sclam[1]) != '' ? trim($sclam[1]) : '-') : '-';
                    $breket     = !empty($breket) ? (trim($breket[1]) != '' ? trim($breket[1]) : '-') : '-';
                    $ros        = !empty($ros) ? (trim($ros[1]) != '' ? trim($ros[1]) : '-') : '-';
                    $t7         = !empty($t7) ? (trim($t7[1]) != '' ? trim($t7[1]) : '-') : '-';
                    $cp         = (trim($cp[1]) != '' ? trim($cp[1]) : '-');
                    $stbid      = !empty($stb_id) ? (trim($stb_id[1]) != '' ? trim($stb_id[1]) : '-') : '-';
                    $spl12      = !empty($spl12) ? (trim($spl12[1]) != '' ? trim($spl12[1]) : '-') : '-';
                    $spl14      = !empty($spl14) ? (trim($spl14[1]) != '' ? trim($spl14[1]) : '-') : '-';
                    $spl18      = !empty($spl18) ? (trim($spl18[1]) != '' ? trim($spl18[1]) : '-') : '-';
                    $cassete    = !empty($cassete) ? (trim($cassete[1]) != '' ? trim($cassete[1]) : '-') : '-';
                    $adapter    = !empty($adapter) ? (trim($adapter[1]) != '' ? trim($adapter[1]) : '-') : '-';
                    $utp        = !empty($utp) ? (trim($utp[1]) != '' ? trim($utp[1]) : '-') : '-';
                    $rj45       = !empty($rj45) ? (trim($rj45[1]) != '' ? trim($rj45[1]) : '-') : '-';
                    $redaman    = !empty($redaman) ? (trim($redaman[1]) != '' ? trim($redaman[1]) : '-') : '-';
                    $prekso     = !empty($prekso) ? (trim($prekso[1]) != '' ? trim($prekso[1]) : '-') : '-';
                    $otp        = !empty($otp) ? (trim($otp[1]) != '' ? trim($otp[1]) : '-') : '-';
                    $ip_cam     = !empty($ip_cam) ? (trim($ip_cam[1]) != '' ? trim($ip_cam[1]) : '-') : '-';
                    $wifi_ext   = !empty($wifi_ext) ? (trim($wifi_ext[1]) != '' ? trim($wifi_ext[1]) : '-') : '-';
                    $orbit      = !empty($orbit) ? (trim($orbit[1]) != '' ? trim($orbit[1]) : '-') : '-';
                    $soc        = !empty($soc) ? (trim($soc[1]) != '' ? trim($soc[1]) : '-') : '-';
                    $precon     = !empty($precon) ? (trim($precon[1]) != '' ? trim($precon[1]) : '-') : '-';
                    $ass_tiang  = !empty($ass_tiang) ? (trim($ass_tiang[1]) != '' ? trim($ass_tiang[1]) : '-') : '-';

                    if (isset($message['location']['latitude'])) {
                        sendApiAction($chatid);

                        $latitude   = $message["location"]["latitude"];
                        $longitude  = $message["location"]["longitude"];
                        $lokasiodp  = $latitude . ',' . $longitude;
                        $meid       = $message["message_id"];

                        $replyfromid     = $message["reply_to_message"]['from']['id'];

                        if ($fromid == $replyfromid) {
                            $cekScProvi      = mysqli_query($koneksi, "SELECT sc_baru,status_id FROM tb_provisioning WHERE sc_baru = '$sc'");
                            $cekScTeknisi    = mysqli_query($koneksi, "SELECT sales_id,progress_to FROM tb_sales WHERE new_sc = '$sc'");
                            if (mysqli_num_rows($cekScProvi) > 0) {
                                while ($d = mysqli_fetch_array($cekScTeknisi)) {
                                    $sales_id = $d['sales_id'];
                                    $ambilt = mysqli_query($koneksi, "SELECT nama_teknisi FROM tb_teknisi WHERE t_telegram_id = '$fromid'");
                                    while ($t = mysqli_fetch_array($ambilt)) {
                                        $udatasales = mysqli_query($koneksi, "UPDATE tb_sales SET loc_cust = '$lokasiodp', tgl_update = '$time' WHERE sales_id='$sales_id'");
                                    }
                                }

                                $alert = "SC : $sc\n";
                                $alert .= "Tikor Pelanggan : $lokasiodp\n\n";
                                $alert .= "Oke, tikor pelanggan berhasil diupdate.";
                                sendApiMsg($chatid, $alert, $meid);
                            } else {
                                // variable pelengkap
                                $odp            = str_replace(' ', '', $odp);
                                $tgl            = date('Y-m-d H:i:s');
                                $post_name      = $message["from"]["username"];
                                $m_id           = $message["reply_to_message"]["message_id"];
                                $g_id           = $chatid;
                                $datel          = getDatel($odp);

                                //$cekmitra       = mysqli_query($koneksi, "SELECT nama_mitra FROM tb_mitra WHERE singkat = '$mitra' OR nama_mitra LIKE '%$mitra%'");
                                $cekut          = mysqli_query($koneksi, "SELECT t_telegram_id FROM tb_teknisi WHERE t_telegram_id = '$fromid'");
                                $cekscsales     = mysqli_query($koneksi, "SELECT sales_id,new_sc,message_from,message_id FROM tb_sales WHERE new_sc = '$sc'");
                                $cekScProvi     = mysqli_query($koneksi, "SELECT sc_baru,status_id FROM tb_provisioning WHERE sc_baru = '$sc'");
                                $ceksckendala   = mysqli_query($koneksi, "SELECT new_sc,status_id FROM tb_sales WHERE (new_sc = '$sc') AND (status_id = 6 OR status_id = 12 OR status_id = 13) AND (progress_to IS NULL)");
                                $cekbc          = mysqli_query($koneksi, "SELECT qrcode FROM tb_qrcode WHERE qrcode='" . $bc . "'");
                                $cekbcpakai     = mysqli_query($koneksi, "SELECT barcode FROM tb_provisioning WHERE barcode = '$bc'");

                                $jadwal_sholat  = file_get_contents("https://api.banghasan.com/sholat/format/json/jadwal/kota/723/tanggal/" . date('Y-m-d') . "");
                                $json           = json_decode($jadwal_sholat, TRUE);
                                $tanggal        =  $json['jadwal']['data']['tanggal'];
                                $status_api     =  $json['jadwal']['status'];
                                $ashar          =  str_replace(':', '', $json['jadwal']['data']['ashar']);
                                $dzuhur         =  str_replace(':', '', $json['jadwal']['data']['dzuhur']);
                                $isya           =  str_replace(':', '', $json['jadwal']['data']['isya']);
                                $maghrib        =  str_replace(':', '', $json['jadwal']['data']['maghrib']);
                                $subuh          =  str_replace(':', '', $json['jadwal']['data']['subuh']);

                                /* else if (mysqli_num_rows($cekmitra) <= 0) {
                                        $text = "⚠️ Mitra tidak ditemukan, silahkan perbaiki nama mitra!";
                                    } */
                                if (mysqli_num_rows($cekut) <= 0) {
                                    $text = '❗️ Kamu belum mendaftar sebagai teknisi pada @slo_jarvisid_bot. Silahkan lakukan pendaftaran dengan klik @slo_jarvisid_bot kemudian ketik /regteknisi atau /help untuk infromasi bantuan.';
                                } else if ($order != 'PSB' && $order != 'MIGRASI' && $order != 'MIG' && $order != 'MO') {
                                    $text = "⚠️ Hanya gunakan PSB, MIG/MIGRASI, dan MO untuk isian ORDER!";
                                } else if ((mysqli_num_rows($cekscsales) <= 0) && (strpos($order, 'PSB') !== false)) {
                                    $text = '❌ SC tidak valid! Mohon Cek kembali nomor SC nya, atau silahkan koordinasi dengan inputers.';
                                } else if (mysqli_num_rows($ceksckendala) > 0) {
                                    while ($k = mysqli_fetch_array($ceksckendala)) {
                                        $sc     = $k['new_sc'];
                                        $status = statusSales($k['status_id']);
                                        $text   = "❌ SC$sc tidak ditolak! Status SC $status, atau silahkan koordinasi dengan TL.";
                                    }
                                } else if (mysqli_num_rows($cekScProvi) > 0) {
                                    while ($d = mysqli_fetch_array($cekScProvi)) {
                                        $text = "SC $sc sudah ada, statusnya : " . statusSC($d['status_id']) . "";
                                    }
                                } elseif (strlen($odp) < 14 || strlen($odp) > 15) {
                                    $text = "⚠️ ODP tidak valid! tulis ODP dengan ODP : ODP-PKL-FAA/001";
                                } else if (strpos($odp, '/') == false) {
                                    $text = "tanda / nya mana? misal : ODP-PKL-FAA/001";
                                } else if (cekStoOdp(substr($odp, 4, 3)) == false) {
                                    $text = "⚠️ Pastikan format ODP, STO nya sudah benar!";
                                } else if ((mysqli_num_rows($cekbc) <= 0) && (strpos($order, 'MIG') !== false || strpos($order, 'PSB') !== false || strpos($order, 'MIGRASI') !== false)) {
                                    $text  = "⚠️ Barcode $bc tidak valid, silahkan perbaiki/ganti barcode nya! *lampirkan foto barcode jika barcode sudah sesuai.";
                                } else if ((mysqli_num_rows($cekbcpakai) > 0) && (strpos($order, 'MIG') !== false || strpos($order, 'PSB') !== false || strpos($order, 'MIGRASI') !== false)) {
                                    $text = "⚠️ Barcode $bc sudah digunakan, silahkan ganti yang lain ya..";
                                } else {
                                    $an = str_replace("Â", "", $an);
                                    $query = mysqli_query($koneksi, "INSERT INTO tb_provisioning (datel, order_type, atas_nama, alamat, cp, voice, internet, odp, port, sisa, sn, sc, sc_baru, mitra, teknisi, crew, barcode, stb_id, redaman, post_by, group_id, message_id, tgl_masuk) VALUES ('$datel', '$order', '$an', '$alamat', '$cp', '$telp', '$inet', '$odp', '$port', '$sisa', '$sn', '$sc', '$sc', '$mitra', '$teknisi', '$crew', '$bc', '$stbid', '$redaman', '$fromid', '$g_id','$m_id', '$tgl')");
                                    $idcreate = mysqli_insert_id($koneksi);
                                    if ($query) {
                                        $insmtr = mysqli_query($koneksi, "INSERT INTO tb_material (create_id, dropcore, sclam, breket, ros, t_tujuh, spl1_2, spl1_4, spl1_8, cassete, adapter, utp, rj45, prekso, otp, ip_cam, wifi_ext, orbit, soc, ass_tiang, precon) VALUES ('$idcreate', '$dc', '$sclam', '$breket', '$ros', '$t7', '$spl12', '$spl14', '$spl18', '$cassete', '$adapter', '$utp', '$rj45', '$prekso', '$otp', '$ip_cam', '$wifi_ext', '$orbit', '$soc', '$ass_tiang', '$precon')");
                                        if ($insmtr) {
                                            if ($status_api != 'error') {
                                                if (date("Hi") > "$dzuhur" && date("Hi") < "1235" && date("N") == 5) {
                                                    $text = "📢 ayoo sholatt jum'at dulu 🕌";
                                                } elseif (date("Hi") > "$dzuhur" && date("Hi") < "1220") {
                                                    $text = "📢 sudah masuk waktu dhuhur.. yuk sholat dulu ya 🕌 \n";
                                                } else if (date("Hi") > "$ashar" && date("Hi") < "1530") {
                                                    $text = "📢 sudah masuk waktu ashar.. yuk sholat dulu ya 🕌";
                                                } else if (date("Hi") > "1800") {
                                                    $text = "lanjut besok lagi ya.. langsung pulang kerumah, hindari kerumunan, jangan lupa mandi sebelum berinteraksi dengan keluarga anda dirumah :)";
                                                } else if (date("Hi") > "$maghrib" && date("Hi") < "1820") {
                                                    $text = "📢 sudah masuk waktu maghrib.. yuk sholat dulu ya 🕌";
                                                } else if (date("Hi") > "$isya" && date("Hi") < "2000") {
                                                    $text = "📢 sudah masuk waktu isya.. yuk sholat dulu ya 🕌";
                                                } else {
                                                    $text  = "yuk lanjut rekan HD 🤙";
                                                }
                                            } else {
                                                $text  = "yuk lanjut rekan HD 🤙";
                                            }

                                            while ($d = mysqli_fetch_array($cekscsales)) {
                                                $sales_id = $d['sales_id'];
                                                $s_telegram_id = $d['message_from'];
                                                $s_m_id = $d['message_id'];
                                                $ambilt = mysqli_query($koneksi, "SELECT nama_teknisi FROM tb_teknisi WHERE t_telegram_id = '$fromid'");
                                                while ($t = mysqli_fetch_array($ambilt)) {
                                                    $savelog = mysqli_query($koneksi, "INSERT INTO tb_log (sales_id, action_by, action_on, action_status) VALUES ('$sales_id','$t[nama_teknisi]','$tgl',7)");
                                                    $udatasales = mysqli_query($koneksi, "UPDATE tb_sales SET loc_cust = '$lokasiodp', status = 'wait_act', status_id = '7', tgl_update = '$tgl', tgl_req_act = '$tgl' WHERE sales_id='$sales_id'");
                                                    $pesantosales = "⚠️ Order dimintakan create oleh teknisi $t[nama_teknisi]";
                                                    sendApiMsg($s_telegram_id, $pesantosales, $s_m_id, 'Markdown');
                                                }
                                            }
                                        } else {
                                            $text  = "Error! Data Material Gagal Disimpan!";
                                            $text .= "Error : " . mysqli_error($koneksi);
                                        }
                                    } else {
                                        $text  = "Error! Data Gagal Disimpan! \n";
                                        $text .= "Error : " . mysqli_error($koneksi);
                                    }
                                }

                                $alert = "SC : $sc\n";
                                $alert .= "Tikor Pelanggan : $lokasiodp";
                                sendApiMsg($chatid, $alert, $meid);
                                sendApiMsg($chatid, $text);
                            }
                        } else {
                            $alert = "Maaf, anda bukan teknisi yang mengirim pesan Request Aktivasi SC $sc.";
                            sendApiMsg($chatid, $alert, $meid);
                        }
                    }
                }

                break;

            case $str == 'KENDALA':
                $reply_message  = $message["reply_to_message"]["text"];
                $reply_message  = preg_replace('/^.+\n/', '', $reply_message);
                $sales_id       = str_replace('JA', '', strtok($reply_message, "\n"));
                $kendala        = strtok("\n");
                $tikor1         = strtok("\n");
                $tikor2         = strtok("\n");

                if (strpos($reply, 'keterangan') !== false) {
                    $jenis_kendala  = cekJenisKendala($kendala);
                    $ket_kendala    = $kendala . '-' . $pesan;

                    if ($kendala == 'RNA' || $kendala == 'ALAMAT' || $kendala == 'BATAL' || $kendala == 'PENDING' || $kendala == 'PENDING INSTALASI') {
                        $update = mysqli_query($koneksi, "UPDATE tb_sales SET tgl_update = '$time', tgl_lapor_k = '$time', `status` = '$jenis_kendala', status_id = 6, keterangan='$pesan', kendala = '$kendala' WHERE sales_id = '$sales_id'");
                        $savelog = mysqli_query($koneksi, "INSERT INTO tb_log (sales_id, action_by, action_on, action_status, a_keterangan) VALUES ($sales_id, '$fromname', '$time', 6, '$ket_kendala')");

                        sendApiAction($chatid);

                        if ($update) {
                            // Send to Sales
                            $ceksales = mysqli_query($koneksi, "SELECT message_from, sales_id, message_id FROM tb_sales WHERE sales_id = '$sales_id'");
                            while ($d = mysqli_fetch_array($ceksales)) {
                                $sales_id       = $d['sales_id'];
                                $s_telegram_id  = $d['message_from'];
                                $s_m_id         = $d['message_id'];

                                $pesantosales = "ORDER SET TO KENDALA : ($kendala) \n";
                                $pesantosales .= "JA$sales_id\n";
                                $pesantosales .= "KET :\n";
                                $pesantosales .= "$pesan \n\n";
                                $pesantosales .= " ~ $fromname";

                                sendApiMsg($s_telegram_id, $pesantosales, $s_m_id, 'Markdown');
                            }

                            $text = "Oke kendala berhasil dilaporkan! Terima kasih :)";
                            sendApiMsg($chatid, $text);
                        } else {
                            $text = "❗️ Maaf laporan gagal dilakukan. Silahkan coba beberapa saat lagi.. $sales_id";
                            $text .= "Error : " . mysqli_error($koneksi) . " ";
                            sendApiMsg($chatid, $text);
                        }
                    } else if ($kendala == 'IJIN TANAM TIANG' || $kendala == 'NJKI' || $kendala == 'TIANG' || $kendala == 'RUTE INSTALASI' || $kendala == 'ODP FULL' || $kendala == 'PT2' || $kendala == 'NO FO/ODP') {
                        $tikor_pelanggan = str_replace(" ", "", explode(":", $tikor1));

                        $update = mysqli_query($koneksi, "UPDATE tb_sales SET tgl_update = '$time', tgl_lapor_k = '$time', loc_cust='$tikor_pelanggan[1]', `status` = '$jenis_kendala', status_id = 6, keterangan='$pesan', kendala = '$kendala' WHERE sales_id = '$sales_id'");
                        $savelog = mysqli_query($koneksi, "INSERT INTO tb_log (sales_id, action_by, action_on, action_status, a_keterangan) VALUES ($sales_id, '$fromname', '$time', 6, '$ket_kendala')");

                        sendApiAction($chatid);

                        if ($update) {
                            // Send to Sales
                            $ceksales = mysqli_query($koneksi, "SELECT message_from, sales_id, message_id FROM tb_sales WHERE sales_id = '$sales_id'");
                            while ($d = mysqli_fetch_array($ceksales)) {
                                $sales_id       = $d['sales_id'];
                                $s_telegram_id  = $d['message_from'];
                                $s_m_id         = $d['message_id'];

                                $pesantosales = "ORDER SET TO KENDALA : ($kendala) \n";
                                $pesantosales .= "JA$sales_id\n";
                                $pesantosales .= "KET :\n";
                                $pesantosales .= "$pesan \n\n";
                                $pesantosales .= " ~ $fromname";

                                sendApiMsg($s_telegram_id, $pesantosales, $s_m_id, 'Markdown');
                            }

                            $text = "Oke kendala berhasil dilaporkan! Terima kasih :)";
                            sendApiMsg($chatid, $text);
                        } else {
                            $text = "❗️ Maaf laporan gagal dilakukan. Silahkan coba beberapa saat lagi.. $sales_id";
                            $text .= "Error : " . mysqli_error($koneksi) . " ";
                            sendApiMsg($chatid, $text);
                        }
                    } else {
                        $tikor_pelanggan    = str_replace(" ", "", explode(":", $tikor1));
                        $tikor_odp          = str_replace(" ", "", explode(":", $tikor2));
                        $ket                = "$pesan | Lokasi ODP : $tikor_odp[1]";

                        $update     = mysqli_query($koneksi, "UPDATE tb_sales SET tgl_update = '$time', tgl_lapor_k = '$time', loc_cust='$tikor_pelanggan[1]', `status` = '$jenis_kendala', status_id = 6, keterangan='$ket', kendala = '$kendala' WHERE sales_id = '$sales_id'");
                        $savelog    = mysqli_query($koneksi, "INSERT INTO tb_log (sales_id, action_by, action_on, action_status, a_keterangan) VALUES ($sales_id, '$fromname', '$time', 6, '$ket_kendala')");

                        sendApiAction($chatid);

                        if ($update) {
                            // Send to Sales
                            $ceksales = mysqli_query($koneksi, "SELECT message_from, sales_id, message_id FROM tb_sales WHERE sales_id = '$sales_id'");
                            while ($d = mysqli_fetch_array($ceksales)) {
                                $sales_id       = $d['sales_id'];
                                $s_telegram_id  = $d['message_from'];
                                $s_m_id         = $d['message_id'];

                                $pesantosales = "ORDER SET TO KENDALA : ($kendala) \n";
                                $pesantosales .= "JA$sales_id\n";
                                $pesantosales .= "KET :\n";
                                $pesantosales .= "$pesan \n\n";
                                $pesantosales .= " ~ $fromname";

                                sendApiMsg($s_telegram_id, $pesantosales, $s_m_id, 'Markdown');
                            }

                            $text = "Oke kendala berhasil dilaporkan! Terima kasih :)";
                            sendApiMsg($chatid, $text);
                        } else {
                            $text = "❗️ Maaf laporan gagal dilakukan. Silahkan coba beberapa saat lagi.. $sales_id";
                            $text .= "Error : " . mysqli_error($koneksi) . " ";
                            sendApiMsg($chatid, $text);
                        }
                    }
                } elseif (strpos($reply, 'lokasi pelanggan') !== false) {
                    if (isset($message['location']['latitude'])) {
                        $latitude   = $message["location"]["latitude"];
                        $longitude  = $message["location"]["longitude"];
                        $lokasicust = $latitude . ',' . $longitude;

                        $alert = "Oke, lokasi pelanggan diterima.";
                        sendApiMsg($chatid, $alert);

                        if ($kendala == 'IJIN TANAM TIANG' || $kendala == 'NJKI' || $kendala == 'TIANG' || $kendala == 'RUTE INSTALASI' || $kendala == 'ODP FULL' || $kendala == 'PT2' || $kendala == 'NO FO/ODP') {
                            sendApiAction($chatid);

                            $text = "KENDALA\n";
                            $text .= "JA$sales_id\n";
                            $text .= "$kendala\n";
                            $text .= "Tikor Pelanggan : $lokasicust\n";
                            $text .= "Oke sekarang tulis keterangan tentang kendala ini:";

                            sendApiMsgReply($chatid, $text);
                        } else {
                            if (strpos($reply, 'Lanjut') !== false) {
                                $tikor_odp = str_replace(" ", "", explode(":", $tikor1));

                                sendApiAction($chatid);

                                $text = "KENDALA\n";
                                $text .= "JA$sales_id\n";
                                $text .= "$kendala\n";
                                $text .= "Tikor Pelanggan : $lokasicust\n";
                                $text .= "Tikor ODP : $tikor_odp[1]\n";
                                $text .= "Oke sekarang tulis keterangan tentang kendala ini:";

                                sendApiMsgReply($chatid, $text);
                            } else {
                                sendApiAction($chatid);

                                $text = "KENDALA\n";
                                $text .= "JA$sales_id\n";
                                $text .= "$kendala\n";
                                $text .= "Tikor Pelanggan : $lokasicust\n";
                                $text .= "Lanjut share lokasi ODP:";

                                sendApiMsgReply($chatid, $text);
                            }
                        }
                    } else {
                        $alert = "Anda tidak mengirmkan lokasi.";
                        sendApiMsg($chatid, $alert);

                        $tikor_odp = str_replace(" ", "", explode(":", $tikor1));

                        $text = "KENDALA\n";
                        $text .= "JA$sales_id\n";
                        $text .= "$kendala\n";
                        $text .= (strpos($reply, 'Lanjut') !== false) ? "Tikor ODP : $tikor_odp[1]\n" : "";
                        $text .= (strpos($reply, 'Lanjut') !== false) ? "Lanjut share lokasi pelanggan:" : "Share lokasi pelanggan:";

                        sendApiMsgReply($chatid, $text);
                    }
                } elseif (strpos($reply, 'lokasi ODP') !== false) {
                    if (isset($message['location']['latitude'])) {
                        $latitude   = $message["location"]["latitude"];
                        $longitude  = $message["location"]["longitude"];
                        $lokasiodp  = $latitude . ',' . $longitude;

                        $alert = "Oke, lokasi ODP diterima.";
                        sendApiMsg($chatid, $alert);

                        if (strpos($reply, 'Lanjut') !== false) {
                            $tikor_pelanggan = str_replace(" ", "", explode(":", $tikor1));

                            sendApiAction($chatid);

                            $text = "KENDALA\n";
                            $text .= "JA$sales_id\n";
                            $text .= "$kendala\n";
                            $text .= "Tikor Pelanggan : $tikor_pelanggan[1]\n";
                            $text .= "Tikor ODP : $lokasiodp\n";
                            $text .= "Oke sekarang tulis keterangan tentang kendala ini:";

                            sendApiMsgReply($chatid, $text);
                        } else {
                            sendApiAction($chatid);

                            $text = "KENDALA\n";
                            $text .= "JA$sales_id\n";
                            $text .= "$kendala\n";
                            $text .= "Tikor ODP : $lokasiodp\n";
                            $text .= "Lanjut share lokasi pelanggan:";

                            sendApiMsgReply($chatid, $text);
                        }
                    } else {
                        $alert = "Anda tidak mengirmkan lokasi.";
                        sendApiMsg($chatid, $alert);

                        $tikor_pelanggan = str_replace(" ", "", explode(":", $tikor1));

                        $text = "KENDALA\n";
                        $text .= "JA$sales_id\n";
                        $text .= "$kendala\n";
                        $text .= (strpos($reply, 'Lanjut') !== false) ? "Tikor ODP : $tikor_pelanggan[1]\n" : "";
                        $text .= (strpos($reply, 'Lanjut') !== false) ? "Lanjut share lokasi ODP:" : "Share lokasi ODP:";

                        sendApiMsgReply($chatid, $text);
                    }
                }

                break;

            case $str == 'ORDER':
                //$pesan      = str_replace(' ', '', $message['text']);
                if ($pesan == '/lapor') {

                    /*
                    sendApiAction($chatid);
                    $text = 'Pelaporan kendala dilakukan via jarvis web. Silahkan laporkan kendala order kepada TL anda.';
                    sendApiMsg($chatid, $text);
                    */

                    $reply_message  = $message["reply_to_message"]["text"];
                    $reply_message  = preg_replace('/^.+\n/', '', $reply_message);
                    $sales_id       = str_replace('JA', '', strtok($reply_message, "\n"));

                    /*
                    $inkeyboard = [
                        [
                            ['text' => 'A. RNA', 'callback_data' => "RNA-$sales_id"],
                        ],
                        [
                            ['text' => 'B. ALAMAT', 'callback_data' => "ALAMAT-$sales_id"],
                        ],
                        [
                            ['text' => 'C. BATAL', 'callback_data' => "BATAL-$sales_id"],
                        ],
                        [
                            ['text' => 'D. PENDING', 'callback_data' => "PENDING-$sales_id"],
                        ],
                        [
                            ['text' => 'E. ODP FULL', 'callback_data' => "ODP FULL-$sales_id"],
                        ],
                        [
                            ['text' => 'F. ODP LOSS', 'callback_data' => "ODP LOSS-$sales_id"],
                        ],
                        [
                            ['text' => 'G. ODP RETI', 'callback_data' => "ODP RETI-$sales_id"],
                        ],
                        [
                            ['text' => 'H. TIANG', 'callback_data' => "TIANG-$sales_id"],
                        ],
                        [
                            ['text' => 'I. PT2', 'callback_data' => "PT2-$sales_id"],
                        ],
                        [
                            ['text' => 'J. NO FO/ODP', 'callback_data' => "NO FO/ODP-$sales_id"],
                        ],
                        [
                            ['text' => 'K. RUTE INSTALASI', 'callback_data' => "RUTE INSTALASI-$sales_id"],
                        ],
                        [
                            ['text' => 'L. ODP BLM LIVE', 'callback_data' => "ODP BLM LIVE-$sales_id"],
                        ],
                        [
                            ['text' => 'M. IJIN TANAM TIANG', 'callback_data' => "IJIN TANAM TIANG-$sales_id"],
                        ],
                        [
                            ['text' => 'N. ONU > 32', 'callback_data' => "ONU > 32-$sales_id"],
                        ],
                        [
                            ['text' => 'O. PENDING INSTALASI', 'callback_data' => "PENDING INSTALASI-$sales_id"],
                        ],
                        [
                            ['text' => 'P. NJKI', 'callback_data' => "NJKI-$sales_id"],
                        ]
                    ];
                    */

                    $inkeyboard = [
                        [
                            ['text' => 'KENDALA PELANGGAN', 'callback_data' => "KENDALA PELANGGAN-$sales_id"],
                        ],
                        [
                            ['text' => 'KENDALA INSTALASI', 'callback_data' => "KENDALA INSTALASI-$sales_id"],
                        ],
                        [
                            ['text' => 'MAINTENANCE', 'callback_data' => "MAINTENANCE-$sales_id"],
                        ],
                        [
                            ['text' => 'KENDALA TEKNIS', 'callback_data' => "KENDALA TEKNIS-$sales_id"],
                        ]
                    ];

                    //$cekid      = mysqli_query($koneksi,"SELECT sales_id,kendala FROM tb_sales WHERE sales_id = '$sales_id'");
                    $cekorder   = mysqli_query($koneksi, "SELECT sales_id,kendala FROM tb_sales WHERE sales_id = '$sales_id' AND status_id = 6");

                    if (mysqli_num_rows($cekorder) > 0) {
                        while ($d = mysqli_fetch_array($cekorder)) {
                            $text = '❗️ Order ID JA' . $sales_id . ' sudah dilaporkan sebagai kendala ' . $d['kendala'] . '';
                            sendApiMsg($chatid, $text, false, 'Markdown');
                        }
                    } else {
                        $cekja   = mysqli_query($koneksi, "SELECT sales_id FROM tb_sales WHERE sales_id = '$sales_id'");

                        if (mysqli_num_rows($cekja) > 0) {
                            sendApiKeyboard($chatid, 'Silahkan pilih jenis kendala nya :', $inkeyboard, true);
                        } else {
                            $text = "❌ JA$sales_id belum diorder oleh TL!";
                            sendApiMsg($chatid, $text, false, 'Markdown');
                        }
                    }
                } elseif (strtok($pesan, "\n") == '/reqsc' || strtok($pesan, "\n") == '/reqsc ') {
                    sendApiAction($chatid);
                    $reply_message  = $message["reply_to_message"]["text"];
                    $reply_message  = preg_replace('/^.+\n/', '', $reply_message);
                    $sales_id       = str_replace('JA', '', strtok($reply_message, "\n"));

                    $cekid    = mysqli_query($koneksi, "SELECT status_id,sales_id FROM tb_sales WHERE sales_id = '$sales_id' AND status_id > 2 AND status_id != 1");
                    $ceksales = mysqli_query($koneksi, "SELECT message_from,sales_id,message_id FROM tb_sales WHERE sales_id = '$sales_id'");
                    //$cekorder = mysqli_query($koneksi,"SELECT sales_id FROM tb_sales WHERE sales_id = '$sales_id' AND status_id = 6");
                    $datenow  = date('Y-m-d H:i:s');

                    if (mysqli_num_rows($cekid) > 0) {
                        while ($d = mysqli_fetch_array($cekid)) {
                            if ($d['status_id'] == 6) {
                                $text = '❌ Order JA' . $sales_id . ' tidak dapat dilakukan request SC, karena statusnya sekarang adalah ' . statusSales($d['status_id']) . '. Silahkan hubungi TL untuk memfollow up KENDALA order ini.';
                            } else {
                                $text = '❌ Order JA' . $sales_id . ' tidak dapat dilakukan request SC, karena statusnya sekarang adalah ' . statusSales($d['status_id']) . '';
                            }
                        }
                    } else {
                        $cekProg    = mysqli_query($koneksi, "SELECT status_id,sales_id FROM tb_sales WHERE sales_id = '$sales_id' AND (status = 'otw' OR status = 'ogp')");
                        if (mysqli_num_rows($cekProg) > 0) {
                            $pesan = strtoupper($message['text']);
                            $arrpsn = explode("\n", $pesan);
                            $datenow = date('Y-m-d H:i:s');

                            if (sizeof($arrpsn) != 4) {
                                $text = 'ERROR request SC! Request SC hanya dengan /reqsc [enter] ODP [enter] PORT no_port [enter] DC';
                            } else {
                                $odp    = str_replace(' ', '', $arrpsn[1]);
                                $port   = str_replace(' ', '', $arrpsn[2]);
                                $dc     = str_replace(' ', '', $arrpsn[3]);
                                if (strlen($odp) < 14 || strlen($odp) > 16) {
                                    $text = 'Penulisan ODP tidak valid. Seharusnya ODP-PKL-FAA/001';
                                } elseif (stripos($odp, '/') == false) {
                                    $text = 'Kurang tanda / nya. Seharusnya ODP-PKL-FAA/001';
                                } elseif (stripos($port, ':') == true) {
                                    $text = 'Perbaiki penulisan port! PORT tanpa titi dua. Seharusnya PORT 1';
                                } elseif (stripos($port, '=') == true) {
                                    $text = 'Perbaiki penulisan port! PORT tanpa sama dengan. Seharusnya PORT 1';
                                } elseif (strlen($port) < 5) {
                                    $text = 'Perbaiki penulisan port! Seharusnya PORT 1';
                                } elseif (stripos($dc, ':') == true) {
                                    $text = 'Perbaiki penulisan dropcore! Seharusnya DC 150 (satuan meter)';
                                } elseif (strlen($dc) < 4) {
                                    $text = 'Perbaiki penulisan dropcore! Seharusnya DC 150 (satuan meter)';
                                } else {
                                    $port  = str_replace("PORT", "", $port);
                                    $dc    = str_replace("DC", "", $dc);
                                    $query = mysqli_query($koneksi, "UPDATE tb_sales SET req_sc_odp='$odp', req_sc_port='$port', req_sc_dc='$dc', status_id = 3, status = 'waitsc', req_sc_by = '$fromid', tgl_req_sc = '$datenow', tgl_update = '$datenow' WHERE sales_id = '$sales_id'");
                                    if ($query) {
                                        $ambilt = mysqli_query($koneksi, "SELECT nama_teknisi FROM tb_teknisi WHERE t_telegram_id = '$fromid'");
                                        while ($d = mysqli_fetch_array($ceksales)) {
                                            $sales_id = $d['sales_id'];
                                            $s_telegram_id = $d['message_from'];
                                            $s_m_id = $d['message_id'];
                                            while ($t = mysqli_fetch_array($ambilt)) {
                                                $savelog = mysqli_query($koneksi, "INSERT INTO tb_log (sales_id, action_by, action_on, action_status) VALUES ('$sales_id','$t[nama_teknisi]','$datenow',3)");
                                                $pesantosales = "⚠️ Order dimintakan SC oleh $t[nama_teknisi]";
                                                sendApiMsg($s_telegram_id, $pesantosales, $s_m_id, 'Markdown');
                                            }
                                        }
                                        $text = '✅ Request SC berhasil dilakukan, silahkan tunggu. Agar lebih efektif pastikan permintaan SC dilakukan saat jaringan dinyatakan feasible, sebelum proses tarik. Tks';
                                    } else {
                                        $text = "❗️ Order JA$sales_id tidak ditemukan.";
                                    }
                                }
                            }
                        } else {
                            $text = "❗️ Order JA$sales_id belum dilakukan update ke OTW & OGP. Lakukan update ke OTW & OGP dahulu. Kemudian silahkan reqsc ulang.";
                        }
                    }

                    $meid     = $message["message_id"];
                    sendApiMsg($chatid, $text, $meid);
                } elseif (strtok($pesan, "\n") == '/create') {
                    sendApiAction($chatid);
                    $reply_message  = $message["reply_to_message"]["text"];
                    $reply_message  = preg_replace('/^.+\n/', '', $reply_message);
                    preg_match_all('/(.*):(.*)/', $reply_message, $output);
                    $ja     = str_replace('JA', '', strtok($reply_message, "\n"));
                    $plgn   = trim(strtoupper($output["2"]["0"]));
                    $cp     = trim(strtoupper($output["2"]["1"]));
                    $odp    = trim(strtoupper($output["2"]["2"]));
                    $alamat = trim(strtoupper($output["2"]["3"]));

                    $text = "!create data\n";
                    $text .= "ORDER : \n";
                    $text .= "JA : $ja\n";
                    $text .= "A/N : $plgn\n";
                    $text .= "ALAMAT : $alamat\n";
                    $text .= "TLP : \n";
                    $text .= "INET : \n";
                    $text .= "ODP : $odp\n";
                    $text .= "ODP REAL :\n";
                    $text .= "PORT : \n";
                    $text .= "SISA : \n";
                    $text .= "SN : \n";
                    $text .= "SC : \n";
                    $text .= "DC : \n";
                    $text .= "MITRA : \n";
                    $text .= "TEKNISI : \n";
                    $text .= "CREW : \n";
                    $text .= "BC : \n";
                    $text .= "SCLAM : \n";
                    $text .= "BREKET : \n";
                    $text .= "ROS : \n";
                    $text .= "T7 : \n";
                    $text .= "CP : \n";
                    $text .= "STB ID : \n";
                    $text .= "SPL 1:2 : \n";
                    $text .= "SPL 1:4 : \n";
                    $text .= "SPL 1:8 : \n";
                    $text .= "CASSETE : \n";
                    $text .= "ADAPTER : \n";
                    $text .= "UTP : \n";
                    $text .= "RJ45 : \n";
                    sendApiMsg($chatid, $text, false, 'Markdown');
                } elseif ($pesan == '/status') {
                    sendApiAction($chatid);
                    $reply_message  = $message["reply_to_message"]["text"];
                    $reply_message  = preg_replace('/^.+\n/', '', $reply_message);
                    $sales_id       = str_replace('JA', '', strtok($reply_message, "\n"));
                    $cekid    = mysqli_query($koneksi, "SELECT * FROM tb_sales LEFT JOIN tb_salesman ON tb_salesman.s_telegram_id=tb_sales.message_from WHERE sales_id = '$sales_id'");
                    if (mysqli_num_rows($cekid) > 0) {
                        while ($d = mysqli_fetch_array($cekid)) {
                            if ($d['fullname'] == null) {
                                $salesman = 'ANONIM';
                            } else {
                                $salesman = $d['fullname'];
                            }
                            $text = "[" . statusSales($d['status_id']) . "]\n";
                            $text  .= "JA$d[sales_id]\n";
                            $text  .= "NAMA PELANGGAN : $d[nama_pelanggan]\n";
                            $text  .= "CP : $d[cp]\n";
                            $text  .= "SC : $d[new_sc]\n";
                            $text  .= "ODP : $d[odp]\n";
                            $text  .= "ALAMAT : $d[alamat]\n";
                            $text  .= "MYIR : $d[myir]\n";
                            $text  .= "PAKET : $d[paket]\n";
                            $text  .= "SALES : $salesman\n";
                            $text  .= "KETERANGAN : \n";
                            $text  .= $d['keterangan'] == '' ? '-' : $d['keterangan'];
                        }
                    } else {
                        $text = "❗️ Maaf sedang tidak dapat mengambil data. Silahkan coba beberapa saat lagi..";
                    }
                    $meid     = $message["message_id"];
                    sendApiMsg($chatid, $text, $meid);
                } elseif ($pesan == '/cekonuid') {
                    sendApiAction($chatid);
                    $reply_message  = $message["reply_to_message"]["text"];
                    $reply_message  = preg_replace('/^.+\n/', '', $reply_message);
                    $sales_id       = str_replace('JA', '', strtok($reply_message, "\n"));
                    $datenow  = date('Y-m-d H:i:s');
                    $cekid    = mysqli_query($koneksi, "SELECT * FROM tb_sales WHERE sales_id = '$sales_id'");
                    if (mysqli_num_rows($cekid) > 0) {
                        $cekreq    = mysqli_query($koneksi, "SELECT * FROM tb_cekonu WHERE sales_id = '$sales_id' AND status = 'REQUEST'");
                        if (mysqli_num_rows($cekreq) <= 0) {
                            while ($d = mysqli_fetch_array($cekid)) {
                                $nama_pelanggan = $d['nama_pelanggan'];
                                $cp             = $d['cp'];
                                $odp            = $d['odp'];
                            }
                            $telegramname = str_replace("'", "", $telegramname);
                            $query = mysqli_query($koneksi, "INSERT INTO tb_cekonu (sales_id,nama_pelanggan,cp,odp,created_at,req_by,group_id,message_id) VALUES ('$sales_id','$nama_pelanggan','$cp','$odp','$datenow','$telegramname','$chatid','$meid')");
                            if ($query) {
                                $text = "✅ Request cek onu untuk JA$sales_id berhasil dilakukan. Mohon menunggu pengecekan HD.";
                            } else {
                                $text = "Error. SIlahkan coba beberapa saat lagi. \n";
                                $text .= "Error : " . mysqli_error($koneksi) . " ";
                            }
                        } else {
                            $text = "JA$sales_id sedang dalam pengecekan HD. Mohon ditunggu.";
                        }
                    } else {
                        $text = "JA$sales_id tidak ditemukan.";
                    }
                    $meid     = $message["message_id"];
                    sendApiMsg($chatid, $text, $meid);
                } elseif ($pesan == '/otw') {
                    sendApiAction($chatid);
                    $reply_message  = $message["reply_to_message"]["text"];
                    $reply_message  = preg_replace('/^.+\n/', '', $reply_message);
                    $sales_id       = str_replace('JA', '', strtok($reply_message, "\n"));
                    $datenow  = date('Y-m-d H:i:s');
                    $cekid    = mysqli_query($koneksi, "SELECT * FROM tb_sales WHERE sales_id = '$sales_id'");
                    while ($d = mysqli_fetch_array($cekid)) {
                        $status_id = $d['status_id'];
                        $progress_to = $d['progress_to'];
                    }
                    if (mysqli_num_rows($cekid) > 0) {
                        $cekTek    = mysqli_query($koneksi, "SELECT * FROM tb_teknisi WHERE t_telegram_id = '$progress_to'");
                        while ($e = mysqli_fetch_array($cekTek)) {
                            $crew = $e['crew'];
                        }
                        $cekCrew   = mysqli_query($koneksi, "SELECT * FROM tb_teknisi WHERE crew = '$crew'");
                        $crewNya = "";
                        foreach ($cekCrew as $row) {
                            $crewNya .= $row['t_telegram_id'] . ",";
                            $crewImp = rtrim($crewNya, ",");
                        }
                        $permitTek = explode(",", $crewImp);
                        if ($fromid == $progress_to || $fromid == $permitTek[0] || $fromid == $permitTek[1]) {
                            $cekreq    = mysqli_query($koneksi, "SELECT * FROM tb_sales WHERE sales_id = '$sales_id' AND `status` = 'ordered'");
                            if (mysqli_num_rows($cekreq) > 0) {
                                $query = mysqli_query($koneksi, "UPDATE tb_sales SET status = 'otw', tgl_update = '$datenow' WHERE sales_id=$sales_id");
                                if ($query) {
                                    $text = "✅ JA$sales_id berhasil diupdate ke OTW ke lokasi";
                                } else {
                                    $text = 'Error. Silahkan coba beberapa saat lagi.';
                                }
                            } else {
                                $text = "JA$sales_id tidak dalam status untuk di OTW. Statusnya : " . statusSales($status_id) . "";
                            }
                        } else {
                            $text = "❌ JA$sales_id bukan order anda. Silahkan koordinasi dengan TL";
                        }
                    } else {
                        $text = "JA$sales_id tidak ditemukan.";
                    }
                    $meid     = $message["message_id"];
                    sendApiMsg($chatid, $text, $meid);
                } elseif ($pesan == '/ogp') {
                    sendApiAction($chatid);
                    $reply_message  = $message["reply_to_message"]["text"];
                    $reply_message  = preg_replace('/^.+\n/', '', $reply_message);
                    $sales_id       = str_replace('JA', '', strtok($reply_message, "\n"));
                    $datenow  = date('Y-m-d H:i:s');
                    $cekid    = mysqli_query($koneksi, "SELECT * FROM tb_sales WHERE sales_id = '$sales_id'");
                    while ($d = mysqli_fetch_array($cekid)) {
                        $status_id = $d['status_id'];
                        $progress_to = $d['progress_to'];
                    }
                    if (mysqli_num_rows($cekid) > 0) {
                        $cekTek    = mysqli_query($koneksi, "SELECT * FROM tb_teknisi WHERE t_telegram_id = '$progress_to'");
                        while ($e = mysqli_fetch_array($cekTek)) {
                            $crew = $e['crew'];
                        }
                        $cekCrew   = mysqli_query($koneksi, "SELECT * FROM tb_teknisi WHERE crew = '$crew'");
                        $crewNya = "";
                        foreach ($cekCrew as $row) {
                            $crewNya .= $row['t_telegram_id'] . ",";
                            $crewImp = rtrim($crewNya, ",");
                        }
                        $permitTek = explode(",", $crewImp);
                        if ($fromid == $progress_to || $fromid == $permitTek[0] || $fromid == $permitTek[1]) {
                            $cekreq    = mysqli_query($koneksi, "SELECT * FROM tb_sales WHERE sales_id = '$sales_id' AND (status = 'scbe' OR status = 'ordered' OR status = 'otw')");
                            if (mysqli_num_rows($cekreq) > 0) {
                                $query = mysqli_query($koneksi, "UPDATE tb_sales SET status = 'ogp', tgl_update = '$datenow' WHERE sales_id=$sales_id");
                                if ($query) {
                                    $text = "✅ JA$sales_id berhasil diupdate ke OGP pengerjaan pemasangan";
                                } else {
                                    $text = 'Error. Silahkan coba beberapa saat lagi.';
                                }
                            } else {
                                $text = "JA$sales_id tidak dalam status untuk di OGP. Statusnya : " . statusSales($status_id) . "";
                            }
                        } else {
                            $text = "❌ JA$sales_id bukan order anda. Silahkan koordinasi dengan TL";
                        }
                    } else {
                        $text = "JA$sales_id tidak ditemukan.";
                    }
                    $meid     = $message["message_id"];
                    sendApiMsg($chatid, $text, $meid);
                }

                break;

            default:
                //source default
                break;
        }
    }

    if ($masuk_switch_m) {
        if ($type_ms == "private") {
            switch (true) {
                case $pesan == '/id':
                    sendApiAction($chatid);
                    $text = 'ID Kamu adalah: ' . $fromid;
                    sendApiMsg($chatid, $text);
                    break;

                case $pesan == '/regteknisi':
                    sendApiAction($chatid);

                    $cekTeknisi = mysqli_query($koneksi, "SELECT id_telegram FROM tb_teknisi WHERE id_telegram = '$fromid' AND nik IS NOT NULL AND nama_teknisi IS NOT NULL AND mitra IS NOT NULL");
                    $ceknonik   = mysqli_query($koneksi, "SELECT id_telegram FROM tb_teknisi WHERE id_telegram = '$fromid' AND nik IS NULL");
                    $ceknoname  = mysqli_query($koneksi, "SELECT id_telegram FROM tb_teknisi WHERE id_telegram = '$fromid' AND nama_teknisi IS NULL");
                    $ceknomitra = mysqli_query($koneksi, "SELECT id_telegram FROM tb_teknisi WHERE id_telegram = '$fromid' AND mitra IS NULL");

                    if (mysqli_num_rows($cekTeknisi) > 0) {
                        $text = '❗️ Kamu sudah terdaftar sebagai Teknisi pada aplikasi ini! ';
                        sendApiMsg($chatid, $text);
                    } else {
                        if (mysqli_num_rows($ceknonik) > 0) {
                            $text = 'Masukan NIK dengan 8 digit angka. Jika kamu belum memiliki nik, gunakan tanggal lahir kamu. misal : 20 Juli 1998, maka masukan nik dengan 20071998';
                            sendApiMsg($chatid, $text);
                            $text = 'Silahkan masukan nik kamu :';
                            sendApiMsgReply($chatid, $text);
                        } elseif (mysqli_num_rows($ceknoname) > 0) {
                            $text = '👤 Masukan nama kamu :';
                            sendApiMsgReply($chatid, $text);
                        } elseif (mysqli_num_rows($ceknomitra) > 0) {
                            $text = 'Masukan Mitra :';
                            sendApiMsgReply($chatid, $text);
                            $text = "Isi Mitra dengan nama perusahaan. misal : HCP, TA, GLOBAL, KOPEGTEL, ZAG, KJS";
                            sendApiMsg($chatid, $text);
                        } else {
                            $text = 'Halo 👋🏻, perkenalkan saya adalah jarvis, robot yang akan membantu pekerjaan teman-teman.';
                            sendApiMsg($chatid, $text);
                            $text = 'Saat ini kamu mencoba untuk mendaftar sebagai Teknisi';
                            sendApiMsg($chatid, $text);
                            $text = 'Masukan NIK dengan 8 digit angka. Jika kamu belum memiliki nik, gunakan tanggal lahir kamu. misal : 20 Juli 1998, maka masukan nik dengan 20071998';
                            sendApiMsg($chatid, $text);
                            $text = 'Silahkan masukan nik kamu :';
                            sendApiMsgReply($chatid, $text);
                        }
                    }

                    break;

                case $pesan == '/help':
                    sendApiAction($chatid);

                    $text = "Halo. Kenalin saya MOPEGABOT, asisten dari Aplikasi MOPEGA. Saya ditugaskan untuk membantu pekerjaan teman-teman teknisi.\n\n";
                    $text .= "📖 Berikut yang bisa saya lakukan :\n\n";
                    $text .= "== Fungsional ==\n";
                    $text .= "/regteknisi untuk registrasi sebagai Teknisi.\n";
                    $text .= "/cek [enter] ODP, untuk melihat nomor isi ODP tersebut.\n";
                    $text .= "/otw untuk update progress menjadi On The Way. (Mereply Pesan Order)\n";
                    $text .= "/ogp untuk update progress menjadi On Going Progress. (Mereply Pesan Order)\n";
                    $text .= "/closed untuk update progress menjadi Closed. (Mereply Pesan Order)\n\n";
                    $text .= "== Non Fungsional ==\n";
                    $text .= "/help untuk melihat informasi bantuan.\n";
                    $text .= "/id untuk melihat informasi ID User Telegram.\n\n";
                    $text .= "😎 Terima Kasih";

                    sendApiMsg($chatid, $text);
                    break;

                case preg_match("/\/cek (.*)/", $pesan, $hasil):
                    sendApiAction($chatid);

                    $sales_id = intval(preg_replace('/[^0-9]+/', '', $hasil[1]), 10);

                    $inkeyboard = [
                        [
                            ['text' => 'A. RNA', 'callback_data' => "RNA-$sales_id"],
                        ],
                        [
                            ['text' => 'B. ALAMAT', 'callback_data' => "ALAMAT-$sales_id"],
                        ],
                        [
                            ['text' => 'C. BATAL', 'callback_data' => "BATAL-$sales_id"],
                        ],
                        [
                            ['text' => 'D. PENDING', 'callback_data' => "PENDING-$sales_id"],
                        ],
                        [
                            ['text' => 'E. ODP FULL', 'callback_data' => "ODP FULL-$sales_id"],
                        ],
                        [
                            ['text' => 'F. ODP LOSS', 'callback_data' => "ODP LOSS-$sales_id"],
                        ],
                        [
                            ['text' => 'G. ODP RETI', 'callback_data' => "ODP RETI-$sales_id"],
                        ],
                        [
                            ['text' => 'H. TIANG', 'callback_data' => "TIANG-$sales_id"],
                        ],
                        [
                            ['text' => 'I. PT2', 'callback_data' => "PT2-$sales_id"],
                        ],
                        [
                            ['text' => 'J. NO FO/ODP', 'callback_data' => "NO FO/ODP-$sales_id"],
                        ],
                        [
                            ['text' => 'K. RUTE INSTALASI', 'callback_data' => "RUTE INSTALASI-$sales_id"],
                        ],
                        [
                            ['text' => 'L. ODP BLM LIVE', 'callback_data' => "ODP BLM LIVE-$sales_id"],
                        ],
                        [
                            ['text' => 'M. IJIN TANAM TIANG', 'callback_data' => "IJIN TANAM TIANG-$sales_id"],
                        ],
                        [
                            ['text' => 'N. ONU > 32', 'callback_data' => "ONU > 32-$sales_id"],
                        ],
                        [
                            ['text' => 'O. PENDING INSTALASI', 'callback_data' => "PENDING INSTALASI-$sales_id"],
                        ],
                        [
                            ['text' => 'P. NJKI', 'callback_data' => "NJKI-$sales_id"],
                        ]
                    ];

                    $cekorder   = mysqli_query($koneksi, "SELECT sales_id FROM tb_kendala WHERE sales_id = '$sales_id'");
                    $datenow    = date('Y-m-d H:i:s');
                    $meid       = $message["message_id"];
                    if (mysqli_num_rows($cekorder) > 0) {
                        while ($d = mysqli_fetch_array($cekorder)) {
                            $text = '❗️ Order ID JA' . $sales_id . ' sudah dilaporkan sebagai kendala ' . $d['kendala'] . '';
                            sendApiMsg($chatid, $text, false, 'Markdown');
                        }
                    } else {
                        $cekja   = mysqli_query($koneksi, "SELECT sales_id FROM tb_sales WHERE sales_id = '$sales_id'");

                        if (mysqli_num_rows($cekja) > 0) {
                            sendApiKeyboard($chatid, 'Silahkan pilih jenis kendala nya :', $inkeyboard, true);
                        } else {
                            $text = "❌ JA$sales_id belum diorder oleh TL!";
                            sendApiMsg($chatid, $text, false, 'Markdown');
                        }
                    }

                    break;


                default:
                    $cekt       = mysqli_query($koneksi, "SELECT t_telegram_id FROM tb_teknisi WHERE t_telegram_id = '$fromid'");
                    $ceknonik   = mysqli_query($koneksi, "SELECT t_telegram_id FROM tb_teknisi WHERE t_telegram_id = '$fromid' AND nik IS NULL");
                    $ceknoname  = mysqli_query($koneksi, "SELECT t_telegram_id FROM tb_teknisi WHERE t_telegram_id = '$fromid' AND nama_teknisi IS NULL");
                    $ceknocrew  = mysqli_query($koneksi, "SELECT t_telegram_id FROM tb_teknisi WHERE t_telegram_id = '$fromid' AND crew IS NULL");
                    $ceknomitra = mysqli_query($koneksi, "SELECT t_telegram_id FROM tb_teknisi WHERE t_telegram_id = '$fromid' AND mitra IS NULL");
                    if (mysqli_num_rows($cekt) <= 0) {
                        $text = 'Silahkan ketik /help untuk melihat menu bantuan :)';
                        sendApiMsg($chatid, $text);
                    } elseif (mysqli_num_rows($ceknonik) > 0) {
                        $text = 'Kamu belum menyelesaikan pendaftaran. Silahkan lanjutkan pendaftarannya';
                        sendApiMsg($chatid, $text);
                        $text = 'Masukan NIK dengan 8 digit angka. Jika kamu belum memiliki nik, gunakan tanggal lahir kamu. misal : 20 Juli 1998, maka masukan nik dengan 20071998';
                        sendApiMsg($chatid, $text);
                        $text = 'Silahkan masukan nik kamu :';
                        sendApiMsgReply($chatid, $text);
                    } elseif (mysqli_num_rows($ceknoname) > 0) {
                        $text = 'Kamu belum menyelesaikan pendaftaran. Silahkan lanjutkan pendaftarannya';
                        sendApiMsg($chatid, $text);
                        $text = '👤 Masukan nama kamu :';
                        sendApiMsgReply($chatid, $text);
                    } elseif (mysqli_num_rows($ceknocrew) > 0) {
                        $text = 'Kamu belum menyelesaikan pendaftaran. Silahkan lanjutkan pendaftarannya';
                        sendApiMsg($chatid, $text);
                        $text = "Masukan KODE DATEL dengan, Contoh : PKL, SLW, BTG, TEG, BRB, PML";
                        sendApiMsg($chatid, $text);
                        $text = '👥 Masukan KODE DATEL :';
                        sendApiMsgReply($chatid, $text);
                    } elseif (mysqli_num_rows($ceknomitra) > 0) {
                        $text = 'Kamu belum menyelesaikan pendaftaran. Silahkan lanjutkan pendaftarannya';
                        sendApiMsg($chatid, $text);
                        $text = 'Masukan Mitra :';
                        sendApiMsgReply($chatid, $text);
                        $text = "Isi Mitra dengan nama perusahaan. misal : HCP, TA, GLOBAL, KOPEGTEL, ZAG, KJS";
                        sendApiMsg($chatid, $text);
                    } else {
                        $text = 'Silahkan ketik /help untuk melihat menu bantuan :)';
                        sendApiMsg($chatid, $text);
                    }

                    break;
            }
        } else {
            switch (true) {
                case $pesan == '/id':
                    sendApiAction($chatid);
                    $text = 'ID Kamu adalah: ' . $fromid;
                    sendApiMsg($chatid, $text);
                    break;

                case $pesan == '/groupid':
                    sendApiAction($chatid);
                    $text = 'GROUP ID : ' . $chatid;
                    sendApiMsg($chatid, $text);
                    break;

                case $pesan == '/help':
                case $pesan == '/help@damanasisten_bot':
                    sendApiAction($chatid);

                    $text = "Halo. Kenalin saya MOPEGABOT, asisten dari Aplikasi MOPEGA. Saya ditugaskan untuk membantu pekerjaan teman-teman teknisi.\n\n";
                    $text .= "📖 Berikut yang bisa saya lakukan :\n\n";
                    $text .= "== Fungsional ==\n";
                    $text .= "/regteknisi untuk registrasi sebagai Teknisi.\n";
                    $text .= "/cek [enter] ODP, untuk melihat nomor isi ODP tersebut.\n";
                    $text .= "/otw untuk update progress menjadi On The Way. (Mereply Pesan Order)\n";
                    $text .= "/ogp untuk update progress menjadi On Going Progress. (Mereply Pesan Order)\n";
                    $text .= "/closed untuk update progress menjadi Closed. (Mereply Pesan Order)\n\n";
                    $text .= "== Non Fungsional ==\n";
                    $text .= "/help untuk melihat informasi bantuan.\n";
                    $text .= "/id untuk melihat informasi ID User Telegram.\n\n";
                    $text .= "😎 Terima Kasih";

                    sendApiMsg($chatid, $text);
                    break;

                default:

                    break;
            }
        }
    }
}
