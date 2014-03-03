<?php
/**
 * 图像文件读取
 */

/**
 * 引入文件
 */
$project_cfg_arr = require_once(G_COMBO_PATH.'config/image_project.conf.php');


/**
 * 初始化
 */
$url_param = combo_unparam($request_uri_arr[3]);

$layout = (string)$url_param['layout'];
if ($layout != 'horizontal') {
    $layout = 'vertical';
}

$image_fragments = array(); // 图像碎片
$new_image_max_width = 0; // 画板最大宽度
$new_image_max_height = 0; // 画板最大高度


/**
 * 数据处理
 */
foreach ($files_arr as $key=>$temp_file_path) {
    // 获取文件类型，检查其他文件是否一样
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

    switch ($file_extension) {
        case 'png':
            $image = imagecreatefrompng($temp_file_full_path);

            // 是否真彩色图像
            if (!isset($is_true_color)) {
                $is_true_color = imageistruecolor($image);
            }
            break;
        case 'gif':
            $image = imagecreatefromgif($temp_file_full_path);

            if (!isset($is_true_color)) {
                $is_true_color = false;
            }
            break;
        case 'jpg':
            $image = imagecreatefromjpeg($temp_file_full_path);
            break;
    }

    if (!$image) {
        combo_echo_error_header(404);
    }

    $image_size = getimagesize($temp_file_full_path);

    $image_fragments[] = array(
        'identifier' => $image,
        'size' => array('w' => (int)$image_size[0], 'h' => (int)$image_size[1])
    );

    // 排列方式计算画板总宽高
    if ($layout == 'horizontal') {
        if ((int)$image_size[1] > $new_image_max_height) {
            $new_image_max_height = (int)$image_size[1];
        }
        $new_image_max_width += (int)$image_size[0];
    } else {
        if ((int)$image_size[0] > $new_image_max_width) {
            $new_image_max_width = (int)$image_size[0];
        }
        $new_image_max_height += (int)$image_size[1];
    }

    $last_modified_time = max((int)$last_modified_time, filemtime($temp_file_full_path));
}

// 创建画板
if ($is_true_color === false) {
    $new_image = imagecreate($new_image_max_width, $new_image_max_height);
} else {
    $new_image = imagecreatetruecolor($new_image_max_width, $new_image_max_height);
}

switch ($file_extension) {
    case 'png':
        imagesavealpha($new_image, true); // 设置保存PNG时保留透明通道信息
        imagealphablending($new_image, false); // 关闭混合模式，以便透明颜色能覆盖原画布

        $trans_colour = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
        imagefill($new_image, 0, 0, $trans_colour);
        break;
    case 'gif':
        $white = imagecolorallocate($new_image, 255, 255, 255);
        imagefilledrectangle($new_image, 0, 0, $new_image_max_width, $new_image_max_height, $white);
        imagecolortransparent($new_image, $white);
        break;
    case 'jpg':
        $bg_colour = imagecolorallocate($new_image, 255, 255, 255);
        imagefill($new_image, 0, 0, $bg_colour);
        break;
}


$dst_x = 0;
$dst_y = 0;
foreach ($image_fragments as $val) {
    imagecopy(
        $new_image, $val['identifier'],
        $dst_x, $dst_y,
        0, 0,
        $val['size']['w'], $val['size']['h']
    );
    imagedestroy($val['identifier']); // 释放关联内存

    // 根据排列方式计算偏移值
    if ($layout == 'horizontal') {
        $dst_x += $val['size']['w'];
    } else {
        $dst_y += $val['size']['h'];
    }
}


// 输出图像
ob_start();
switch ($file_extension) {
    case 'png':
        imagepng($new_image);
        break;
    case 'gif':
        imagegif($new_image);
        break;
    case 'jpg':
        imagejpeg($new_image, NULL, 100);
        break;
}
$contents = ob_get_contents();
ob_end_clean();


imagedestroy($new_image); // 释放关联内存
?>
