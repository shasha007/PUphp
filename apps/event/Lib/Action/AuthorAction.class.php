<?php

/**
 * FrontAction
 * 活动页面
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class AuthorAction extends SchoolbaseAction {

    private $eventId;
    private $obj;

    public function _initialize() {
        parent::_initialize();
        //用户信息
        if (!$this->smid) {
            $this->error('您不是该校用户，请先登录');
        }
        //活动
        $id = intval($_REQUEST['id']);
        if ($id <= 0) {
            $this->error("错误的访问页面，请检查链接");
        }
        $this->event->setMid($this->mid);
        $map = array();
        $map['isDel'] = 0;
        $map['id'] = $id;
        $result = $this->event->where($map)->find();
        if ($result) {
            //初审，终审，超级管理员
            if ($result['is_prov_event']) {
                if ($result['uid'] != $this->mid && !$this->rights['can_admin']) {
                    $this->error('您没有权限管理该活动');
                }
            } elseif ($this->user['can_event2']
                    || ($this->mid == $result['audit_uid'] && $this->user['can_event'])
                    || $this->rights['can_admin']) {

            } elseif ($result['uid'] != $this->mid) {
                $this->error('您没有权限管理该活动');
            } else {
                if(($result['school_audit']==1 || $result['school_audit']==5 )){
                    if(ACTION_NAME=='index'||ACTION_NAME=='member'||ACTION_NAME=='finish'||ACTION_NAME=='flash'||ACTION_NAME=='playerFlash'
                            ||ACTION_NAME=='orga'||ACTION_NAME=='photo'||ACTION_NAME=='player'||ACTION_NAME=='playerFlash'
                            ||ACTION_NAME=='memberAudit'||ACTION_NAME=='newsList'||ACTION_NAME=='qrcode'||ACTION_NAME=='excel'){

                    }else{
                        $this->error('活动已完结或未通过审核');
                    }
                }
            }
            $result['canFinish'] = ($result['eTime'] <= time());
            $this->obj = $result;
            $this->assign($result);
        } else {
            $this->assign('jumpUrl', U('/School/index'));
            $this->error('活动不存在或，未通过审核或，已删除');
        }
        $this->assign('eventId', $id);
        $this->setTitle($result['title']);
        $this->eventId = $id;
    }

    /**
     * 活动管理首页
     */
    public function index() {
        //计算待审核人数
        $result['verifyCount'] = D('EventUser')->where("status = 0 AND eventId ={$result['id']}")->count();
        $this->assign($result);
        if ($this->obj['is_prov_event']) {
            $this->assign('showInSchool', D('EventSchool2')->schoolString($this->eventId));
        }
        $this->display();
    }

    //重新申请活动
    public function renew() {
        if ($this->obj['school_audit'] != 6) {
            $this->error('该活动不可重新申请');
        }
        $res = M('event')->where('id=' . $this->eventId)->setField('school_audit', 0);
        if ($res) {
            //发短信给初审人
            $event=M('event')->where("id=$this->eventId")->field('audit_uid,title')->find();
            $mobile=M('user')->where( "uid=".$event['audit_uid'])->field("mobile")->find();
             if($mobile['mobile']){
                 $isSend=M('user_privacy')->where("`key`='active' AND uid=".$event['audit_uid'])->field('value')->find();
                 if($isSend['value']==1){
                     $msg = '您有新的活动"' . $event['title'] . '"等待您的审核';
                     service('Sms')->sendsms($mobile['mobile'], $msg);
                 }
             }

            $this->success('重新申请成功');
        }
        $this->error('重新申请失败');
    }

    //删除活动
    public function autorDel() {
        if ($this->obj['school_audit'] != 0 && $this->obj['school_audit'] != 6) {
            $this->error('您无权删除该活动');
        }
        $res = M('event')->where('id=' . $this->eventId)->setField('isDel', 1);
        if ($res) {
            $this->assign('jumpUrl', U('event/School/board', array('cat' => 'launch')));
            $this->success('删除成功');
        }
        $this->error('删除失败');
    }

    /**
     * edit
     * 编辑活动
     * @access public
     * @return void
     */
    public function edit() {
        $this->assign('schoolOrga', D('SchoolOrga')->getAll($this->school['id']));
        $school = model('Schools')->makeLevel0Tree($this->sid);
        $this->assign('addSchool', $school);
        $typeDao = D('EventType');
        $type = $typeDao->getType2($this->sid);
        $this->assign('type', $type);
        //获取附件名称
        $this->assign('attach',getAttach($this->obj['attachId']));
        $this->display();
    }

    public function editProv() {
        $typeDao = D('EventType');
        $data['areas'] = D('EventSchool2','event')->editbarSchool($this->eventId);
        $is_prov = M('event')->where('id='.$this->eventId)->getField('is_prov_event') ;
        if ($is_prov ==1) {
            $this->assign('prov',($data['areas']['0']['prov'])) ;
        }
        $type = $typeDao->getType2();
        $this->assign('type', $type);
        $this->assign('onlineTime', D('EventOnline','event')->getOnlineTime($this->eventId));
        $this->display();
    }

    /**
     * doEditEvent
     * 修改活动
     * @access public
     * @return void
     */
    public function doEditEvent() {
        $map['startline'] = intval(_paramDate($_POST['startline']));
        $map['deadline'] = _paramDate($_POST['deadline']);
        $map['sTime'] = _paramDate($_POST['sTime']);
        $map['eTime'] = _paramDate($_POST['eTime']);
        if ($map['sTime'] > $map['eTime']) {
            $this->error("结束时间不得早于开始时间");
        }
        if ($map['deadline'] < $map['startline']) {
            $this->error("报名截止时间不得早于报名开始时间");
        }
        if ($map['deadline'] > $map['eTime']) {
            $this->error('报名截止时间不能晚于活动结束时间');
        }
        $map['address'] = t($_POST['address']);

        if ($this->obj['school_audit'] < 1 && $_POST['hidden']) {
            if (mb_strlen($_POST['title'], 'UTF8') > 30) {
                $this->error('活动名称最大30个字！');
            }
            $title = t($_POST['title']);
            if (mb_strlen($_POST['description'], 'UTF8') <= 0 || mb_strlen($_POST['description'], 'UTF8') > 250) {
                $this->error("活动简介1到250字!");
            }
            $textarea = t($_POST['description']);

            if(isset($_POST['imgid1'][0]) && $_POST['imgid1'][0]>0){
                $map['coverId'] = intval($_POST['imgid1'][0]);
            }
            $defaultBanner = intval($_POST['banner']);
            if (isset($_POST['imgid2'][0]) && $_POST['imgid2'][0]>0){
                $map['logoId'] = intval($_POST['imgid2'][0]);
            }elseif($defaultBanner){
                $map['logoId'] = 0;
                $map['default_banner'] = intval($_POST['banner']);
            }
            if(isset($_POST['file1'][0]) && $_POST['file1'][0]>0){
                $map['attachId'] = intval($_POST['file1'][0]);
            }

            $map['title'] = $title;
            $map['typeId'] = intval($_POST['typeId']);
            $map['contact'] = t($_POST['contact']);
            $map['cost'] = intval($_POST['cost']);
            $map['costExplain'] = keyWordFilter(t($_POST['costExplain']));
            $map['show_in_xyh'] = isset($_POST['show_in_xyh']) ? 1 : 0;
            $map['description'] = $textarea;
        }
        if(t($_POST['limitCount']) == '无限制'){
            $map['limitCount'] = 6000000;
        }else{
            $map['limitCount'] = intval($_POST['limitCount']);
        }
        $map['allow'] = isset($_POST['allow']) ? 1 : 0;
        $map['isTicket'] = isset($_POST['isTicket']) ? 1 : 0;
        $map['need_tel'] = isset($_POST['need_tel']) ? 1 : 0;
        $map['free_attend'] = isset($_POST['free_attend']) ? 1 : 0;

        if ($this->event->doEditEvent($map, array(), $this->obj)) {
            $this->assign('jumpUrl', U('/Author/index', array('id' => $this->obj['id'], 'uid' => $this->mid)));
            $this->success($this->appName . '修改成功！');
        } else {
            $this->error($this->appName . '修改失败');
        }
    }

    public function doEditProv() {
        if (mb_strlen($_POST['title'], 'UTF8') > 30) {
            $this->error('活动名称最大30个字！');
        }
        $title = t($_POST['title']);
        if (mb_strlen($_POST['description'], 'UTF8') <= 0 || mb_strlen($_POST['description'], 'UTF8') > 250) {
            $this->error("活动简介1到250字!");
        }
        $textarea = t($_POST['description']);
        $map['sTime'] = _paramDate($_POST['sTime']);
        $map['eTime'] = _paramDate($_POST['eTime']);
        $map['startline'] = intval(_paramDate($_POST['startline']));
        $map['deadline'] = _paramDate($_POST['deadline']);
        if(!$map['deadline']){
            $map['deadline'] = $map['eTime'];
        }
        if ($map['sTime'] > $map['eTime']) {
            $this->error("结束时间不得早于开始时间");
        }
        if ($map['deadline'] < $map['startline']) {
            $this->error("报名截止时间不得早于报名开始时间");
        }
        if ($map['deadline'] > $map['eTime']) {
            $this->error('报名截止时间不能晚于结束时间');
        }
        $onlineTime = intval(_paramDate($_POST['onlineTime']));
        if($onlineTime>0 && time()>=$onlineTime){
            $this->error('定时上线不能早于当前时间');
        }

        //得到上传的图片
        if(isset($_POST['imgid1'][0]) && $_POST['imgid1'][0]>0){
            $map['coverId'] = intval($_POST['imgid1'][0]);
        }
        $defaultBanner = intval($_POST['banner']);
        if (isset($_POST['imgid2'][0]) && $_POST['imgid2'][0]>0){
            $map['logoId'] = intval($_POST['imgid2'][0]);
        }elseif($defaultBanner){
            $map['logoId'] = 0;
            $map['default_banner'] = intval($_POST['banner']);
        }
        $map['title'] = $title;
        if(t($_POST['limitCount']) == '无限制'){
            $map['limitCount'] = 6000000;
        }else{
            $map['limitCount'] = intval($_POST['limitCount']);
        }
        $map['address'] = t($_POST['address']);
        $map['cost'] = intval($_POST['cost']);
        $map['costExplain'] = keyWordFilter(t($_POST['costExplain']));
        $map['codelimit'] = intval(t($_POST['codelimit']));
        $map['typeId'] = intval($_POST['typeId']);
        $map['contact'] = t($_POST['contact']);
        $map['allow'] = isset($_POST['allow']) ? 1 : 0;
        $map['isTicket'] = isset($_POST['isTicket']) ? 1 : 0;
        $map['need_tel'] = isset($_POST['need_tel']) ? 1 : 0;
        $map['free_attend'] = isset($_POST['free_attend']) ? 1 : 0;
        $map['description'] = $textarea;
        if ($this->event->doEditEvent($map, array(), $this->obj)) {
            D('EventSchool2')->addSchool($this->eventId);
            $this->assign('jumpUrl', U('/Author/index', array('id' => $this->obj['id'], 'uid' => $this->mid)));
            D('EventOnline')->editOnline($this->eventId,$onlineTime);
            $this->success($this->appName . '修改成功！');
        } else {
            $this->error($this->appName . '修改失败');
        }
    }

    /**
     * doEndAction
     * 结束活动
     * @access public
     * @return void
     */
    public function doEndAction() {
        $id = $_POST['id'];
        $this->event->setMid($this->mid);
        echo $this->event->doEditData(time(), $id);
    }

    /**
     * doDeleteEvent
     * 删除活动
     * @access public
     * @return void
     */
    public function doDeleteEvent() {
        $eventid['id'] = intval($_REQUEST['id']);    //要删除的id.
        $eventid['uid'] = $this->mid;
        $result = $this->event->doDeleteEvent($eventid);
        if (false != $result) {
            echo 1;
        } else {
            echo 0;               //删除失败
        }
    }

    /**
     * member
     * 活动成员
     * @access public
     * @return void
     */
    public function member() {
        if (!empty($_POST)) {
            $_SESSION['backend_event_user_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['backend_event_user_search']);
        } else {
            unset($_SESSION['backend_event_user_search']);
        }
        $_POST['realname'] = t(trim($_POST['realname']));
        $_POST['realname'] && $map['realname'] = array('like', $_POST['realname'] . "%");
        $this->assign($_POST);
        $publishUid = M('event')->where('id = '.$this->eventId)->getField('uid');
        $this->assign('publishUid',$publishUid);
        $map['status'] = array('gt', 0);
        $map['eventId'] = $this->eventId;
        $order = 'id DESC';
        if ($_GET['orderKey'] && $_GET['orderType']) {
            $_GET['orderKey'] = t($_GET['orderKey']);
            $_GET['orderType'] = t($_GET['orderType']);
            $order = $_GET['orderKey'] . ' ' . $_GET['orderType'];
            $this->assign('orderKey', $_GET['orderKey']);
            $this->assign('orderType', $_GET['orderType']);
        }
        //取得成员列表
        //$result = D('EventUser')->getUserList($map);
//        $db_prefix = C('DB_PREFIX');
//        $result = D('EventUser')->table("{$db_prefix}event_user AS a ")
//                        ->join("{$db_prefix}event_img AS b ON a.id=b.uid")
//                        ->join("{$db_prefix}event_flash AS c ON a.id=c.uid")
//                        ->field('a.* , count(DISTINCT b.id) AS img, count(DISTINCT c.id) AS flash')->group('a.id')
//                        ->where($map)->order($order)->findPage(10);
        $result = D('EventUser')->where($map)->order($order)->findPage(10);
        $uids = getSubByKey($result['data'], 'uid');
        $uidMap = array('uid'=>array('in', $uids));
        $users = M('user')->where($uidMap)->field('uid,year,major,email')->findAll();
        $userArr = array();
        foreach ($users as $v) {
            $userArr[$v['uid']] = $v;
        }
        unset($users);
        $cxs = M('event_cx')->where($uidMap)->field('uid,total,attend')->findAll();
        $cxArr = array();
        foreach ($cxs as $v) {
            $grad = ceil($v['attend']*100/$v['total']);
            $cxArr[$v['uid']] = $grad;
        }
        unset($cxs);
        foreach ($result['data'] as $k => $v) {
            $uid = $v['uid'];
            $result['data'][$k]['year'] = $userArr[$uid]['year'];
            $result['data'][$k]['major'] = $userArr[$uid]['major'];
            $result['data'][$k]['email'] = getUserEmailNum($userArr[$uid]['email']);
            $result['data'][$k]['cx'] = isset($cxArr[$v['uid']]) ? $cxArr[$v['uid']].'%' : '暂无';
        }
        $this->assign($result);
        $attent = 0;
        if($result['data']){
            $map['status'] = 2;
            $attent = D('EventUser')->where($map)->count();
        }
        $this->assign('attentCount',$attent);
        $this->setTitle('成员列表');
        // 院校
        $this->assign('can_admin', $this->rights['can_admin']);
        $this->assign('can_jf', $this->canJf());
        $this->display();
    }

    public function player() {
        if (!empty($_POST)) {
            $_SESSION['e_a_player'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['e_a_player']);
        } else {
            unset($_SESSION['e_a_player']);
        }
        $_POST['realname'] = t(trim($_POST['realname']));
        $_POST['realname'] && $map['realname'] = array('like', "%" . $_POST['realname'] . "%");
        $this->assign($_POST);
        $sid = $_SESSION['userInfo']['sid'] ; 
        $istuan = isTuanRole($sid) ;
        $isjingying = 0 ;
        if (M('event')->where("id=$this->eventId and es_type>0")->count()) {
             $isjingying = 1 ;
        }
        //$order = 'isHot DESC,id DESC';
        $order = 'id DESC';
        if ($_GET['orderKey'] && $_GET['orderType']) {
            $order = $_GET['orderKey'] . ' ' . $_GET['orderType'];
            $this->assign('orderKey', $_GET['orderKey']);
            $this->assign('orderType', $_GET['orderType']);
        }
        if ($istuan && $isjingying) {
            $map['recommPid'] = $this->eventId;
            $map['status'] = 1;
            $this->assign('tuanjy',1) ; //团省委并且是 特殊活动
            $result = M('event_player')->join('ts_event_special_tickets s on ts_event_player.id = s.player_id')->field('id,uid,path,realname,school,s.ticket,stoped,isRecomm,sort')->where($map)->order($order)->findPage(10);
            
        }else{
            if ($isjingying) {
                $this->assign('jingying',$isjingying) ;
            }
            $map['eventId'] = $this->eventId;
            $map['_string'] = 'status=1 OR recommPid>0';
            $result = M('event_player')->field('id,uid,path,realname,school,ticket,stoped,isRecomm,sort')->where($map)->order($order)->findPage(10);
        }
        
        
        $this->assign($result);
        $showRecomm = false;
        $provId = M('event_recomm')->getField('provId', 'eventId='.$this->eventId);
        if($provId){
            $provEvent = M('event')->where('id='.$provId)->field('title,deadline')->find();
//            if($provEvent['deadline']>time()){
                $showRecomm = true;
                $this->assign('recommName', $provEvent['title']);
//            }
        }
        //检测当前活动是否为特殊活动
        $es_type = M('event')->where('id = '.$this->eventId)->getField('es_type');
        if($es_type > 0)
        {
            $this->assign('flag',1);
        }

        $this->assign('showRecomm', $showRecomm);
        $this->setTitle('选手列表');
        $this->display();
    }

    public function provEvent()
    {
        $id = intval($_REQUEST['id']);
        $es_type = M('event')->where('id = '.$id)->getField('es_type');
        $e_map['es_type'] = intval($es_type);
        // 活动状态的条件
        $e_map['isDel'] = 0 ;
        $e_map['school_audit'] = 2;
        $e_map['status'] = array('GT',0);
        //获取上级省团委的sid
        $provinceId = intval($_SESSION['userInfo']['schoolEvent']['provinceId']);
        $s_map['provinceId'] = $provinceId;
        $s_map['is_tuan'] = 1;
        $sid = M('school')->where($s_map)->getField('id');
        $e_map['is_school_event'] = $sid;
        $eventLists = M('event')->where($e_map)->select();
        $this->assign('provEvent',$eventLists);
        $this->assign('playid',intval($_REQUEST['playid'])) ;
        $this->assign('id',$id);
        $this->display();
    }
//    public function player() {
//        if (!empty($_POST)) {
//            $_SESSION['e_a_player'] = serialize($_POST);
//        } else if (isset($_GET[C('VAR_PAGE')])) {
//            $_POST = unserialize($_SESSION['e_a_player']);
//        } else {
//            unset($_SESSION['e_a_player']);
//        }
//        $_POST['realname'] = t(trim($_POST['realname']));
//        $_POST['realname'] && $map['realname'] = array('like', "%" . $_POST['realname'] . "%");
//        $this->assign($_POST);
//
//        $map['a.eventId'] = $this->eventId;
//        $map['a.status'] = 1;
//        //$order = 'a.isHot DESC,a.id DESC';
//        $order = 'a.id DESC';
//        if ($_GET['orderKey'] && $_GET['orderType']) {
//            $order = $_GET['orderKey'] . ' ' . $_GET['orderType'];
//            $this->assign('orderKey', $_GET['orderKey']);
//            $this->assign('orderType', $_GET['orderType']);
//        }
//        $db_prefix = C('DB_PREFIX');
//        $result = D('EventUser')->table("{$db_prefix}event_player AS a ")
//                        ->join("{$db_prefix}event_img AS b ON a.id=b.uid")
//                        ->join("{$db_prefix}event_flash AS c ON a.id=c.uid")
//                        ->field('a.id,a.path,a.realname,a.school,a.ticket,a.stoped, count(DISTINCT b.id) AS img, count(DISTINCT c.id) AS flash')->group('a.id')
//                        ->where($map)->order($order)->findPage(10);
//        $result = D('EventUser')->field('id,path,realname,school,ticket,stoped')->where($map)->order($order)->findPage(10);
//        $this->assign($result);
//        $this->setTitle('选手列表');
//        $this->display();
//    }
    public function playerUpload() {
        $map['status'] = 0;
        //$order = 'isHot DESC,id DESC';
        $order = 'id DESC';
        if ($_GET['orderKey'] && $_GET['orderType']) {
            $order = $_GET['orderKey'] . ' ' . $_GET['orderType'];
            $this->assign('orderKey', $_GET['orderKey']);
            $this->assign('orderType', $_GET['orderType']);
        }
        $sid = $_SESSION['userInfo']['sid'] ; 
        $istuan = isTuanRole($sid) ;
        $isjingying = M('event')->where("id=$this->eventId and es_type>0")->count() ;
        if ($istuan && $isjingying) {
            $map['recommPid'] = $this->eventId ;
            $result = M('event_player')->field('id,uid,path,realname,school,ticket,stoped')->where($map)->order($order)->findPage(10);
        }else{
            $map['eventId'] = $this->eventId;
            $map['recommPid'] = 0 ;
            $result = M('event_player')->field('id,uid,path,realname,school,ticket,stoped')->where($map)->order($order)->findPage(10);
        }
        $this->assign($result);
        $this->setTitle('选手审核列表');
        $this->display();
    }
//    public function playerUpload() {
//        $map['a.eventId'] = $this->eventId;
//        $map['a.status'] = 0;
//        //$order = 'a.isHot DESC,a.id DESC';
//        $order = 'a.id DESC';
//        if ($_GET['orderKey'] && $_GET['orderType']) {
//            $order = $_GET['orderKey'] . ' ' . $_GET['orderType'];
//            $this->assign('orderKey', $_GET['orderKey']);
//            $this->assign('orderType', $_GET['orderType']);
//        }
//        $db_prefix = C('DB_PREFIX');
//        $result = D('EventUser')->table("{$db_prefix}event_player AS a ")
//                        ->join("{$db_prefix}event_img AS b ON a.id=b.uid")
//                        ->join("{$db_prefix}event_flash AS c ON a.id=c.uid")
//                        ->field('a.id,a.path,a.realname,a.school,a.ticket,a.stoped, count(DISTINCT b.id) AS img, count(DISTINCT c.id) AS flash')->group('a.id')
//                        ->where($map)->order($order)->findPage(10);
//        $this->assign($result);
//        $this->setTitle('选手审核列表');
//        $this->display();
//    }

    /**
     * 成员审核
     * @access public
     * @return void
     */
    public function memberAudit() {
        if (!empty($_POST)) {
            $_SESSION['backend_event_user_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['backend_event_user_search']);
        } else {
            unset($_SESSION['backend_event_user_search']);
        }
        $_POST['realname'] = t(trim($_POST['realname']));
        $_POST['realname'] && $map['realname'] = array('like', $_POST['realname'] . "%");
        $this->assign($_POST);

        $map['a.status'] = 0;
        $map['eventId'] = $this->eventId;
        //取得成员列表
        $db_prefix = C('DB_PREFIX');
        $dao = M('');
        $result = $dao->table("{$db_prefix}event_user AS a ")
                        ->join("{$db_prefix}event_cx AS b ON a.uid=b.uid")
                        ->field('a.id,a.uid,a.realname,a.tel,a.sex,b.total,b.attend')
                        ->where($map)->order('id desc')->findPage(10);
        $uids = getSubByKey($result['data'], 'uid');
        $uidMap = array('uid'=>array('in', $uids));
        $users = M('user')->where($uidMap)->field('uid,year,major')->findAll();
        $userArr = array();
        foreach ($users as $v) {
            $userArr[$v['uid']] = $v;
        }
        unset($users);
        foreach ($result['data'] as $k => $v) {
            $uid = $v['uid'];
            $result['data'][$k]['year'] = $userArr[$uid]['year'];
            $result['data'][$k]['major'] = $userArr[$uid]['major'];
        }
        $this->assign($result);
        $this->setTitle('成员审核');
        $this->display();
    }

//    /**
//     * 成员照片列表
//     */
//    public function memberImg() {
//        $map['id'] = intval($_REQUEST['uid']);
//        $map['eventId'] = $this->eventId;
//        $user = D('EventUser')->where($map)->find();
//        if(!$user){
//            $this->error('成员不存在');
//        }
//        $this->assign('member', $user);
//        $map['uid'] = intval($_REQUEST['uid']);
//        unset($map['id']);
//        $list = D('EventImg')->where($map)->order('`id` DESC')->findPage(10);
//        $this->assign($list);
//        $this->display();
//    }
//    /**
//     * 成员视频列表
//     */
//    public function memberFlash() {
//        $map['id'] = intval($_REQUEST['uid']);
//        $map['eventId'] = $this->eventId;
//        $user = D('EventUser')->where($map)->find();
//        if(!$user){
//            $this->error('成员不存在');
//        }
//        $this->assign('member', $user);
//        $map['uid'] = intval($_REQUEST['uid']);
//        unset($map['id']);
//        $list = D('EventFlash')->where($map)->order('`id` DESC')->findPage(10);
//        $this->assign($list);
//        $this->display();
//    }
    /**
     * 成员照片列表
     */
    public function playerImg() {
        $map['id'] = intval($_REQUEST['uid']);
        $user = D('EventPlayer')->where($map)->find();
        if (!$user) {
            $this->error('选手不存在');
        }
        $this->assign('member', $user);
        $map['uid'] = intval($_REQUEST['uid']);
        unset($map['id']);
        $list = D('EventImg')->where($map)->order('`id` DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }

    /**
     * 成员视频列表
     */
    public function playerFlash() {
        $map['id'] = intval($_REQUEST['uid']);
        $user = D('EventPlayer')->where($map)->find();
        if (!$user) {
            $this->error('选手不存在');
        }
        $this->assign('member', $user);
        $map['uid'] = intval($_REQUEST['uid']);
        unset($map['id']);
        $list = D('EventFlash')->where($map)->order('`id` DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }

    /**
     * 审核成员
     */
    public function doAuditMember() {
        if ($this->obj['limitCount'] < 1) {
            $this->error('参加活动人数已满');
        }
        $data['id'] = intval($_POST['mid']);
        $data['eventId'] = $this->eventId;
        if ($this->event->doArgeeUser($data)) {
            $this->success('审核通过');
        }
        $this->error('审核失败');
    }
   /**
     * 批量审核成员
     */
      public function batchAllowMember() {
        if ($this->obj['limitCount'] < 1) {
            $this->error('参加活动人数已满');
        }
        $uidarr = explode(',', $_POST['mids']);
        $c = count($uidarr);
        if($this->obj['limitCount']<$c){
            $this->error('剩余名额不足'.$c.'个，重新选择通过的成员');
        }
        $data['eventId'] = $this->eventId;
        foreach ($uidarr as $v) {
            $data['id'] = intval($v);
            $this->event->doArgeeUser($data);
        }
        $this->success('审核成功');
    }

    /**
     * 删除成员
     */
    public function doDeleteMember() {
        $data['id'] = intval($_POST['mid']);
        $data['eventId'] = $this->eventId;
        $res = $this->event->doDelUser($data,true);
        if ($res['status']) {
            $this->success($res['msg']);
        }
        $this->error($res['msg']);
    }

       /**
     * 批量审核成员
     */
      public function batchDelMember() {
        $data['id'] = array('IN', t($_POST ['mids']));
        $data['eventId'] = $this->eventId;
        $data['status'] = 0;
        if (M('event_user')->where($data)->delete()) {
            $this->success('拒绝成功');
        }else{
            $this->error('拒绝失败');
        }
    }

    /**
     * 活动开启投票
     */
    public function doChangeIsTicket() {
        $map['id'] = $this->eventId;
        $act = $_REQUEST['type'];  //动作
        if ($this->event->doIsTicket($map, $act)) {
            $this->success('操作成功');
        }
        $this->error('操作失败');
    }

    //重复投票
    public function doRepeatedVote() {
        $map['id'] = $this->eventId;
        $act = $_REQUEST['type'];  //动作
        if ($this->event->doRepeatedVote($map, $act)) {
            $this->success('操作成功');
        }
        $this->error('操作失败');
    }

    /**
     * 用户开启投票
     */
    public function doChangeVote() {
        $map['id'] = intval($_REQUEST['uid']);
        $map['eventId'] = $this->eventId;
        $act = $_REQUEST['type'];  //动作
        if (D('EventPlayer')->doChangeVote($map, $act)) {
            $this->success('操作成功');
        }
        $this->error('操作失败');
    }

//    public function editUser() {
//        $this->assign('type', 'add');
//        if ($uid = (int) $_REQUEST['uid']) {
//            $this->assign('type', 'edit');
//            $map['eventId'] = $this->eventId;
//            $map['id'] = $uid;
//            $user = D('EventUser')->where($map)->find();
//            if (!$user) {
//                $this->error('无法找到对象');
//            }
//            $this->assign("holdUser", $user);
//        }
//        $this->display();
//    }

    public function editPlayer() {
        $this->assign('type', 'add');
        $pid = (int) $_REQUEST['pid'];
        if ($pid) {
            $this->assign('type', 'edit');
            $map['eventId'] = $this->eventId;
            $map['id'] = $pid;
            $user = D('EventPlayer')->where($map)->field('id,realname,school,content,paramValue,path')->find();
            if (!$user) {
                $this->error('无法找到对象');
            }
            if($user['paramValue']){
                $user['paramValue'] = unserialize($user['paramValue']);
            }else{
                $user['paramValue'] = array();
            }
            $this->assign("holdUser", $user);
        }
        $param = D('EventParameter')->getParam($this->eventId);
        $this->assign($param);
        $fdjs = "<script language='javascript'>";
        $fdjs = $fdjs . "function checkUpPlayer(){ ";
        foreach ($param['defaultName'] as $key => $val) {
            if($key!='path'){
                $fdjs = $fdjs . "if (document.myform.$key.value.length == 0) {\n";
                $fdjs = $fdjs . "alert('$val 不能为空');\n";
                $fdjs = $fdjs . "document.myform.$key.focus();\n";
                $fdjs = $fdjs . "return false;}\n";
            }
        }
        foreach ($param['parameter'] as $key => $val) {
            $k=$key+1;
            if ($val[2] == 1 && $val[1] != 4) {
                $fdjs = $fdjs . "if (document.myform.para$k.value.length == 0) {\n";
                $fdjs = $fdjs . "alert('$val[0] 不能为空');\n";
                $fdjs = $fdjs . "document.myform.para$k.focus();\n";
                $fdjs = $fdjs . "return false;}\n";
            }
        }
        $fdjs = $fdjs . "}</script>";
        $this->assign('fdjs',$fdjs);
        $config = getPhotoConfig();
        $this->assign($config);
        $this->display();
    }

    public function addPlayer() {
        $this->assign('type', 'add');
        $param = D('EventParameter')->getParam($this->eventId);
        $this->assign($param);
        $fdjs = "<script language='javascript'>";
        $fdjs = $fdjs . "function checkUpPlayer(){ ";
        foreach ($param['defaultName'] as $key => $val) {
            if($key!='path'){
                $fdjs = $fdjs . "if (document.myform.$key.value.length == 0) {\n";
                $fdjs = $fdjs . "alert('$val 不能为空');\n";
                $fdjs = $fdjs . "document.myform.$key.focus();\n";
                $fdjs = $fdjs . "return false;}\n";
            }
        }
        foreach ($param['parameter'] as $key => $val) {
            $k=$key+1;
            if ($val[2] == 1 && $val[1] != 4) {
                $fdjs = $fdjs . "if (document.myform.para$k.value.length == 0) {\n";
                $fdjs = $fdjs . "alert('$val[0] 不能为空');\n";
                $fdjs = $fdjs . "document.myform.para$k.focus();\n";
                $fdjs = $fdjs . "return false;}\n";
            }
        }
        $fdjs = $fdjs . "}</script>";
        $this->assign('fdjs',$fdjs);
        $config = getPhotoConfig();
        $this->assign($config);
        $this->display();
    }

    public function doAddPlayer() {
        if (empty($_REQUEST['realname'])) {
            $this->error('选手不能为空！');
        }
        $pid = intval($_REQUEST['pid']);
        if (empty($pid)) {
            $this->_insertPlayer();
        } else {
            $this->_updatePlayer($pid);
        }
    }

    private function _insertPlayer() {
        $calcData = $this->_getPlayerData('add');
        $data = $calcData['data'];
        $res = D('EventPlayer')->add($data);
        if ($res) {
            $imgCount = count($_POST['imgs1']);
            if($imgCount>1){
                for($i=1;$i<$imgCount;$i++){
                    $this->_addPhoto($_POST['imgs1'][$i], $res);
                }
            }
            $attachts = array_merge($calcData['attachs'],$_POST['imgid1']);
            model('Attach')->reliveAttach($attachts);
            $this->assign('jumpUrl', U('/Author/player', array('id' => $this->eventId)));
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }
    private function _addPhoto($path,$uid,$title=''){
        $data['path'] = $path;
        $data['eventId'] = $this->eventId;
        $data['uid'] = $uid;
        $data['title'] = $title;
        $data['cTime'] = time();
        D('EventImg')->add($data);
    }

    private function _getPlayerData($type='edit') {
        if($type=='edit'){
            $info = eventUpload();
            if ($info['status']) {
                $data['data']['path'] = $info['info'][0]['savepath'].$info['info'][0]['savename'];
            } elseif ($info['info'] != '没有选择上传文件') {
                $this->error($info['info']);
            }
        }else{
            if(empty($_REQUEST['imgs1'])){
                $this->error('请上传头像');
            }
            $data['data']['path'] = $_REQUEST['imgs1'][0];
        }
        $param = D('EventParameter')->getParam($this->eventId);
        $paramValue = array();
        $attachs = array();
        foreach ($param['parameter'] as $k => $v) {
            $key = $k+1;
            if($v[1] == 3 && $type=='add'){
                $key = 'file'.$key;
                $input = isset($_POST[$key][0])?$_POST[$key][0]:'';
            }else{
                $key = 'para'.$key;
                $input = isset($_POST[$key])?$_POST[$key]:'';
            }
            //视频
            if($v[1] == 4){
                $paramValue[] = '';
            }else{
                if($v[2]==1 && $input==''){
                    $this->error($v[0].' 不能为空');
                }
                $paramValue[] = t($input,'nl');
                //附件
                if($v[1] == 3 && $input){
                    $attachs[] = $input;
                }
            }
        }
        $data['data']['paramValue'] = serialize($paramValue);
        $data['data']['cTime'] = time();
        $data['data']['eventId'] = $this->eventId;
        $data['data']['content'] = t($_REQUEST['content'],'nl');
        $data['data']['realname'] = t($_REQUEST['realname']);
        $data['data']['school'] = t($_REQUEST['school']);
        $data['attachs'] = $attachs;
        return $data;
    }

    private function _updatePlayer($id) {
        $map['id'] = $id;
        $daoUser = D('EventPlayer');
        $user = $daoUser->where($map)->find();
        if (!$user) {
            $this->error('非法参数，选手不存在！');
        }
        $calcData = $this->_getPlayerData();
        $data = $calcData['data'];
        if ($daoUser->where("id={$id}")->save($data)) {
            //删除旧头像
            if ($user['path'] && isset($data['path'])) {
                deletePath($user['path']);
            }
            model('Attach')->reliveAttach($calcData['attachs']);
            $this->assign('jumpUrl', U('/Author/player', array('id' => $this->eventId)));
            $this->success('修改成功！');
        } else {
            //失败提示
            $this->error('修改失败！');
        }
    }

    //删除选手
    public function doDeletePlayer() {
        $pid = intval($_POST['pid']);
        if (!$pid) {
            $this->error('选手不存在');
        }
        $data['id'] = $pid;
        $player = M('event_player')->where('id=' . $pid)->field('uid,isRecomm,recommPid')->find();
        if(!$player){
            $this->error('选手不存在');
        }
        $uid = $player['uid'];
        $delFile = $player['recommPid'] ? false : true;
        $res = D('EventPlayer')->doDelPlayer($data,$delFile);
        if ($res) {
            if(!$player['isRecomm'] && $player['recommPid']){
                M('event_player')->setField('isRecomm',0,'id=' . $player['recommPid']);
            }
            if ($uid) {
                // 发送通知
                $title = D('Event')->getField('title', 'id=' . $_POST['id']);
                $notify_dao = service('Notify');
                $link = U('event/Front/index', array('id' => $_POST['id']));
                $url = '<a href="' . $link . '">' . $title . '</a>';
                $notify_data['title'] = $url;
                $reason = '管理员删除';
                if(!empty($_POST['reject'])){
                    $reason = t($_POST['reject']);
                }
                $notify_data['reason'] = $reason;
                $notify_dao->sendIn($uid, 'event_delplayer', $notify_data);
            }
            $this->success('操作成功');
        }
        $this->error('操作失败');
    }

    /**
     * newslist
     * 活动新闻列表
     * @access public
     * @return void
     */
    public function newsList() {
        if (!empty($_POST)) {
            $_SESSION['backend_event_news_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['backend_event_news_search']);
        } else {
            unset($_SESSION['backend_event_news_search']);
        }
        $_POST['newsTitle'] = t(trim($_POST['newsTitle']));
        $_POST['newsTitle'] && $map['title'] = array('like', "%" . $_POST['newsTitle'] . "%");
        $this->assign($_POST);
        $map['eventId'] = $this->eventId;
        $list = D('EventNews')->where($map)->order('`id` DESC')->findPage(10);
        $this->assign('list', $list);
        $this->setTitle('新闻列表');
        $this->display();
    }

    public function newsEdit() {
        $this->assign('type', 'add');
        if ($nid = (int) $_REQUEST['nid']) {
            $this->assign('type', 'edit');
            $map['eventId'] = $this->eventId;
            $map['id'] = $nid;
            $news = D('EventNews')->where($map)->find();
            if (!$news) {
                echo("无法找到对象!");
                return;
            }
            $this->assign("news", $news);
        }
        $this->display();
    }

    public function doNewsEdit() {
        $title = t($_POST['title']);
        if (empty($title)) {
            $this->error('新闻名称不能为空！');
        }
        if (mb_strlen($title, 'UTF8') > 60) {
            $this->error('新闻名称最大60个字！');
        }
        $nid = intval($_REQUEST['nid']);
        if (empty($nid)) {
            $this->insertNews();
        } else {
            $this->updateNews($nid);
        }
    }

    public function insertNews() {
        $title = t($_POST['title']);
        $map['title'] = $title;
        $map['eventId'] = $this->eventId;
        $map['content'] = t(h($_POST['content']));
        $map['cTime'] = time();
        $map['uTime'] = time();

        if (D('EventNews')->add($map)) {
            //保存成功则刷新页面
            $this->assign('jumpUrl', U('/Author/newsList', array('id' => $this->eventId)));
            $this->success('添加成功！');
        } else {
            //失败提示
            $this->error('添加失败！');
        }
    }

    public function updateNews($id) {
        $title = t($_POST['title']);
        $map['title'] = $title;
        $map['content'] = t(h($_POST['content']));
        $map['uTime'] = time();
        if (D('EventNews')->where("id={$id}")->save($map)) {
            $this->assign('jumpUrl', U('/Author/newsList', array('id' => $this->eventId)));
            $this->success('修改成功！');
        } else {
            //失败提示
            $this->error('修改失败！');
        }
    }

    /**
     * newsDelete
     * 删除新闻
     * @access public
     * @return int
     */
    public function deleteNews() {
        $ids = explode(',', t($_POST ['nid']));
        $map['id'] = array('in', $ids);
        if ($result = D('EventNews')->doDelete($map)) {
            $this->success('删除成功！');
        } else {
            $this->error('删除失败！');
        }
    }

    public function flash() {
        if (!empty($_POST)) {
            $_SESSION['backend_event_flash_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['backend_event_flash_search']);
        } else {
            unset($_SESSION['backend_event_flash_search']);
        }
        $_POST['flashTitle'] = t(trim($_POST['flashTitle']));
        $_POST['flashTitle'] && $map['title'] = array('like', "%" . $_POST['flashTitle'] . "%");
        $this->assign($_POST);
        $map['eventId'] = $this->eventId;
        $map['show'] = 1;
        $list = D('EventFlash')->where($map)->order('`id` DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }

    public function addFlash() {
        if (isset($_REQUEST['uid'])) {
            $this->assign('memberId', intval($_REQUEST['uid']));
        }
        $this->display();
    }

    public function doAddFlash() {
        $link = t($_POST['link']);
        $parseLink = parse_url($link);
        if (preg_match("/(youku.com|ku6.com|sina.com.cn|yixia.com|t.cn)$/i", $parseLink['host'], $hosts)) {
            $link = getShortUrl($link);
            $addonsData = array();
            Addons::hook("weibo_type", array("typeId" => 3, "typeData" => $link, "result" => &$addonsData));
            $addonsData = unserialize($addonsData['type_data']);
            //var_dump($addonsData);die;
            $addonsData['title'] = preg_replace(array('/—在线播放(.*)/', '/ 在线观看(.*)/'), '', $addonsData['title']);
            $data['title'] = $addonsData['title'];
            $data['path'] = $addonsData['flashimg'] ? $addonsData['flashimg'] : '';
            $data['flashvar'] = $addonsData['flashvar'];
            $data['host'] = $addonsData['host'];
            $data['link'] = $link;
            $data['eventId'] = $this->eventId;
            $jumpUrl = U('/Author/flash', array('id' => $this->eventId));
            if (isset($_POST['uid'])) {
                $data['uid'] = intval($_POST['uid']);
                $jumpUrl = U('/Author/playerFlash', array('id' => $this->eventId, 'uid' => $data['uid']));
            }
            $data['cTime'] = time();
            if (D('EventFlash')->add($data)) {
                //成功提示
                $this->assign('jumpUrl', $jumpUrl);
                $this->success('添加成功！');
            } else {
                //失败提示
                $this->error('添加失败！');
            }
        } else {
            $this->error(L('only_support_video'));
        }
    }

    public function deleteFlash() {
        $ids = explode(',', t($_POST ['nid']));
        $map['id'] = array('in', $ids);
        if ($result = D('EventFlash')->doDelete($map)) {
            $this->success('删除成功！');
        } else {
            $this->error('删除失败！');
        }
    }

    public function photo() {
        if (!empty($_POST)) {
            $_SESSION['backend_event_photo_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['backend_event_photo_search']);
        } else {
            unset($_SESSION['backend_event_photo_search']);
        }
        $_POST['photoTitle'] = t(trim($_POST['photoTitle']));
        $_POST['photoTitle'] && $map['title'] = array('like', "%" . $_POST['photoTitle'] . "%");
        $this->assign($_POST);
        $map['eventId'] = $this->eventId;
        $map['uid'] = 0;

        $list = D('EventImg')->where($map)->order('`id` DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }

    public function editPhoto() {
        $this->assign('type', 'add');
        if ($id = (int) $_REQUEST['gid']) {
            $this->assign('type', 'edit');
            $photo = D('EventImg')->find($id);
            if (!$photo) {
                echo("无法找到对象!");
                return;
            }
            $this->assign("photo", $photo);
        }
        $config = getPhotoConfig();
        $this->assign($config);
        $this->display();
    }

    public function editPlayerImg() {
        $map['id'] = intval($_REQUEST['uid']);
        $user = D('EventPlayer')->where($map)->find();
        if (!$user) {
            $this->error('成员不存在');
        }
        $this->assign('member', $user);
        $this->assign('type', 'add');
        if ($id = (int) $_REQUEST['gid']) {
            $this->assign('type', 'edit');
            $photo = D('EventImg')->find($id);
            if (!$photo) {
                echo("无法找到对象!");
                return;
            }
            $this->assign("photo", $photo);
        }
        $config = getPhotoConfig();
        $this->assign($config);
        $this->display();
    }

//    public function editMemberImg() {
//        $map['id'] = intval($_REQUEST['uid']);
//        $map['eventId'] = $this->eventId;
//        $user = D('EventUser')->where($map)->find();
//        if(!$user){
//            $this->error('成员不存在');
//        }
//        $this->assign('member', $user);
//        $this->assign('type', 'add');
//        if ($id = (int) $_REQUEST['gid']) {
//            $this->assign('type', 'edit');
//            $photo = D('EventImg')->find($id);
//            if (!$photo) {
//                echo("无法找到对象!");
//                return;
//            }
//            $this->assign("photo", $photo);
//        }
//        $this->display();
//    }

    public function doEditPhoto() {
        $title = t($_POST['title']);
        if (mb_strlen($title, 'UTF8') > 60) {
            $this->error('标题最大60个字！');
        }
        $id = intval($_REQUEST['gid']);
        if (empty($id)) {
            $this->insertPhoto();
        } else {
            $this->updatePhoto($id);
        }
    }

    /**
     * 活动添加修改成员的信息
     */
    private function _getPhotoData($insert = true) {
        $info = eventUpload();
        if ($info['status']) {
            $data['path'] = $info['info'][0]['savepath'].$info['info'][0]['savename'];
        } elseif ($insert) {
            $this->error("上传出错! " . $info['info']);
        } elseif ($info['info'] != '没有选择上传文件') {
            $this->error($info['info']);
        }
        $data['eventId'] = $this->eventId;
        if (isset($_POST['uid'])) {
            $data['uid'] = intval($_POST['uid']);
        }
        $data['upUid'] = $this->mid;
        $data['title'] = t($_POST['title']);
        $data['cTime'] = time();
        return $data;
    }

    public function insertPhoto() {
        $data = $this->_getPhotoData();
        if ($res = D('EventImg')->add($data)) {
            if (isset($data['uid'])) {
                $this->assign('jumpUrl', U('/Author/playerImg', array('id' => $this->eventId, 'uid' => $data['uid'])));
            } else {
                $this->assign('jumpUrl', U('/Author/photo', array('id' => $this->eventId)));
            }
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    public function updatePhoto($id) {
        $map['id'] = $id;
        $map['eventId'] = $this->eventId;
        $dao = D('EventImg');
        $img = $dao->where($map)->find();
        if (!$img) {
            $this->error('非法参数，图片不存在！');
        }
        $data = $this->_getPhotoData(false);
        if ($dao->where("id={$id}")->save($data)) {
            if (isset($data['uid'])) {
                $this->assign('jumpUrl', U('/Author/playerImg', array('id' => $this->eventId, 'uid' => $data['uid'])));
            } else {
                $this->assign('jumpUrl', U('/Author/photo', array('id' => $this->eventId)));
            }
            $this->success('修改成功！');
        } else {
            //失败提示
            $this->error('修改失败！');
        }
    }

    public function deletePhoto() {
        $ids = explode(',', t($_POST ['nid']));
        $map['id'] = array('in', $ids);
        if (D('EventImg')->doDelete($map)) {
            $this->success('删除成功！');
        } else {
            $this->error('删除失败！');
        }
    }

    public function multiPhoto() {
        $config = getPhotoConfig();
        $this->assign($config);
        $this->display();
    }

    public function playerMultiPhoto() {
        $map['id'] = intval($_REQUEST['uid']);
        $map['eventId'] = $this->eventId;
        $user = D('EventPlayer')->where($map)->find();
        if (!$user) {
            $this->error('成员不存在');
        }
        $this->assign('member', $user);
        $config = getPhotoConfig();
        $this->assign($config);
        $this->display();
    }

    //执行单张图片上传
    public function upload_single_pic() {
        $info = eventUpload();
        if ($info['status']) {
            //保存图片信息
            $data['path'] = $info['info'][0]['savepath'].$info['info'][0]['savename'];
            $data['eventId'] = $this->eventId;
            if (isset($_REQUEST['uid'])) {
                $data['uid'] = intval($_REQUEST['uid']);
            }
            $data['title'] = t($_REQUEST['title']);
            if(!$data['title']){
                $data['title'] = '';
            }
            $data['cTime'] = time();
            if ($res = D('EventImg')->add($data)) {
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            }
            //启用session记录flash上传的图片数，也可以防止意外提交。
            //$upload_count	=	intval($_SESSION['upload_count']);
            //$_SESSION['upload_count']	=	$upload_count + 1;
        } else {
            //上传出错
            echo "0";
        }
    }

    //上传后执行编辑操作
    public function mutiEditPhotos() {
        $upnum = intval($_REQUEST['upnum']);
        if ($upnum == 0)
            $this->error('请至少上传一张图片！');
        $jumpUrl = U('/Author/photo', array('id' => $this->eventId));
        if (isset($_POST['uid'])) {
            $jumpUrl = U('/Author/playerImg', array('id' => $this->eventId, 'uid' => intval($_POST['uid'])));
        }
        $this->assign('jumpUrl', $jumpUrl);
        $this->success('添加成功');
    }

    public function orga() {
        $this->assign('orga', D('EventOrga')->getEventOrga($this->eventId));
        $this->display();
    }

    public function editOrga() {
        $this->assign('orga', D('EventOrga')->getEventOrga($this->eventId));
        $this->display();
    }

    public function doEditOrga() {
        $title = t($_POST['title']);
        if (empty($title)) {
            $this->error('标题不能为空！');
        }
        if (mb_strlen($_POST['title'], 'UTF8') > 10) {
            $this->error('标题最大10个字！');
        }
        $map['title'] = $title;
        $map['content'] = t(h($_POST['content']));
        $map['cTime'] = time();
        $dao = D('EventOrga');
        $has = $dao->getField('eventId',"eventId={$this->eventId}");
        if($has){
            $res = $dao->where("eventId={$this->eventId}")->save($map);
        }else{
            $map['eventId'] = $this->eventId;
            $res = $dao->where("eventId={$this->eventId}")->add($map);
        }
        if ($res) {
            $this->assign('jumpUrl', U('/Author/orga', array('id' => $this->eventId)));
            $this->success('修改成功！');
        } else {
            $this->error('修改失败！');
        }
    }

    public function finish() {
        $template = 'editFinish';
        if ($this->obj['school_audit'] == 3 || $this->obj['school_audit'] == 5) {
            $template = 'finish';
        }
        $this->display($template);
    }

    public function editFinish() {
        if($this->obj['endattach']){
            $this->assign('attach',getAttach($this->obj['endattach']));
        }
        $this->display();
    }

    //申请完结活动
    public function doFinish() {
        if ($this->obj['school_audit'] == 5) {
            $this->error('已审核完毕，不可重复提交！');
        }
        if (!$this->obj['canFinish']) {
            $this->error('活动尚未结束，不可提交完结！');
        }
        //得到上传的图片
        if (isset($_POST['imgs1'][0]) && $_POST['imgs1'][0]>0){
            $data['print_img'] = $_POST['imgs1'][0];
        }elseif(!$this->obj['print_img']) {
            $this->error('请上传图片');
        }
        if(isset($_POST['file1'][0]) && $_POST['file1'][0]>0){
            $data['endattach'] = intval($_POST['file1'][0]);
        }

        $data['print_text'] = t($_POST['print_text'], true);
        if (empty($data['print_text'])) {
            $this->error('请填写总结');
        }
        $data['school_audit'] = 3;
        $data['fTime'] = time();
        $data['pay'] = $_POST['pay'];
        if (M('event')->where('id=' . $this->eventId)->save($data)) {
            //删除旧的，激活新的
            $reliveid = array();
            if(isset($data['print_img'])){
                $reliveid[] = $_POST['imgid1'][0];
                if($this->obj['print_img']){
                    tsDelFile($this->obj['print_img']);
                }
            }
            if(isset($data['endattach'])){
                $reliveid[] = $data['endattach'];
                if($this->obj['endattach']){
                    model('Attach')->deleteAttach($this->obj['endattach'], true);
                }
            }
            if(!empty($reliveid)){
                model('Attach')->reliveAttach($reliveid);
            }
            $this->assign('jumpUrl', U('/Author/finish', array('id' => $this->eventId)));
            $this->success('操作成功，请等待审核');
        }
        $this->error('操作失败');
        die;
    }

    public function qrcode() {
        //  $this->assign('qrcode', $this->event->getQrCode($this->eventId));  //第一种签到方式
        $this->assign('adminCode', $this->event->getAdminCode($this->eventId));
        $this->display();
    }

    public function excel() {
        $id = intval($_REQUEST['id']);
        $list = M('event_user')
                ->where('eventId=' . $id)
                ->field('uid,realname,status,sex,tel,addCredit,remark,credit,score,cTime')
                ->order('id')
                ->findAll();
        $daouser = M('user');
        $content = array();
        $classUser = M('user_a');
        foreach ($list as $k => $v) {
            $user = $daouser->where('uid =' . $v['uid'])->field('email,year,major')->find();
            $userClass = $classUser->where('uid =' . $v['uid'])->getField('class');
            $content[$k]['id'] = $k + 1;
            $content[$k]['number'] = "'".getUserEmailNum($user['email']);
            $content[$k]['realname'] = $v['realname'];
            $content[$k]['status'] = $v['status'] == 2 ? 已签到 : 未签到;
            $content[$k]['credit'] = $v['credit'];
            $content[$k]['addCredit'] = $v['addCredit'];
            $content[$k]['remark'] = $v['remark'];
            $content[$k]['score'] = $v['score'];
            $content[$k]['sex'] = $v['sex'] ? 男 : 女;
            $content[$k]['school'] = tsGetSchoolByUid($v['uid']);
            $content[$k]['year'] = $user['year'];
            $content[$k]['major'] = $user['major'];
            $content[$k]['class'] = empty($userClass) ? '' : $userClass;
            $content[$k]['tel'] = $v['tel'];
            $content[$k]['cTime'] = date('Y-m-d H:i', $v['cTime']);
        }
        closeDb();
        $arr = array('序号','学号', '姓名', '签到','学分','附加学分','备注','活动积分', '性别', '学校', '年级', '专业', '班级', '电话','报名时间');
        array_unshift($content, $arr);
        service('Excel')->export2($content, $this->obj['title']);
    }


    public function playerExcel() {
        $id = intval($_REQUEST['id']);
        $list = M('event_player')
                ->where('status=1 AND eventId=' . $id)
                ->field('id,uid,realname,ticket,school')
                ->findAll();
        $daouser = M('user');
        $content = array();
        foreach ($list as $k => $v) {
            $user = $daouser->where('uid =' . $v['uid'])->field('email,sex')->find();
            $content[$k]['id'] = $k + 1;
            $content[$k]['ID'] = $v['id'];
            if ($v['uid']) {
                $content[$k]['number'] = getUserEmailNum($user['email']);
                $content[$k]['school'] = tsGetSchoolByUid($v['uid']);
            } else {

                $content[$k]['number'] = '添加选手';
                $content[$k]['school'] = $v['school'];
            }
            $content[$k]['realname'] = $v['realname'];
            $content[$k]['sex'] = $user['sex'] ? 男 : 女;
            $content[$k]['ticket'] = $v['ticket'];
        }
        closeDb();
        $arr = array('序号','ID', '学号','学校', '姓名', '性别', '投票数');
        array_unshift($content, $arr);
        service('Excel')->export2($content, $this->obj['title']);
    }

    public function userAttend() {
        $mid = $_REQUEST['mid'];
        $id = $_REQUEST['id'];
        if (!$this->rights['can_admin']) {
            $this->error('你没有权限');
        }
        $res = $this->event->adminUserAttend($mid, $id);
        if ($res) {
            $this->success('签到成功');
        } else {
            $this->error($this->event->getError());
        }
    }

    //修改最大投票数
    public function editMaxVote() {
        $vote = intval($_POST['val']);
        if($vote<1||$vote>100){
            $this->error('输入1-100的数字');
        }
        $res = $this->event->setField('maxVote',$vote,'id='.$this->eventId);
        if ($res) {
            $this->success('修改成功！');
        } else {
            $this->error('修改失败');
        }
    }

        //附件下载
   public function download(){
		$fid = intval($_REQUEST['fid']) > 0 ?  intval($_REQUEST['fid']) : 0;
		if($fid == 0) exit();
		//下载函数
		//import("ORG.Net.Http");             //调用下载类

		$file_info = getAttach($fid);
		$file_path = UPLOAD_PATH . '/' .$file_info['savepath'].'/' .$file_info['savename'];
		if (file_exists($file_path)) {
			include_once(SITE_PATH . '/addons/libs/Http.class.php');
			$file_info['name'] = iconv("utf-8", 'gb2312', $file_info['name']);
			Http::download($file_path, $file_info['name']);
		}
             $this->error('文件不存在');
   }


    //添加成员备注
    public function addRemark(){
        if ($this->obj['school_audit'] ==5 && !($this->rights['can_admin'] || $this->user['can_event2'])) {
            $this->error('该活动已完结，不可更改');
        }
        $remark =  t($_POST['val']);
        if(get_str_length($remark)>6){
             $this->error('字符长度不得超过6个字');
        }
       $map['eventId'] =  $this->eventId;
       $map['uid'] =  intval($_POST['uid']);

       $res = M('event_user')->where($map)->setField('remark',$remark);
       if($res){
            $this->success('备注成功');
       }else{
            $this->error('备注失败');
       }
    }

    //活动附加学分
    public function addCredit() {
        if ($this->obj['school_audit'] < 3) {
            $this->error('该活动未申请完结,无法加分');
        }
        if ($this->obj['school_audit'] ==5) {
            $this->error('该活动已完结，不可更改');
        }
        if (!$this->canJf()) {
            $this->error('您无权限操作');
        }
        $num = t($_POST['credit'])*100/100;
        if($num<0){
            $this->error('请输入大于等于0的数字');
        }
        $webconfig = $this->get('webconfig');
        if($num>$webconfig['max_credit']){
            $this->error('学分最大'.$webconfig['max_credit']);
        }
        $userId = intval($_POST['userId']);
        $daoUser = M('event_user');
        $eventUser = $daoUser->where("id=" . $userId)->field('remark,status')->find();
        if(!$eventUser['remark']){
            $this->error('成员无备注，不可加分');
        }
        if($eventUser['status']!=2){
            $this->error('成员尚未签到，不可加分');
        }
        $res = $daoUser->where("id=" . $userId)->setField('addCredit', $num);
        if ($res) {
//            $uid = $eventUser['uid'];
//            $credit = $num - $eventUser['addCredit'];
//            //更新用户表
//            M('user')->setInc('school_event_credit', "uid=" . $uid, $credit);
//            //更新排行榜
//            if($credit>0){
//                $this->event->upEday($uid, $eventUser['usid'], $credit);
//            }else{
//                $plusCredit = 0-$credit;
//                $eday = M('tj_eday')->where('uid='.$uid.' and credit>='.$plusCredit)->order('id DESC')->find();
//                if($eday){
//                    $data['id'] = $eday['id'];
//                    $data['credit'] = $eday['credit']+$credit;
//                    M('tj_eday')->save($data);
//                }
//            }
//            //更新缓存
//            $userLoginInfo = S('S_userInfo_'.$uid);
//            if(!empty($userLoginInfo)) {
//                $userLoginInfo['school_event_credit'] = $userLoginInfo['school_event_credit']+$credit;
//                S('S_userInfo_'.$uid, $userLoginInfo);
//                if($_SESSION['userInfo']['uid'] == $uid){
//                    $_SESSION['userInfo'] = $userLoginInfo;
//                }
//            }
            $this->success('编辑成功,活动完结后发放');
        } else {
            $this->error('编辑失败');
        }
    }

        public function addScore() {
        if ($this->obj['school_audit'] < 3) {
            $this->error('该活动未申请完结,无法加分');
        }
        if ($this->obj['school_audit'] == 5) {
            $this->error('该活动已完结，不可更改');
        }
        if (!$this->canJf()) {
            $this->error('您无权限操作');
        }
        $num = intval($_POST['credit']);
        if ($num < 0) {
            $this->error('请输入大于等于0的数字');
        }
        $webconfig = $this->get('webconfig');
        if($num>$webconfig['max_score']){
            $this->error('积分最大'.$webconfig['max_score']);
        }
        $daoUser = M('event_user');
        $userId = intval($_POST['userId']);
        $eventUser = $daoUser->where("id=" . $userId)->field('remark,status,addScore,uid')->find();
        if(!$eventUser['remark']){
            $this->error('成员无备注，不可加分');
        }
        if($eventUser['status']!=2){
            $this->error('成员尚未签到，不可加分');
        }
        $res = $daoUser->where("id=" . $userId)->setField('addScore', $num);
        if ($res) {
            $this->success('编辑成功,活动完结后发放');
        } else {
            $this->error('编辑失败');
        }
    }

    //差异给分权限
    public function canJf(){
        //超管和初审人
        if ($this->rights['can_admin'] || ($this->mid == $this->obj['audit_uid'])) {
            return true;
        }
        //终审
        if ($this->user['can_event2']) {
            //校领导
            if($this->user['event_level']==10){
                return true;
            //操作人员院=活动所在院
            }elseif($this->user['sid1']==$this->obj['sid']){
                return true;
            }
        }
        return false;
    }

    public function doChangeUpload() {
        $map['id'] = $this->eventId;
        $act = $_REQUEST['type'];  //动作
        if ($this->event->doPlayerUpload($map, $act)) {
            $this->success('操作成功');
        }
        $this->error('操作失败');
    }

    public function doAllowPlayer() {
        $uids = explode(',', $_POST['pid']);
        $data['id'] = array('in',$uids);
        // $data['eventId'] = $this->eventId;     用于省团委审核
        if (D('EventPlayer')->doAllowPlayer($data)) {
            $this->success('审核通过');
        }
        $this->error('审核失败');
    }

    public function doAuditReason() {
        $pid = intval($_GET['pid']);
        $id = intval($_GET['id']);
        $this->assign('id', $id);
        $this->assign('pid', $pid);
        $this->display();
    }
    public function ticketConfig() {
        $voteYear = M('EventVoteyear')->where("eid=$this->eventId")->field('allowYear,allowSid1')->find();
        $this->assign($voteYear);
        $sidArr = array();
        if($voteYear['allowSid1']){
            $sidArr = explode(',', $voteYear['allowSid1']);
        }
        $this->assign('sidArr',$sidArr);
        $this->display();
    }

    public function doTicketConfig(){
        $data['isTicket'] = $_REQUEST['isTicket']?1:0;
        $data['repeated_vote'] = $_REQUEST['repeated_vote']?1:0;
        $data['repeatTicket'] = $_REQUEST['repeatTicket']?1:0;     //是否支持每日投票
        $data['maxVote'] = intval($_REQUEST['maxVote']);
        $data['allTicket'] = $_REQUEST['allTicket']?1:0;
        $data['player_sorted'] = intval($_REQUEST['player_sorted']);
        $data['is_check_in'] = $_REQUEST['is_check_in']?1:0;
        if($data['maxVote']<=0){
            $data['maxVote'] = 1;
        }elseif($data['maxVote']>100){
            $data['maxVote'] = 100;
        }
        M('event')->where('id=' . $this->eventId)->save($data);
        $_REQUEST['sid'] = $this->sid;
        D('EventVoteyear')->doAdd($this->eventId,$_REQUEST);
            $this->success('操作成功');

    }

    public function showPlayer(){
        $id = intval($_REQUEST['pid']);
        $map['id'] = $id;
        $map['eventId'] = $this->eventId;
        $app = D('EventPlayer')->where($map)->find();
        if (!$app) {
            $this->error('选手不存在或已被删除！');
        }
        if($app['paramValue']){
            $app['paramValue'] = unserialize($app['paramValue']);
        }else{
            $app['paramValue'] = array();
        }
        $this->assign('app', $app);
        $flash = D('EventFlash')->where(array('uid' => $id))->order('id DESC')->findAll();
        $this->assign('flash', $flash);
        $img = D('EventImg')->where(array('uid' => $id))->order('id ASC')->findAll();
        $this->assign('img', $img);
        $param = D('EventParameter')->getParam($this->eventId);
        $this->assign($param);
        $this->display();
    }

    public function upConfig(){
        $param = D('EventParameter')->getParam($this->eventId);
        $this->assign($param);
        $this->assign('paramCount',count($param['parameter']));
        $this->display();
    }

    public function doUpConfig(){
        $_POST['eventId'] = $this->eventId;
        $dao = D('EventParameter');
        $res = $dao->editParam($_POST);
        if($res){
            $this->success('保存成功');
        }
        $this->error($dao->getError());
    }

    public function recommPlayer(){
        $pid = intval($_POST['pid']);
        if(!$pid){
            $this->error('选手不存在');
        }
        $provId = M('event_recomm')->getField('provId', 'eventId='.$this->eventId);
        if(!$provId){
            $this->error('上级活动不存在');
        }
        $provEvent = M('event')->where('id='.$provId)->field('title,deadline,player_upload')->find();
        if(!$provEvent){
            $this->error('上级活动已删除');
        }
        if($provEvent['deadline']<time()){
            $this->error('上级活动报名时间已结束');
        }
//        if(!$provEvent['player_upload']){
//            $this->error('上级活动已关闭上传资料');
//        }
        $player = M('event_player')->where('id='.$pid)
                ->field('uid,sid,path,realname,school,content,paramValue,isRecomm')->find();
        if(!$player){
            $this->error('选手不存在');
        }
        if($player['isRecomm']){
            $this->error('选手已提交，不可重复提交');
        }
        //上传选手
        $player['eventId'] = $provId;
        $player['cTime'] = time();
        $player['status'] = 0;
        $player['recommPid'] = $pid;
        $newId = M('event_player')->add($player);
        if(!$newId){
            $this->error('操作失败');
        }
        //上传图片
        $imgs = M('event_img')->where('uid='.$pid)->field('path,title')->findAll();
        foreach ($imgs as $v) {
            $v['eventId'] = $provId;
            $v['uid'] = $newId;
            $v['cTime'] = time();
            M('event_img')->add($v);
        }
        //上传flash
        $imgs = M('event_flash')->where('uid='.$pid)->field('path,link,title,flashvar,host')->findAll();
        foreach ($imgs as $v) {
            $v['eventId'] = $provId;
            $v['uid'] = $newId;
            $v['cTime'] = time();
            $v['show'] = 0;
            M('event_flash')->add($v);
        }
        $data = array('isRecomm'=>1,'recommPid'=>$newId);
        M('event_player')->where('id='.$pid)->save($data);
        $this->success('操作成功');
    }

    public function batchAttend() {
        if (!$this->rights['can_admin']) {
            $this->error('你没有权限');
        }
        $uidarr = explode(',', $_POST['mids']);
        if(empty($uidarr)){
            $this->error('成员错误');
        }
        $map['id'] = array('in',$uidarr);
        $map['eventId'] = $this->eventId;
        $map['status'] = 1;
        $data['status'] = 2;
        $res = M('event_user')->where($map)->save($data);
        if($res){
            $this->success('签到成功');
        }
        $this->error('操作失败');
    }

    public function allAttend() {
        if (!$this->rights['can_admin']) {
            $this->error('你没有权限');
        }
        $map['eventId'] = $this->eventId;
        $map['status'] = 1;
        $data['status'] = 2;
        $res = M('event_user')->where($map)->save($data);
        if($res){
            $gid = M('event')->getField('gid', 'id='.$this->eventId);
            $this->success($res.'人签到成功');
        }
        $this->error('已全部签到');
    }

    //推荐到上级活动

    public function recommends(){
        $result['status'] = 0 ;
        $result['info'] = '添加失败' ;
        $playId = intval($_REQUEST['playid']) ;
        $eventId = intval($_REQUEST['id']) ;
        $data['isHot'] = 0 ;
        $data['stoped'] = 0 ;
        $data['status'] = 0 ;   
        $data['isRecomm'] = 1 ;
        $data['recommPid'] = $eventId ;
        $map['id'] = $playId ;
        $dat = M('event_player')->where($map)->save($data);
        if ($dat !==false) {
            $dat['player_id'] = $playId ;
            M('event_special_tickets')->add($dat) ;
            $result['status'] = 1 ;
            $result['info'] = '添加成功' ;
        }
        echo json_encode($result) ;
    }

    //排序
    public function dosort(){
        $map['id'] = intval($_REQUEST['id']) ;
        $data['sort'] = intval($_REQUEST['sort']) ;
        if ($map['id']>0) {
            if (M('event_player')->where($map)->save($data) !== false) {
                echo 1 ; die ;
            }
        }
        echo 0 ;
    }
}
