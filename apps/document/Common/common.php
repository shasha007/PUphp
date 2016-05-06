<?php

function get_cover($savepath) {
	if(strlen(trim($savepath))==0) {
		return SITE_URL . '/apps/document/Tpl/default/Public/images/cover.jpg';
	}
	return PIC_URL.'/data/uploads/' . $savepath;
}


function get_document_url($savepath) {
	return UPLOAD_PATH . '/' . $savepath;
}

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

//获取应用配置参数
function getConfig($key=NULL){
	$config = model('Xdata')->lget('document');
	$config['document_raws'] || $config['document_raws']=10;
	($config['document_max_size']=floatval($config['document_max_size'])*1024*1024) || $config['document_max_size']=-1;
	$config['document_file_ext'] || $config['document_file_ext']='doc,docx,ppt,pptx,pdf';
	$config['max_document_num'] || $config['max_document_num']=0;
	$config['allow_word_pre'] || $config['allow_word_pre']=0;
	if($key==NULL){
		return $config;
	}else{
		return $config[$key];
	}
}


function getCategorySelect($pid=0) {
        return json_encode(D('Category')->_makeTree($pid));
}

function getSchoolSelect($pid=0) {
        return json_encode(model('Schools')->_makeTree($pid));
}
function getSchoolName($id) {
		if($id==0) return "";
        $title = model('Schools')->getField('title','id='.$id);
        return $title;
}
function getCategoryName($id) {
		if($id==0) return "";
        $title = D('Category')->getField('title','id='.$id);
        return $title;
}
function getCategoryTree($id,$onlyShowPid=false) {
	$tree	=	D('Category')->_makeParentTree($id,$onlyShowPid);
	return $tree;
}

function getSize($bytes) {

	        if ($bytes >= 1073741824)
	        {
	            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
	        }
	        elseif ($bytes >= 1048576)
	        {
	            $bytes = number_format($bytes / 1048576, 2) . ' MB';
	        }
	        elseif ($bytes >= 1024)
	        {
	            $bytes = number_format($bytes / 1024, 2) . ' KB';
	        }
	        elseif ($bytes > 1)
	        {
	            $bytes = $bytes . ' bytes';
	        }
	        elseif ($bytes == 1)
	        {
	            $bytes = $bytes . ' byte';
	        }
	        else
	        {
	            $bytes = '0 bytes';
	        }

	        return $bytes;

}

?>
