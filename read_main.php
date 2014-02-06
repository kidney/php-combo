<?php
/**
 * combo�ļ���ȡ
 */
/**
 * ���峣��
 */
define('G_FILE_CACHE_DIST_PATH', './cache/');
error_reporting(0);


/**
 * �����ļ�
 */
require_once('./include/combo_func_common.inc.php');


/**
 * ��ʼ��
 */
$G_HTTP_HEADER_CONTENT_TYPE_CFG = array(
    'js' => 'application/x-javascript',
    'map' => 'text/plain',
    'css' => 'text/css',

    'png' => 'image/png',
    'gif' => 'image/gif',
    'jpg' => 'image/jpeg',

    'html' => 'text/html'
);
$G_CACHE_LIFE_CYCLE = 3600 * 24 * 365; // ������������

$script_url = ltrim($_SERVER['SCRIPT_URL'], '/');
$request_uri = $_SERVER['REQUEST_URI'];

if ($script_url == '' && $request_uri == '/') {
    combo_echo_error_header(403);
}

if (preg_match('/[^a-zA-Z0-9\.\,\?\=\-\_\/~&]/', $request_uri)) {
    combo_echo_error_header(404);
}


/**
 * ���ݴ���
 */
/**
 * ���ļ�ʱ, ��ַ��ʽת��, ��:
 * http://example.com/utility/jquery/1.8.2/jquery.js
 *  =>
 * http://example.com/??utility/jquery/1.8.2/jquery.js
 */
if (!strstr($request_uri, '??')) {
    $script_url = '';
    $request_uri = '/??'.ltrim($request_uri, '/');
}

$request_uri_arr = explode('?', $request_uri);

// �������ͷ�Ƿ�����ѹ��
$http_accept_encoding = $_SERVER['HTTP_ACCEPT_ENCODING'];
$gzip = strstr($http_accept_encoding, 'gzip');
$deflate = strstr($http_accept_encoding, 'deflate');
$encoding_type = $gzip ? 'gzip' : ($deflate ? 'deflate' : 'none');


$file_name_hash = md5($request_uri_arr[0].'??'.$request_uri_arr[2]);
$file_ver_hash = md5($request_uri_arr[3]); // ��ʱ���ܻ���null

$files_arr = explode(',', $request_uri_arr[2]); // ���ÿ���ļ�, ����ѭ����ȡ

// ��ȡ�ļ�����
$file_extension = combo_get_file_extension($files_arr[0]);

if (!isset($G_HTTP_HEADER_CONTENT_TYPE_CFG[$file_extension])) {
    $file_extension = 'html';
}

$cache_file_full_path = G_FILE_CACHE_DIST_PATH.$file_name_hash.'/'.$file_ver_hash;
$cache_filemtime = filemtime($cache_file_full_path);

// �ļ�����
if ($cache_filemtime !== false) {
    // �ͻ����ѻ���, ֱ�ӷ���304
    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
        strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $cache_filemtime) {

        header('HTTP/1.0 304 Not Modified');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', $cache_filemtime).' GMT');
        header('Expires: '.gmdate('D, j M Y H:i:s', (time() + $G_CACHE_LIFE_CYCLE)).' GMT');
        header('Cache-Control: max-age='.$G_CACHE_LIFE_CYCLE);
        exit();
    }

    // �ͻ����޻���, ��ȡ����
    if ($fp = fopen($cache_file_full_path, 'rb')) {
        header('HTTP/1.0 200 OK');
        header('Cache-Control: max-age='.$G_CACHE_LIFE_CYCLE);
        if ($encoding_type != 'none') {
            header('Content-Encoding: '.$encoding_type);
        }
        header('Expires: '.gmdate('D, j M Y H:i:s', (time() + $G_CACHE_LIFE_CYCLE)).' GMT');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', $cache_filemtime).' GMT');

        header('Content-Type: '.$G_HTTP_HEADER_CONTENT_TYPE_CFG[$file_extension]);

        // see: http://www.raditha.com/wiki/Readfile_vs_include
        fpassthru($fp);
        fclose($fp);
        exit();
    } else {
        combo_echo_error_header(400);
    }
}


// û���ҵ��ļ�, �ܴ�������
switch ($file_extension) {
    case 'js':
    case 'css':
        include_once('./read_text.php');
        break;
    case 'png':
    case 'gif':
    case 'jpg':
        include_once('./read_image.php');
        break;
    default:
        combo_echo_error_header(403);
        break;
}

header('HTTP/1.0 200 OK');
header('Cache-Control: max-age='.$G_CACHE_LIFE_CYCLE);
if ($encoding_type != 'none') {
    header('Content-Encoding: '.$encoding_type);
}
header('Expires: '.gmdate('D, j M Y H:i:s', (time() + $G_CACHE_LIFE_CYCLE)).' GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s', $last_modified_time).' GMT');
header('Content-Type: '.$G_HTTP_HEADER_CONTENT_TYPE_CFG[$file_extension]);

if ($encoding_type != 'none') {
    $contents = gzencode($contents, 9, $encoding_type == 'gzip' ? FORCE_GZIP : FORCE_DEFLATE);
}

echo $contents;
//echo '/* '.$cache_file_full_path.' */'; // debug path

// �ļ��м��
if (!is_dir(G_FILE_CACHE_DIST_PATH.$file_name_hash)) {
    mkdir(G_FILE_CACHE_DIST_PATH.$file_name_hash, 0777);
}

// д���ļ��������ļ��޸�ʱ��
if ($fp = fopen($cache_file_full_path, 'wb')) {
    fwrite($fp, $contents);
    fclose($fp);
    touch($cache_file_full_path, $last_modified_time);
}
?>