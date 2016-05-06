<?php

function getPhotoConfig($key = NULL) {
    $config = model('Xdata')->lget('photo');
    $config['album_raws'] || $config['album_raws'] = 6;
    $config['photo_raws'] || $config['photo_raws'] = 8;
    $config['photo_preview'] == 0 || $config['photo_preview'] = 1;
    ($config['photo_max_size'] = floatval($config['photo_max_size']) * 1024 * 1024) || $config['photo_max_size'] = -1;
    $config['photo_file_ext'] || $config['photo_file_ext'] = 'jpeg,gif,jpg,png';
    $config['max_flash_upload_num'] || $config['max_flash_upload_num'] = 10;
    $config['open_watermark'] == 0 || $config['open_watermark'] = 1;
    $config['watermark_file'] || $config['watermark_file'] = 'public/images/watermark.png';
    if ($key == NULL) {
        return $config;
    } else {
        return $config[$key];
    }
}

function getArea($id){
   return  D('TravelArea','travel')->getAreaName($id);
}
function getCat($id){
   return  M('travel_cat')->getField('name','id='.$id);
}

?>
