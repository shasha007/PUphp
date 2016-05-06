<?php


//获取隐私
function get_privacy($privacy) {
	//根据隐私情况，显示隐私
	if($privacy==0){
		//仅主人可见
		return '仅主人可见';
	}else{
		//任何人都可见
		return '任何人都可见';
	}
}

//获取照隐私
function get_privacy_code($privacy) {
	//根据隐私情况，显示相册隐私
	if($privacy==4){
		//持密码可见
		return 'password';
	}elseif($privacy==3){
		//仅主人可见
		return 'self';
	}elseif($privacy==2){
		//仅我关注的人可见
		return 'following';
	}else{
		//任何人都可见
		return 'everyone';
	}
}

//根据存储路径，获取图片真实URL
function get_document_url($savepath) {
	if(strlen($savepath)>0) {
			return PIC_URL.'/data/uploads/' . $savepath;
	} else {
			return null;
	}
}

//获取应用配置参数
function getConfig($key=NULL){
	//$config = model('Xdata')->lget('document');
	$config['document_raws'] || $config['document_raws']=8;
	if($key==NULL){
		return $config;
	}else{
		return $config[$key];	
	}
}

function getDocCategorySelect($pid=0) {
        return json_encode(D('DocCategory')->_makeTree($pid));
}

function getCategorySelect($pid=0) {
        return json_encode(D('Category')->_makeTree($pid));
}


function getDocCategoryName($id) {
        $title = D('DocCategory')->getField('title','id='.$id);
        return $title;
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
