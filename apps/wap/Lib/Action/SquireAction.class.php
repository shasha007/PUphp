<?php 
/**
* 话题广场
*/
class SquireAction extends BaseAction {
	public function index(){
		// 点击量+1
		D('WeiboSquireHit','weibo')->hitAdd($this->mid) ;
		$list = M('weibo')->where('themes_id=0 AND isdel=0')->field("weibo_id,content,uid,ctime,type_data,heart,comment,isHide,isTop")->order('isTop desc,weibo_id desc')->limit("$this->_item_count")->select() ;	
		foreach ($list as $key => $value) {
			if ((time()-$value['ctime'])<86400) {
				$list[$key]['ctime'] = dateformat($value['ctime']) ;
			}else{
				$list[$key]['ctime'] = date('Y-m-d',$value['ctime']) ;	
			}
			$list[$key]['uid']=$value['uid'];
			$list[$key]['uname'] = getUserField($value['uid'],'uname') ;
			$list[$key]['sex'] = getUserField($value['uid'],'sex') ;
			$sid = getUserField($value['uid'],'sid') ;
	        $list[$key]['type_data'] = unserialize($list[$key]['type_data']) ;
	        if(!empty($list[$key]['type_data'][0]))
			{
				foreach ($list[$key]['type_data'] as &$item)
				{
					$item['picurl'] = telnetPictures($item['picurl']);
					$item['thumbmiddleurl'] = telnetPictures($item['thumbmiddleurl']);
					$item['thumburl'] = telnetPictures($item['thumburl']);
				}
			}
			elseif($list[$key]['type_data'])
			{
					$pi['picurl'] = telnetPictures($list[$key]['type_data']['picurl']);
					$pi['thumbmiddleurl'] = telnetPictures($list[$key]['type_data']['thumbmiddleurl']);
					$pi['thumburl'] = telnetPictures($list[$key]['type_data']['thumburl']);
					unset($list[$key]['type_data']) ;
					$list[$key]['type_data'][] = $pi ;			
			}else{
				$list[$key]['type_data']=array();
			}
			$list[$key]['content'] = buildAndroidCode(replaceSelfEmoji($list[$key]['content'])) ;		
			$list[$key]['schoolname'] = tsGetSchoolNameById($sid) ;
			$list[$key]['face'] =getUserFace($value['uid'],'b') ; 
			$list[$key]['is_friend'] = checkFriden($this->mid,$value['uid']) ; 
			$list[$key]['isHeart'] = D('Heart','weibo')->isHearted($value['weibo_id'],$this->mid)?1:0; 
			if ($list[$key]['isHide'] ==  1) {
				$list[$key]['uname'] = '某同学' ;
				$list[$key]['face'] =getUserFace('','b') ; 
				$list[$key]['is_friend'] = 1 ;
			}		
		}
		$map['isShow'] = 1 ;
		$map['isDel'] = 0 ;
		$themes = M('weibo_themes')->where($map)->field()->order('isTop desc , id desc')->select() ;
		$this->assign('list',$list) ;
		$this->assign('themes',$themes) ;
		$this->display() ;
	}
	public function page(){
		$limit = ($this->_page-1)*$this->_item_count ;
		$list = M('weibo')->where('themes_id=0')->field("weibo_id,content,uid,ctime,type_data,heart,comment,isHide,isTop")->order('weibo_id desc')->limit("$limit,$this->_item_count")->select() ;	
		foreach ($list as $key => $value) {
			if ((time()-$value['ctime'])<86400) {
				$list[$key]['ctime'] = dateformat($value['ctime']) ;
			}else{
				$list[$key]['ctime'] = date('Y-m-d',$value['ctime']) ;	
			}
			$list[$key]['uid']=$value['uid'];
			$list[$key]['uname'] = getUserField($value['uid'],'uname') ;
			$list[$key]['sex'] = getUserField($value['uid'],'sex') ;
			$sid = getUserField($value['uid'],'sid') ;
	        $list[$key]['type_data'] = unserialize($list[$key]['type_data']) ;
	        if(!empty($list[$key]['type_data'][0]))
			{
				foreach ($list[$key]['type_data'] as &$item)
				{
					$item['picurl'] = telnetPictures($item['picurl']);
					$item['thumbmiddleurl'] = telnetPictures($item['thumbmiddleurl']);
					$item['thumburl'] = telnetPictures($item['thumburl']);
				}
			}
			elseif($list[$key]['type_data'])
			{
					$pi['picurl'] = telnetPictures($list[$key]['type_data']['picurl']);
					$pi['thumbmiddleurl'] = telnetPictures($list[$key]['type_data']['thumbmiddleurl']);
					$pi['thumburl'] = telnetPictures($list[$key]['type_data']['thumburl']);
					unset($list[$key]['type_data']) ;
					$list[$key]['type_data'][] = $pi ;			
			}else{
				$list[$key]['type_data']=array();
			}
			$list[$key]['content'] = buildAndroidCode(replaceSelfEmoji($list[$key]['content'])) ;			
			$list[$key]['schoolname'] = tsGetSchoolNameById($sid) ;
			$list[$key]['face'] =getUserFace($value['uid'],'') ; 
			$list[$key]['is_friend'] = checkFriden($this->mid,$value['uid']) ; 
			$list[$key]['isHeart'] = D('Heart','weibo')->isHearted($value['weibo_id'],$this->mid)?1:0;  
			if ($list[$key]['isHide'] ==  1) {
				$list[$key]['uname'] = '某同学' ;
				$list[$key]['face'] =getUserFace('','b') ; 
				$list[$key]['is_friend'] = 1 ;
			}				
		}
		if (!$list) {
			echo 0 ; die ;
		}
		$this->assign('list',$list) ;
		$this->display() ;
	}


	public function delete_weibo(){
		$weibo_id = intval($_REQUEST['weibo_id']) ;
		if ($weibo_id < 1) {
			echo 0 ; die ;
		}
		$weibo_uid = M('weibo')->where('weibo_id='.$weibo_id)->getField('uid') ;
		if ($this->mid != $weibo_uid) {
			echo 0 ; die ;
		}
		$data['isdel'] = 1 ;
		if (M('weibo')->where('weibo_id='.$weibo_id)->save($data) !== false) {
			echo 1 ; die ;
		}
		echo 0 ; 
	}
}





?>