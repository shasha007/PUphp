<?php

import('home.Action.PubackAction');

class AdminAction extends PubackAction {

    public $forum;

    public function _initialize() {
        parent::_initialize();
        $this->forum = D('Forum');
    }

    public function index() {
        $map['tid'] = 0;
        $map['rid'] = 0;
        $map['isDel']=0;
        if (!empty($_POST)) {
            $_SESSION['admin_forum_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_forum_search']);
        } else {
            unset($_SESSION['admin_forum_search']);
        }
        $_POST['title'] && $map['content'] = array('like', '%' . t($_POST['title']) . '%');
        $list = $this->forum->where($map)->order('id DESC')->findPage(10);
        foreach($list['data'] as &$value){
            $value['pic_o']='';
            $value['pic_m']='';
            if($value['photoId']){
                $attach = getAttach($value['photoId']);
                $file = $attach['savepath'] . $attach['savename'];
                $value['pic_o'] = tsMakeThumbUp($file, 60, 60, 'no');
                $value['pic_m'] = tsMakeThumbUp($file, 465, 0, 'f');
            }
        }
        $this->assign($list);
        $this->display();
    }


    public function detail() {
        $tid = $_REQUEST['id'];
        //找到该帖子
        $result = $this->forum->find($tid);
         $result['pic_o']='';
         $result['pic_m']='';
         if($result['photoId']){
                $attach = getAttach($result['photoId']);
                $file = $attach['savepath'] . $attach['savename'];
                $result['pic_o'] = tsMakeThumbUp($file, 60, 60, 'no');
                $result['pic_m'] = tsMakeThumbUp($file, 465, 0, 'f');
         }
        //找到所有评论
        $comment = $this->forum->where('tid=' . $tid)->findAll();
        $comment = orderArray($comment, 'id');
//        foreach($comment as &$value){
//            if($value['isDel']==1){
//                $value['content']='该内容已经被删除！';
//            }
//        }
        $this->assign('comment', $comment);
        $this->assign($result);
        $this->display();
    }

    public function put_course_to_recycle() {
        $gid = is_array($_POST ['gid']) ? implode(',', $_POST ['gid']) : $_POST ['gid']; // 判读是不是数组
//        var_dump($gid);die;
        //isDel 0给成1
        $data['isDel']=1;
        $map['id'] = array('IN', $gid);
        $res = $this->forum->where($map)->save($data);

        if ($res) {
            $where['tid'] = array('IN', $gid);
            $this->forum->where($where)->save($data);
            D('ForumNotice')->where($where)->delete();
            if (strpos($_POST ['gid'], ',')) {
                echo 1;
            } else {
                echo 2;
            }
        } else {
            echo 0;
        }
    }

    //删除评论或回复
    public function del_Comment_reply(){
       $id=$_POST['id'];
       $data['isDel']=1;
       $res=$this->forum->where('id='.$id)->save($data);
       if($res){
           $where='rid='.$id.' or hid='.$id;
           D('ForumNotice')->where($where)->delete();
           echo 1;
       }else{
           echo 0;
       }
    }
    public function excel() {
          set_time_limit(0);
        if (!intval($_GET['p'])) {
            $page = 1;
        } else {
            $page = intval($_GET['p']);
        }
        $limit = 1000;
        $offset = ($page - 1) * $limit;
        $list = $this->forum
                 ->where('isDel=0')
                ->field('content,uid,cTime')
                ->limit("$offset,$limit")
                ->findAll();
//        $User = M('user');
        foreach ($list as $k => $v) {
//            $list[$k]['email'] = getUserEmailNum($User->getField('email', 'uid=' . $v['uid']));
            $list[$k]['uid'] = getUserRealName($v['uid']);
            $list[$k]['sid'] = tsGetSchoolByUid($v['uid']);
            $list[$k]['cTime'] = date('Y-m-d h:i', $v['cTime']);
        }
        closeDb();
        $arr = array('泡泡','发布人', '发布时间','所属学校');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'吐泡泡');
    }

    public function taPhoto() {
        $daoPhoto = M('makefriends_photo');
        $map['isDel'] = 0;
        $list = $daoPhoto->where($map)->field('photoId,uid,sid,cTime,praiseCount,backCount,recommend')->order('photoId DESC')->findPage(10);
        foreach($list['data'] as &$value){
            $attach = getAttach($value['photoId']);
            $file = $attach['savepath'] . $attach['savename'];
            $value['pic_o'] = tsMakeThumbUp($file, 60, 60, 'no');
            $value['pic_m'] = tsMakeThumbUp($file, 100, 100, 'f');
        }
        $this->assign($list);
        $this->display();
    }
    
    public function taGift() {
        $daoPhoto = M('makefriends_gift');
        $list = $daoPhoto->field('uid,sid,toid,giftNum,buyNum,day')->order('id DESC')->findPage(10);
        foreach($list['data'] as &$value){
            $value['freeNum'] = $value['giftNum']-$value['buyNum'];
        }
        $this->assign($list);
        $this->display();
    }
    public function weekinit(){
        D('MakefriendsUser', 'makefriends')->initWeekUser();
        D('MakefriendsPhoto', 'makefriends')->initWeekPhoto();
        $this->success('重置成功');
    }

    public function doDelete() {
        if (empty($_POST['ids'])) {
            echo 0;
            exit;
        }
        $map['photoId'] = array('in', t($_POST['ids']));
        $data['isDel'] = 1;
        $res = M('makefriends_photo')->where($map)->save($data);
        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }

    /**
     * ta秀审核页
     */
    public function taShowPhoto() {
        $daoPhoto = M('makefriends_photo');
        $map['isDel'] = 0;
        $map['is_audit'] = 0;
        $map['act_id'] = array('GT',0);
        $list = $daoPhoto->where($map)->field('photoId,uid,sid,cTime,praiseCount,backCount')->order('photoId DESC')->findPage(10);
        foreach($list['data'] as &$value){
            $attach = getAttach($value['photoId']);
            $file = $attach['savepath'] . $attach['savename'];
            $value['pic_o'] = tsMakeThumbUp($file, 60, 60, 'no');
            $value['pic_m'] = tsMakeThumbUp($file, 100, 100, 'f');
        }
        $this->assign($list);
        $this->display();
    }
    
    /**
     * ta秀排行榜
     */
    public function taShowRank()
    {
    	$act_id = intval($_REQUEST['act_id']);
    	$daoPhoto = M('makefriends_photo');
        $map['isDel'] = 0;
//         $map['is_audit'] = 1;
//         $map['audit_result'] = 1;
	    $map['audit_result'] = array('ELT','1');
        if($act_id) $map['act_id'] = $act_id;
        $list = $daoPhoto->where($map)->field('act_id,photoId,uid,sid,cTime,praiseCount,backCount')
        		->order('weekCount DESC')->findPage(10);
        foreach($list['data'] as $k => &$value){
            $attach = getAttach($value['photoId']);
            $file = $attach['savepath'] . $attach['savename'];
            $value['pic_o'] = tsMakeThumbUp($file, 60, 60, 'no');
            $value['pic_m'] = tsMakeThumbUp($file, 100, 100, 'f');

            $where = array(
            		'act_id'=>$value['id'],
            		'photoId'=>$value['photoId'],
            );
            $value['rank'] = $this->getRankShow($value['act_id'],$value['photoId']);
        }
        $this->assign($list);
        $this->display();
    }
    
    /**
     * ta秀活动
     */
    public function taShowActivity()
    {
    	$daoPhoto = M('makefriends_activity');
    	$map['is_del'] = 0;
        $list = $daoPhoto->where($map)->order('stime DESC')->findPage(10);
        foreach($list['data'] as $k => &$value){
            $attach = getAttach($value['att_id']);
            $file = $attach['savepath'] . $attach['savename'];
            $value['pic_o'] = tsMakeThumbUp($file, 60, 60, 'no');
            $value['pic_m'] = tsMakeThumbUp($file, 100, 100, 'f');
            $map = array('act_id'=>$value['id'],'audit_result'=>1);
	        $map['isDel'] = 0;
	        $map['audit_result'] = array('ELT','1');
// 	        $map['is_audit'] = 1;
            $value['joinCount'] = M('makefriends_photo')->where($map)->count();
            $value['rank'] = $k+1;
        }
        $this->assign($list);
        $this->display();
    }
    
    public function getRankShow($act_id,$photoId)
    {
    	$where = array(
    			'act_id'=>$act_id,
    			'photoId'=>$photoId,
    			'isDel'=>0,
    			'audit_result'=>1,
    	);
    	$userMaxPraise = M('makefriends_photo')->field('weekCount,photoId')
    	->where($where)
    	->find();
    	$where = array(
    			'weekCount'=>array('GT',$userMaxPraise['weekCount']),
    			'act_id'=>$act_id,
    			'isDel'=>0,
    			'audit_result'=>1,
    	);
    	$res = M('makefriends_photo')->where($where)->order('weekCount DESC')->count();
    	return $res+1;
    }
    
    /**
     * 拒绝ta秀图片
     */
    public function doFailShowPhoto() {
        if (empty($_POST['ids'])) {
            echo 0;
            exit;
        }
        $map['photoId'] = array('in', t($_POST['ids']));
        $data['is_audit'] = 1;
        $data['audit_result'] = 2;
        $res = M('makefriends_photo')->where($map)->save($data);
        	$res = M('makefriends_photo')->where($map)->select();
        	$uids = array();
        	foreach($res as $k => $v) {
        		$uids[] = $v['uid'];
        	}
        	$this->sendNotice($uids);
            echo 1;
    }
    
    public function sendNotice($uids = array())
    {
    	$notify_dao = service('Notify');
    	$notify_data['title'] = "系统拒绝了您上传到Ta秀秀场的违反活动规则的照片，请到相册中查看";
    	$res = $notify_dao->sendIn($uids, 'forum_tashow_refuse', $notify_data);
    }

    /**
     * 通过ta秀图片
     */
    public function doSucShowPhoto() {
        if (empty($_POST['ids'])) {
            echo 0;
            exit;
        }
        $map['photoId'] = array('in', t($_POST['ids']));
        $data['is_audit'] = 1;
        $data['audit_result'] = 1;
        $res = M('makefriends_photo')->where($map)->save($data);
        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }
    
    public function editShowActivity() {
    	if($_POST) {
    		if (!$_POST['id'] && empty($_FILES['img']["tmp_name"])) {
    			$this->error('LOGO文件不可为空');
    		}
    		if (!empty($_FILES['img']["tmp_name"]) && substr($_FILES['img']['type'], 0, 5) != 'image') {
    			$this->error('LOGO文件格式错误');
    		}
	        if (!isImg($_FILES['pic']['tmp_name'])) {
	            $this->error = '图片格式不对';
	            return false;
	        }
	        if($_FILES['img']["tmp_name"]) {
		        list($sr_w, $sr_h) = @getimagesize($_FILES['img']['tmp_name']);
		        $options = array();
		        $options['allow_exts'] = 'jpeg,gif,jpg,png,bmp';
		        $info = X('Xattach')->upload('makefriendsAct', $options);
		        if (!$info['status']) {
		            $this->error = '图片上传失败';
		            return false;
		        }
		        $aid = $info['info'][0]['id'];
	        }
        
                $data['title'] = t($_POST['title']);
                $data['stime'] = $_POST['sTime'];
                $data['etime'] = $_POST['eTime'];
                $data['rule'] = t($_POST['content']);
    		if($_POST['id']) {
    			if($aid) $data['att_id'] = $aid;
    			$map = array('id'=>$_POST['id']);
    			$res = M('makefriends_activity')->where($map)->save($data);
    		} else {
    			$data['att_id'] = $aid;
    			$data['create_time'] = date('Y-m-d H:i:s');
    			$res = M('makefriends_activity')->add($data);
    		}
    		header("Location:/index.php?app=forum&mod=Admin&act=taShowActivity");
    		exit;
    	} else {
	    	$id = intval($_GET['actid']);
	    	if ($id) {
	    		$data = M('makefriends_activity')->WHERE("id='$id'")->find();
	    		$this->assign('inforlist', $data);
	    	}
	    	$this->display();
    	}
    }

    /**
     * 删除活动
     */
    public function doDeleteActivity() {
        if (empty($_POST['ids'])) {
            echo 0;
            exit;
        }
        $map['id'] = array('in', t($_POST['ids']));
        $data['is_del'] = 1;
        $res = M('makefriends_activity')->where($map)->save($data);
        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }


    // taxiu 推荐到微博
    public function recommendToWeibo(){
        $map['isShow'] = 1 ;
        $map['isDel'] = 0 ;
        $result = M('weibo_themes')->where($map)->field('id,name')->order('id desc')->select() ;
        $this->assign('result',$result) ;
        $this->assign($_REQUEST) ;
        $this->display() ;
    }

    public function doRecommendToWeibo(){
        // 添加微博的话
        $uid = intval($_REQUEST['uid']) ;
        $photoId = intval($_REQUEST['photoId']) ;
        $id = intval($_REQUEST['id']) ;
        if ($id>0) {
            $content = M('forum')->where('id='.$id)->getField('content') ;
            $data['content'] = $content ;
            $data['themes_id'] = intval($_REQUEST['themes_id']) ?intval($_REQUEST['themes_id']) : 0 ;
            $data['isHide'] = 1 ;
            $type = '' ;
            $type_data = array() ;
        }elseif($photoId>0){
            $type_data = array() ;
            $picture = getAttach($photoId) ;
            $type = 1 ;
            $type_data[] = $picture['savepath'].$picture['savename'] ;
            $data['content'] = '分享图片' ;
            $data['themes_id'] = intval($_REQUEST['themes_id']) ?intval($_REQUEST['themes_id']) : 0 ; 
        }else{
            $this->error('参数错误') ;
        }
        // dump($data) ; dump($type_data) ; dump($uid) ;die ;
        $rid = D('Weibo','weibo')->publish( $uid,$data,$this->data['from'],$type,$type_data,array('sina'));    
        if ($rid) {
                $da['recommend'] = 1 ;
            if ($id > 0) {
                M('forum')->where('id='.$id)->save($da) ;
            }elseif($photoId>0){
                M('makefriends_photo')->where('photoId='.$photoId)->save($da) ;
                // dump(M('makefriends_photo')->getlastSql()) ; die ;                
            }
            $this->success('推荐成功') ;
        }
        $this->error('推荐失败') ;
    }
}

?>
