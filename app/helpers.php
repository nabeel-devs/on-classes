<?php

use Illuminate\Support\Facades\Auth;

if (!function_exists('jsonResponse')) {

    function jsonResponse(bool $success, $data = null, $message = null, int $statusCode = 200)
    {
        $response = [
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ];

        return response()->json($response, $statusCode);
    }
}


if (!function_exists('_user')) {

    function _user()
    {
        return Auth::user();
    }
}
