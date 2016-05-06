<?php

//根据存储路径，获取图片真实URL
function get_coupon_url($savepath) {
    if (strlen($savepath) > 0) {
        return PIC_URL.'/data/uploads/' . $savepath;
    } else {
        return SITE_URL . '/apps/coupon/Appinfo/ico_app_large.gif';
    }
}

function realityImageURL($img) {
    $imgURL = sprintf('%s/apps/coupon/Tpl/default/Public/coupon/%s', SITE_URL, $img); //默认的礼物图片地址
    if (file_exists(sprintf('./apps/coupon/Tpl/default/Public/coupon/%s', $img))) {
        return $imgURL;
    } else {//若默认里没有则返回自定义的礼物图片地址
        return sprintf('%s/data/uploads/coupon/%s', SITE_URL, $img);
    }
}

?>
