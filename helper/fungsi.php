<?php


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
 *  File        : fungsi.php
 *  Tujuan      : Untuk menjalankan fungsi telegram
 *  ____________________________________________________________
*/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'PHPMailer/src/Exception.php';
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';

function myPre($value)
{
    echo '<pre>';
    print_r($value);
    echo '</pre>';
}

function apiRequest($method, $data)
{
    if (!is_string($method)) {
        error_log("Nama method harus bertipe string!\n");

        return false;
    }

    if (!$data) {
        $data = [];
    } elseif (!is_array($data)) {
        error_log("Data harus bertipe array\n");

        return false;
    }


    $url = 'https://api.telegram.org/bot' . $GLOBALS['token'] . '/' . $method;

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ],
    ];
    $context = stream_context_create($options);

    $result = file_get_contents($url, false, $context);

    return $result;
}

function getApiUpdate($offset)
{
    $method = 'getUpdates';
    $data['offset'] = $offset;

    $result = apiRequest($method, $data);

    $result = json_decode($result, true);
    if ($result['ok'] == 1) {
        return $result['result'];
    }

    return [];
}

function sendApiMsg($chatid, $text, $msg_reply_id = false, $parse_mode = false, $disablepreview = false)
{
    $method = 'sendMessage';
    $data = ['chat_id' => $chatid, 'text'  => $text];
    if ($msg_reply_id) {
        $data['reply_to_message_id'] = $msg_reply_id;
    }
    if ($parse_mode) {
        $data['parse_mode'] = $parse_mode;
    }
    if ($disablepreview) {
        $data['disable_web_page_preview'] = $disablepreview;
    }

    $result = apiRequest($method, $data);
}

function sendApiImg($chat_id, $text, $img)
{
    $bot_url    = "https://api.telegram.org/bot889705435:AAHn3CpJQLhGktaxJUGwZol1kbTVTMQa6qs/";
    $url        = $bot_url . "sendPhoto?chat_id=" . $chat_id;
    $post_fields = array(
        'chat_id' => $chat_id,
        'caption' => 'Last Update ' . $text . ' on ' . date('Y-m-d H:i:s') . '',
        'photo'   => new CURLFile(realpath("/home/jarvisid/newjarvis.jarvisid.com/tmp/" . $img . ".png"))
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type:multipart/form-data"
    ));
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    $output = curl_exec($ch);
}

function sendApiMsgReply($chatid, $text, $msg_reply_id = false, $parse_mode = false, $disablepreview = false, $replym = true)
{
    $method = 'sendMessage';
    $data = ['chat_id' => $chatid, 'text'  => $text];
    $replyMarkup = ['force_reply' => true];

    if ($msg_reply_id) {
        $data['reply_to_message_id'] = $msg_reply_id;
    }
    if ($parse_mode) {
        $data['parse_mode'] = $parse_mode;
    }
    if ($disablepreview) {
        $data['disable_web_page_preview'] = $disablepreview;
    }
    if ($replym) {
        $data['reply_markup'] = json_encode($replyMarkup);
    }

    $result = apiRequest($method, $data);
}

function sendApiAction($chatid, $action = 'typing')
{
    $method = 'sendChatAction';
    $data = [
        'chat_id' => $chatid,
        'action'  => $action,

    ];
    $result = apiRequest($method, $data);
}

function sendApiKeyboard($chatid, $text, $keyboard = [], $inline = false)
{
    $method = 'sendMessage';
    $replyMarkup = [
        'keyboard'        => $keyboard,
        'resize_keyboard' => true,
    ];

    $data = [
        'chat_id'    => $chatid,
        'text'       => $text,
        'parse_mode' => 'Markdown',

    ];

    $inline
        ? $data['reply_markup'] = json_encode(['inline_keyboard' => $keyboard])
        : $data['reply_markup'] = json_encode($replyMarkup);

    $result = apiRequest($method, $data);
}

function editMessageText($chatid, $message_id, $text, $keyboard = [], $inline = false)
{
    $method = 'editMessageText';
    $replyMarkup = [
        'keyboard'        => $keyboard,
        'resize_keyboard' => true,
    ];

    $data = [
        'chat_id'    => $chatid,
        'message_id' => $message_id,
        'text'       => $text,
        'parse_mode' => 'Markdown',

    ];

    $inline
        ? $data['reply_markup'] = json_encode(['inline_keyboard' => $keyboard])
        : $data['reply_markup'] = json_encode($replyMarkup);

    $result = apiRequest($method, $data);
}

