<?php
/**
* 
*/
class WeiboContentAction extends BaseAction{
	public function index(){
		$weibo_id = intval($_REQUEST['weibo_id']) ;
/*		if ($weibo_id<1) {
			$this->error('参数错误') ;
		}*/
		// 点击量+1
		D('WeiboHit','weibo')->hitAdd($this->mid,$weibo_id) ;
		$info = M()->table('ts_weibo as w ')->join('ts_user u on u.uid = w.uid')->field('u.uid,u.uname,u.sex,u.sid,w.weibo_id,w.themes_id,w.ctime,w.content,w.comment,w.heart,w.type_data,w.isHide')->where('weibo_id='.$weibo_id)->find() ;
			if ((time()-$info['ctime'])<86400) {
				$info['ctime'] = dateformat($info['ctime']) ;
			}else{
				$info['ctime'] = date('Y-m-d',$info['ctime']) ;	
			}
		$info['content'] = buildAndroidCode(replaceSelfEmoji($info['content'])) ;				
		$info['schoolname'] = tsGetSchoolNameById($info['sid']) ;
		$info['isHeart'] = D('Heart','weibo')->isHearted($info['weibo_id'],$this->mid)?1:0;
        $info['type_data'] = unserialize($info['type_data']) ;
        $info['weibo_favourite'] = M('weibo_favorite')->where('weibo_id='.$weibo_id.' AND uid='.$this->mid)->find()?1:0 ;
        if(!empty($info['type_data'][0]))
		{
			foreach ($info['type_data'] as &$item)
			{
				$item['picurl'] = telnetPictures($item['picurl']);
				$item['thumbmiddleurl'] = telnetPictures($item['thumbmiddleurl']);
				$item['thumburl'] = telnetPictures($item['thumburl']);
			}
		}
		elseif($info['type_data'])
		{
				$pi['picurl'] = telnetPictures($info['type_data']['picurl']);
				$pi['thumbmiddleurl'] = telnetPictures($info['type_data']['thumbmiddleurl']);
				$pi['thumburl'] = telnetPictures($info['type_data']['thumburl']);
				unset($info['type_data']) ;
				$info['type_data'][] = $pi ;			
		}else{
			$info['type_data']=array();
		}
		if ($info['isHide'] ==  1 ) {
			$info['uname'] = '某同学' ;
			$info['face'] =getUserFace('','b') ; 
			$info['is_friend'] = 1 ;
		}else{
			$info['face'] =getUserFace($info['uid'],'') ; 
			$info['is_friend'] = checkFriden($this->mid,$info['uid']) ; 
		}
        $theme_name = M('weibo_themes')->where('id='.$info['themes_id'])->getField('name') ;
        $info['theme_name'] = $theme_name?$theme_name:'话题' ;
        $map['weibo_id'] = $weibo_id ;
        $map['isdel'] = 0 ;
        $comments = M('weibo_comment')->where($map)->field('content,comment_id,uid,ctime,isHide')->order('comment_id desc')->limit("$this->_item_count")->select() ; 
       	foreach ($comments as $key => $value) {
       		$comments[$key]['content'] = replaceSelfEmoji($comments[$key]['content']) ;
       		$comments[$key]['face'] = getUserFace($value['uid'],'b') ;
       		$comments[$key]['sex'] = getUserField($value['uid'],'sex') ;
       		$comments[$key]['uname'] = getUserField($value['uid'],'uname') ;
       		$sid = getUserField($value['uid'],'sid') ;
       		$comments[$key]['schoolname'] = tsGetSchoolNameById($sid) ;
       		if ((time()-$value['ctime'])<86400) {
				$comments[$key]['ctime'] = dateformat($value['ctime']) ;
			}else{
				$comments[$key]['ctime'] = date('Y-m-d',$value['ctime']) ;	
			}
			if ($comments[$key]['isHide'] ==  1 ||($comments[$key]['uid'] == $info['uid'] && $info['isHide'] ==1 )) {
				$comments[$key]['isHide'] = 1 ;
				$comments[$key]['uname'] = '某同学' ;
				$comments[$key]['face'] =getUserFace('','b') ; 
				$comments[$key]['is_friend'] = 1 ;
			}				
       	}
    	$this->assign('comments',$comments) ;
		$this->assign('info',$info) ;
		$this->display() ;
	}

