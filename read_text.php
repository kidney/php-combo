<?php
/**
 * �ı��ļ���ȡ
 */

/**
 * �����ļ�
 */
$config_file_name = ($file_extension == 'js') ? 'script' : 'css';
$project_cfg_arr = require_once(G_COMBO_PATH.'config/'.$config_file_name.'_project.conf.php');


/**
 * ��ʼ��
 */


/**
 * ���ݴ���
 */
foreach ($files_arr as $key=>$temp_file_path) {
    // ��ȡ�ļ����ͣ�����Ƿ�һ��
    if ($key > 0) {
        $tmp_file_extension = combo_get_file_extension($temp_file_path);
        if ($tmp_file_extension != $file_extension) {
            combo_echo_error_header(400);
        }
    }

    if ($script_url) { // �Ƿ���ָ������Ŀ¼
        $temp_file_path = $script_url.$temp_file_path;
    }

    $temp_fragment_arr = explode('/', $temp_file_path);
    if (!isset($temp_fragment_arr[1])) {
        combo_echo_error_header(404);
    }

    // ��һλ����Ŀ¼����
    $temp_fragment_arr[0] = $project_cfg_arr[$temp_fragment_arr[0]]['path'];
    $temp_file_full_path = join('/', $temp_fragment_arr);

    $tmp_content = file_get_contents($temp_file_full_path);
    if (!$tmp_content) {
        combo_echo_error_header(404);
    }
    $result_arr[] = $tmp_content;

    $last_modified_time = max((int)$last_modified_time, filemtime($temp_file_full_path));
}

$contents = "/*! cache: ".date('Y-m-d H:i:s')." */\n"; // ���Ӹ�cache��ʱ��
$contents .= join("\n", $result_arr);
?>