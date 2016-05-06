<?php
class TopicAction extends BaseAction {
	public function index(){
		$uid = $this->mid ;
		$user_id = intval($_REQUEST['uid']) ;
		$themes_id = intval($_REQUEST['themes_id']) ;
		$pu_user_id = M('weibo_themes_config')->where('themesId='.$themes_id)->getField('uid') ;
		//获取pu置顶微博		
		if ($themes_id>0) {
			if ($pu_user_id) {
				$map['uid'] = array('NEQ',$pu_user_id) ;
				$pu_weibo = M('weibo')->where('uid='.$pu_user_id)->field("weibo_id,content,uid,ctime,type_data,heart,comment,isHide,isTop")->order('weibo_id desc')->find() ;
				$pu_weibo['isPu'] = 1 ;				
			}
			//话题点击量
			D('WeiboThemesHit','weibo')->hitAdd($this->mid,$themes_id) ;
			$map['themes_id'] = array('EQ',$themes_id) ;
			$this->assign('themes_id',$themes_id) ;
			$name = M('weibo_themes')->where('id='.$themes_id)->getField('name') ;
		}elseif($user_id>0){
			$map['uid'] = array('EQ',$user_id) ;
			$map['isHide'] = array('NEQ',1) ;
			$this->assign('search_user_id',$user_id) ;
			$name = 'TA的话题' ;
		}else{
			$this->error('参数错误') ;
		}
		$map['isdel'] = array('EQ',0) ;
		$list = M('weibo')->where($map)->field("weibo_id,content,uid,ctime,type_data,heart,comment,isHide,isTop")->order('isTop desc,weibo_id desc')->limit($this->_item_count)->select() ;	
		if ($pu_weibo) {
			array_unshift($list,$pu_weibo) ;
		}
		foreach ($list as $key => $value) {
			if ((time()-$value['ctime'])<86400) {
				$list[$key]['ctime'] = dateformat($value['ctime']) ;
			}else{
				$list[$key]['ctime'] = date('Y-m-d',$value['ctime']) ;	
			}
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
		$this->assign('list',$list) ;
		$this->assign('name',$name) ;
		$this->display() ;
	}


	public function search(){
		$user_id = intval($_REQUEST['uid']) ;
		$themes_id = intval($_REQUEST['themes_id']) ;
		if ($themes_id>0) {
			$map['w.themes_id'] = array('EQ',$themes_id) ;
			$this->assign('themes_id',$themes_id) ;
			$name = M('weibo_themes')->where('id='.$themes_id)->getField('name') ;
		}elseif($user_id>0){
			$map['isHide'] = array('NEQ',1) ;
			$map['u.uid'] = array('EQ',$user_id) ;
			$this->assign('user_id',$user_id) ;
			$name = 'TA的话题' ;
		}else{
			echo 0 ; die ;
		}
		$map['w.isdel'] = array('EQ',0) ;
		$limit = ($this->_page-1)*$this->_item_count ;
		$aid = intval($_REQUEST['aid']) ;
		$sid = intval($_REQUEST['sid']) ;
		switch ($aid) {
			case '2':
				$map['u.province'] = getUserField($this->mid,'province') ;
				break;
			case '3':
				$map['u.city'] = getUserField($this->mid,'city') ;
				break;
			case '4':
				$map['u.sid'] = getUserField($this->mid,'sid') ;
				break;
		}
		switch ($sid) {
			case '2':
				$map['u.sex'] = 0 ;
				break;
			case '3':
				$map['u.sex'] = 1 ;
				break;
		}
		$list = M()->table('ts_weibo as w')->join('ts_user u on w.uid=u.uid')->field('u.sex,u.uid,u.uname,u.sid,w.weibo_id,w.content,w.type_data,w.ctime,w.heart,w.comment,w.isHide,w.isTop')->limit("$limit,$this->_item_count")->where($map)->order('w.weibo_id desc')->select() ;
		if (!$list) {
			echo 0 ; die ;
		}else{
			foreach ($list as $key => $value) {
				if ((time()-$value['ctime'])<86400) {
					$list[$key]['ctime'] = dateformat($value['ctime']) ;
				}else{
					$list[$key]['ctime'] = date('Y-m-d',$value['ctime']) ;	
				}
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
				$list[$key]['schoolname'] = tsGetSchoolNameById($value['sid']) ;
				$list[$key]['face'] =getUserFace($value['uid'],'b') ; 
				$list[$key]['is_friend'] = checkFriden($this->mid,$value['uid']) ; 
				$list[$key]['isHeart'] = D('Heart','weibo')->isHearted($value['weibo_id'],$this->mid)?1:0;
				if ($list[$key]['isHide'] ==  1) {
					$list[$key]['uname'] = '某同学' ;
					$list[$key]['face'] =getUserFace('','b') ; 
					$list[$key]['is_friend'] = 1 ;
				}					
			}
			$this->assign('list',$list) ;
			$this->display() ;
		}
	}

}
?>