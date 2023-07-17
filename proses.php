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
            case $reply == 'Silahkan masukan nik kamu :':

                if (is_numeric($pesan) == false) {
                    $text = 'Isi NIK hanya dengan angka!';
                    sendApiMsg($chatid, $text);

                    $text = 'Silahkan masukan nik kamu :';
                    sendApiMsgReply($chatid, $text);
                } else {
                    $query = mysqli_query($koneksi, "INSERT INTO tb_teknisi (id_telegram, nik) VALUES ($fromid, '$pesan')");

                    sendApiAction($chatid);

                    if ($query) {
                        $text = "NIK kamu $pesan, berhasil disimpan.";
                        sendApiMsg($chatid, $text);

                        $text = 'Masukan nama kamu :';
                        sendApiMsgReply($chatid, $text);
                    } else {
                        $text = "❗️ Maaf pendaftaran gagal dilakukan. Silahkan coba beberapa saat lagi..";
                    }
                }

                break;

            case $reply == 'Masukan nama kamu :':

                $query = mysqli_query($koneksi, "UPDATE tb_teknisi SET nama_teknisi = '$pesan' WHERE id_telegram = '$fromid'");

                sendApiAction($chatid);

                if ($query) {
                    $text = "Nama kamu $pesan, berhasil disimpan.";
                    sendApiMsg($chatid, $text);

                    $text = 'Masukan Mitra :';
                    sendApiMsgReply($chatid, $text);
                    $text = "Isi Mitra dengan nama perusahaan. misal : HCP, TA, GLOBAL, KOPEGTEL, ZAG, KJS";
                    sendApiMsg($chatid, $text);
                } else {
                    $text = "❗️ Maaf pendaftaran gagal dilakukan. Silahkan coba beberapa saat lagi..";
                }

                break;

            case $reply == 'Masukan Mitra :':

                $query = mysqli_query($koneksi, "UPDATE tb_teknisi SET mitra = '$pesan' WHERE id_telegram = '$fromid'");

                sendApiAction($chatid);

                if ($query) {
                    $text = "Mitra kamu $pesan, berhasil disimpan.";
                    sendApiMsg($chatid, $text);

                    $text = "Pendaftaran berhasil dilakukan, kamu akan diberitahu kembali jika akun ini sudah diapprove oleh admin :)";
                    sendApiMsg($chatid, $text);
                } else {
                    $text = "❗️ Maaf pendaftaran gagal dilakukan. Silahkan coba beberapa saat lagi..";
                }

                break;

            case $str == 'ORDER':
                if ($pesan == '/otw') {
                    sendApiAction($chatid);

                    $reply_message  = $message["reply_to_message"]["text"];
                    $reply_message  = preg_replace('/^.+\n/', '', $reply_message);

                    $tiket          = strtok($reply_message, "\n");
                    $datenow        = date('Y-m-d H:i:s');
                    $cekTiket       = mysqli_query($koneksi, "SELECT * FROM tb_gangguan WHERE tiket = '$tiket'");

                    while ($d = mysqli_fetch_array($cekTiket)) {
                        $id_gangguan    = $d['id_gangguan'];
                        $status         = $d['status'];
                        $teknisi        = $d['teknisi'];
                    }

                    if (mysqli_num_rows($cekTiket) > 0) {
                        if ($fromid == $teknisi) {
                            $cekStatus = mysqli_query($koneksi, "SELECT * FROM tb_gangguan WHERE tiket = '$tiket' AND `status` = '1'");
                            if (mysqli_num_rows($cekStatus) > 0) {
                                $query      = mysqli_query($koneksi, "UPDATE tb_gangguan SET `status` = '2', otw_at = '$datenow' WHERE tiket = '$tiket'");
                                $queryLog   = mysqli_query($koneksi, "INSERT INTO tb_log (id_gangguan, action, keterangan, waktu) VALUES ('$id_gangguan', '2', 'Teknisi menuju lokasi pelanggan', '$datenow')");
                                if ($query && $queryLog) {
                                    $text = "✅ Tiket $tiket berhasil diupdate ke On The Way lokasi pelanggan.";
                                } else {
                                    $text = "Mohon Maaf, sistem sedang ada kendala. Silahkan coba beberapa saat lagi.\n";
                                    $text .= "Error : " . mysqli_error($koneksi);
                                }
                            } else {
                                $text = "Tiket $tiket tidak dalam status untuk di OTW. Statusnya : " . statusOrder($status) . "";
                            }
                        } else {
                            $text = "❌ Tiket $tiket bukan order anda. Silahkan koordinasi dengan TL";
                        }
                    } else {
                        $text = "Tiket $tiket tidak ditemukan.";
                    }

                    $meid     = $message["message_id"];
                    sendApiMsg($chatid, $text, $meid);
                } elseif ($pesan == '/ogp') {
                    sendApiAction($chatid);

                    $reply_message  = $message["reply_to_message"]["text"];
                    $reply_message  = preg_replace('/^.+\n/', '', $reply_message);

                    $tiket          = strtok($reply_message, "\n");
                    $datenow        = date('Y-m-d H:i:s');
                    $cekTiket       = mysqli_query($koneksi, "SELECT * FROM tb_gangguan WHERE tiket = '$tiket'");

                    while ($d = mysqli_fetch_array($cekTiket)) {
                        $id_gangguan    = $d['id_gangguan'];
                        $status         = $d['status'];
                        $teknisi        = $d['teknisi'];
                    }

                    if (mysqli_num_rows($cekTiket) > 0) {
                        if ($fromid == $teknisi) {
                            $cekStatus = mysqli_query($koneksi, "SELECT * FROM tb_gangguan WHERE tiket = '$tiket' AND `status` = '2'");
                            if (mysqli_num_rows($cekStatus) > 0) {
                                $query      = mysqli_query($koneksi, "UPDATE tb_gangguan SET `status` = '3', ogp_at = '$datenow' WHERE tiket = '$tiket'");
                                $queryLog   = mysqli_query($koneksi, "INSERT INTO tb_log (id_gangguan, action, keterangan, waktu) VALUES ('$id_gangguan', '3', 'Teknisi sedang melakukan pengecekan gangguan', '$datenow')");
                                if ($query && $queryLog) {
                                    $text = "✅ Tiket $tiket berhasil diupdate ke On Going Progress pengerjaan oleh Teknisi.";
                                } else {
                                    $text = "Mohon Maaf, sistem sedang ada kendala. Silahkan coba beberapa saat lagi.\n";
                                    $text .= "Error : " . mysqli_error($koneksi);
                                }
                            } else {
                                $text = "Tiket $tiket tidak dalam status untuk di OGP. Statusnya : " . statusOrder($status) . "";
                            }
                        } else {
                            $text = "❌ Tiket $tiket bukan order anda. Silahkan koordinasi dengan TL";
                        }
                    } else {
                        $text = "Tiket $tiket tidak ditemukan.";
                    }

                    $meid     = $message["message_id"];
                    sendApiMsg($chatid, $text, $meid);
                } elseif (strtok($pesan, "\n") == '/closed' || strtok($pesan, "\n") == '/closed ') {
                    sendApiAction($chatid);

                    $reply_message  = $message["reply_to_message"]["text"];
                    $reply_message  = preg_replace('/^.+\n/', '', $reply_message);

                    if (strpos($pesan, 'Penyebab :') == false) {
                        $text = 'Baris PERBAIKAN Tidak Ditemukan. Perbaiki Penulisan Laporan Close! gunakan /formatclosed untuk melihat format laporan close';
                    } elseif (strpos($pesan, 'Perbaikan :') == false) {
                        $text = 'Baris PENYEBAB Tidak Ditemukan. Perbaiki Penulisan Laporan Close! gunakan /formatclosed untuk melihat format laporan close';
                    } else {
                        preg_match('/PENYEBAB\s{0,1}:(.+)/i', $pesan, $penyebab);
                        preg_match('/PERBAIKAN\s{0,1}:(.+)/i', $pesan, $perbaikan);

                        $penyebab   = (trim($penyebab[1]) != '' ? trim($penyebab[1]) : '-');
                        $perbaikan  = (trim($perbaikan[1]) != '' ? trim($perbaikan[1]) : '-');

                        if ($penyebab != '-') {
                            if ($perbaikan != '-') {
                                $tiket          = strtok($reply_message, "\n");
                                $datenow        = date('Y-m-d H:i:s');
                                $cekTiket       = mysqli_query($koneksi, "SELECT * FROM tb_gangguan WHERE tiket = '$tiket'");
                                $id_pelanggan   = '';
                                $keterangan     = '';
                                $report_date    = '';

                                while ($d = mysqli_fetch_array($cekTiket)) {
                                    $id_gangguan    = $d['id_gangguan'];
                                    $id_pelanggan   = $d['id_pelanggan'];
                                    $status         = $d['status'];
                                    $teknisi        = $d['teknisi'];
                                    $keterangan     = $d['ket'];
                                    $report_date    = $d['report_date'];
                                }

                                if (mysqli_num_rows($cekTiket) > 0) {
                                    if ($fromid == $teknisi) {
                                        $cekStatus = mysqli_query($koneksi, "SELECT * FROM tb_gangguan WHERE tiket = '$tiket' AND `status` = '3'");
                                        if (mysqli_num_rows($cekStatus) > 0) {
                                            $query      = mysqli_query($koneksi, "UPDATE tb_gangguan SET `status` = '4', closed_at = '$datenow', penyebab = '$penyebab', perbaikan = '$perbaikan' WHERE tiket = '$tiket'");
                                            $queryLog   = mysqli_query($koneksi, "INSERT INTO tb_log (id_gangguan, action, keterangan, waktu) VALUES ('$id_gangguan', '4', 'Gangguan berhasil dikerjakan dan jaringan sudah normal', '$datenow')");
                                            if ($query) {
                                                $nama_pelanggan = '';
                                                $email          = '';

                                                $cek_pelanggan  = mysqli_query($koneksi, "SELECT nama_pelanggan, email FROM tb_pelanggan WHERE id_pelanggan = '$id_pelanggan'");
                                                while ($row = mysqli_fetch_assoc($cek_pelanggan)) {
                                                    $nama_pelanggan = $row['nama_pelanggan'];
                                                    $email          = $row['email'];
                                                }

                                                $data_pelanggan = [
                                                    'nama_pelanggan'    => $nama_pelanggan,
                                                    'email'             => $email
                                                ];

                                                $data_gangguan  = [
                                                    'tiket'         => $tiket,
                                                    'ket'           => $keterangan,
                                                    'report_date'   => $report_date
                                                ];

                                                notifEmail($data_pelanggan, $data_gangguan);

                                                $text = "✅ Tiket $tiket berhasil Closed oleh Teknisi.";
                                            } else {
                                                $text = "Mohon Maaf, sistem sedang ada kendala. Silahkan coba beberapa saat lagi.\n";
                                                $text .= "Error : " . mysqli_error($koneksi);
                                            }
                                        } else {
                                            $text = "Tiket $tiket tidak dalam status untuk di Closed. Statusnya : " . statusOrder($status) . "";
                                        }
                                    } else {
                                        $text = "❌ Tiket $tiket bukan order anda. Silahkan koordinasi dengan TL";
                                    }
                                } else {
                                    $text = "Tiket $tiket tidak ditemukan.";
                                }
                            } else {
                                $text = "PERBAIKAN tidak valid, lengkapi dengan benar isian Perbaikan.";
                            }
                        } else {
                            $text = "PENYEBAB tidak valid, lengkapi dengan benar isian Penyebab.";
                        }
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
                            $text = 'Masukan nama kamu :';
                            sendApiMsgReply($chatid, $text);
                        } elseif (mysqli_num_rows($ceknomitra) > 0) {
                            $text = 'Masukan Mitra :';
                            sendApiMsgReply($chatid, $text);
                            $text = "Isi Mitra dengan nama perusahaan. misal : HCP, TA, GLOBAL, KOPEGTEL, ZAG, KJS";
                            sendApiMsg($chatid, $text);
                        } else {
                            $text = 'Halo 👋🏻, perkenalkan saya adalah MOPEGA BOT, robot yang akan membantu pekerjaan teman-teman.';
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

                case $pesan == '/formatclosed':
                    sendApiAction($chatid);
                    $text = 'Reply order dengan format dibawah ini 👇';
                    sendApiMsg($chatid, $text);

                    $text = "/closed\n";
                    $text .= "Penyebab : \n";
                    $text .= "Perbaikan : \n";
                    sendApiMsg($chatid, $text);
                    break;

                case $pesan == '/help':
                    sendApiAction($chatid);

                    $text = "Halo. Kenalin saya MOPEGA BOT, asisten dari Aplikasi MOPEGA. Saya ditugaskan untuk membantu pekerjaan teman-teman teknisi.\n\n";
                    $text .= "📖 Berikut yang bisa saya lakukan :\n\n";
                    $text .= "== Fungsional ==\n";
                    $text .= "/regteknisi untuk registrasi sebagai Teknisi.\n";
                    $text .= "/cekodp #[odp], untuk melihat nomor isi ODP tersebut.\n";
                    $text .= "/otw untuk update progress menjadi On The Way. (Mereply Pesan Order)\n";
                    $text .= "/ogp untuk update progress menjadi On Going Progress. (Mereply Pesan Order)\n";
                    $text .= "/closed untuk update progress menjadi Closed. (Mereply Pesan Order)\n";
                    $text .= "/formatclosed untuk melihat format pesan Closed.\n\n";
                    $text .= "== Non Fungsional ==\n";
                    $text .= "/help untuk melihat informasi bantuan.\n";
                    $text .= "/id untuk melihat informasi ID User Telegram.\n\n";
                    $text .= "😎 Terima Kasih";

                    sendApiMsg($chatid, $text);
                    break;

                case preg_match("/\/cekodp (.*)/", $pesan, $hasil):
                    sendApiAction($chatid);

                    $pesan      = explode(" ", $message['text']);
                    $odp        = strtoupper(str_replace("#", "", trim($pesan[1])));
                    $koordinat  = "-";

                    $cekODP         = mysqli_query($koneksi, "SELECT * FROM tb_pelanggan WHERE odp = '$odp' ORDER BY port");
                    $koordinatODP   = mysqli_query($koneksi, "SELECT koordinat FROM tb_odp WHERE nama_odp = '$odp'");
                    while ($row = mysqli_fetch_assoc($koordinatODP)) {
                        $koordinat = $row['koordinat'];
                    }

                    if (mysqli_num_rows($cekODP) > 0) {
                        $text   = "Daftar Nomer di $odp \n";
                        $text   .= "Koordinat : $koordinat \n -- \n";
                        foreach ($cekODP as $data) {
                            $port   = $data['port'];
                            $inet   = $data['no_internet'] != '' ? $data['no_internet'] : '-';
                            $voice  = $data['no_voice'] != '' ? $data['no_voice'] : '-';
                            $text   .= "$port. $inet - $voice \n";
                        }
                    } else {
                        $text       = "Mohon maaf, $odp belum ada datanya";
                    }

                    sendApiMsg($chatid, $text, false);
                    break;


                case $pesan == '/testemail':
                    sendApiAction($chatid);
                    // testNotifEmail();
                    $data_pelanggan = [
                        'nama_pelanggan'    => "ANDI RAHARJO",
                        'email'             => "nurilmuslichin16@gmail.com"
                    ];

                    $data_gangguan  = [
                        'tiket'         => "IN12345",
                        'ket'           => "TEST EMAIL",
                        'report_date'   => date('Y-m-d H:i:s')
                    ];
                    notifEmail($data_pelanggan, $data_gangguan);
                    $text = 'Test Kirim Email Berhasil!';
                    sendApiMsg($chatid, $text);
                    break;

                default:

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

                case $pesan == '/formatclosed':
                    sendApiAction($chatid);
                    $text = 'Reply order dengan format dibawah ini 👇';
                    sendApiMsg($chatid, $text);

                    $text = "/closed\n";
                    $text .= "Penyebab : \n";
                    $text .= "Perbaikan : \n";
                    sendApiMsg($chatid, $text);
                    break;

                case $pesan == '/help':
                case $pesan == '/help@mopega_bot':
                    sendApiAction($chatid);

                    $text = "Halo. Kenalin saya MOPEGA BOT, asisten dari Aplikasi MOPEGA. Saya ditugaskan untuk membantu pekerjaan teman-teman teknisi.\n\n";
                    $text .= "📖 Berikut yang bisa saya lakukan :\n\n";
                    $text .= "== Fungsional ==\n";
                    $text .= "/regteknisi untuk registrasi sebagai Teknisi.\n";
                    $text .= "/cekodp #[odp], untuk melihat nomor isi ODP tersebut.\n";
                    $text .= "/otw untuk update progress menjadi On The Way. (Mereply Pesan Order)\n";
                    $text .= "/ogp untuk update progress menjadi On Going Progress. (Mereply Pesan Order)\n";
                    $text .= "/closed untuk update progress menjadi Closed. (Mereply Pesan Order)\n";
                    $text .= "/formatclosed untuk melihat format pesan Closed.\n\n";
                    $text .= "== Non Fungsional ==\n";
                    $text .= "/help untuk melihat informasi bantuan.\n";
                    $text .= "/id untuk melihat informasi ID User Telegram.\n";
                    $text .= "/groupid untuk melihat informasi ID Group Telegram.\n\n";
                    $text .= "😎 Terima Kasih";

                    sendApiMsg($chatid, $text);
                    break;

                default:

                    break;
            }
        }
    }
}
