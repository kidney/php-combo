<?php

function combo_echo_error_header($http_code) {
    $http_code = (int)$http_code;

    switch ($http_code) {
        case 400:
            header('HTTP/1.0 400 Bad Request');
            $title = '400 Bad Request';
            break;
        case 403:
            header('HTTP/1.0 403 Forbidden');
            $title = '403 Forbidden';
            break;
        case 404:
            header('HTTP/1.0 404 Not Found');
            $title = '404 Not Found';
            break;
        default:
            echo '';
            exit();
            break;
    }

    $html = '<!DOCTYPE HTML><html><head><title>'.$title.'</title></head><body><h1>'.$title.'</h1></body></html>';
    echo $html;
    exit();
}

function combo_unparam($str) {
    $param_arr = explode('&', $str);

    $param_result = array();
    foreach ($param_arr as $param_info) {
        $tmp_arr = explode('=', $param_info);

        $param_result[$tmp_arr[0]] = $tmp_arr[1];
    }

    return $param_result;
}

function combo_get_file_extension($str) {
    if (strstr($str, '.map')) {
        $file_extension = 'map';
    } elseif (strstr($str, '.js')) {
        $file_extension = 'js';
    } elseif (strstr($str, '.css')) {
        $file_extension = 'css';
    } elseif (strstr($str, '.png')) {
        $file_extension = 'png';
    } elseif (strstr($str, '.gif')) {
        $file_extension = 'gif';
    } elseif (strstr($str, '.jpg')) {
        $file_extension = 'jpg';
    } else {
        $strSplit = explode('.', $str);
        $file_extension = end($strSplit);
    }

    return $file_extension;
}
?>