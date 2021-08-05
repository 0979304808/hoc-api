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