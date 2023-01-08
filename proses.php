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
                if (strtok($pesan, "\n") == '/reqsc' || strtok($pesan, "\n") == '/reqsc ') {
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
                } elseif ($pesan == '/otw') {
                    sendApiAction($chatid);

                    $reply_message  = $message["reply_to_message"]["text"];
                    $reply_message  = preg_replace('/^.+\n/', '', $reply_message);

                    $tiket          = strtok($reply_message, "\n");
                    $datenow        = date('Y-m-d H:i:s');
                    $cekTiket       = mysqli_query($koneksi, "SELECT * FROM tb_gangguan WHERE tiket = '$tiket'");

                    while ($d = mysqli_fetch_array($cekTiket)) {
                        $status     = $d['status'];
                        $teknisi    = $d['teknisi'];
                    }

                    if (mysqli_num_rows($cekTiket) > 0) {
                        if ($fromid == $teknisi) {
                            $cekStatus = mysqli_query($koneksi, "SELECT * FROM tb_gangguan WHERE tiket = '$tiket' AND `status` = '1'");
                            if (mysqli_num_rows($cekStatus) > 0) {
                                $query = mysqli_query($koneksi, "UPDATE tb_gangguan SET `status` = '2', otw_at = '$datenow' WHERE tiket = '$tiket'");
                                if ($query) {
                                    $text = "✅ Tiket $tiket berhasil diupdate ke On The Way lokasi pelanggan.";
                                } else {
                                    $text = "Error. Silahkan coba beberapa saat lagi.\n";
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
                        $status     = $d['status'];
                        $teknisi    = $d['teknisi'];
                    }

                    if (mysqli_num_rows($cekTiket) > 0) {
                        if ($fromid == $teknisi) {
                            $cekStatus = mysqli_query($koneksi, "SELECT * FROM tb_gangguan WHERE tiket = '$tiket' AND `status` = '2'");
                            if (mysqli_num_rows($cekStatus) > 0) {
                                $query = mysqli_query($koneksi, "UPDATE tb_gangguan SET `status` = '3', ogp_at = '$datenow' WHERE tiket = '$tiket'");
                                if ($query) {
                                    $text = "✅ Tiket $tiket berhasil diupdate ke On Going Progress pengerjaan oleh Teknisi.";
                                } else {
                                    $text = "Error. Silahkan coba beberapa saat lagi.\n";
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

                case $pesan == '/help':
                    sendApiAction($chatid);

                    $text = "Halo. Kenalin saya MOPEGA BOT, asisten dari Aplikasi MOPEGA. Saya ditugaskan untuk membantu pekerjaan teman-teman teknisi.\n\n";
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
                case $pesan == '/help@mopega_bot':
                    sendApiAction($chatid);

                    $text = "Halo. Kenalin saya MOPEGA BOT, asisten dari Aplikasi MOPEGA. Saya ditugaskan untuk membantu pekerjaan teman-teman teknisi.\n\n";
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
