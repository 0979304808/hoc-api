<?php

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
