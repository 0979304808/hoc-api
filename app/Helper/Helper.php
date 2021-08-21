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




if(!function_exists('curl_post')){
    /**
     * Get data via curl
     *
     * @return object
     */
    function curl_post($url, $data){
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
    if ($a > 104857600){
        unlink(public_path('audio/' . $fullpath));
        return null;
    }
    fclose($fp);
    return public_path('audio/'.$fullpath);
}

// Check dữ liệu và trả về chuỗi
function CheckLevel($str,$result = null) {
    $datas = explode(' ',$str);
    foreach ($datas as $data){
        $Toeic = CheckToefl($data,'Toeic');
        $Toefl = CheckToefl($data,'Toefl');
        $Ielts = CheckToefl($data,'Ielts');
        $result[] = '<span class="'.trim(  ( ($Toefl ? $Toefl : null).($Ielts ? " ".$Ielts : null).( $Toeic ? " ".$Toeic : null ) ) ? ( ($Toefl ? $Toefl : null).($Ielts ? " ".$Ielts : null).( $Toeic ? " ".$Toeic : null ) ) : "unknown" , ' ' ).'">'.$data.'</span>';
    }
    return implode(" ", $result);
}

// Lấy dữ liệu file json và check với preg_match
function CheckToefl($data,$file){
    $path = storage_path() . "/data/".$file.".json";
    $json = json_decode(file_get_contents($path));
    foreach ($json as $key=>$value){
        if ($key){
            $key = checkString($key);
            if (preg_match("/\b(" . $key. ")\b/i",strtolower($data))){
                return $file.'-'.$value;
            }
        }
    }
    return null;
}

// Xóa các ký tự đặc biệt
function checkString($string)
{
    return preg_replace('/([^\pL\.\ ]+)/u', '', strip_tags($string));
}


// Level
function Level($data,$file){
    $path = storage_path() . "/data/".$file.".json";
    $json = json_decode(file_get_contents($path));
    $result = null ;
    $result1 = [];
    $result2 = [];
    $result3 = [];
    $result4 = [];
    foreach ($json as $key=>$value){
        if ($key){
            $key = checkString($key);
            if (preg_match("/\b(" . $key. ")\b/i",strtolower(checkString($data)))){
                if ($value == 1){
                    $result1[] = $key;
                }
                if ($value == 2){
                    $result2[] = $key;
                }
                if ($value == 3){
                    $result3[] = $key;
                }
                if ($value == 4){
                    $result4[] = $key;
                }
                $result = [ '1' => $result1,$result2,$result3,$result4 ];
            }
        }
    }
    return $result;
}