function sendApiHideKeyboard($chatid, $text)
{
    $method = 'sendMessage';
    $data = [
        'chat_id'       => $chatid,
        'text'          => $text,
        'parse_mode'    => 'Markdown',
        'reply_markup'  => json_encode(['hide_keyboard' => true]),

    ];

    $result = apiRequest($method, $data);
}

function sendApiSticker($chatid, $sticker, $msg_reply_id = false)
{
    $method = 'sendSticker';
    $data = [
        'chat_id'  => $chatid,
        'sticker'  => $sticker,
    ];

    if ($msg_reply_id) {
        $data['reply_to_message_id'] = $msg_reply_id;
    }

    $result = apiRequest($method, $data);
}

function strposa($haystack, $needle, $offset = 0)
{
    if (!is_array($needle)) $needle = array($needle);
    foreach ($needle as $query) {
        if (strpos($haystack, $query, $offset) !== false) return true;
    }
    return false;
}

function tgl_indo($tanggal)
{
    $bulan = array(
        1 =>   'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    );
    $pecahkan = explode('-', $tanggal);

    return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
}

function statusOrder($status)
{
    switch ($status) {
        case '0':
            return "Wait Order";
            break;

        case '1':
            return "Ordered";
            break;

        case '2':
            return "On The Way";
            break;

        case '3':
            return "On Going Progress";
            break;

        default:
            return "Closed";
            break;
    }
}

function notifEmail($pelanggan, $gangguan)
{
    $mail = new PHPMailer(true);

    try {
        //Server settings
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->isSMTP();
        $mail->Host       = 'mail.mopega.my.id';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'admin@mopega.my.id';
        $mail->Password   = '^Mopega*';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        //Recipients
        $mail->setFrom('admin@mopega.my.id', 'Admin Mopega');
        $mail->addAddress($pelanggan['email'], $pelanggan['nama_pelanggan']);

        $nama_pelanggan = $pelanggan['nama_pelanggan'];
        $tiket          = $gangguan['tiket'];
        $keterangan     = $gangguan['ket'];
        $tgl_lapor      = $gangguan['report_date'];

        $html = 'Hai, ' . $nama_pelanggan . '. <br/><br/>';
        $html .= 'Berikut Tiket Gangguan yang sudah dilaporkan, <br/>';
        $html .= '<b>Tiket</b> : ' . $tiket . ' <br/>';
        $html .= '<b>Keterangan</b> : ' . $keterangan . ' <br/>';
        $html .= '<b>Tanggal Lapor</b> : ' . $tgl_lapor . ' <br/><br/>';
        $html .= '<b>Status</b> : SELESAI DIKERJAKAN <br/><br/>';
        $html .= 'Silahkan bisa cek nomor untuk mengetahui riwayat Gangguan yang pernah dilaporkan, di <a href="https://mopega.my.id/web/ceknomor">Cek Nomor</a>. <br/><br/>';
        $html .= 'Terimakasih.';

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'Tiket Gangguan Selesai Dikerjakan';
        $mail->Body    = $html;
        $mail->AltBody = `Berikut Tiket Gangguan $tiket, dengan keluhan "$keterangan" yang dilaporkan pada tanggal $tgl_lapor selesai dikerjakan.`;

        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

function testNotifEmail()
{
    $mail = new PHPMailer(true);

    try {
        //Server settings
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->isSMTP();
        $mail->Host       = 'mail.mopega.my.id';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'admin@mopega.my.id';
        $mail->Password   = '^Mopega*';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        //Recipients
        $mail->setFrom('admin@mopega.my.id', 'Admin Mopega');
        $mail->addAddress('nurilmuslichin16@gmail.com', 'Nuril Muslichin');

        $html = 'Hai, Nuril Muslichin. <br/><br/>';
        $html .= 'Berikut Tiket Gangguang yang sudah dilaporkan, <br/>';
        $html .= '<b>Tiket</b> : IN3452 <br/>';
        $html .= '<b>Keterangan</b> : ONT Mati tidak bisa koneksi internet <br/>';
        $html .= '<b>Tanggal Lapor</b> : 2023-07-16 <br/><br/>';
        $html .= 'Silahkan bisa cek berkala untuk mengetahui status Gangguan secara realtime, di <a href="https://mopega.my.id/web/track">Tracking Tiket Gangguan</a>. <br/><br/>';
        $html .= 'Terimakasih.';

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'Tiket Gangguan Berhasil Dibuat';
        $mail->Body    = $html;
        $mail->AltBody = `Berikut Tiket Gangguan IN3452, dengan keluhan "ONT Mati tidak bisa koneksi internet" yang dilaporkan pada tanggal 2023-07-16`;

        $mail->send();
        echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
