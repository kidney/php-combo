<?php
/**
 * 文本文件读取
 */

/**
 * 引入文件
 */
$config_file_name = ($file_extension == 'js') ? 'script' : 'css';
$project_cfg_arr = require_once(G_COMBO_PATH.'config/'.$config_file_name.'_project.conf.php');


/**
 * 初始化
 */


/**
 * 数据处理
 */
foreach ($files_arr as $key=>$temp_file_path) {
    // 获取文件类型，检查是否一样
    if ($key > 0) {
        $tmp_file_extension = combo_get_file_extension($temp_file_path);
        if ($tmp_file_extension != $file_extension) {
            combo_echo_error_header(400);
        }
    }

    if ($script_url) { // 是否已指定父级目录
        $temp_file_path = $script_url.$temp_file_path;
    }

    $temp_fragment_arr = explode('/', $temp_file_path);
    if (!isset($temp_fragment_arr[1])) {
        combo_echo_error_header(404);
    }

    // 第一位进行目录对配
    $temp_fragment_arr[0] = $project_cfg_arr[$temp_fragment_arr[0]]['path'];
    $temp_file_full_path = join('/', $temp_fragment_arr);

    $tmp_content = file_get_contents($temp_file_full_path);
    if (!$tmp_content) {
        combo_echo_error_header(404);
    }
    $result_arr[] = $tmp_content;

    $last_modified_time = max((int)$last_modified_time, filemtime($temp_file_full_path));
}

$contents = "/*! cache: ".date('Y-m-d H:i:s')." */\n"; // 增加个cache的时间
$contents .= join("\n", $result_arr);
?>