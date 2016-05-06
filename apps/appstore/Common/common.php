<?php

//获取应用配置参数
function getConfig($key=NULL){
	$config = model('Xdata')->lget('appstore');
	$config['item_raws'] || $config['item_raws']=10;
	if($key==NULL){
		return $config;
	}else{
		return $config[$key];	
	}
}

//根据存储路径，获取图片真实URL
function get_document_url($savepath) {
	if(strlen($savepath)>0) {
			return PIC_URL.'/data/uploads/' . $savepath;
	} else {
			return SITE_URL . '/apps/appstore/Appinfo/ico_app_large.png';
	}
}
function get_image_url($savepath) {
	if(strlen(trim($savepath))>0) {

			return '<img src="'.PIC_URL.'/data/uploads/' . $savepath.'" style="width:25px;height:25px" valign="middle"/>';
			
	}
	return "";
}

function get_image($savepath, $url) {
	if(strlen(trim($savepath))>0) {
		if(strlen(trim($url))>0) {
			return '<a href="'.$url.'"><img src="'.PIC_URL.'/data/uploads/' . $savepath.'" /></a>';
		} else {
			return '<img src="'.PIC_URL.'/data/uploads/' . $savepath.'" />';
		}
	}
	return "";
}

function get_link($savepath, $url, $style="") {
	if(strlen(trim($savepath))>0) {
		if(strlen(trim($url))>0) {
			return '<a href="'.$url.'" target="_blank"><img src="'.PIC_URL.'/data/uploads/' . $savepath.'" '.$style.'/><i></i></a>';
		} else {
			return '<a href="javascript:void(0)"><img src="'.PIC_URL.'/data/uploads/' . $savepath.'" '.$style.'/><i></i></a>';
		}
	}
	return "";
}

//获取群组分类
function getCategorySelect($pid=0, $type=1) {
        return json_encode(D('Category')->_makeTree($pid, $type));
}

//获取分类名称
function getCategoryName($id) {
        $title = D('Category')->getField('title','id='.$id);
        return $title;
}

function getCategoryTree($id,$onlyShowPid=false) {
	$tree	=	D('Category')->_makeParentTree($id,$onlyShowPid);
	return $tree;
}

?>
