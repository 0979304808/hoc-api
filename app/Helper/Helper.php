<?php

use Illuminate\Support\Facades\File;

function success($data, $code = 200)
{
    return response([
        'status' => $code,
        'data' => $data
    ]);
}

function error($message, $code)
{
    return response([
        'error' => true,
        'status' => $code,
        'message' => $message
    ], $code);
}

function responseMsg($message, $code = 200)
{
    return response($message, $code);
}

// NoSql
function nosql($table, $method, $documentID = null, $payload = null)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:5984/' . $table . '/' . $documentID);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($payload != null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    }
    curl_setopt($ch, CURLOPT_USERPWD, 'admin:hoanghung1');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-type: application/json',
        'Accept: */*'
    ));

    $response = curl_exec($ch);

    curl_close($ch);

    return $response;
}


if (!function_exists('curl_post')) {
    /**
     * Get data via curl
     *
     * @return object
     */
    function curl_post($url, $data)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => urldecode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
}


// Lưu Audio
function audio($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);
    curl_close($ch);

    $fullpath = basename($url);

    if (File::exists(public_path('audio/' . $fullpath))) {
        unlink(public_path('audio/' . $fullpath));
    }
    $fp = fopen(public_path('audio/' . $fullpath), 'x');
    $a = fwrite($fp, $result);
    if ($a > 104857600) {
        unlink(public_path('audio/' . $fullpath));
        return null;
    }
    fclose($fp);
    return public_path('audio/' . $fullpath);
}

// Check dữ liệu và trả về chuỗi
function CheckLevel($str)
{
    $text = '';
    $datas = explode(' ', $str);
    foreach ($datas as $key => $data) {
        $dataCheck = hand_trim($data);
        $Toeic = CheckToefl($data, 'Toeic');
        $Toefl = CheckToefl($data, 'Toefl');
        $Ielts = CheckToefl($data, 'Ielts');
        $html_span = '<span class="' . trim((($Toefl ? $Toefl : null) . ($Ielts ? " " . $Ielts : null) . ($Toeic ? " " . $Toeic : null)) ? (($Toefl ? $Toefl : null) . ($Ielts ? " " . $Ielts : null) . ($Toeic ? " " . $Toeic : null)) : "unknown", ' ') . '">' . $dataCheck . '</span>';
        $word = str_replace($dataCheck, $html_span, $data);
        $text .= " " . $word;
    }
    return $text;
}

// Lấy dữ liệu file json và check với preg_match
function CheckToefl($data, $file)
{
    if ($file === "Toeic") {
        $json = JsonDataToeic();
    }
    if ($file === "Toefl") {
        $json = JsonDataToefl();
    }
    if ($file === "Ielts") {
        $json = JsonDataIelts();
    }
    foreach ($json as $key => $value) {
        if ($key) {
            $key = checkString($key);
            if (preg_match("/\b(" . $key . ")\b/i", strtolower($data))) {
                return $file . '-' . $value;
            }
        }
    }
    return null;
}

function JsonDataToeic()
{
    $json = File::get(storage_path() . "/data/Toeic.json");
    return json_decode($json);
}

function JsonDataToefl()
{
    $json = File::get(storage_path() . "/data/Toefl.json");
    return json_decode($json);
}

function JsonDataIelts()
{
    $json = File::get(storage_path() . "/data/Ielts.json");
    return json_decode($json);
}

// Xóa các ký tự đặc biệt
function checkString($string)
{
    return preg_replace('/([^\pL\.\ ]+)/u', '', strip_tags($string));
}


// Level
function Level($data, $file)
{
    if ($file === "Toeic") {
        $json = JsonDataToeic();
    }
    if ($file === "Toefl") {
        $json = JsonDataToefl();
    }
    if ($file === "Ielts") {
        $json = JsonDataIelts();
    }
    $result = [];
    $result1 = [];
    $result2 = [];
    $result3 = [];
    $result4 = [];
    foreach ($json as $key => $value) {
        if ($key) {
            $key = checkString($key);
            if (preg_match("/\b(" . $key . ")\b/i", strtolower(checkString($data)))) {
                if ($value == 1) {
                    array_push($result1, $key);
                }
                if ($value == 2) {
                    array_push($result2, $key);
                }
                if ($value == 3) {
                    array_push($result3, $key);
                }
                if ($value == 4) {
                    array_push($result4, $key);
                }
                $result = ['1' => $result1, $result2, $result3, $result4];
            }
        }
    }
    return $result;
}

function CheckData($key, $data)
{
    preg_match("/\b(" . $key . ")\b/i", strtolower(checkString($data)), $matches);
    return $matches;
}

// Loại bỏ hết ký tự đặc biệt
function hand_trim($str)
{
    $pattern = '/[^a-zA-Z0-9]+/';
    $check = preg_match_all($pattern, $str, $mt);
    if ($check) {
        foreach ($mt[0] as $trim_str) {
            $str = trim($str, addcslashes($trim_str, '.'));
        }
    }
    return $str;
}