	public function page(){
		$limit = ($this->_page-1)*$this->_item_count ;
		$weibo_id = intval($_REQUEST['weibo_id']) ;
		if ($weibo_id<1) {
			echo 0 ; die ;
		}
		$map['weibo_id'] = $weibo_id ;
        $map['isdel'] = 0 ;
		$comments = M('weibo_comment')->where($map)->field('content,comment_id,uid,ctime')->order('comment_id desc')->limit("$limit,$this->_item_count")->select() ; 
		if ($comments) {
	       	foreach ($comments as $key => $value) {
	       		$comments[$key]['content'] = replaceSelfEmoji($comments[$key]['content']) ;
	       		$comments[$key]['face'] = getUserFace($value['uid'],'b') ;
	       		$comments[$key]['sex'] = getUserField($value['uid'],'sex') ;
	       		$comments[$key]['uname'] = getUserField($value['uid'],'uname') ;
	       		$sid = getUserField($value['uid'],'sid') ;
	       		$comments[$key]['schoolname'] = tsGetSchoolNameById($sid) ;
	       		if ((time()-$value['ctime'])<86400) {
					$comments[$key]['ctime'] = dateformat($value['ctime']) ;
				}else{
					$comments[$key]['ctime'] = date('Y-m-d',$value['ctime']) ;
				}
	       	}
			if ($comments[$key]['isHide'] ==  1 ||($comments[$key]['uid'] == $info['uid'] && $info['isHide'] ==1 )) {
				$comments[$key]['isHide'] = 1 ;
				$comments[$key]['uname'] = '某同学' ;
				$comments[$key]['face'] =getUserFace('','b') ; 
				$comments[$key]['is_friend'] = 1 ;
			}		
		}else{
			echo 0 ; die ;
		}
        //dump($comments) ; die ;
    	$this->assign('comments',$comments) ;
    	$this->display() ; 
	}

	public function commentDel(){
		$id = intval($_REQUEST['id']) ;
		$result =  D('Comment','weibo')->deleteComments($id,$this->mid) ;
		if ($result) {
			M('weibo')->where('weibo_id='.$id)->setDec('comment') ;
		}
		$this->ajaxReturn($result) ;
	}



	public function collect_weibo(){
		$weibo_id = intval($_REQUEST['weibo_id']) ;
		if ($weibo_id<1) {
			echo 0 ; die ;
		}
		$da['weibo_id'] = $weibo_id ;
		$da['uid'] = $this->mid ;
		if (intval($_REQUEST['status']) === 1) {
			if (M('weibo_favorite')->add($da)) {
				echo 1 ; die ;			
			}
		}elseif(intval($_REQUEST['status']) === 2){
			if (M('weibo_favorite')->where($da)->delete()) {
				echo 2 ; die ;			
			}			
		}
		echo  0 ;
	}

	//微博评论
	public function weibo_comment(){
        if(!canPublish($this->mid)){
            $res = array();
            $res['status'] = 0;
            $res['msg'] = '操作太频繁,请稍后再试';
            $this->ajaxReturn($res);
            die ;
        }
        $post['reply_comment_id'] = 0;  //回复 评论的ID
        $post['weibo_id']         = intval($_REQUEST['weibo_id'] );          //回复 微博的ID
        $post['content']          = $_REQUEST['comment_content'];         	//回复内容     
        $post['transpond']        = intval($_REQUEST['transpond']);           //是否同是发布一条微博
        $post['from']             = intval($_REQUEST['from']);        		 //来自哪里
        $post['isHide']           = intval($_REQUEST['isHide']) ;             //是否匿名（1匿名，0不匿名）
                    	
        $id = D('Comment','weibo')->doaddcomment( $this->mid ,$post,true );
        if(empty($id)){
            $res = array();
            $res['status'] = 0;
            $res['msg'] = '发送失败';
        }else{
            $res = array();
            $res['status'] = 1;
            $res['msg'] = '发送成功';
        }
        $this->ajaxReturn($res);		
	}

}


?>