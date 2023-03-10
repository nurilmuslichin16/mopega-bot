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
