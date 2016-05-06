<?php

//根据存储路径，获取图片真实URL
function getThumb($filename,$width=190,$height=240,$t='f') {
    if(empty($filename)){
        $thumb = SITE_URL . '/apps/event/Tpl/default/Public/images/user_pic_big.gif';
    }else{
        if(strpos($filename, '/')){
            $thumb = tsMakeThumbUp($filename,$width,$height,$t);
        }else{
            $thumb = tsMakeThumbUp('event/'.$filename,$width,$height,$t);
        }
    }
    return $thumb;
}

function realityImageURL($img) {
    $imgURL = sprintf('%s/apps/hold/Tpl/default/Public/hold/%s', SITE_URL, $img); //默认的礼物图片地址
    if (file_exists(sprintf('./apps/hold/Tpl/default/Public/hold/%s', $img))) {
        return $imgURL;
    } else {//若默认里没有则返回自定义的礼物图片地址
        return sprintf('%s/data/uploads/hold/%s', SITE_URL, $img);
    }
}

function get_flash_url($host, $flashvar) {
    $flashAddr = array(
        'youku.com' => 'http://player.youku.com/player.php/sid/FLASHVAR/v.swf',
        'ku6.com' => 'http://player.ku6.com/refer/FLASHVAR/v.swf',
        //'sina.com.cn' => 'http://vhead.blog.sina.com.cn/player/outer_player.swf?vid=FLASHVAR',
        'sina.com.cn' => 'http://you.video.sina.com.cn/api/sinawebApi/outplayrefer.php/vid=FLASHVAR/s.swf',
        //'tudou.com' => 'http://www.tudou.com/v/FLASHVAR',
        //'tudou.com' => 'http://www.tudou.com/v/FLASHVAR/&autoPlay=true/v.swf',
        //'youtube.com' => 'http://www.youtube.com/v/FLASHVAR',
        //'sohu.com' => 'http://v.blog.sohu.com/fo/v4/FLASHVAR',
        //'sohu.com' => 'http://share.vrs.sohu.com/FLASHVAR/v.swf',
        //'mofile.com' => 'http://tv.mofile.com/cn/xplayer.swf?v=FLASHVAR',
        'music' => 'FLASHVAR',
        'flash' => 'FLASHVAR'
    );
    $result = '';
    if (isset($flashAddr[$host])) {
        $result = str_replace('FLASHVAR', $flashvar, $flashAddr[$host]);
    }
    return $result;
}

function noContentImg($img) {
    if (!$img) {
        return __THEME__ . '/images/nocontent.png';
    } else {
        return $img;
    }
}

?>
